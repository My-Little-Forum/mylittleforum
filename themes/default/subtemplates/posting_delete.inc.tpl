{config_load file=$language_file section="delete_posting"}
{if $no_authorisation}
<p class="caution">{$smarty.config.$no_authorisation}</p>
{else}
<h1>{#delete_postings_hl#}</h1>
<p class="caution">{#caution#}</p>
<p>{if $admin||$mod}{#delete_posting_replies_confirm#}{else}{#delete_posting_confirm#}{/if}</p>
<p><strong>{$subject}</strong> - <strong>{$name}</strong>, {$formated_time}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="posting" />
<input type="hidden" name="delete_posting" value="{$id}" />
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
{if $back}<input type="hidden" name="back" value="{$back}" />{/if}
{if $page}<input type="hidden" name="page" value="{$page}" />{/if}
{if $category}<input type="hidden" name="category" value="{$category}" />{/if}
<input type="submit" name="delete_posting_confirm" value="{#delete_posting_submit#}" />
</div>
</form>
{/if}
