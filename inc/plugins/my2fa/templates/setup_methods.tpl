<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="my2fa__setup-methods tborder">
	<tr>
		<td class="thead" colspan="2"><strong>{$lang->my2fa_title} - {$lang->my2fa_setup}</strong></td>
	</tr>
	<tr>
		<td class="tcat" colspan="2">{$lang->my2fa_description}</td>
	</tr>
	{$setupMethodRows}
</table>
<script>
	var setupDisableButtons = $('.my2fa__button--deactivate');
	setupDisableButtons.on('click', function(event) {
		event.preventDefault();
		var form = $(this).closest('form');

		MyBB.prompt('{$lang->my2fa_setup_deactivate_confirmation}', {
			buttons: [
				{title: yes_confirm, value: true},
				{title: no_confirm, value: false}
			],
			submit: function (e, v, m, f) {
				if (v)
					form.submit();
			}
		})
	})
</script>