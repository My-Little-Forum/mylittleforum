{config_load file=$language_file section="delete_posting"}
{if $no_authorisation}
 <p class="notice caution">{#no_authorisation#}</p>
{else}
 <h1>{#flag_ham_hl#}</h1>
{if !$id}
 {#postings_doesnt_exist#}
{elseif $akismet_spam == 0 && $akismet_spam_check_status == 1 && $b8_spam == 0 && $b8_training_type == 1}
 {#posting_not_flagged_as_spam#}
{else}
 <p class="notice info">{#caution#}</p>
 <p>{#flag_ham_warning#}</p>
 <p><span class="subject">{$subject}</span> - <span class="metadata"><span class="author-name">{$name}</span>, <span class="tail"><time datetime="{$ISO_time}">{$formated_time}</time></span></span></p>
 <form action="index.php" method="post" accept-charset="{#charset#}">
  <input type="hidden" name="mode" value="posting" />
  <input type="hidden" name="id" value="{$id}" />
  <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
  <div class="buttonbar">
   <button name="report_flag_ham_submit" value="{#report_flag_ham_submit#}">{#report_flag_ham_submit#}</button>
   <button name="flag_ham_submit" value="{#flag_ham_submit#}">{#flag_ham_submit#}</button>
  </div>
 </form>
{/if}
{/if}
