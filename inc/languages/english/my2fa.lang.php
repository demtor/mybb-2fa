<?php

$l['my2fa_title'] = "Two-factor authentication";
$l['my2fa_description'] = "Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to log in.";

$l['my2fa_activated_success'] = "Two-factor authentication successfully enabled!";
$l['my2fa_deactivated_success'] = "Two-factor authentication successfully disabled.";
$l['my2fa_verified_success'] = "Two-factor authentication successfully verified!";
$l['my2fa_password_confirmed_success'] = "Password successfully confirmed!";
$l['my2fa_current_trusted_device_removed_success'] = "The current trusted device has been successfully removed!";
$l['my2fa_other_trusted_devices_removed_success'] = "The other trusted devices have been successfully removed!";
$l['my2fa_code_error'] = "Two-factor code verification failed. Please try again.";
$l['my2fa_verification_blocked_error'] = "You are blocked from logging in for around 5 minutes due to repeated authentication failures.";
$l['my2fa_xmlhttp_error'] = "Please refresh the page and verify/activate your two-factor authentication.";
$l['my2fa_archive_error'] = "Please return to the full version and verify/activate your two-factor authentication.";
$l['my2fa_admin_cp_error'] = "Please return to the website and activate your two-factor authentication.";

$l['my2fa_activate_button'] = "Enable";
$l['my2fa_deactivate_button'] = "Disable";
$l['my2fa_manage_button'] = "Manage";
$l['my2fa_confirm_button'] = "Confirm";
$l['my2fa_cancel_button'] = "Cancel";

$l['my2fa_usercp_setup_wol'] = "Editing <a href=\"usercp.php?action=my2fa\">Two-factor authentication</a>";
$l['my2fa_misc_verification_wol'] = "Verifying Two-factor authentication";

$l['my2fa_setup'] = "Setup";
$l['my2fa_setup_method_activation_date'] = "Enabled on: {1}";
$l['my2fa_setup_deactivate_confirmation'] = "Are you sure you wish to disable {1} two-factor authentication?";
$l['my2fa_setup_trusted_devices'] = "Trusted Devices";
$l['my2fa_setup_trusted_devices_description'] = "Manage your current and other trusted devices.";
$l['my2fa_setup_current_trusted_device'] = "This device is currently trusted until {1}. This means you will not need to complete two-step verification from this device until then.";
$l['my2fa_setup_other_trusted_devices'] = "There are {1} other device(s) currently trusted on your account. If you have lost access to a trusted device it is recommended that you stop trusting every device (button below) and <a href=\"usercp.php?action=password\">change your password</a>.";
$l['my2fa_setup_other_trusted_devices_log'] = "Other trusted devices log";
$l['my2fa_setup_other_trusted_devices_log_generation'] = "Generated On";
$l['my2fa_setup_other_trusted_devices_log_expiry'] = "Expire On";
$l['my2fa_setup_remove_current_trusted_device'] = "Stop trusting this device";
$l['my2fa_setup_remove_other_trusted_devices'] = "Stop trusting other devices";
$l['my2fa_setup_remove_current_trusted_device_confirmation'] = "Are you sure you wish to stop trusting this device?";
$l['my2fa_setup_remove_other_trusted_devices_confirmation'] = "Are you sure you wish to stop trusting other devices?";
$l['my2fa_setup_forced_group_notice'] = "To continue using this website, you must setup two-factor authentication.";

$l['my2fa_verification'] = "Verification";
$l['my2fa_verification_description'] = "This extra step shows it is really you trying to sign in. Select a method to sign in with.";
$l['my2fa_verification_help'] = "Get help";
$l['my2fa_verification_help_description'] = "Contact us if you have a problem.";
$l['my2fa_verification_trust_device'] = "Trust this device for {1} days";
$l['my2fa_verification_trust_device_description'] = "If checked, you will not need to re-test this device for the next {1} days.";

$l['my2fa_confirm_password'] = "Confirm password";
$l['my2fa_confirm_password_description'] = "Please verify your identity to continue.";

$l['my2fa_totp'] = "Authenticator App";
$l['my2fa_totp_description'] = "Use app-generated codes (TOTP).";
$l['my2fa_totp_activation_instruction_main'] = "Scan this barcode with your app.";
$l['my2fa_totp_activation_instruction_1'] = "Scan the image below with the two-factor authentication app on your phone (e.g. Authy or Google Authenticator).";
$l['my2fa_totp_activation_instruction_2'] = "Enter the six-digit code from the application.";
$l['my2fa_totp_activation_instruction_3'] = "After scanning the barcode image, the app will display a six-digit code that you can enter below.";
$l['my2fa_totp_activation_instruction_secret_key_1'] = "If you can't use a barcode,";
$l['my2fa_totp_activation_instruction_secret_key_2'] = "enter this text code instead";
$l['my2fa_totp_activation_secret_key'] = "Your two-factor secret";
$l['my2fa_totp_verification_instruction'] = "Open the two-factor authentication app on your device to view your authentication code and verify your identity.";

$l['my2fa_email'] = "Email Confirmation";
$l['my2fa_email_description'] = "Use codes sent to the email associated with your account.";
$l['my2fa_email_activation_request_instruction_main'] = "Request a verification code to your account email address.";
$l['my2fa_email_activation_request_instruction_1'] = "<strong>NB</strong>: other two-factor authentication methods should be chosen over this if possible.";
$l['my2fa_email_activation_request_instruction_2'] = "A single-use security code will be sent to your email address at <strong>{1}</strong>.";
$l['my2fa_email_activation_instruction_main'] = "Use the verification code sent to your email address.";
$l['my2fa_email_activation_instruction_1'] = "Enter the six-digit code that we just sent to you.";
$l['my2fa_email_activation_instruction_2'] = "An email has been sent to your account email address containing a six-digit code that you can enter below.";
$l['my2fa_email_activation_already_requested_code_error'] = "You have already requested a verification code recently. Please try again in {1} minute(s).";
$l['my2fa_email_verification_instruction'] = "An email has been sent to <strong>{1}</strong> with a single-use verification code to verify your identity.";
$l['my2fa_email_verification_already_emailed_code_error'] = "The verification code has already been emailed to you. If you have not received anything, please retry in {1} minute(s).";
$l['my2fa_email_notification_subject'] = "Your login verification code at {1}";
$l['my2fa_email_notification_message'] = "{1},

In order to complete your login (or two-factor authentication setup), please use the following code:

------------------------------------------
{2}
------------------------------------------

If you did not initiate this request, please change your password urgently.

To manage your two-factor authentication settings, you can go to the following URL:
{3}/usercp.php?action=my2fa

Thank you,
{4} Staff";
