{config_load file=$language_file section="edit_pw"}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[characters]":$settings.min_pw_length}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="user" />
<p><strong>{#edit_pw_old#}</strong><br />
<input type="password" size="25" name="old_pw" /></p>
<p><strong>{#edit_pw_new#}</strong><br />
<input type="password" size="25" name="new_pw" maxlength="255" /></p>
<p><strong>{#edit_pw_conf#}</strong><br />
<input type="password" size="25" name="new_pw_conf" maxlength="255" /></p>
<p><input type="submit" name="edit_pw_submit" value="{#submit_button_ok#}" /></p>
</div>
</form>
