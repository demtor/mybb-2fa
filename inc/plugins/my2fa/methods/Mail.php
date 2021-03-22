<?php

namespace My2FA\Methods;

class Mail extends AbstractMethod
{
    public const METHOD_ID = 2;
    public const ORDER = 2;

    protected static $definitions = [];

    public static function getDefinitions(): array
    {
        global $lang;

        \My2FA\loadUserLanguage();

        self::$definitions['name'] = $lang->my2fa_mail;
        self::$definitions['description'] = $lang->my2fa_mail_description;

        return self::$definitions;
    }

    public static function handleVerification(array $user, string $verificationUrl, array $viewParams = []): string
    {
        global $mybb, $lang, $theme;

        extract($viewParams);

        $method = \My2FA\selectMethods()[self::METHOD_ID];
        $userMethod = \My2FA\selectUserMethods($user['uid'], (array) self::METHOD_ID)[self::METHOD_ID];

        if (!isset($mybb->input['code']))
        {
            self::sendCode($user);    
        }

        if (self::hasUserReachedMaximumAttempts($user['uid']))
        {
            $errors = inline_error((array) $lang->my2fa_verification_blocked_error);
        }
        else if (isset($mybb->input['code']))
        {
            if (self::isCodeValid((int)$user['uid'], $mybb->get_input('code', \MyBB::INPUT_INT)))
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

        eval('$mailVerification = "' . \My2FA\template('method_mail_verification') . '";');
        return $mailVerification;
    }

    public static function handleActivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        global $mybb, $lang, $theme, $db;

        extract($viewParams);

        $method = \My2FA\selectMethods()[self::METHOD_ID];

        if (isset($mybb->input['request']))
        {
            if(!$mybb->get_input('request', \MyBB::INPUT_INT))
            {
                self::sendCode($user);
            }
            else if (isset($mybb->input['code']))
            {
                if (self::isCodeValid((int)$user['uid'], $mybb->get_input('code', \MyBB::INPUT_INT)))
                {
                    self::completeActivation($user['uid'], $setupUrl);
                }
                else
                {
                    $errors = inline_error((array) $lang->my2fa_code_error);
                }
            }
    
            $main_description = $lang->sprintf($lang->my2fa_mail_activation_instruction_main_1, $user['email']);
    
            eval('$mailActivation = "' . \My2FA\template('method_mail_activation') . '";');
            return $mailActivation;
        }

        $request_description = $lang->sprintf($lang->my2fa_mail_activation_instruction_request_1, $user['email']);

        eval('$mailRequest = "' . \My2FA\template('method_mail_request') . '";');
        return $mailRequest;
    }

    public static function handleDeactivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        self::completeDeactivation($user['uid'], $setupUrl);
    }

    private static function isCodeValid(int $uid, int $code): bool
    {
        global $db;

        $query = $db->simple_select('my2fa_mail_codes', '*', "uid='{$uid}' AND code='{$code}'");

        return $db->num_rows($query) > 0;
    }

    private static function sendCode(array $user): int
    {
        global $db, $lang, $mybb;

        $code = (int)mt_rand(100000, 999999);

        $db->replace_query('my2fa_mail_codes', [
            'uid' => (int)$user['uid'],
            'code' => $code,
            'dateline' => TIME_NOW
        ]);

        my_mail(
            $user['email'],
            $lang->my2fa_mail_activation_instruction_request_mail_subject,
            $lang->sprintf(
                $lang->my2fa_mail_activation_instruction_request_mail_message,
                $user['username'],
                $code,
                $mybb->settings['bburl'],
                $mybb->settings['bbname'],
            ),
        );

        return $code;
    }
}
