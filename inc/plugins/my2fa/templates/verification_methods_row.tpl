<tr data-my2fa-method="{$method['id']}">
	<td class="trow1">
		<form action="{$verificationUrl}&method={$method['id']}" method="post">
            <input type="hidden" name="verify" value="1" />
			<input type="hidden" name="redirect_url" value="{$redirectUrl}" />
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<input type="submit" class="my2fa__button my2fa__button--link my2fa__button--verify" value="{$method['definitions']['name']}" />
		</form>
		<div class="smalltext">{$method['definitions']['description']}</div>
	</td>
</tr>