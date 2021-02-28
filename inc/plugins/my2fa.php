<?php

if (!defined('IN_MYBB'))
    exit('Denied.');

if (!defined('PLUGINLIBRARY'))
    define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

define('MY2FA_ROOT', MYBB_ROOT . 'inc/plugins/my2fa/');

$GLOBALS['my2faAutoload'] = [
    'My2FA\\Methods\\' => 'methods',
    'BaconQrCode\\' => 'vendor/bacon/bacon-qr-code/src',
    'PragmaRX\\Google2FA\\' => 'vendor/pragmarx/google2fa/src',
    'ParagonIE\\ConstantTime\\' => 'vendor/paragonie/constant_time_encoding/src',
    'DASPRiD\\Enum\\' => 'vendor/dasprid/enum/src'
];

spl_autoload_register(function ($className)
{
    global $my2faAutoload;

    foreach ($my2faAutoload as $namespace => $path)
    {
        if (strpos($className, $namespace) === 0)
        {
            $classNameUnprefixed = strtr($className, [$namespace => '', '\\' => '/']);
            require MY2FA_ROOT . $path . '/' . $classNameUnprefixed . '.php';

            break;
        }
    }
});

require MY2FA_ROOT . 'utils.php';
require MY2FA_ROOT . 'data.php';
require MY2FA_ROOT . 'core.php';
require MY2FA_ROOT . 'rendering.php';

if (!defined('IN_ADMINCP'))
{
    $plugins->add_hook('global_start', 'my2fa_global_start', -22);
    $plugins->add_hook('xmlhttp', 'my2fa_xmlhttp', -22);
    $plugins->add_hook('archive_start', 'my2fa_archive_start', -22);

    $plugins->add_hook('global_intermediate', 'my2fa_global_intermediate');
    $plugins->add_hook('datahandler_login_complete_end', 'my2fa_datahandler_login_complete_end');
    $plugins->add_hook('misc_start', 'my2fa_misc_start');
    $plugins->add_hook('usercp_menu_built', 'my2fa_usercp_menu_built');
    $plugins->add_hook('usercp_start', 'my2fa_usercp_start');

    $plugins->add_hook('build_friendly_wol_location_end', 'my2fa_build_wol_location');
}
else
{
    $plugins->add_hook('admin_load', 'my2fa_admin_load');

    $plugins->add_hook('admin_settings_print_peekers', 'my2fa_settings_peekers');
    //$plugins->add_hook('admin_config_settings_change', 'my2fa_settings_change');

    $plugins->add_hook('admin_tools_do_recount_rebuild', 'my2fa_admin_do_recount_rebuild');
    $plugins->add_hook('admin_tools_recount_rebuild_output_list', 'my2fa_admin_recount_rebuild_output');
}

$plugins->add_hook('task_logcleanup', 'my2fa_task_logcleanup');
$plugins->add_hook('task_usercleanup', 'my2fa_task_usercleanup');

function my2fa_info()
{
    return [
        'name'          => 'My2FA',
        'description'   => 'Two-factor authentication for added account security.',
        'website'       => 'https://github.com/demtor/MyBB-My2FA',
        'author'        => 'demtor',
        'authorsite'    => 'https://github.com/demtor',
        'version'       => '1.0-alpha',
        'compatibility' => '18*'
    ];
}

function my2fa_install()
{
    global $db;

    if (!file_exists(PLUGINLIBRARY))
    {
        flash_message('PluginLibrary missing.', 'error');
        admin_redirect('index.php?module=config-plugins');
    }

    if (!$db->field_exists('has_my2fa', 'users'))
        $db->add_column('users', 'has_my2fa', "tinyint(1) NOT NULL DEFAULT 0");

    if (!$db->field_exists('my2fa_storage', 'sessions'))
        $db->add_column('sessions', 'my2fa_storage', "TEXT");

    $db->write_query("
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."my2fa_user_methods` (
            `uid` int unsigned NOT NULL,
            `method_id` varchar(20) NOT NULL,
            `data` varchar(255) NOT NULL DEFAULT '',
            `activated_on` int unsigned NOT NULL,
            PRIMARY KEY (`uid`, `method_id`)
        ) ENGINE=InnoDB" . $db->build_create_table_collation()
    );

    $db->write_query("
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."my2fa_tokens` (
            `tid` varchar(32) NOT NULL,
            `uid` int unsigned NOT NULL,
            `generated_on` int unsigned NOT NULL DEFAULT 0,
            `expire_on` int unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY (`tid`),
            KEY `IX_uid` (`uid`)
        ) ENGINE=InnoDB" . $db->build_create_table_collation()
    );

    $db->write_query("
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."my2fa_logs` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `uid` int unsigned NOT NULL,
            `event` varchar(40) NOT NULL,
            `data` varchar(255) NOT NULL DEFAULT '',
            `inserted_on` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `IX_uei` (`uid`, `event`, `inserted_on`)
        ) ENGINE=InnoDB" . $db->build_create_table_collation()
    );
}

function my2fa_uninstall()
{
    global $PL, $db;
    $PL or require_once PLUGINLIBRARY;

    $PL->settings_delete('my2fa');
    $PL->stylesheet_delete('my2fa');
    $PL->templates_delete('my2fa');

    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

    find_replace_templatesets('usercp_nav_profile',
        '#' . preg_quote('<!-- my2faUsercpSetupNav -->') . '#i',
        ''
    );
    find_replace_templatesets('header',
        '#' . preg_quote('{$notifiedGroupNotice}') . '#i',
        ''
    );

    if ($db->field_exists('has_my2fa', 'users'))
        $db->drop_column('users', 'has_my2fa');

    if ($db->field_exists('my2fa_storage', 'sessions'))
        $db->drop_column('sessions', 'my2fa_storage');

    $db->drop_table('my2fa_user_methods');
    $db->drop_table('my2fa_tokens');
    $db->drop_table('my2fa_logs');
}

function my2fa_is_installed()
{
    global $db;

    return $db->table_exists('my2fa_user_methods');

    // temp
    return $db->fetch_field(
        $db->simple_select('settinggroups', '1 AS occurs', "name = 'my2fa'", ['limit' => 1]),
        'occurs'
    );
}

function my2fa_activate()
{
    global $PL, $mybb;
    $PL or require_once PLUGINLIBRARY;

    #todo: remember to insert everything in a lang file using PL makelang parameter
    $PL->settings('my2fa',
        'My2FA',
        'Manage settings for the two-factor authentication of users.',
        [
            'enable_device_trusting' => [
                'title'       => 'Enable Device Trusting',
                'description' => 'Allow users to trust their device (browser) during verificaton through a checkbox.',
                'optionscode' => 'yesno',
                'value'       => 1
            ],
            'device_trusting_duration_in_days' => [
                'title'       => 'Device Trusting Duration (days)',
                'description' => 'For how many days can the device be remembered?',
                'optionscode' => 'numeric',
                'value'       => 30
            ],
            'enable_acp_integration' => [
                'title'       => 'Enable ACP Integration',
                'description' => 'Integrate My2FA into the admin panel.',
                'optionscode' => 'yesno',
                'value'       => 1
            ],
            'disable_device_trusting_in_acp' => [
                'title'       => 'Disable Device Trusting in ACP',
                'description' => 'Disable device trusting for the admin panel, if enabled.',
                'optionscode' => 'yesno',
                'value'       => 1
            ],
            'max_verification_attempts' => [
                'title'       => 'Maximum Verification Attempts',
                'description' => 'Max number of incorrect attempts before the user is blocked for <strong>5 minutes</strong> during 2FA verification.',
                'optionscode' => 'text',
                'value'       => 5
            ],
            'forced_groups' => [
                'title'       => 'Forced Groups',
                'description' => 'Select which user groups are forced to have 2FA activated.',
                'optionscode' => 'groupselect',
                'value'       => ''
            ],
            'notified_groups' => [
                'title'       => 'Notified Groups',
                'description' => 'Select which user groups will be continually notified with a global notice to activate 2FA.',
                'optionscode' => 'groupselect',
                'value'       => ''
            ],
            'totp_board_name' => [
                'title'       => 'TOTP: QR Code, Board Name',
                'description' => 'Insert the board name that will be viewed in your user authenticator app.',
                'optionscode' => 'text',
                //'value'       => preg_replace('/\s+/', '-', $mybb->settings['bbname'])
                'value'       => $mybb->settings['bbname']
            ],
            'totp_qr_code_renderer' => [
                'title'       => 'TOTP: QR Code Renderer',
                'description' => 'SvgImageBackEnd (suggested) renders SVG files using XMLWriter (libxml); ImagickImageBackEnd renders raster images using the Imagick library.',
                'optionscode' => My2FA\getMultiOptionscode('radio', [
                    'svg_image_back_end' => 'SvgImageBackEnd',
                    'imagick_image_back_end' => 'ImagickImageBackEnd',
                    'web_api' => 'Web API'
                ]),
                'value' => 'svg_image_back_end' 
            ],
            'totp_qr_code_web_api' => [
                'title'       => 'TOTP: QR Code, Web API',
                'description' => 'If Web API is selected in the QR Code Renderer setting, use {1} to indicate the QR Code URL.',
                'optionscode' => 'text',
                'value'       => 'https://api.qrserver.com/v1/create-qr-code/?data={1}'
            ]
        ]
    );

    $PL->stylesheet('my2fa',
        file_get_contents(MY2FA_ROOT . 'stylesheet/main.css'),
        'misc.php?my2fa|usercp.php?my2fa'
    );

    $templatesDirIterator = new DirectoryIterator(MY2FA_ROOT . 'templates');

    $templates = [];
    foreach ($templatesDirIterator as $template)
    {
        if (!$template->isFile())
            continue;

        $pathName = $template->getPathname();
        $pathInfo = pathinfo($pathName);

        if ($pathInfo['extension'] === 'tpl')
            $templates[$pathInfo['filename']] = file_get_contents($pathName);
    }

    if ($templates)
        $PL->templates('my2fa', 'My2FA', $templates);

    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

    find_replace_templatesets('usercp_nav_profile',
        '#' . preg_quote('{$changenameop}') . '#i',
        '{$changenameop}<!-- my2faUsercpSetupNav -->'
    );
    find_replace_templatesets('header',
        '#' . preg_quote('{$pm_notice}') . '#i',
        '{$pm_notice}{$notifiedGroupNotice}'
    );
}

function my2fa_deactivate()
{
    global $PL;
    $PL or require_once PLUGINLIBRARY;

    $PL->stylesheet_deactivate('my2fa');
}

function my2fa_settings_peekers(&$peekers)
{
    $myPeekers = [
        'new Peeker($(".setting_my2fa_enable_device_trusting"), $("
            #row_setting_my2fa_device_trusting_duration_in_days
        "), 1, true)',
        'new Peeker($(".setting_my2fa_enable_acp_integration"), $("
            #row_setting_my2fa_disable_device_trusting_in_acp
        "), 1, true)',
        'new Peeker($(".setting_my2fa_totp_qr_code_renderer"), $("
            #row_setting_my2fa_totp_qr_code_web_api
        "), "web_api", true)'
    ];

    $myPeekers = preg_replace('/(?<!new)\s+/', '', $myPeekers);
    array_push($peekers, ...$myPeekers);
}

// dead function
function my2fa_settings_change()
{
    global $mybb;

    $totpBoardNameSetting = &$mybb->input['upsetting']['my2fa_totp_board_name'] ?? null;

    if ($mybb->request_method === 'post' && $totpBoardNameSetting)
        $totpBoardNameSetting = preg_replace('/\s+/', '-', $totpBoardNameSetting);
}

/*
 * Hooks
 */

function my2fa_global_start()
{
    global $mybb, $session, $my2faUser;

    if (!$mybb->user['uid'])
        return;

    $my2faUser = $mybb->user;

    if (My2FA\isUserVerificationRequired($my2faUser['uid']))
    {
        #todo: maybe include possible ajax request
        if (!My2FA\hasUserBeenRedirected())
        {
            My2FA\updateSessionStorage($session->sid, ['is_redirected' => 1]);
            My2FA\redirectToVerification();
        }

        $session->load_guest();

        $mybb->user['ismoderator'] = False;
        $mybb->post_code = generate_post_check();
    }
    else if (
        My2FA\doesUserHave2faEnabled($my2faUser['uid']) &&
        !My2FA\isSessionTrusted()
    ) {
        My2FA\setSessionTrusted();
    }

    if (My2FA\isUserForcedToHave2faActivated($mybb->user['uid']))
        My2FA\redirectToSetup();
}

function my2fa_xmlhttp()
{
    global $mybb, $lang;

    if (!$mybb->user['uid'])
        return;

    if (
        My2FA\isUserVerificationRequired($mybb->user['uid']) ||
        My2FA\isUserForcedToHave2faActivated($mybb->user['uid'])
    ) {
        My2FA\loadLanguage();
        xmlhttp_error($lang->my2fa_xmlhttp_error);
    }
}

function my2fa_archive_start()
{
    global $mybb, $lang;

    if (!$mybb->user['uid'])
        return;

    if (
        My2FA\isUserVerificationRequired($mybb->user['uid']) ||
        My2FA\isUserForcedToHave2faActivated($mybb->user['uid'])
    ) {
        My2FA\loadLanguage();
        archive_error($lang->my2fa_archive_error);
    }
}

function my2fa_global_intermediate()
{
    global $mybb, $lang,
    $notifiedGroupNotice;

    $notifiedGroupNotice = null;
    if (My2FA\doesUserNeedsGlobalNotice($mybb->user['uid']))
    {
        My2FA\loadLanguage();
        eval('$notifiedGroupNotice = "' . My2FA\template('global_notice_notified_group') . '";');
    }
}

#todo: add password_confirmed_at? also in other inputs with password confirmation
function my2fa_datahandler_login_complete_end($userHandler)
{
    global $session;

    if (My2FA\isUserVerificationRequired($userHandler->login_data['uid']))
        My2FA\updateSessionStorage($session->sid, ['is_redirected' => 0]);
}

function my2fa_misc_start()
{
    global $mybb, $lang, $my2faUser,
    $headerinclude, $header, $footer, $theme;

    if (
        $my2faUser['uid'] &&
        $mybb->get_input('action') === 'my2fa' &&
        My2FA\isUserVerificationRequired($my2faUser['uid'])
    ) {
        My2FA\loadLanguage();

        $verificationContent = My2FA\getVerificationForm($my2faUser, 'misc.php?action=my2fa');

        eval('$miscVerification = "' . My2FA\template('misc_verification') . '";');
        exit(output_page($miscVerification));
    }
}

function my2fa_usercp_menu_built()
{
    global $mybb, $lang, $usercpnav;

    My2FA\loadLanguage();

    eval('$my2faUsercpSetupNav = "' . My2FA\template('usercp_setup_nav') . '";');
    $usercpnav = str_replace('<!-- my2faUsercpSetupNav -->', $my2faUsercpSetupNav, $usercpnav);
}

function my2fa_usercp_start()
{
    global $mybb, $lang,
    $headerinclude, $header, $footer, $theme, $usercpnav;

    if ($mybb->input['action'] === 'my2fa')
    {
        My2FA\loadLanguage();
        My2FA\passwordConfirmationCheck('usercp.php?action=my2fa', 20);

        $forcedGroupNotice = null;
        if (My2FA\isUserForcedToHave2faActivated($mybb->user['uid']))
            eval('$forcedGroupNotice = "' . My2FA\template('setup_notice_forced_group') . '";');

        $setupContent = My2FA\getSetupForm($mybb->user, 'usercp.php?action=my2fa');

        eval('$usercpSetup = "' . My2FA\template('usercp_setup') . '";');
        exit(output_page($usercpSetup));
    }
}

function my2fa_build_wol_location(&$wol)
{
    global $lang;

    if (strpos($wol['user_activity']['location'], 'usercp.php?action=my2fa') !== False)
    {
        My2FA\loadLanguage();

        $wol['user_activity']['activity'] = 'my2fa_usercp_setup';
        $wol['location_name'] = $lang->my2fa_usercp_setup_wol;
    }
    else if (strpos($wol['user_activity']['location'], 'misc.php?action=my2fa') !== False)
    {
        My2FA\loadLanguage();

        $wol['user_activity']['activity'] = 'my2fa_misc_verification';
        $wol['location_name'] = $lang->my2fa_misc_verification_wol;
    }
}

function my2fa_admin_load()
{
    global $mybb, $lang, $page;

    if (My2FA\isAdminVerificationRequired($mybb->user['uid']))
    {
        My2FA\loadUserLanguage();

        //$mybb->input['redirect_url'] ??= My2FA\getCurrentUrl(); // PHP 7.4
        $mybb->input['redirect_url'] = $mybb->input['redirect_url'] ?? My2FA\getCurrentUrl();

        $verificationContent = My2FA\getVerificationForm($mybb->user, 'index.php?action=my2fa', False, False);

        exit(My2FA\getAdminVerificationPage($verificationContent));
    }
    else if (
        My2FA\doesUserHave2faEnabled($mybb->user['uid']) &&
        !My2FA\isAdminSessionTrusted()
    ) {
        My2FA\setAdminSessionTrusted();
    }

    if (My2FA\isUserForcedToHave2faActivated($mybb->user['uid']))
    {
        My2FA\loadUserLanguage();

        $page->output_header($lang->access_denied);
        $page->output_error($lang->my2fa_admin_cp_error);
        $page->output_footer();
    }
}

function my2fa_task_logcleanup()
{
    global $db;

    // 1 hour old logs
    $db->delete_query('my2fa_logs', "inserted_on < " . (TIME_NOW - 60*60));
}

function my2fa_task_usercleanup()
{
    global $db;

    $db->delete_query('my2fa_tokens', "expire_on < " . TIME_NOW);
}

function my2fa_admin_recount_rebuild_output()
{
    global $lang, $form, $form_container;

    $form_container->output_cell("
        <label>Rebuild My2FA (users.has_my2fa)</label>
        <div class=\"description\">
            Update user has_my2fa to reflect the correct value. Use it whenever you enable or disable a My2FA method.
        </div>
    ");
	$form_container->output_cell($lang->na);
	$form_container->output_cell(
        $form->generate_submit_button($lang->go, ['name' => 'do_rebuild_has_my2fa_values'])
    );

	$form_container->construct_row();
}

function my2fa_admin_do_recount_rebuild()
{
    global $db, $mybb;

    if (!isset($mybb->input['do_rebuild_has_my2fa_values']))
        return;

    $methodIdsStr = implode("','", array_column(My2FA\selectMethods(), 'id'));

    if ($methodIdsStr)
    {
        /*
        $db->write_query("
            UPDATE ".TABLE_PREFIX."users u
            SET u.has_my2fa = 1
            WHERE
                u.has_my2fa = 0 AND
                EXISTS (
                    SELECT um.uid
                    FROM ".TABLE_PREFIX."my2fa_user_methods um
                    WHERE um.uid = u.uid AND um.method_id IN('{$methodIdsStr}')
                )
        ");

        $db->write_query("
            UPDATE ".TABLE_PREFIX."users u
            SET u.has_my2fa = 0
            WHERE
                u.has_my2fa = 1 AND
                NOT EXISTS (
                    SELECT um.uid
                    FROM ".TABLE_PREFIX."my2fa_user_methods um
                    WHERE um.uid = u.uid AND um.method_id IN('{$methodIdsStr}')
                )
        ");
        */

        $query = $db->simple_select(
            'my2fa_user_methods',
            'DISTINCT uid',
            "method_id IN ('{$methodIdsStr}')"
        );

        $validUserIds = [];
        while ($userMethod = $db->fetch_array($query))
        {
            $validUserIds[] = $userMethod['uid'];
        }

        $validUserIdsStr = implode(',', $validUserIds);

        $db->update_query(
            'users',
            ['has_my2fa' => 1],
            "has_my2fa = 0 AND uid IN ({$validUserIdsStr})"
        );
        $db->update_query(
            'users',
            ['has_my2fa' => 0],
            "has_my2fa = 1 AND uid NOT IN ({$validUserIdsStr})"
        );
    }

    log_admin_action('my2fa');

    flash_message('The user has_my2fa values have been rebuilt successfully.', 'success');
    admin_redirect('index.php?module=tools-recount_rebuild');
}
