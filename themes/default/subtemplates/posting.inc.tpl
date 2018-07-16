{config_load file=$language_file section="posting"}
{config_load file=$language_file section="thread_entry"}
{if $captcha}{config_load file=$language_file section="captcha"}{/if}
{if $no_authorisation}
<p class="caution">{$smarty.config.$no_authorisation|replace:"[minutes]":$settings.edit_period}</p>
{if $text}
<textarea onfocus="this.select()" onclick="this.select()" readonly="readonly" cols="80" rows="21" name="text">{$text}</textarea>
{/if}
{else}
<h1>{if $posting_mode==0 && $id==0}{#new_topic_hl#}{elseif $posting_mode==0 && $id>0}{#reply_hl#}{elseif $posting_mode==1}{#edit_hl#}{/if}</h1>
{if $posting_mode==0 && $id>0 && $name_repl_subnav}
<p id="reply-to">{#reply_to_posting_marking#|replace:"[name]":$name_repl_subnav}</p>
{/if}

{if $errors}
<p class="caution">{#error_headline#}</p>
<ul style="margin-bottom:25px;">
{section name=mysec loop=$errors}
<li>{assign var="error" value=$errors[mysec]}{$smarty.config.$error|replace:"[text_length]":$text_length|replace:"[text_maxlength]":$settings.text_maxlength|replace:"[word]":$word|replace:"[minutes]":$minutes|replace:"[not_accepted_word]":$not_accepted_word|replace:"[not_accepted_words]":$not_accepted_words}</li>
{/section}
</ul>
{elseif isset($minutes_left_to_edit)}
<p class="caution">{if $settings.user_edit_if_no_replies==1}{#minutes_left_to_edit_reply#|replace:"[minutes]":$minutes_left_to_edit}{else}{#minutes_left_to_edit#|replace:"[minutes]":$minutes_left_to_edit}{/if}</p>
{/if}

{if $preview}
{if $preview_hp && !$email}
{assign var=email_hp value=" <a href=\"$preview_hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" alt=\"$homepage_alt\" width=\"13\" height=\"13\" /></a>"}
{elseif !$preview_hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$id\"><img src=\"$THEMES_DIR/$theme/images/email.png\" alt=\"$email_alt\" width=\"13\" height=\"10\" /></a>"}
{elseif $preview_hp && $email}
{assign var=email_hp value=" <a href=\"$preview_hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" alt=\"$homepage_alt\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$id\"><img src=\"$THEMES_DIR/$theme/images/email.png\" alt=\"$email_alt\" width=\"13\" height=\"10\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}
<h3 class="preview">{#preview_headline#}</h3>
<div class="preview">
<div class="posting">
<h1 class="postingheadline">{$preview_subject}{if $category_name} <span class="category">({$category_name})</span>{/if}</h1>
<p class="author">{if $preview_location}{#posted_by_location#|replace:"[name]":$preview_name|replace:"[email_hp]":$email_hp|replace:"[location]":$preview_location|replace:"[time]":$preview_formated_time}{else}{#posted_by#|replace:"[name]":$preview_name|replace:"[email_hp]":$email_hp|replace:"[time]":$preview_formated_time}{/if}</p>
{if $preview_text}{$preview_text}{else}<p>{#no_text#}</p>{/if}
{if $preview_signature && $show_signature==1}
<p class="signature">---<br />
{$preview_signature}</p>
{/if}
</div>
</div>
{/if}
<form action="index.php" method="post" id="postingform" accept-charset="{#charset#}">
<div>
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
<input type="hidden" name="back" value="{$back}" />
<input type="hidden" name="mode" value="{$mode}" />
<input type="hidden" name="id" value="{$id}" />
<input type="hidden" name="uniqid" value="{$uniqid}" />
<input type="hidden" name="posting_mode" value="{$posting_mode}" />
{if $session}
<input type="hidden" name="{$session.name}" value="{$session.id}" />
{/if}

{if $quote}
<input type="hidden" id="quote" value="true" />
{/if}
{if $form_type==0}
<input type="hidden" id="name_required" value="true" />
{/if}
{if !$settings.empty_postings_possible}
<input type="hidden" id="text_required" value="true" />
{/if}
{if $terms_of_use_agreement}
<input type="hidden" id="terms_of_use_required" value="true" />
{/if}
{if $data_privacy_agreement}
<input type="hidden" id="data_privacy_agreement_required" value="true" />
{/if}

{if $form_type==0}
<fieldset>

<p>
<label for="name" class="input">{#name_marking#}</label>
<input id="name" type="text" size="40" name="{$fld_user_name}" value="{if $name}{$name}{/if}" maxlength="{$settings.username_maxlength}"  tabindex="1" />
</p>

<p>
<label for="email" class="input">{#email_marking#}</label>
<input id="email" type="text" size="40" name="{$fld_user_email}" value="{if $email}{$email}{/if}" maxlength="{$settings.email_maxlength}" tabindex="2" />&nbsp;<span class="xsmall">{#optional_email#}</span>
</p>

<p class="hp">
<label for="repeat_email" class="main">{#honeypot_field_marking#}</label>
<input id="repeat_email" type="text" size="40" name="{$fld_repeat_email}" value="{if $honey_pot_email}{$honey_pot_email}{/if}" maxlength="{$settings.email_maxlength}" tabindex="-1" />
</p>

<p>
<label for="hp" class="input">{#hp_marking#}</label>
<input id="hp" type="text" size="40" name="{$fld_hp}" value="{if $hp}{$hp}{/if}" maxlength="{$settings.hp_maxlength}" tabindex="3" />&nbsp;<span class="xsmall">{#optional#}</span>
</p>

<p class="hp">
<label for="phone" class="main">{#honeypot_field_marking#}</label>
<input id="phone" class="login" type="text" size="30" name="{$fld_phone}" value="{if $honey_pot_phone}{$honey_pot_phone}{/if}" maxlength="35" tabindex="-1" />
</p>

<p>
<label for="location" class="input">{#location_marking#}</label>
<input id="location" type="text" size="40" name="{$fld_location}" value="{if $location}{$location}{/if}" maxlength="{$settings.location_maxlength}" tabindex="4" />&nbsp;<span class="xsmall">{#optional#}</span>
</p>

{if $settings.remember_userdata == 1 && $posting_mode==0 && !$user}
<p>
<input id="setcookie" class="checkbox" type="checkbox" name="setcookie" value="1"{if $setcookie} checked="checked"{/if} />&nbsp;<label for="setcookie">{#remember_userdata_marking#}</label>{if $cookie} &nbsp;<span id="delete_cookie"><a href="index.php?mode=delete_cookie">{#delete_cookie_linkname#}</a></span>{/if}
</p>
{/if}

</fieldset>
{/if}

<fieldset>
{if $categories}
	<p><label for="p_category" class="input">{#category_marking#}</label>
	<select id="p_category" size="1" name="p_category" tabindex="5"{if $posting_mode==0 && $id>0 || $posting_mode==1 && $pid>0} disabled="disabled"{/if}>
		{foreach key=key item=val from=$categories}
			{if $key!=0}<option value="{$key}"{if $key==$p_category} selected="selected"{/if}>{$val}</option>{/if}
		{/foreach}
	</select></p>
	{if $posting_mode==0 && $id>0 || $posting_mode==1 && $pid>0}
		<input type="hidden" name="p_category" value="{$p_category}" />
	{/if}
{/if}

<p><label for="subject" class="input">{#subject_marking#}</label>
<input id="subject" type="text" size="50" name="{$fld_subject}" value="{if $subject}{$subject}{/if}" maxlength="{$settings.subject_maxlength}" tabindex="6" />
</p>

{if ($admin ||$mod) && $settings.tags}
<p>
<label for="tags" class="input">{#tags_marking#}</label>
<input id="tags" type="text" size="50" name="tags" value="{$tags|default:""}" maxlength="253" tabindex="-1" />&nbsp;<span class="xsmall">{#tags_note#}</span>
</p>
{/if}
</fieldset>

<fieldset id="message">
<label for="text" class="textarea">{#message_marking#}</label><br />

<textarea id="text" cols="80" rows="21" name="text" tabindex="7">{if $text}{$text}{/if}</textarea>

<div id="format-bar">
{if $settings.bbcode}
<div id="bbcode-bar">
{*<!--
Here you can insert custom BBCode buttons. If you leave this div empty
the default buttons will be inserted. Example button:
<button title="Foo bar!" name="foo">Foo</button>
-->*}
</div>
{if $settings.smilies && $smilies}
<div id="smiley-bar">
{*<!--
Like custom BBCode buttons, example: 
<button title="Insert smiley" name=":-)"><img src="..." /></button>
-->*}
</div>
{/if}

{*<!--
This list is read out to generte the default BBCode buttons or displayed if
JavaScript isn't available. 
-->*}
<dl id="bbcode-instructions">
<dt id="b" title="{#bbcode_bold_label#}">{#bbcode_bold_title#}</dt>
<dd>{#bbcode_bold_instruction#}</dd>
<dt id="i" title="{#bbcode_italic_label#}">{#bbcode_italic_title#}</dt>
<dd>{#bbcode_italic_instruction#}</dd>
<dt id="link" title="{#bbcode_link_label#}">{#bbcode_link_title#}</dt>
<dd>{#bbcode_link_instruction#}</dd>
{if $settings.bbcode_color}
<dt id="color" title="{#bbcode_color_label#}">{#bbcode_color_title#}</dt>
<dd>{#bbcode_color_instruction#}</dd>
{/if}
{if $settings.bbcode_size}
<dt id="size" title="{#bbcode_size_label#}">{#bbcode_size_title#}</dt>
<dd id="small" title="{#bbcode_size_label_small#}">{#bbcode_size_instruction_small#}</dd>
<dd id="large" title="{#bbcode_size_label_large#}">{#bbcode_size_instruction_large#}</dd>
{/if}
<dt id="list" title="{#bbcode_list_label#}">{#bbcode_list_title#}</dt>
<dd>{#bbcode_list_instruction#}</dd>
{if $settings.bbcode_img}
<dt id="img" title="{#bbcode_image_label#}">{#bbcode_image_title#}</dt>
<dd title="{#bbcode_image_label_default#}">{#bbcode_image_instr_default#}</dd>
<dd id="left" title="{#bbcode_image_label_left#}">{#bbcode_image_instr_left#}</dd>
<dd id="right" title="{#bbcode_image_label_right#}">{#bbcode_image_instr_right#}</dd>
<dd id="thumbnail" title="{#bbcode_image_label_thumb#}">{#bbcode_image_instr_thumb#}</dd>
<dd id="thumbnail-left" title="{#bbcode_image_label_thumb_left#}">{#bbcode_image_instr_thumb_left#}</dd>
<dd id="thumbnail-right" title="{#bbcode_image_label_thumb_right#}">{#bbcode_image_instr_thumb_right#}</dd>
{/if}
{if $upload_images}
<dt id="upload" title="{#bbcode_upload_label#}">{#bbcode_upload_title#}</dt>
<dd><a href="index.php?mode=upload_image">{#bbcode_upload_instruction#}</a></dd>
{/if}
{if $settings.bbcode_latex && $settings.bbcode_latex_uri}
<dt id="tex" title="{#bbcode_tex_label#}">{#bbcode_tex_title#}</dt>
<dd>{#bbcode_tex_instruction#}</dd>
{/if}
{if $settings.bbcode_code}
<dt id="code" title="{#bbcode_code_label#}">{#bbcode_code_title#}</dt>
<dd id="inlinecode" title="{#bbcode_code_label_inline#}">{#bbcode_code_instruction_inline#}</dd>
<dd title="{#bbcode_code_label_general#}">{#bbcode_code_instruction_general#}</dd>
{if $code_languages}
{foreach from=$code_languages item=code_language}
<dd id="{$code_language}" title="{#bbcode_code_label_specific#|replace:"[language]":$code_language}">{#bbcode_code_instruction_spec#|replace:"[language]":$code_language}</dd>
{/foreach}
{/if}
{/if}
</dl>
{/if}

{if $settings.smilies && $smilies}
<dl id="smiley-instructions">
{foreach name="smilies" from=$smilies item=smiley}
<dt class="{if $smarty.foreach.smilies.index<6}default{else}additional{/if}" title="{#insert_smiley_title#}">{$smiley.code}</dt>
<dd><img src="images/smilies/{$smiley.file}" alt="{$smiley.code}" /></dd>
{/foreach}
</dl>
{/if}

</div>
</fieldset>

{if $signature || $provide_email_notification || $provide_sticky || $terms_of_use_agreement || $data_privacy_agreement}
<fieldset>
{if $signature}
<p>
<input id="show_signature" type="checkbox" name="show_signature" value="1"{if $show_signature && $show_signature==1} checked="checked"{/if} />&nbsp;<label for="show_signature">{#show_signature_marking#}</label>
</p>
{/if}

{if $provide_email_notification}
<p>
<input id="email_notification" type="checkbox" name="email_notification" value="1"{if $email_notification && $email_notification==1} checked="checked"{/if} />&nbsp;<label for="email_notification">{if $id==0}{#email_notific_reply_thread#}{else}{#email_notific_reply_post#}{/if}</label>
</p>
{/if}

{if $provide_sticky}
<p>
<input id="sticky" type="checkbox" name="sticky" value="1"{if $sticky && $sticky==1} checked="checked"{/if} />&nbsp;<label for="sticky">{#sticky_thread#}</label>
</p>
{/if}

{if $terms_of_use_agreement}
{assign var=terms_of_use_url value=$settings.terms_of_use_url}
<p>
<input id="terms_of_use_agree" tabindex="8" type="checkbox" name="terms_of_use_agree" value="1"{if $terms_of_use_agree && $terms_of_use_agree==1} checked="checked"{/if} />&nbsp;<label for="terms_of_use_agree">{if $terms_of_use_url}{#terms_of_use_agreement#|replace:"[[":"<a id=\"terms_of_use\" href=\"$terms_of_use_url\">"|replace:"]]":"</a>"}{else}{#terms_of_use_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label>
</p>
{/if}
{if $data_privacy_agreement}
{assign var=data_privacy_statement_url value=$settings.data_privacy_statement_url}
<p>
<input id="data_privacy_statement_agree" tabindex="9" type="checkbox" name="data_privacy_statement_agree" value="1"{if $data_privacy_statement_agree && $data_privacy_statement_agree==1} checked="checked"{/if} />&nbsp;<label for="data_privacy_statement_agree">{if $data_privacy_statement_url}{#data_privacy_agreement#|replace:"[[":"<a id=\"data_privacy_statement\" href=\"$data_privacy_statement_url\">"|replace:"]]":"</a>"}{else}{#data_privacy_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label>
</p>
{/if}
</fieldset>
{/if}

{if $captcha}
<fieldset>
<legend>{#captcha_marking#}</legend>
{if $captcha.type==2}
<p><img class="captcha" src="modules/captcha/captcha_image.php?{$session.name}={$session.id}" alt="{#captcha_image_alt#}" width="180" height="40" /><br />
<label for="captcha_code">{#captcha_expl_image#}</label><br />
<input id="captcha_code" type="text" name="captcha_code" value="" size="10" tabindex="9" /></p>
{else}
<p><label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label><input id="captcha_code" type="text" name="captcha_code" value="" size="5" maxlength="5" tabindex="10" /></p>
{/if}
</fieldset>
{/if}

<fieldset>
<p><input type="submit" name="save_entry" value="{#message_submit_button#}" title="{#message_submit_title#}" tabindex="11" />&nbsp;<input type="submit" name="preview" value="{#message_preview_button#}" title="{#message_preview_title#}" tabindex="11" /> <img id="throbber-submit" class="js-visibility-hidden" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</fieldset>

</div>
</form>
{/if}
