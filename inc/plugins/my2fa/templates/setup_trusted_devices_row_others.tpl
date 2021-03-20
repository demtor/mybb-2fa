<tr>
	<td class="trow2">
		{$lang->my2fa_setup_other_trusted_devices}
		<table class="my2fa__other-trusted-devices-log">
			<caption>{$lang->my2fa_setup_other_trusted_devices_log}</caption>
			<tr>
				<th>{$lang->my2fa_setup_other_trusted_devices_log_generation}</th>
				<th>{$lang->my2fa_setup_other_trusted_devices_log_expiry}</th>
			</tr>
			{$otherTrustedDevicesLogRows}
		</table>
		<form action="{$setupUrl}" method="get">
			<input type="hidden" name="action" value="my2fa" />
			<input type="hidden" name="remove_trusted_devices" value="1" />
			<input type="hidden" name="others" value="1" />
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<input type="submit" class="my2fa__remove-trusted-devices-button my2fa__remove-trusted-devices-button--others button" value="{$lang->my2fa_setup_remove_other_trusted_devices}" data-my2fa-form-confirm="{$lang->my2fa_setup_remove_other_trusted_devices_confirmation}" />
		</form>
	</td>
</tr>