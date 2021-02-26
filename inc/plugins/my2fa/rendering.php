<?php

namespace My2FA;

function getVerificationForm(array $user, string $verificationUrl, bool $includeBreadcrumb = True, bool $includeExtraRows = True): ?string
{
    global $mybb, $lang, $theme;

    $output = null;
    $mybb->input['method'] = $mybb->get_input('method');

    if (!isset($theme))
        $theme = getDefaultTheme();

    $methods = selectMethods();
    $userMethods = selectUserMethods($user['uid']);

    $redirectUrl = htmlspecialchars_uni($mybb->get_input('redirect_url'));
    $redirectUrlQueryStr = htmlspecialchars_uni(
        redirectUrlAsQueryString(urldecode($mybb->get_input('redirect_url')))
    );

    if (
        isset($methods[$mybb->input['method']]) &&
        isset($userMethods[$mybb->input['method']]) &&
        $mybb->get_input('verify') === '1'
    ) {
        verify_post_check($mybb->get_input('my_post_key'));

        $method = $methods[$mybb->input['method']];

        if ($includeBreadcrumb)
        {
            add_breadcrumb($lang->my2fa_title, $verificationUrl . $redirectUrlQueryStr);
            add_breadcrumb($method['definitions']['name']);
        }

        eval('$verificationFormButtons = "' . template('verification_form_buttons') . '";');

        $verificationTrustDeviceOption = null;
        if (isDeviceTrustingAllowed())
        {
            $lang->my2fa_verification_trust_device = $lang->sprintf(
                $lang->my2fa_verification_trust_device,
                setting('trust_device_duration_in_days')
            );
            $lang->my2fa_verification_trust_device_description = $lang->sprintf(
                $lang->my2fa_verification_trust_device_description,
                setting('trust_device_duration_in_days')
            );

            $checkboxInputState = 'checked';
            if (isset($mybb->input['trust_device']) && $mybb->input['trust_device'] !== '1')
            {
                $checkboxInputState = null;
            }

            eval('$verificationFormTrustDeviceOption = "' . template('verification_form_trust_device_option') . '";');
        }

        $output = $method['className']::handleVerification($user, $verificationUrl, compact(
            'verificationUrl',
            'redirectUrl',
            'redirectUrlQueryStr',
            'verificationFormButtons',
            'verificationFormTrustDeviceOption'
        ));
    }
    else
    {
        if ($includeBreadcrumb)
            add_breadcrumb($lang->my2fa_title);

        $verificationMethodRows = null;
        foreach ($userMethods as $userMethod)
        {
            $method = $methods[$userMethod['method_id']] ?? [];

            if (!$method)
                continue;

            eval('$verificationMethodRows .= "' . template('verification_methods_row') . '";');
        }

        if ($includeExtraRows)
            eval('$verificationExtraRows .= "' . template('verification_extra_rows') . '";');

        eval('$output = "' . template('verification_methods') . '";');
    }

    return $output;
}

function getSetupForm(array $user, string $setupUrl, bool $includeBreadcrumb = True): ?string
{
    global $mybb, $lang, $theme;

    $output = null;
    $mybb->input['method'] = $mybb->get_input('method');

    $methods = selectMethods();
    $userMethods = selectUserMethods($user['uid']);

    if (isset($methods[$mybb->input['method']]))
    {
        verify_post_check($mybb->get_input('my_post_key'));

        $method = $methods[$mybb->input['method']];

        if (
            $mybb->get_input('deactivate') === '1' &&
            $method['className']::canBeDeactivated() &&
            isset($userMethods[$mybb->input['method']])
        ) {
            $output = $method['className']::handleDeactivation($user, $setupUrl);
        }
        else
        {
            add_breadcrumb($lang->my2fa_title, $setupUrl);
            add_breadcrumb($method['definitions']['name']);

            if (
                $mybb->get_input('activate') === '1' &&
                $method['className']::canBeDeactivated() &&
                !isset($userMethods[$mybb->input['method']])
            ) {
                eval('$setupFormButtons = "' . template('setup_form_buttons') . '";');

                $output = $method['className']::handleActivation($user, $setupUrl, compact(
                    'setupFormButtons'
                ));
            }
            else if (
                $mybb->get_input('manage') === '1' &&
                $method['className']::canBeManaged() &&
                isset($userMethods[$mybb->input['method']])
            ) {
                $output = $method['className']::handleManagement($user, $setupUrl);
            }
        }
    }
    else
    {
        add_breadcrumb($lang->my2fa_title);

        $setupMethodRows = null;
        foreach ($methods as $method)
        {
            if (!$method['className']::canBeActivated())
                continue;

            if (!isset($userMethods[$method['id']]))
            {
                eval('$setupMethodRows .= "' . template('setup_methods_row') . '";');
            }
            else
            {
                $userMethod = $userMethods[$method['id']];

                $lang->my2fa_setup_method_activation_date = $lang->sprintf(
                    $lang->my2fa_setup_method_activation_date,
                    my_date('d M Y', $userMethod['activated_on'])
                );
                $lang->my2fa_setup_deactivate_confirmation = $lang->sprintf(
                    $lang->my2fa_setup_deactivate_confirmation,
                    $method['definitions']['name']
                );

                if ($method['className']::canBeDeactivated())
                    eval('$setupDeactivateButton = "' . template('setup_button_deactivate') . '";');

                if ($method['className']::canBeManaged())
                    eval('$setupManageButton = "' . template('setup_button_manage') . '";');

                eval('$setupMethodRows .= "' . template('setup_methods_row_enabled') . '";');
            }
        }

        $trustedDevices = null;
        if (
            isDeviceTrustingAllowed() &&
            doesUserHave2faEnabled($user['uid']) &&
            ($userTokens = selectUserTokens($user['uid']))
        ) {
            $currentUserToken = [];
            $otherUserTokens = $userTokens;

            if (isset($userTokens[$mybb->cookies['my2fa_token']]))
            {
                $currentUserToken = $userTokens[$mybb->cookies['my2fa_token']];
                unset($otherUserTokens[$mybb->cookies['my2fa_token']]);
            }

            if ($mybb->get_input('remove_trusted_devices') === '1')
            {
                verify_post_check($mybb->get_input('my_post_key'));

                if ($mybb->get_input('current') === '1' && $currentUserToken)
                {
                    deleteUserTokens($user['uid'], (array) $currentUserToken['tid']);
                    redirect($setupUrl, $lang->my2fa_current_trusted_device_removed_success);
                }
                else if ($mybb->get_input('others') === '1' && $otherUserTokens)
                {
                    deleteUserTokens($user['uid'], array_keys($otherUserTokens));
                    redirect($setupUrl, $lang->my2fa_other_trusted_devices_removed_success);
                }
            }

            $currentTrustedDeviceRow = null;
            if ($currentUserToken)
            {
                $lang->my2fa_setup_current_trusted_device = $lang->sprintf(
                    $lang->my2fa_setup_current_trusted_device,
                    my_date('relative', $userTokens[$mybb->cookies['my2fa_token']]['expire_on'])
                );

                eval('$currentTrustedDeviceRow = "' . template('setup_trusted_devices_row_current') . '";');
            }

            $otherTrustedDevicesRow = null;
            if ($otherUserTokens)
                eval('$otherTrustedDevicesRow = "' . template('setup_trusted_devices_row_others') . '";');
 
            eval('$trustedDevices = "' . template('setup_trusted_devices') . '";');
        }

        eval('$output = "' . template('setup_methods') . '";');
    }

    return $output;
}

// Based on admin/inc/class_page.php pattern
function getAdminVerificationPage(string $verificationContent): string
{
    global $mybb, $lang, $theme;

    $copyYear = COPY_YEAR;

    $stylesheetLocations = getDefaultGlobalStylesheetLocations();

    $stylesheetHtml = null;
    foreach ($stylesheetLocations as $stylesheetLocation)
    {
        $stylesheetHtml .= <<<HTML
<link rel="stylesheet" type="text/css" href="{$mybb->settings['bburl']}/{$stylesheetLocation}" />\n\t
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$lang->my2fa_title}</title>
    <meta name="author" content="MyBB Group" />
    <meta name="copyright" content="Copyright {$copyYear} MyBB Group." />
    {$stylesheetHtml}
    <style>
        body, html, #container { height: 100%; margin: 0; }
        #container { display: flex; align-items: center; justify-content: center; text-align: left; }
    </style>
</head>
<body>
    <div id="container">
        <div class="verification-wrap">
            {$verificationContent}
        </div>
    </div>
</body>
</html>
HTML;
}
