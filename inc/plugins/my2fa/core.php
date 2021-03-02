<?php

namespace My2FA;

function isUserVerificationRequired(int $userId): bool
{
    return
        doesUserHave2faEnabled($userId) &&
        !isSessionTrusted($userId) && !isDeviceTrusted($userId)
    ;
}

function isAdminVerificationRequired(int $userId): bool
{
    return
        doesUserHave2faEnabled($userId) &&
        !isAdminSessionTrusted() && !isDeviceTrusted($userId)
    ;
}

function isSessionTrusted(int $userId): bool
{
    global $session;

    //return strpos($session->sid, 'my2fa=') === 0;

    // temp due to how mybb currently handles sessions
    $sessionStorage = selectSessionStorage($session->sid);

    return
        isset($sessionStorage['verified_by']) &&
        $sessionStorage['verified_by'] === $userId
    ;
}

function isAdminSessionTrusted(): bool
{
    global $admin_session;

    return strpos($admin_session['sid'], 'my2fa=') === 0;
}

function isDeviceTrusted(int $userId): bool
{
    global $mybb;

    if (
        !isset($mybb->cookies['my2fa_token']) ||
        !isDeviceTrustingAllowed()
    ) {
        return False;
    }

    $userToken = selectUserTokens($userId, (array) $mybb->cookies['my2fa_token']);

    return (bool) $userToken;
}

function isDeviceTrustingAllowed(): bool
{
    return
        setting('enable_device_trusting') &&
        (!defined('IN_ADMINCP') || !setting('disable_device_trusting_in_acp'))
    ;
}

function hasUserBeenRedirected(): bool
{
    global $session;

    return selectSessionStorage($session->sid)['is_redirected'] ?? False;
}

function doesUserHave2faEnabled(int $userId): bool
{
    return selectUserHasMy2faField($userId);
}

function isRedirectUrlValid(string $redirectUrl): bool
{
    global $mybb;

    $boardUrlHost = parse_url($mybb->settings['bburl'], PHP_URL_HOST);
    $redirectUrlHost = parse_url($redirectUrl, PHP_URL_HOST);

    return
        $redirectUrlHost === $boardUrlHost &&
        strpos(parse_url($redirectUrl, PHP_URL_QUERY), 'ajax=') === False
    ;
}

function isUserForcedToHave2faActivated(int $userId): bool
{
    return
        !doesUserHave2faEnabled($userId) &&
        is_member(setting('forced_groups'), $userId)
    ;
}

function doesUserNeedsGlobalNotice(int $userId): bool
{
    return
        !doesUserHave2faEnabled($userId) &&
        is_member(setting('notified_groups'), $userId)
    ;
}

function setSessionTrusted(int $userId): void
{
    global $session;

    //updateSession($session->sid, [
    //    'sid' => substr_replace($session->sid, 'my2fa=', 0, 6)
    //]);

    // temp due to how mybb currently handles sessions
    updateSessionStorage($session->sid, ['verified_by' => $userId]);
}

function setAdminSessionTrusted(): void
{
    global $admin_session;

    updateAdminSession($admin_session['sid'], [
        'sid' => substr_replace($admin_session['sid'], 'my2fa=', 0, 6)
    ]);
}

function setDeviceTrusted(int $userId): void
{
    global $mybb;

    $expirationTime = setting('device_trusting_duration_in_days') * 60*60*24 + TIME_NOW;

    $userTokenResult = insertUserToken([
        'uid' => $userId,
        'expire_on' => $expirationTime,
    ]);

    \my_setcookie('my2fa_token', $userTokenResult['tid'], $expirationTime, True);
}

function redirectToVerification(): void
{
    global $mybb;

    if (
        defined('THIS_SCRIPT') &&
        THIS_SCRIPT === 'misc.php' &&
        $mybb->get_input('action') === 'my2fa'
    )
        return;

    $redirectUrlQueryStr = redirectUrlAsQueryString(getCurrentUrl());

    redirect("{$mybb->settings['bburl']}/misc.php?action=my2fa{$redirectUrlQueryStr}");
}

function redirectToSetup(): void
{
    global $mybb;

    if (
        defined('THIS_SCRIPT') &&
        THIS_SCRIPT === 'usercp.php' &&
        $mybb->get_input('action') === 'my2fa'
    )
        return;

    redirect("{$mybb->settings['bburl']}/usercp.php?action=my2fa");
}

function passwordConfirmationCheck(string $redirectUrl, int $maxAllowedMinutes): void
{
    global $db, $mybb, $session, $lang,
    $headerinclude, $header, $theme, $footer;

    $sessionStorage = selectSessionStorage($session->sid);

    if ($sessionStorage['password_confirmed_at'] + 60*$maxAllowedMinutes < TIME_NOW)
    {
        loadLanguage();

        if ($mybb->get_input('my2fa_password_confirmation'))
        {
            \verify_post_check($mybb->get_input('my_post_key'));

            if (\validate_password_from_uid($mybb->user['uid'], $mybb->get_input('password')))
            {
                updateSessionStorage($session->sid, ['password_confirmed_at' => TIME_NOW]);
                redirect($redirectUrl, $lang->my2fa_password_confirmed_success);
            }
            else
            {
                $errors = \inline_error($lang->error_invalidpassword);
            }
        }

        eval('$passwordConfirmationPage = "' . template('password_confirmation') . '";');
        \output_page($passwordConfirmationPage);

        exit;
    }
}

function redirectUrlAsQueryString(?string $redirectUrl): ?string
{
    return $redirectUrl
        ? '&redirect_url=' . urlencode($redirectUrl)
        : null
    ;
}
