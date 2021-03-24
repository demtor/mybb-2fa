{$errors}
<form action="{$setupUrl}&method={$method['id']}" method="post" class="my2fa__activation my2fa__activation--{$method['id']}">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="tcat">{$lang->my2fa_email_activation_request_instruction_main}</td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
                <p>{$lang->my2fa_email_activation_request_instruction_1}</p>
				<p>{$lang->my2fa_email_activation_request_instruction_2}</p>
			</td>
		</tr>
	</table>
	<br />
    <input type="hidden" name="request_code" value="1" />
    <input type="hidden" name="confirm_code" value="1" />
	{$setupFormButtons}
</form>