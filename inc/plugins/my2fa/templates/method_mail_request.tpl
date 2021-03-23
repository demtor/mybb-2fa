{$errors}
<form action="{$setupUrl}&method={$method['id']}" method="post" class="my2fa__activation my2fa__activation--{$method['id']}">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="tcat">{$lang->my2fa_mail_activation_instruction_request}</td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
				<p>
					{$request_description}
				</p>
			</td>
		</tr>
	</table>
	<br />
    <input type="hidden" name="request_code" value="1" />
    <input type="hidden" name="confirm_code" value="1" />
	{$setupFormButtons}
</form>