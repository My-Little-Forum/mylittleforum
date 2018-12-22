{config_load file=$language_file section="remove_user_account"}
<h1 class="caution">{#remove_user_account_h1#}</h1>
<p>{#remove_user_account_warning#|replace:"[user_name]":$user_name}</p>
<div>
	<form action="index.php" method="post" accept-charset="{#charset#}">
		<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
		<input type="hidden" name="mode" value="user" />
		<input type="hidden" name="action" value="edit_profile" />
		<input type="submit" name="remove_account_submit" value="{#submit_button_ok#}" />
		<input type="submit" value="{#submit_button_cancel#}">
	</form>
</div>