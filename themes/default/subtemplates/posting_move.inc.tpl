{config_load file=$language_file section="move_posting"}
{if $no_authorisation}
<p class="caution">{$smarty.config.$no_authorisation}</p>
{else}
{assign var="input_move_to" value="<input type=\"text\" name=\"move_to\" value=\"\" size=\"5\" onclick=\"document.getElementById('move_mode_1').checked=false; document.getElementById('move_mode_1').checked='checked'; \" />"}
<h1>{#move_posting_hl#}</h1>
{if $errors}{include file="$theme/subtemplates/errors.inc.tpl"}{/if}
<p><strong>{$subject}</strong> - <strong>{$name}</strong>, {$formated_time}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="posting" />
<input type="hidden" name="move_posting" value="{$move_posting}" />
<input type="hidden" name="back" value="{$back}" />
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
{if $posting_type==0}
{assign var="input_move_to" value="<input type=\"text\" name=\"move_to\" value=\"\" size=\"5\" />"}
<input type="hidden" name="move_mode" value="1" />
<p>{#move_posting#|replace:"[number]":$input_move_to}</p>
{else}
{assign var="input_move_to" value="<input type=\"text\" name=\"move_to\" value=\"\" size=\"5\" onclick=\"document.getElementById('move_mode_1').checked=false; document.getElementById('move_mode_1').checked='checked'; \" />"}
<p><input id="move_mode_0" type="radio" name="move_mode" value="0" checked="checked" /> <!--<label for="move_mode_0">-->{#move_posting_new_thread#}<!--</label>--><br />
<input id="move_mode_1" type="radio" name="move_mode" value="1" /> <!--<label for="move_mode_1">-->{#move_posting#|replace:"[number]":$input_move_to}<!--</label>--></p>
{/if}
<p><input type="submit" name="move_posting_submit" value="{#move_posting_submit#}" /></p>
</div>
</form>
{/if}
