{if $no_authorisation}
<p class="caution">{#no_authorisation#}</p>
{elseif $page}
{$page.content}
{else}
<p class="caution">{#page_doesnt_exist#}</p>
{/if}
