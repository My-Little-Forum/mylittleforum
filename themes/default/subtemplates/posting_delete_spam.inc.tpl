{config_load file=$language_file section="delete_posting"}
{if $no_authorisation}
 <p class="notice caution">{#no_authorisation#}</p>
{else}
 <h1>{#delete_spam_hl#}</h1>
 <p class="notice caution">{#caution#}</p>
 <p>{#delete_spam_confirm#}</p>
 <form action="index.php" method="post" accept-charset="{#charset#}">
  <input type="hidden" name="mode" value="posting" />
  <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
  <div>
   <button name="delete_spam_submit" value="{#delete_posting_submit#}">{#delete_posting_submit#}</button>
  </div>
 </form>
{/if}
