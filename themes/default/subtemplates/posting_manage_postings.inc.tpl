{config_load file=$language_file section="manage_postings"}
{if $no_authorisation}
 <p class="notice caution">{$smarty.config.$no_authorisation}</p>
{else}
{assign var='input_days' value='</label><input type="text" name="days" value="" size="5" />'}
 <h1>{#manage_postings_hl#}</h1>
 <form action="index.php" method="post" accept-charset="{#charset#}">
  <input type="hidden" name="mode" value="posting" />
  <fieldset class="manage-postings">
   <legend><img class="icon" src="{$THEMES_DIR}/{$theme}/images/marker-active.svg" width="12" height="12" alt="" /><span>{#mark_postings#}</span></legend>
   <ul>
    <li><input id="mark_mode_1" type="radio" name="mark_mode" value="1" /><label for="mark_mode_1">{$smarty.config.mark_old_threads|replace:"[days]":$input_days}</li>
    <li><input id="mark_mode_2" type="radio" name="mark_mode" value="2" /><label for="mark_mode_2">{#mark_all_postings#}</label></li>
    <li><input id="mark_mode_3" type="radio" name="mark_mode" value="3" /><label for="mark_mode_3">{#unmark_all_postings#}</label></li>
   </ul>
   <div>
    <button name="mark_submit" value="{#submit_button_ok#}">{#submit_button_ok#}</button>
   </div>
  </fieldset>
 </form>

 <form action="index.php" method="post" accept-charset="{#charset#}">
  <input type="hidden" name="mode" value="posting" />
  <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
  <fieldset class="manage-postings">
   <legend><img class="icon" src="{$THEMES_DIR}/{$theme}/images/general-lock-closed.svg" width="12" height="12" alt="" /><span>{#lock_postings#}</span></legend>
   <ul>
    <li><input id="lock_mode_1" type="radio" name="lock_mode" value="1" /><label for="lock_mode_1">{$smarty.config.lock_old_threads|replace:"[days]":$input_days}</li>
    <li><input id="lock_mode_2" type="radio" name="lock_mode" value="2" /><label for="lock_mode_2">{#lock_all_postings#}</label></li>
    <li><input id="lock_mode_3" type="radio" name="lock_mode" value="3" /><label for="lock_mode_3">{#unlock_all_postings#}</label></li>
   </ul>
   <div>
    <button name="lock_submit" value="{#submit_button_ok#}">{#submit_button_ok#}</button>
   </div>
  </fieldset>
 </form>
{/if}
