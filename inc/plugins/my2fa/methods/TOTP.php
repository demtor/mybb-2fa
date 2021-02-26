<?php

namespace My2FA\Methods;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

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
            if (self::isUserOtpValid($user['uid'], $mybb->input['otp'], $userMethod['data']['secret_key']))
            {
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

        if (isset($mybb->input['otp']))
        {
            $mybb->input['otp'] = str_replace(' ', '', $mybb->input['otp']);

            if (self::isUserOtpValid($user['uid'], $mybb->input['otp'], $sessionStorage['totp_secret_key']))
            {
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

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            \My2FA\setting('totp_board_name'),
            $user['username'],
            $sessionStorage['totp_secret_key']
        );

        $qrCodeRendered = self::getQrCodeRendered($qrCodeUrl);

        eval('$totpActivation = "' . \My2FA\template('method_totp_activation') . '";');
        return $totpActivation;
    }

    public static function handleDeactivation(array $user, string $setupUrl, array $viewParams = []): string
    {
        self::completeDeactivation($user['uid'], $setupUrl);
    }

    private static function getQrCodeRendered(string $qrCodeUrl)
    {
        $qrCodeRenderer = \My2FA\setting('totp_qr_code_renderer');

        if ($qrCodeRenderer === 'web_api')
        {
            $imageSrc = str_replace('{1}', $qrCodeUrl, \My2FA\setting('totp_qr_code_web_api'));
            $qrCodeRendered = '<img src="' . $imageSrc . '">';
        }
        else
        {
            $writer = new Writer(
                new ImageRenderer(
                    new RendererStyle(200),
                    $qrCodeRenderer === 'imagick_image_back_end'
                        ? new ImagickImageBackEnd()
                        : new SvgImageBackEnd()
                )
            );

            if ($qrCodeRenderer === 'imagick_image_back_end')
            {
                $imageSrc = 'data:image/png;base64,' . base64_encode($writer->writeString($qrCodeUrl));
                $qrCodeRendered = '<img src="' . $imageSrc . '">';
            }
            else
            {
                $qrCodeRendered = $writer->writeString($qrCodeUrl);
            }
        }

        return '<div class="my2fa__qr-code">' . $qrCodeRendered . '</div>';
    }

    private static function isUserOtpValid(int $userId, string $otp, string $secretKey): bool
    {
        $google2fa = new Google2FA();

        return
            strlen($otp) === 6 &&
            is_numeric($otp) &&
            $google2fa->verifyKey($secretKey, $otp) &&
            !self::isUserCodeAlreadyUsed($userId, $otp, 30+120*2)
            //|| (int) $otp === 123456 // test
        ;
    }
}
