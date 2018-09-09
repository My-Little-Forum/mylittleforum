{config_load file=$language_file section="unsubscribe_posting"}
{if $unsubscribe_status === true}
<h2>{#unsubscribed_hl#}</h2>
<p>{#unsubscribed_message#}</p>
{else}
<h2>{#unsubscribe_error_hl#}</h2>
<p class="caution">{#unsubscribe_error_message#}</p>
{/if}
