{config_load file=$language_file section="delete_posting"}
{config_load file=$language_file section="release_posting"}
{if $no_authorisation}
	<p class="notice caution">{#release_no_authorisation#}</p>
{else}
	<h1>{#release_moderation_hl#}</h1>
	{if !$id}
		<p class="notice caution">{#postings_doesnt_exist#}</p>
	{else}
		<p class="notice caution">{#caution#}</p>
		<p>{#release_moderation_explanation#}</p>
		<p><strong>{$subject}</strong> - <strong>{$name}</strong>, {$formated_time}</p>
		<form action="index.php" method="post" accept-charset="{#charset#}">
			<input type="hidden" name="mode" value="posting" />
			<input type="hidden" name="back" value="{$back}" />
			<input type="hidden" name="id" value="{$id}" />
			<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
			<div class="buttonbar">
				<button name="release_posting_submit" value="{#release_submit#}">{#release_submit#}</button>
{if $settings.b8_entry_check==1}				<button name="release_posting_submit with_training" value="{#release_submit_and_train#}">{#release_submit_and_train#}</button>{/if}
			</div>
		</form>
	{/if}
{/if}
