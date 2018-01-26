{config_load file=$language_file section="edit_email"}
<p class="caution">{#caution#}</p>
<p class="normal">{#edit_email_exp#}</p>
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
<input type="hidden" name="mode" value="user" />
<p><label for="new_email" class="main">{#edit_email_new#}</label><br />
<input id="new_email" type="text" size="25" name="new_email" value="{$new_user_email|default:''}" maxlength="{$settings.email_maxlength}" /></p>
<p><label for="new_email_confirm" class="main">{#edit_email_new_confirm#}</label><br />
<input id="new_email_confirm" type="text" size="25" name="new_email_confirm" value="" maxlength="{$settings.email_maxlength}" /></p>
<p><label for="pw_new_email" class="main">{#edit_email_pw#}</label><br />
<input id="pw_new_email" type="password" size="25" name="pw_new_email" /></p>
<p><input type="submit" name="edit_email_submit" value="{#submit_button_ok#}" /></p>
</div>
</form>
