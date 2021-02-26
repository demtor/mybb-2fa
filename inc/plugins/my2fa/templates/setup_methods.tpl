<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="my2fa__setup-methods tborder">
	<tr>
		<td class="thead" colspan="2"><strong>{$lang->my2fa_title} - {$lang->my2fa_setup}</strong></td>
	</tr>
	<tr>
		<td class="tcat" colspan="2">{$lang->my2fa_description}</td>
	</tr>
	{$setupMethodRows}
</table>
{$trustedDevices}
<script>
	var confirmationButtons = $('[data-my2fa-form-confirm]');
	confirmationButtons.on('click', function(event) {
		var form = $(this).closest('form');
		var message = $(this).data('my2fa-form-confirm');

		MyBB.prompt(message, {
			buttons: [
				{title: yes_confirm, value: true},
				{title: no_confirm, value: false}
			],
			submit: function (e, v, m, f) {
				if (v)
					form.submit();
			}
		})

		return false;
	})
</script>