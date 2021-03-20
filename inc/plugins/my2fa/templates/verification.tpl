<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="my2fa__verification-methods tborder">
	<tr>
		<td class="thead"><strong>{$lang->my2fa_title} - {$lang->my2fa_verification}</strong></td>
	</tr>
	<tr>
		<td class="tcat">{$lang->my2fa_verification_description}</td>
	</tr>
	<tr>
		<td class="trow1">{$lang->username} <strong>{$user['username_escaped']}</strong></td>
	</tr>
	{$verificationMethodRows}
	{$verificationExtraRows}
</table>