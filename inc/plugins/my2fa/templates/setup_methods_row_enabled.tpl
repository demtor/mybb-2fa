<tr data-my2fa-method="{$method['publicName']}">
	<td class="trow1" width="100%">
		<strong>{$method['definitions']['name']}</strong>
		<span class="smalltext">($lang->my2fa_setup_method_activation_date)</span>
		<div class="smalltext">{$method['definitions']['description']}</div>
	</td>
	<td class="my2fa__control-buttons trow1">
		{$setupManageButton}
		{$setupDeactivateButton}
	</td>
</tr>