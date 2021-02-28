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
                    'id' => $className::METHOD_ID,
                    'className' => $className,
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
        $usersMethods[$userId] = [];
        $methodIdsStr = implode("','", array_column(selectMethods(), 'id'));

        if ($methodIdsStr)
        {
            $query = $db->simple_select(
                'my2fa_user_methods',
                '*',
                "uid = {$userId} AND method_id IN ('{$methodIdsStr}')"
            );

            while ($userMethod = $db->fetch_array($query))
            {
                $userMethod['data'] = json_decode($userMethod['data'], True) ?? [];
                $usersMethods[$userId][$userMethod['method_id']] = $userMethod;
            }
        }
    }

    return $usersMethods[$userId];
}

function selectUserTokens(int $userId, array $tokenIds = []): array
{
    global $db;

    $whereClause = null;

    if ($tokenIds)
    {
        $tokenIds = getDataItemsEscaped($tokenIds);
        $whereClause = " AND tid IN ('" . implode("','", $tokenIds) . "')";
    }

    $query = $db->simple_select(
        'my2fa_tokens',
        '*',
        "uid = {$userId}{$whereClause} AND expire_on > " . TIME_NOW
    );

    $userTokens = [];
    while ($userToken = $db->fetch_array($query))
    {
        $userTokens[$userToken['tid']] = $userToken;
    }

    return $userTokens;
}

function selectUserLogs(int $userId, string $event, int $secondsInterval): array
{
    global $db;

    $query = $db->simple_select(
        'my2fa_logs',
        '*',
        "
            uid = {$userId} AND
            event = '" . $db->escape_string($event) . "' AND
            inserted_on > " . (TIME_NOW - $secondsInterval) . "
        "
    );

    $userLogs = [];
    while ($userLog = $db->fetch_array($query))
    {
        $userLog['data'] = json_decode($userLog['data'], True) ?? [];
        $userLogs[] = $userLog;
    }

    return $userLogs;
}

function selectSessionStorage(string $sessionId): array
{
    global $db;
    static $sessionsStorage;

    if (!isset($sessionsStorage[$sessionId]))
    {
        $sessionStorage = $db->fetch_field(
            $db->simple_select(
                'sessions',
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

    $data = getDataItemsEscaped($data);

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

    $data = getDataItemsEscaped($data);
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

    $data = getDataItemsEscaped($data);

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

function updateUserMethod(int $userId, string $methodId, array $data): void
{
    global $db;

    $data = getDataItemsEscaped($data);

    $db->update_query(
        'my2fa_user_methods',
        $data,
        "uid = {$userId} AND method_id = '" . $db->escape_string($methodId) . "'"
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

function deleteUserMethod(int $userId, string $methodId): void
{
    global $db;

    $userMethods = selectUserMethods($userId);

    // if you want, add untrust session too (not necessary, but for convention)
    if (count($userMethods) === 1)
    {
        updateUserHasMy2faField($userId, False);
        deleteUserTokens($userId);
    }

    $db->delete_query(
        'my2fa_user_methods',
        "uid = {$userId} AND method_id = '" . $db->escape_string($methodId) . "'"
    );
}

function deleteUserTokens(int $userId, array $tokenIds = [])
{
    global $db, $mybb;

    $whereClause = null;

    if ($tokenIds)
    {
        $tokenIds = getDataItemsEscaped($tokenIds);
        $whereClause = " AND tid IN ('" . implode("','", $tokenIds) . "')";
    }

    $db->delete_query('my2fa_tokens', "uid = {$userId}{$whereClause}");

    if (!$tokenIds || in_array($mybb->cookies['my2fa_token'], $tokenIds))
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
