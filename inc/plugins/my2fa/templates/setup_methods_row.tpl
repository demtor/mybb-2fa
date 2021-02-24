<tr data-my2fa-method="{$method['publicName']}">
	<td class="trow1" width="100%">
		<strong>{$method['definitions']['name']}</strong>
		<div class="smalltext">{$method['definitions']['description']}</div>
	</td>
	<td class="my2fa__control-buttons trow1">
		<form action="{$setupUrl}&method={$method['publicName']}" method="post">
            <input type="hidden" name="activate" value="1" />
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<input type="submit" class="my2fa__button my2fa__button--activate button" value="{$lang->my2fa_activate_button}" />
		</form>
	</td>
</tr>