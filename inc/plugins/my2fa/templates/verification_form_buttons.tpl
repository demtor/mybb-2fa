<div style="text-align:center">
	<input type="hidden" name="verify" value="1" />
	<input type="hidden" name="redirect_url" value="{$redirectUrl}" />
	<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
	<a href="{$verificationUrl}{$redirectUrlQueryStr}" class="my2fa__cancel-button">{$lang->my2fa_cancel_button}</a>
	<input type="submit" class="my2fa__confirm-button button" value="{$lang->my2fa_confirm_button}" />
</div>