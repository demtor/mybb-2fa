<tr>
	<td class="trow1">
		{$lang->my2fa_setup_current_trusted_device}
		<form action="{$setupUrl}" method="get">
			<input type="hidden" name="action" value="my2fa" />
			<input type="hidden" name="remove_trusted_devices" value="1" />
			<input type="hidden" name="current" value="1" />
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<input type="submit" class="my2fa__remove-trusted-devices-button my2fa__remove-trusted-devices-button--current button" value="{$lang->my2fa_setup_remove_current_trusted_device}" data-my2fa-form-confirm="{$lang->my2fa_setup_remove_current_trusted_device_confirmation}" />
		</form>
	</td>
</tr>