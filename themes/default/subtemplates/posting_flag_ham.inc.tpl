{config_load file=$language_file section="delete_posting"}
{if $no_authorisation}
<p class="caution">{#no_authorisation#}</p>
{else}
<h1>{#flag_ham_hl#}</h1>
{if !$id}
{#postings_doesnt_exist#}
{elseif $spam==0}
{#posting_not_flagged_as_spam#}
{else}
<p class="caution">{#caution#}</p>
<p>{#flag_ham_note#}</p>
<p><strong>{$subject}</strong> - <strong>{$name}</strong>, {$disp_time|date_format:#time_format#}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="posting" />
<input type="hidden" name="id" value="{$id}" />
<input type="submit" name="report_flag_ham_submit" value="{#report_flag_ham_submit#}" /> <input type="submit" name="flag_ham_submit" value="{#flag_ham_submit#}" />
</div>
</form>
{/if}
{/if}
