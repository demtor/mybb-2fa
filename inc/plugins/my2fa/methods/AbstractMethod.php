<?php

namespace My2FA\Methods;

abstract class AbstractMethod
{
    public const METHOD_ID = null;
    public const ORDER = 22;

    protected static $definitions = [
        'name' => null,
        'description' => null
    ];

    public static function getDefinitions(): array
    {
        return static::$definitions;
    }

    abstract public static function handleVerification(array $user, string $verificationUrl, array $viewParams = []): string;

    public static function handleActivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        return "";
    }

    public static function handleDeactivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        return "";
    }

    public static function handleManagement(array $user, string $setupUrl, array $viewParams = []): string
    {
        return "";
    }

    public static function canBeActivated(): bool
    {
        return True;
    }

    public static function canBeDeactivated(): bool
    {
        return True;
    }

    public static function canBeManaged(): bool
    {
        return False;
    }

    final protected static function hasUserReachedMaximumAttempts(int $userId): bool
    {
        return
            \My2FA\countUserLogs($userId, 'failed_attempt', 60*5)
            >= \My2FA\setting('max_verification_attempts')
        ;
    }

    final protected static function recordFailedAttempt(int $userId): void
    {
        \My2FA\insertUserLog([
            'uid' => $userId,
            'event' => 'failed_attempt',
            'data' => [
                'method_id' => static::METHOD_ID
            ]
        ]);
    }

    final protected static function isUserCodeAlreadyUsed(int $userId, string $code, int $secondsInterval): bool
    {
        $userLogs = \My2FA\selectUserLogs($userId, 'succesful_attempt', $secondsInterval);

        foreach ($userLogs as $userLog)
        {
            if (
                $userLog['data']['method_id'] == static::METHOD_ID &&
                $userLog['data']['code'] == $code
            ) {
                return True;
            }
        }

        return False;
    }

    final protected static function recordSuccessfulAttempt(int $userId, string $code): void
    {
        \My2FA\insertUserLog([
            'uid' => $userId,
            'event' => 'succesful_attempt',
            'data' => [
                'method_id' => static::METHOD_ID,
                'code' => $code
            ]
        ]);
    }

    final protected static function completeVerification(int $userId): void
    {
        global $mybb, $lang;

        \My2FA\loadLanguage();

        $redirectUrl = \My2FA\isRedirectUrlValid($mybb->get_input('redirect_url'))
            ? urldecode($mybb->input['redirect_url'])
            : 'index.php'
        ;

        if (
            \My2FA\isDeviceTrustingAllowed() &&
            $mybb->get_input('trust_device') === '1'
        ) {
            \My2FA\setDeviceTrusted($userId);
        }

        if (defined('IN_ADMINCP'))
        {
            \My2FA\setAdminSessionTrusted();

            \flash_message($lang->my2fa_verified_success, 'success');
            \admin_redirect($redirectUrl);
        }
        else
        {
            \My2FA\setSessionTrusted();

            \My2FA\redirect($redirectUrl, $lang->my2fa_verified_success);
        }
    }

    final protected static function completeActivation(int $userId, string $setupUrl, array $userMethodData = []): void
    {
        global $lang;

        if (!\My2FA\isSessionTrusted())
            \My2FA\setSessionTrusted();

        \My2FA\insertUserMethod([
            'uid' => $userId,
            'method_id' => static::METHOD_ID,
            'data' => $userMethodData
        ]);
        \My2FA\redirect($setupUrl, $lang->my2fa_activated_success);
    }

    final protected static function completeDeactivation(int $userId, string $setupUrl): void
    {
        global $lang;

        \My2FA\deleteUserMethod($userId, static::METHOD_ID);
        \My2FA\redirect($setupUrl, $lang->my2fa_deactivated_success);
    }
}
