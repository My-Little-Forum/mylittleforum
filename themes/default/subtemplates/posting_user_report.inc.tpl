{if $no_authorisation}
	<p class="caution">{#no_authorisation#}</p>
{else}
	<h2>{#report_posting_hl#}</h2>
	{if !$id}
		<p>{#postings_doesnt_exist#}</p>
	{else}
		<p class="caution">{#caution#}</p>
		<p>{#report_posting_warning#}</p>
		<p><strong>{$subject}</strong> - <strong>{$name}</strong>, {$disp_time|date_format:#time_format#}</p>
		<form action="index.php" method="post" accept-charset="{#charset#}">
		<div>
			<input type="hidden" name="mode" value="posting" />
			<input type="hidden" name="id" value="{$id}" />
			<input type="hidden" name="reporter" value="{$user_id}" />
			<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
{if $back}			<input type="hidden" name="back" value="{$back}" />
{/if}
			<select size="1" name="posting_report_reason">
{foreach $report_reasons as $reason}
				<option value="{$reason.id}">{$reason.val}</option>
{/foreach}
			</select>
			<input type="submit" name="report_posting_submit" value="{#report_posting_submit#}" />
		</div>
		</form>
	{/if}
{/if}
