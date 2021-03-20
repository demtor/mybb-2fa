<?php

namespace My2FA\Methods;

class Skeleton extends AbstractMethod
{
    public const METHOD_ID = 22;

    protected static $definitions = [
        'name' => 'Skeleton',
        'description' => 'A implementation sample of a 2FA method.',
    ];

    public static function handleVerification(array $user, string $verificationUrl, array $viewParams = []): string
    {
        global $mybb, $lang, $theme;

        extract($viewParams);

        $method = \My2FA\selectMethods()[self::METHOD_ID];
        $userMethod = \My2FA\selectUserMethods($user['uid'], (array) self::METHOD_ID)[self::METHOD_ID];

        if (self::hasUserReachedMaximumAttempts($user['uid']))
        {
            $errors = inline_error((array) $lang->my2fa_verification_blocked_error);
        }
        else if (isset($mybb->input['otp']))
        {
            if (self::isOtpValid($mybb->input['otp']))
            {
                self::completeVerification($user['uid']);
            }
            else
            {
                self::recordFailedAttempt($user['uid']);

                $errors = self::hasUserReachedMaximumAttempts($user['uid'])
                    ? inline_error((array) $lang->my2fa_verification_blocked_error)
                    : inline_error((array) $lang->my2fa_code_error)
                ;
            }
        }

        eval('$skeletonVerification = "' . \My2FA\template('method_skeleton_verification') . '";');
        return $skeletonVerification;
    }

    public static function handleActivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        global $mybb, $lang, $theme;

        extract($viewParams);

        $method = \My2FA\selectMethods()[self::METHOD_ID];

        if (isset($mybb->input['otp']))
        {
            if (self::isOtpValid($mybb->input['otp']))
            {
                self::completeActivation($user['uid'], $setupUrl);
            }
            else
            {
                $errors = inline_error((array) $lang->my2fa_code_error);
            }
        }

        eval('$skeletonActivation = "' . \My2FA\template('method_skeleton_activation') . '";');
        return $skeletonActivation;
    }

    public static function handleDeactivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        self::completeDeactivation($user['uid'], $setupUrl);
    }

    private static function isOtpValid(string $otp): bool
    {
        return (int) $otp === 123;
    }
}
