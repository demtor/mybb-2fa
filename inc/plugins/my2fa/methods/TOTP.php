<?php

namespace My2FA\Methods;

use PragmaRX\Google2FA\Google2FA;

class TOTP extends AbstractMethod
{
    public const METHOD_ID = 'totp';

    protected static $definitions = [];

    public static function getDefinitions(): array
    {
        global $lang;

        \My2FA\loadUserLanguage();

        self::$definitions['name'] = $lang->my2fa_totp_name;
        self::$definitions['description'] = $lang->my2fa_totp_description;

        return self::$definitions;
    }

    public static function handleVerification(array $user, string $verificationUrl, array $viewParams = []): string
    {
        global $mybb, $session, $lang, $theme;

        extract($viewParams);

        $method = \My2FA\selectMethods()[self::METHOD_ID];
        $userMethod = \My2FA\selectUserMethods($user['uid'])[self::METHOD_ID];

        if (self::hasUserReachedMaximumAttempts($user['uid']))
        {
            $errors = inline_error([$lang->my2fa_verification_blocked_error]);
        }
        else if (isset($mybb->input['otp']))
        {
            if (
                self::isOtpValid($mybb->input['otp'], $userMethod['data']['secret_key']) &&
                !self::isUserCodeAlreadyUsed($user['uid'], $mybb->input['otp'], 30+120*2)
            ) {
                self::recordSuccessfulAttempt($user['uid'], $mybb->input['otp']);
                self::completeVerification($user['uid']);
            }
            else
            {
                self::recordFailedAttempt($user['uid']);
                $errors = inline_error([$lang->my2fa_code_error]);
            }
        }

        eval('$totpVerification = "' . \My2FA\template('method_totp_verification') . '";');
        return $totpVerification;
    }

    public static function handleActivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        global $mybb, $session, $lang, $theme;

        extract($viewParams);

        $google2fa = new Google2FA();

        $method = \My2FA\selectMethods()[self::METHOD_ID];
        $sessionStorage = \My2FA\selectSessionStorage($session->sid);

        if (!isset($sessionStorage['totp_secret_key']))
        {
            $sessionStorage['totp_secret_key'] = $google2fa->generateSecretKey();

            \My2FA\updateSessionStorage($session->sid, [
                'totp_secret_key' => $sessionStorage['totp_secret_key']
            ]);
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            \My2FA\setting('totp_board_name'),
            $user['username'],
            $sessionStorage['totp_secret_key']
        );

        // very temp
        $qrCode = "https://api.qrserver.com/v1/create-qr-code/?data={$qrCodeUrl}";

        if (isset($mybb->input['otp']))
        {
            $mybb->input['otp'] = str_replace(' ', '', $mybb->input['otp']);

            if (
                self::isOtpValid($mybb->input['otp'], $sessionStorage['totp_secret_key']) &&
                !self::isUserCodeAlreadyUsed($user['uid'], $mybb->input['otp'], 30+120*2)
            ) {
                \My2FA\deleteFromSessionStorage($session->sid, ['totp_secret_key']);

                self::recordSuccessfulAttempt($user['uid'], $mybb->input['otp']);
                self::completeActivation($user['uid'], $setupUrl, [
                    'secret_key' => $sessionStorage['totp_secret_key']
                ]);
            }
            else
            {
                $errors = inline_error([$lang->my2fa_code_error]);
            }
        }

        eval('$totpActivation = "' . \My2FA\template('method_totp_activation') . '";');
        return $totpActivation;
    }

    public static function handleDeactivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        self::completeDeactivation($user['uid'], $setupUrl);
    }

    private static function isOtpValid(string $otp, string $secretKey): bool
    {
        $google2fa = new Google2FA();

        return
            strlen($otp) === 6 &&
            $google2fa->verifyKey($secretKey, $otp)
            //|| (int) $otp === 123456 // test
        ;
    }
}