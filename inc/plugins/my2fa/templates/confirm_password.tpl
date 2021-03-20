<html>
<head>
	<title>{$mybb->settings['bbname']} - {$lang->my2fa_confirm_password}</title>
	{$headerinclude}
</head>
<body>
	{$header}
	{$errors}
	<form action="{$redirectUrl}" method="post" class="my2fa__password-confirmation">
		<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
			<tr>
				<td class="thead" style="text-align:center"><strong>{$lang->my2fa_title}<br />{$lang->my2fa_confirm_password}</strong></td>
			</tr>
			<tr>
				<td class="tcat" style="text-align:center">{$lang->my2fa_confirm_password_description}</td>
			</tr>
			<tr>
				<td class="trow1">
					<div class="my2fa__password-confirmation-fields">
						<label for="password" class="float_left"><strong>{$lang->password}</strong></label>
						<div class="float_right">
							<a href="member.php?action=lostpw">{$lang->lost_password}</a>
						</div>
						<br class="clear" />
					</div>
					<input type="password" name="password" id="password" class="textbox" autofocus />
				</td>
			</tr>
		</table>
		<br />
		<div style="text-align:center">
			<input type="hidden" name="my2fa_confirm_password" value="1" />
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<input type="submit" class="button" value="{$lang->my2fa_confirm_button}" />
		</div>
	</form>
	{$footer}
</body>
</html>