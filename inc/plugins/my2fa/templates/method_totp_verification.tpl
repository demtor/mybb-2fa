{$errors}
<form action="{$verificationUrl}&method={$method['id']}" method="post" class="my2fa__verification my2fa__verification--{$method['id']}">
	<table cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" border="0" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
				<p>{$lang->my2fa_totp_verification}</p>
				<input type="text" name="otp" class="textbox" style="text-align:center" placeholder="123456" autocomplete="off" autofocus />
			</td>
		</tr>
	</table>
	<br />
	{$verificationFormTrustDeviceOption}
	{$verificationFormButtons}
</form>