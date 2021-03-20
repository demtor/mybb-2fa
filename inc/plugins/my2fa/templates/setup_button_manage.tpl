<form action="{$setupUrl}&method={$method['id']}" method="post">
	<input type="hidden" name="manage" value="1" />
	<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
	<input type="submit" class="my2fa__method-button my2fa__method-button--manage button" value="{$lang->my2fa_manage_button}" />
</form>