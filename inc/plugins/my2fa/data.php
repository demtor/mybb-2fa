<?php

namespace My2FA;

function selectMethods(): array
{
    static $methods;

    if (!isset($methods))
    {
        $filenames = scandir(MY2FA_ROOT . 'methods');

        $methods = [];
        foreach ($filenames as $filename)
        {
            $className = 'My2FA\Methods\\' . basename($filename, '.php');

            if (
                $filename[0] !== '.' &&
                is_subclass_of($className, 'My2FA\Methods\AbstractMethod')
            ) {
                $methods[$className::METHOD_ID] = [
                    'className' => $className,
                    'publicName' => $className::METHOD_ID,
                    'definitions' => $className::getDefinitions(),
                    'canBeActivated' => $className::canBeActivated(),
                    'canBeDeactivated' => $className::canBeDeactivated(),
                    'canBeManaged' => $className::canBeManaged(),
                ];
            }
        }
    }

    return $methods;
}

function selectUserMethods(int $userId): array
{
    global $db;
    static $usersMethods;

    if (!isset($usersMethods[$userId]))
    {
        $query = $db->simple_select('my2fa_user_methods', '*', "uid = {$userId}");

        $usersMethods[$userId] = [];
        while ($userMethod = $db->fetch_array($query))
        {
            $userMethod['data'] = json_decode($userMethod['data'], True) ?? [];
            $usersMethods[$userId][$userMethod['name']] = $userMethod;
        }
    }

    return $usersMethods[$userId];
}

function selectUserToken(int $userId, string $tokenId)
{
    global $db;
    static $usersToken;

    if (!isset($usersToken[$userId]))
    {
        $query = $db->simple_select('my2fa_tokens', '*',
            "uid = {$userId} AND tid = '" . $db->escape_string($tokenId) . "'"
        );

        $usersToken[$userId] = $db->fetch_array($query) ?? [];
    }

    return $usersToken[$userId];
}

function selectUserTokens(int $userId): array
{
    global $db;
    static $usersTokens;

    if (!isset($usersTokens[$userId]))
    {
        $query = $db->simple_select('my2fa_tokens', '*', "uid = {$userId}");

        $usersTokens[$userId] = [];
        while ($userToken = $db->fetch_array($query))
        {
            $usersTokens[$userId][$userToken['tid']] = $userToken;
        }
    }

    return $usersTokens[$userId];
}

function selectUserLogs(int $userId, string $event, int $secondsInterval): array
{
    global $db;
    static $userLogs;

    if (!isset($userLogs[$userId]))
    {
        $query = $db->simple_select('my2fa_logs', '*',
            "
                uid = {$userId} AND
                event = '" . $db->escape_string($event) . "' AND
                inserted_on > " . (TIME_NOW - $secondsInterval) . "
            "
        );

        $userLogs[$userId] = [];
        while ($userLog = $db->fetch_array($query))
        {
            $userLog['data'] = json_decode($userLog['data'], True) ?? [];
            $userLogs[$userId][] = $userLog;
        }
    }

    return $userLogs[$userId];
}

function selectSessionStorage(string $sessionId): array
{
    global $db;
    static $sessionsStorage;

    if (!isset($sessionsStorage[$sessionId]))
    {
        $sessionStorage = $db->fetch_field(
            $db->simple_select('sessions',
                'my2fa_storage',
                "sid = '" . $db->escape_string($sessionId) . "'"
            ),
            'my2fa_storage'
        );

        $sessionsStorage[$sessionId] = json_decode($sessionStorage, True) ?? [];
    }

    return $sessionsStorage[$sessionId];
}

function selectUserHasMy2faField(int $userId): bool
{
    global $db, $mybb;
    static $usersHasMy2faField;

    if (!isset($usersHasMy2faField[$userId]))
    {
        if ($userId === (int) $mybb->user['uid'])
        {
            $usersHasMy2faField[$userId] = $mybb->user['has_my2fa'];
        }
        else
        {
            $usersHasMy2faField[$userId] = $db->fetch_field(
                $db->simple_select('users', 'has_my2fa', "uid = {$userId}"),
                'has_my2fa'
            );
        }
    }

    return $usersHasMy2faField[$userId];
}

function insertUserMethod(array $data): array
{
    global $db;

    array_walk_recursive($data, function(&$item) {
        global $db;
        $item = $db->escape_string($item);
    });

    if (!empty($data['data']))
        $data['data'] = json_encode($data['data']);
    else
        unset($data['data']);

    $data += [
        'activated_on' => TIME_NOW
    ];

    if (!selectUserHasMy2faField($data['uid']))
        updateUserHasMy2faField($data['uid'], True);

    $db->insert_query('my2fa_user_methods', $data);

    return $data;
}

function insertUserToken(array $data): array
{
    global $db;

    $data = array_map([$db, 'escape_string'], $data);
    $data += [
        'tid' => random_str(32),
        'generated_on' => TIME_NOW
    ];

    $db->insert_query('my2fa_tokens', $data);

    return $data;
}

function insertUserLog(array $data): array
{
    global $db;

    array_walk_recursive($data, function(&$item) {
        global $db;
        $item = $db->escape_string($item);
    });

    if (!empty($data['data']))
        $data['data'] = json_encode($data['data']);
    else
        unset($data['data']);

    $data += [
        'inserted_on' => TIME_NOW
    ];

    $db->insert_query('my2fa_logs', $data);

    return $data;
}

function updateUserMethod(int $userId, string $method, array $data): void
{
    global $db;

    $data = array_map([$db, 'escape_string'], $data);

    $db->update_query('my2fa_user_methods',
        $data,
        "uid = {$userId} AND name = '" . $db->escape_string($method) . "'"
    );
}

function updateSessionStorage(string $sessionId, array $data): void
{
    updateSession($sessionId, [
        'my2fa_storage' => json_encode(
            array_merge(selectSessionStorage($sessionId), $data)
        )
    ]);
}

function updateUserHasMy2faField(int $userId, bool $hasMy2faField): void
{
    global $db;

    $db->update_query('users', ['has_my2fa' => (int) $hasMy2faField], "uid = {$userId}");
}

function deleteUserMethod(int $userId, string $method): void
{
    global $db;

    $userMethods = selectUserMethods($userId);

    // if you want, add untrust session too (not necessary, but for convention)
    if (count($userMethods) === 1)
    {
        updateUserHasMy2faField($userId, False);
        deleteUserTokens($userId);
    }

    $db->delete_query('my2fa_user_methods',
        "uid = {$userId} AND name = '" . $db->escape_string($method) . "'"
    );
}

function deleteUserTokens(int $userId)
{
    global $db;

    $db->delete_query('my2fa_tokens', "uid = {$userId}");
    \my_unsetcookie('my2fa_token');
}

function deleteFromSessionStorage(string $sessionId, array $sessionKeys)
{
    updateSession($sessionId, [
        'my2fa_storage' => json_encode(
            array_diff_key(selectSessionStorage($sessionId), array_flip($sessionKeys))
        )
    ]);
}