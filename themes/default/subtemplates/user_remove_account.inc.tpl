{config_load file=$language_file section="remove_user_account"}
<h1>{#remove_user_account_h1#}</h1>
<p class="notice caution">{#remove_user_account_warning#|replace:"[user_name]":$user_name}</p>
{if $errors}
<p class="notice caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
 {assign var="error" value=$errors[mysec]}
 <li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
 <form action="index.php" method="post" accept-charset="{#charset#}">
  <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
  <input type="hidden" name="mode" value="user" />
  <input type="hidden" name="action" value="edit_profile" />
  <div>
   <button name="remove_account_submit" value="{#submit_button_ok#}">{#submit_button_ok#}</button>
   <button type="reset" value="{#submit_button_cancel#}">{#submit_button_cancel#}</button>
  </div>
 </form>
 <div>
  <label for="password">{#remove_user_confirm_password#}</label>
  <input id="password" type="password" name="user_password" spellcheck="false" autocomplete="off" writingsuggestions="false" size="25" autofocus required />
 </div>
