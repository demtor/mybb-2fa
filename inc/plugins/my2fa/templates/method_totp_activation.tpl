{$errors}
<form action="{$setupUrl}&method={$method['id']}" method="post" class="my2fa__activation my2fa__activation--{$method['id']}">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="tcat">{$lang->my2fa_totp_activation_instruction_main}</td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
				<p>
					{$lang->my2fa_totp_activation_instruction_1}
					<br />
					{$lang->my2fa_totp_activation_instruction_secret_key_1} <a href="javascript:void(0)" title="{$sessionStorage['totp_secret_key']}" id="secret-code">{$lang->my2fa_totp_activation_instruction_secret_key_2}</a>.
				</p>
				{$qrCodeRendered}
				<p>
					<strong>{$lang->my2fa_totp_activation_instruction_2}</strong>
					<br />
					{$lang->my2fa_totp_activation_instruction_3}
				</p>
				<input type="text" name="otp" class="textbox" style="text-align:center" placeholder="123456" autocomplete="off" autofocus />
			</td>
		</tr>
	</table>
	<br />
	{$setupFormButtons}
</form>
<div id="secret-code-modal" style="display:none">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder" style="text-align:center">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_totp_activation_secret_key}</strong></td>
		</tr>
		<tr>
			<td class="trow1"><code>{$sessionStorage['totp_secret_key']}</code></td>
		</tr>
	</table>
</div>
<script type="text/javascript">
	$('#secret-code').on('click', function(event) {
		$('#secret-code-modal').modal({
			fadeDuration: 250,
			keepelement: true
		});
	});
</script>