{config_load file=$language_file section="manage_postings"}
{if $no_authorisation}
<p class="caution">{$smarty.config.$no_authorisation}</p>
{else}
{assign var='input_days' value='</label><input type="text" name="days" value="" size="5" />'}
<h1>{#manage_postings_hl#}</h1>
<form action="index.php" method="post" accept-charset="{#charset#}">
<fieldset class="manage-postings">
<legend><img src="{$THEMES_DIR}/{$theme}/images/marked.png" width="11" height="11" alt="" /> {#mark_postings#}</legend>
<input type="hidden" name="mode" value="posting" />
<p><input id="mark_mode_1" type="radio" name="mark_mode" value="1" /><label for="mark_mode_1">{$smarty.config.mark_old_threads|replace:"[days]":$input_days}<br />
<input id="mark_mode_2" type="radio" name="mark_mode" value="2" /><label for="mark_mode_2">{#mark_all_postings#}</label><br />
<input id="mark_mode_3" type="radio" name="mark_mode" value="3" /><label for="mark_mode_3">{#unmark_all_postings#}</label></p>
<p><input type="submit" name="mark_submit" value="{#submit_button_ok#}" /></p>
</fieldset>
</form>

<form action="index.php" method="post" accept-charset="{#charset#}">
<fieldset class="manage-postings">
<legend><img src="{$THEMES_DIR}/{$theme}/images/locked.png" width="14" height="12" alt="" /> {#lock_postings#}</legend>
<input type="hidden" name="mode" value="posting" />
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
<p><input id="lock_mode_1" type="radio" name="lock_mode" value="1" /><label for="lock_mode_1">{$smarty.config.lock_old_threads|replace:"[days]":$input_days}<br />
<input id="lock_mode_2" type="radio" name="lock_mode" value="2" /><label for="lock_mode_2">{#lock_all_postings#}</label><br />
<input id="lock_mode_3" type="radio" name="lock_mode" value="3" /><label for="lock_mode_3">{#unlock_all_postings#}</label></p>
<p><input type="submit" name="lock_submit" value="{#submit_button_ok#}" /></p>
</fieldset>
</form>
{/if}
