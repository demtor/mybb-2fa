<html>
<head>
	<title>{$mybb->settings['bbname']} - {$lang->my2fa_password_confirmation}</title>
	{$headerinclude}
</head>
<body>
	{$header}
	{$errors}
	<form action="" method="post" class="my2fa__confirm-password">
		<table cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" border="0" class="tborder">
			<tr>
				<td class="thead" align="center"><strong>{$lang->my2fa_title}<br />{$lang->my2fa_password_confirmation}</strong></td>
			</tr>
			<tr>
				<td class="tcat" align="center">{$lang->my2fa_password_confirmation_description}</td>
			</tr>
			<tr>
				<td class="trow1">
					<div class="my2fa__confirm-password-fields">
						<label for="password" class="float_left"><strong>{$lang->password}</strong></label>
						<span class="float_right">
							<a href="member.php?action=lostpw">{$lang->lost_password}</a>
						</span>
						<br class="clear" />
					</div>
					<input type="password" name="password" id="password" class="textbox" autofocus />
				</td>
			</tr>
		</table>
		<br />
		<div style="text-align:center">
			<input type="hidden" name="my2fa_password_confirmation" value="1" />
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<input type="submit" class="button" value="{$lang->my2fa_confirm_button}" />
		</div>
	</form>
	{$footer}
</body>
</html>