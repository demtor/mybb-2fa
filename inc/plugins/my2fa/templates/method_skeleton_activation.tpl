{$errors}
<form action="{$setupUrl}&method={$method['publicName']}" method="post" class="my2fa__activation my2fa__activation--{$method['publicName']}">
	<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead"><strong>{$lang->my2fa_title} - {$method['definitions']['name']}</strong></td>
		</tr>
		<tr>
			<td class="tcat">Just fill the input</td>
		</tr>
		<tr>
			<td class="trow1" style="text-align:center">
				<p>Insert 123 in order to enable it.</p>
				<input type="text" name="otp" class="textbox" style="text-align:center" placeholder="123" autocomplete="off" autofocus />
			</td>
		</tr>
	</table>
	<br />
	{$setupFormButtons}
</form>