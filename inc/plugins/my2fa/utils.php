<?php

namespace My2FA;

function setting(string $name): string
{
    global $mybb;

    return $mybb->settings['my2fa_' . $name];
}

function csvSetting(string $name): array
{
    return array_filter(explode(',', setting($name)));
}

function template(string $name): string
{
    global $templates;

    return $templates->get('my2fa_' . $name);
}

function loadLanguage(): void
{
    global $lang;

    if (!isset($lang->my2fa_title))
        $lang->load('my2fa');
}

function loadUserLanguage(): void
{
    global $lang;

    if (!isset($lang->my2fa_title))
        $lang->load('my2fa', True);
}

function getMultiOptionscode(string $type, array $options): string
{
    $formattedMultiOptionscode = $type;

    foreach ($options as $name => $value)
    {
        $formattedMultiOptionscode .= "\n{$name}={$value}";
    }

    return $formattedMultiOptionscode;
}

function getCurrentUrl(): ?string
{
    global $mybb;

    if (empty($_SERVER['HTTP_HOST']))
        return null;

    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (
            isset($_SERVER['REQUEST_SCHEME']) &&
            $_SERVER['REQUEST_SCHEME'] === 'https'
        ) ||
        (
            isset($_SERVER['SERVER_PORT']) &&
            (int) $_SERVER['SERVER_PORT'] === 443
        ) ||
        (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
        ) ||
        parse_url($mybb->settings['bburl'], PHP_URL_SCHEME) === 'https'
    ;

    $hostUrl = ($isHttps ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

    $requestUri = null;
    if (!empty($_SERVER['REQUEST_URI']))
    {
        $requestUri = explode('?', $_SERVER['REQUEST_URI'], 2);
        $requestUri[0] = urldecode($requestUri[0]);
        $requestUri = implode('?', $requestUri);
    }

    return $hostUrl . $requestUri;
}

function getDefaultTheme()
{
    global $cache;

    if (!$cache->read('default_theme'))
        $cache->update_default_theme();

    $defaultTheme = $cache->read('default_theme');
    $defaultTheme = array_merge($defaultTheme, \my_unserialize($defaultTheme['properties']));
    $defaultTheme['stylesheets'] = \my_unserialize($defaultTheme['stylesheets']);

    return $defaultTheme;
}

#todo: include order if you want
function getDefaultGlobalStylesheetLocations()
{
    $defaultTheme = getDefaultTheme();

    return preg_replace(
        '/^css\.php\?stylesheet=(\d+)$/',
        'css.php?stylesheet%5B0%5D=$1',
        array_unique(array_merge(
            $defaultTheme['stylesheets']['global']['global'],
            ...array_column($defaultTheme['stylesheets'], 'my2fa')
        ))
    );
}

function getDataItemsEscaped(array $data): array
{
    array_walk_recursive($data, function (&$item)
    {
        global $db;

        if (is_numeric($item) || is_bool($item))
        {
            $item = (int) $item;
        }
        else
        {
            $item = $db->escape_string($item);
        }
    });

    return $data;
}

function updateSession(string $sessionId, array $data): void
{
    global $db, $session;

    $data = getDataItemsEscaped($data);

    $db->update_query(
        'sessions',
        $data,
        "sid = '" . $db->escape_string($sessionId) . "'"
    );

    if (
        isset($data['sid'], $session->sid) &&
        $session->sid === $sessionId &&
        $data['sid'] !== $sessionId
    ) {
        \my_setcookie('sid', $data['sid'], -1, True);
        $session->sid = $data['sid'];
    }
}

function updateAdminSession(string $sessionId, array $data): void
{
    global $db, $admin_session;

    $data = getDataItemsEscaped($data);

    $db->update_query(
        'adminsessions',
        $data,
        "sid = '" . $db->escape_string($sessionId) . "'"
    );

    if (
        isset($data['sid'], $admin_session['sid']) &&
        $sessionId === $admin_session['sid'] &&
        $sessionId !== $data['sid']
    ) {
        \my_setcookie('adminsid', $data['sid'], '', True, 'lax');
        $admin_session['sid'] = $data['sid'];
    }
}

function redirect(string $url, string $message = null): void
{
    global $mybb;

    if (!$message)
        $mybb->settings['redirects'] = 0;

    \redirect($url, $message);
}
