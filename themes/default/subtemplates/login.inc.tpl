{config_load file=$language_file section="login"}
{if $ip_temporarily_blocked}
{#login_message#}
<p class="caution">{#login_ip_temp_blocked#}</p>
{else}
{if $login_message && $smarty.config.$login_message}
<p class="{if $login_message=='account_activated' || $login_message=='mail_sent' || $login_message=='pw_sent'}ok{else}caution{/if}">{$smarty.config.$login_message}</p>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="{$mode}" />
{if $back}<input type="hidden" name="back" value="{$back}" />{/if}
{if $id}<input type="hidden" name="id" value="{$id}" />{/if}
<p><label for="login" class="main">{#login_username#}</label><br /><input id="login" class="login" type="text" name="username" size="25" /></p>
<p><label for="password" class="main">{#login_password#}</label><br /><input id="password" class="login" type="password" name="userpw" size="25" /></p>
{if $settings.autologin==1}
<p class="small"><input id="autologin" type="checkbox" name="autologin_checked" value="true" /> <label for="autologin">{#login_auto#}</label></p>
{/if}
<p><input type="submit" value="{#login_submit#}" /></p>
</div>
</form>
<p class="small">{#login_advice#}</p>
<p class="small"><a href="index.php?mode=login&amp;action=pw_forgotten">{#pw_forgotten_link#}</a></p>
{/if}
