<table cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" border="0" class="my2fa__verification-methods tborder">
	<tr>
		<td class="thead"><strong>{$lang->my2fa_title} - {$lang->my2fa_verification}</strong></td>
	</tr>
	<tr>
		<td class="tcat">{$lang->my2fa_verification_description}</td>
	</tr>
	{$verificationMethodRows}
	{$verificationExtraRows}
</table>