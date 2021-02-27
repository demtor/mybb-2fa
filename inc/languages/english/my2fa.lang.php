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

$l['my2fa_totp_name'] = "Authenticator App";
$l['my2fa_totp_description'] = "Use app-generated codes (TOTP).";
$l['my2fa_totp_main_instruction'] = "Scan this barcode with your app.";
$l['my2fa_totp_instruction_1'] = "Scan the image below with the two-factor authentication app on your phone.";
$l['my2fa_totp_instruction_2'] = "Enter the six-digit code from the application.";
$l['my2fa_totp_instruction_3'] = "After scanning the barcode image, the app will display a six-digit code that you can enter below.";
$l['my2fa_totp_manual_secret_key_1'] = "If you can’t use a barcode,";
$l['my2fa_totp_manual_secret_key_2'] = "enter this text code instead";
$l['my2fa_totp_secret_key'] = "Your two-factor secret";
$l['my2fa_totp_verification'] = "Open the two-factor authentication app on your device to view your authentication code and verify your identity.";

$l['my2fa_password_confirmation'] = "Confirm password";
$l['my2fa_password_confirmation_description'] = "Please verify your identity to continue.";
$l['my2fa_global_notified_group_notice'] = "You are advised to <a href=\"usercp.php?action=my2fa\">activate the two-factor authentication</a> on your account for added account security.";

$l['my2fa_setup'] = "Setup";
$l['my2fa_setup_method_activation_date'] = "Activated on: {1}";
$l['my2fa_setup_deactivate_confirmation'] = "Are you sure you wish to disable {1} two-factor authentication?";
$l['my2fa_setup_trusted_devices'] = "Trusted Devices";
$l['my2fa_setup_trusted_devices_description'] = "Manage your current and other trusted devices.";
$l['my2fa_setup_current_trusted_device'] = "This device is currently trusted until {1}. This means you will not need to complete two-step verification from this device until then.";
$l['my2fa_setup_other_trusted_devices'] = "There are other devices currently trusted on your account, if you have lost access to a trusted device it is recommended that you stop trusting that device.";
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
