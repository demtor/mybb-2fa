{$errors}
<form action="{$setupUrl}&method={$method['id']}" method="post" class="my2fa__activation my2fa__activation--{$method['id']}">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="tcat">{$lang->my2fa_totp_main_instruction}</td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
				<p>
					{$lang->my2fa_totp_instruction_1}
					{$lang->my2fa_totp_manual_secret_key_1} <a href="javascript:void(0)" title="{$secretKey}" class="open-secret-code" data-selector="#secret-code" rel="modal:open">{$lang->my2fa_totp_manual_secret_key_2}</a>.
				</p>
				{$qrCodeRendered}
				<p>
					<strong>{$lang->my2fa_totp_instruction_2}</strong>
					<br />{$lang->my2fa_totp_instruction_3}
				</p>
				<input type="text" name="otp" class="textbox" style="text-align:center" placeholder="123456" autocomplete="off" autofocus />
			</td>
		</tr>
	</table>
	<br />
	{$setupFormButtons}
</form>
<div id="secret-code" style="display:none">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead" style="text-align:center"><strong>{$lang->my2fa_totp_secret_key}</strong></td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center"><code>{$sessionStorage['totp_secret_key']}</code></td>
		</tr>
	</table>
</div>
<script type="text/javascript">
	$('a.open-secret-code').click(function(event) {
		event.preventDefault();

		$($(this).attr('data-selector')).modal({
			fadeDuration: 250,
			keepelement: true
		});

		return false;
	});
</script>