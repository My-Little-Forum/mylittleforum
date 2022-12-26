{if $no_authorisation}
<p class="notice caution">{#no_authorisation#}</p>
{elseif $page}
{$page.content}
{else}
<p class="notice caution">{#page_doesnt_exist#}</p>
{/if}
