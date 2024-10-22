{config_load file=$language_file section="edit_pw"}
{if $errors}
<p class="notice caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[characters]":$settings.min_pw_length|replace:"[digits]":$settings.min_pw_digits|replace:"[lowercase_letters]":$settings.min_pw_lowercase_letters|replace:"[uppercase_letters]":$settings.min_pw_uppercase_letters|replace:"[special_characters]":$settings.min_pw_special_characters}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
 <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
 <input type="hidden" name="mode" value="user" />
 <div>
  <label for="old-pw">{#edit_pw_old#}</label>
  <input type="password" spellcheck="false" autocomplete="off" writingsuggestions="false" size="25" name="old_pw" id="old-pw" autofocus required />
 </div>
 <div>
  <label for="new-pw">{#edit_pw_new#}</label>
  <input type="password" spellcheck="false" autocomplete="off" writingsuggestions="false" size="25" name="new_pw" id="new-pw" maxlength="255" required />
 </div>
  <button name="edit_pw_submit" value="{#submit_button_ok#}">{#submit_button_ok#}</button>
 </div>
</form>
