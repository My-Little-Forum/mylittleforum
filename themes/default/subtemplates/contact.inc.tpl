{config_load file=$language_file section="contact"}
{if $captcha}{config_load file=$language_file section="captcha"}{/if}
{if $error_message}
<p class="notice caution">{$smarty.config.$error_message}</p>
{elseif $sent}
<p class="notice ok">{#email_sent#}</p>
{else}
<h1>{if $recipient_name}{$smarty.config.contact_user_hl|replace:"[recipient_name]":"$recipient_name"}{else}{#contact_hl#}{/if}</h1>
{if $errors}
<p class="notice caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[text_length]":$text_length|replace:"[text_maxlength]":$settings.email_text_maxlength|replace:"[not_accepted_word]":$not_accepted_word|replace:"[not_accepted_words]":$not_accepted_words}</li>
{/section}
</ul>
{/if}
 <form action="index.php" method="post" accept-charset="{#charset#}" id="postingform">
  <input type="hidden" name="mode" value="contact" />
{if $id}  <input type="hidden" name="id" value="{$id}" />{/if}
{if $recipient_user_id}  <input type="hidden" name="recipient_user_id" value="{$recipient_user_id}" />{/if}
{if $session}  <input type="hidden" name="{$session.name}" value="{$session.id}" />{/if}
  <fieldset id="message">
{if not $user_id}
   <div>
    <label for="sender_email" class="input">{#sender_address_caption#}</label>
    <input id="sender_email" type="email" name="sender_email" value="" size="50" required />
   </div>
{/if}
   <div>
    <label for="subject" class="input">{#subject_caption#}</label>
    <input id="subject" type="text" name="subject" value="{$subject|default:""}" size="50" maxlength="{$settings.email_subject_maxlength}" required />
   </div>
   <div id="entry-input">
    <label for="message" class="input">{#message_caption#}</label>
    <textarea id="message" name="text" rows="20" cols="80" required>{$text|default:""}</textarea>
   </div>
  </fieldset>
{if $captcha}
  <fieldset>
{if $captcha.type==2}
   <div>
    <span class="label-like">{#captcha_marking#}</span><br />
    <img class="captcha" src="modules/captcha/captcha_image.php?{$session.name}={$session.id}" alt="{#captcha_image_alt#}" width="180" height="40" /><br />
    <label for="captcha_code">{#captcha_expl_image#}</label>
    <input id="captcha_code" type="text" name="captcha_code" value="" size="10" />
   </div>
{else}
   <div>
    <span class="label-like">{#captcha_marking#}</span><br />
    <label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label>
    <input id="captcha_code" type="text" name="captcha_code" value="" size="5" />
   </div>
{/if}
  </fieldset>
{/if}
{if $user_id}
  <fieldset>
   <div>
    <input id="confirmation_email" type="checkbox" name="confirmation_email" value="1" /><label for="confirmation_email">{#sender_confirmation_caption#}</label>
   </div>
  </fieldset>
{/if}
  <fieldset>
    <div class="buttonbar">
     <button name="message_submit" value="{#message_submit_caption#}" onclick="document.getElementById('throbber-submit').removeAttribute('hidden');">{#message_submit_caption#}</button>
     <img id="throbber-submit" src="{$THEMES_DIR}/{$theme}/images/throbber.svg" alt="" width="18" height="18" hidden />
    </div>
  </fieldset>
 </form>
{/if}