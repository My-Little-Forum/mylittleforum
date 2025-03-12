{config_load file=$language_file section="login"}
{if $ip_temporarily_blocked}
{#login_message#}
 <p class="notice caution">{#login_ip_temp_blocked#}</p>
{else}
{if $login_message && $smarty.config.$login_message}
 <p class="notice {if $login_message=='account_activated' || $login_message=='mail_sent' || $login_message=='pw_sent'}ok{else}caution{/if}">{$smarty.config.$login_message}</p>
{/if}
 <form action="index.php" method="post" accept-charset="{#charset#}">
  <input type="hidden" name="mode" value="{$mode}" />
{if $back}  <input type="hidden" name="back" value="{$back}" />{/if}
{if $id}  <input type="hidden" name="id" value="{$id}" />{/if}
  <div>
   <label for="login" class="main">{#login_username#}</label>
   <input id="login" class="login" type="text" name="username" size="25" required />
  </div>
  <div>
   <label for="password" class="main">{#login_password#}</label>
   <input id="password" class="login" type="password" name="userpw" spellcheck="false" autocomplete="off" writingsuggestions="false" size="25" required />
  </div>
 <div id="card">
{if $settings.autologin==1}
  <div class="small">
   <input id="autologin" type="checkbox" name="autologin_checked" value="true" /><label for="autologin">{#login_auto#}</label>
  </div>
{/if}
  <div>
   <button value="{#login_submit#}">{#login_submit#}</button>
  </div>
 </form>
 <p class="small">{#login_advice#}</p>
 <p class="small"><a href="index.php?mode=login&amp;action=pw_forgotten">{#pw_forgotten_link#}</a></p>
 </div>
{/if}
