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
	<div style="text-align:center">
		<input type="hidden" name="activate" value="1" />
		<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
		<a href="{$setupUrl}" class="my2fa__cancel-button">{$lang->my2fa_cancel_button}</a>
		<input type="submit" class="my2fa__confirm-button button" name="request" value="{$lang->my2fa_request_button}" />
	</div>
</form>