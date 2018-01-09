{config_load file=$language_file section="contact"}
{if $captcha}{config_load file=$language_file section="captcha"}{/if}
{if $error_message}
<p class="caution">{$smarty.config.$error_message}</p>
{elseif $sent}
<p class="ok">{#email_sent#}</p>
{else}
<h1>{if $recipient_name}{$smarty.config.contact_user_hl|replace:"[recipient_name]":"$recipient_name"}{else}{#contact_hl#}{/if}</h1>
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[text_length]":$text_length|replace:"[text_maxlength]":$settings.email_text_maxlength|replace:"[not_accepted_word]":$not_accepted_word|replace:"[not_accepted_words]":$not_accepted_words}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="contact" />
{if $id}<input type="hidden" name="id" value="{$id}" />{/if}
{if $recipient_user_id}<input type="hidden" name="user_id" value="{$recipient_user_id}" />{/if}
{if $session}<input type="hidden" name="{$session.name}" value="{$session.id}" />{/if}
<p><label for="sender_email">{#sender_address_caption#}</label><br />
<input id="sender_email" type="text" name="sender_email" value="{$sender_email}" size="50" /></p>
<p><label for="subject">{#subject_caption#}</label><br />
<input id="subject" type="text" name="subject" value="{$subject|default:""}" size="50" maxlength="{$settings.email_subject_maxlength}" /></p>
<p><label for="message">{#message_caption#}</label><br />
<textarea id="message" name="text" rows="20" cols="80">{$text|default:""}</textarea></p>
{if $captcha}
{if $captcha.type==2}
<p><strong>{#captcha_marking#}</strong><br />
<img class="captcha" src="modules/captcha/captcha_image.php?{$session.name}={$session.id}" alt="{#captcha_image_alt#}" width="180" height="40" /><br />
<label for="captcha_code">{#captcha_expl_image#}</label> <input id="captcha_code" type="text" name="captcha_code" value="" size="10" /></p>
{else}
<p><strong>{#captcha_marking#}</strong><br />
<label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label><input id="captcha_code" type="text" name="captcha_code" value="" size="5" /></p>
{/if}
{/if}
<p><input type="submit" name="message_submit" value="{#message_submit_caption#}" onclick="document.getElementById('throbber-submit').classList.remove('js-visibility-hidden');" /> <img id="throbber-submit" class="js-visibility-hidden" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</div>
</form>
{/if}