{$errors}
<form action="{$setupUrl}&method={$method['id']}" method="post" class="my2fa__activation my2fa__activation--{$method['id']}">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="tcat">{$lang->my2fa_email_activation_instruction_main}</td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
				<p>
					<strong>{$lang->my2fa_email_activation_instruction_1}</strong>
					<br />
					{$lang->my2fa_email_activation_instruction_2}
				</p>
				<input type="text" name="code" class="textbox" style="text-align:center" placeholder="123456" autocomplete="off" autofocus />
			</td>
		</tr>
	</table>
	<br />
    <input type="hidden" name="confirm_code" value="1" />
	{$setupFormButtons}
</form>