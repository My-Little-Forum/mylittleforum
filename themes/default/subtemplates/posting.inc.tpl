{config_load file=$language_file section="posting"}
{config_load file=$language_file section="user"}
{if $captcha}{config_load file=$language_file section="captcha"}{/if}
{if $no_authorisation}
<p class="notice caution">{$smarty.config.$no_authorisation|replace:"[minutes]":$settings.edit_period}</p>
{if $text}
<textarea onfocus="this.select()" onclick="this.select()" readonly="readonly" cols="80" rows="21" name="text">{$text}</textarea>
{/if}
{else}
<h1>{if $posting_mode==0 && $id==0}{#new_topic_hl#}{elseif $posting_mode==0 && $id>0}{#reply_hl#}{elseif $posting_mode==1}{#edit_hl#}{/if}</h1>
{if $posting_mode==0 && $id>0 && $name_repl_subnav}
<p id="reply-to">{#reply_to_posting_marking#|replace:"[name]":$name_repl_subnav}</p>
{/if}

{if $errors}
<p class="notice caution">{#error_headline#}</p>
<ul style="margin-bottom:25px;">
{section name=mysec loop=$errors}
 <li>{assign var="error" value=$errors[mysec]}{$smarty.config.$error|replace:"[text_length]":$text_length|replace:"[text_maxlength]":$settings.text_maxlength|replace:"[word]":$word|replace:"[minutes]":$minutes|replace:"[not_accepted_word]":$not_accepted_word|replace:"[not_accepted_words]":$not_accepted_words}</li>
{/section}
</ul>
{elseif isset($minutes_left_to_edit)}
<p class="notice caution">{if $settings.user_edit_if_no_replies==1}{#minutes_left_to_edit_reply#|replace:"[minutes]":$minutes_left_to_edit}{else}{#minutes_left_to_edit#|replace:"[minutes]":$minutes_left_to_edit}{/if}</p>
{/if}

{if $preview}
{if $preview_hp && !$email}
{assign var=email_hp value=" <a href=\"$preview_hp\"><img src=\"$THEMES_DIR/$theme/images/general-homepage.svg\" alt=\"{#homepage#}\" width=\"13\" height=\"13\" /></a>"}
{elseif !$preview_hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$id\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" alt=\"{#email#}\" width=\"13\" height=\"13\" /></a>"}
{elseif $preview_hp && $email}
{assign var=email_hp value=" <a href=\"$preview_hp\"><img src=\"$THEMES_DIR/$theme/images/general-homepage.svg\" alt=\"{#homepage#}\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$id\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" alt=\"{#email#}\" width=\"13\" height=\"13\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}
<h3 class="preview">{#preview_headline#}</h3>
<div class="preview">
<div class="posting">
<div class="header">
<h1 class="postingheadline">{$preview_subject}{if $category_name} <span class="category">({$category_name})</span>{/if}</h1>
<p class="author">{if $preview_location}{#posted_by_location#|replace:"[name]":$preview_name|replace:"[email_hp]":$email_hp|replace:"[location]":$preview_location}{else}{#posted_by#|replace:"[name]":$preview_name|replace:"[email_hp]":$email_hp}{/if}<time datetime="{$preview_ISO_time}">{$preview_formated_time}</time></p>
</div>
<div class="wrapper">
<div class="body">{if $preview_text}{$preview_text}{else}<p>{#no_text#}</p>{/if}</div>
{if $preview_signature && $show_signature==1}
<div class="signature"><p>---<br />
{$preview_signature}</p>
</div>
{/if}
</div>
</div>
</div>
{/if}
<form action="index.php" method="post" id="postingform" accept-charset="{#charset#}">
 <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
 <input type="hidden" name="back" value="{$back}" />
 <input type="hidden" name="mode" value="{$mode}" />
 <input type="hidden" name="id" value="{$id}" />
 <input type="hidden" name="uniqid" value="{$uniqid}" />
 <input type="hidden" name="posting_mode" value="{$posting_mode}" />
{if $session} <input type="hidden" name="{$session.name}" value="{$session.id}" />{/if}

{if $quote} <input type="hidden" id="quote" value="true" />{/if}
{if $form_type==0} <input type="hidden" id="name_required" value="true" />{/if}
{if !$settings.empty_postings_possible} <input type="hidden" id="text_required" value="true" />{/if}
{if $terms_of_use_agreement} <input type="hidden" id="terms_of_use_required" value="true" />{/if}
{if $data_privacy_agreement} <input type="hidden" id="data_privacy_agreement_required" value="true" />{/if}

{if $form_type==0}
 <fieldset>

  <div>
   <label for="name" class="input">{#name_marking#}</label>
   <input id="name" type="text" size="40" name="{$fld_user_name}" value="{if $name}{$name}{/if}" maxlength="{$settings.username_maxlength}" required />
  </div>

  <div>
   <label for="email" class="input">{#email_marking#} <span class="xsmall">{#optional_email#}</span></label>
   <input id="email" type="email" size="40" name="{$fld_user_email}" value="{if $email}{$email}{/if}" maxlength="{$settings.email_maxlength}" />
  </div>

  <div class="hp">
   <label for="repeat_email" class="main">{#honeypot_field_marking#}</label>
   <input id="repeat_email" type="email" size="40" name="{$fld_repeat_email}" value="{if $honey_pot_email}{$honey_pot_email}{/if}" maxlength="{$settings.email_maxlength}" tabindex="-1" />
  </div>

  <div>
   <label for="hp" class="input">{#hp_marking#} <span class="xsmall">{#optional#}</span></label>
   <input id="hp" type="url" size="40" name="{$fld_hp}" value="{if $hp}{$hp}{/if}" maxlength="{$settings.hp_maxlength}" />
  </div>

  <div class="hp">
   <label for="phone" class="main">{#honeypot_field_marking#}</label>
   <input id="phone" class="login" type="tel" size="30" name="{$fld_phone}" value="{if $honey_pot_phone}{$honey_pot_phone}{/if}" maxlength="35" />
  </div>

  <div>
   <label for="location" class="input">{#location_marking#} <span class="xsmall">{#optional#}</span></label>
   <input id="location" type="text" size="40" name="{$fld_location}" value="{if $location}{$location}{/if}" maxlength="{$settings.location_maxlength}" />
  </div>

{if $settings.remember_userdata == 1 && $posting_mode==0 && !$user}
  <div>
   <input id="setcookie" type="checkbox" name="setcookie" value="1"{if $setcookie} checked="checked"{/if} /><label for="setcookie">{#remember_userdata_marking#}</label>{if $cookie} <a href="index.php?mode=delete_cookie"><img src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/delete-cross.svg" alt="" width="12" height="12" /><span>{#delete_cookie_linkname#}</span></a>{/if}
  </div>
{/if}

 </fieldset>
{/if}

 <fieldset>
{if $categories}
  <div>
   <label for="p_category" class="input">{#category_marking#}</label>
   <select id="p_category" size="1" name="p_category" {if $posting_mode==0 && $id>0 || $posting_mode==1 && $pid>0} disabled="disabled"{/if}>
{foreach key=key item=val from=$categories}
{if $key!=0}    <option value="{$key}"{if $key==$p_category} selected="selected"{/if}>{$val}</option>
{/if}
{/foreach}
   </select>
  </div>
{if $posting_mode==0 && $id>0 || $posting_mode==1 && $pid>0}
   <input type="hidden" name="p_category" value="{$p_category}" />
{/if}
{/if}

  <div>
   <label for="subject" class="input">{#subject_marking#}</label>
   <input id="subject" type="text" size="50" name="{$fld_subject}" value="{if $subject}{$subject}{/if}" maxlength="{$settings.subject_maxlength}" required />
  </div>

{* Tags
	0 == Off
	1 == Admins/Mods
	2 == reg. Users
	3 == everyone
*}
{if $settings.tags > 0 && ( ($settings.tags == 1 && ($admin || $mod)) || ($settings.tags == 2 && ($user_type === 0 || $admin || $mod)) || $settings.tags > 2 )}
  <div>
   <label for="tags" class="input">{#tags_marking#} <span class="xsmall">{#tags_note#}</span></label>
   <input id="tags" type="text" size="50" name="tags" value="{$tags|default:""}" maxlength="253" tabindex="-1" />
  </div>
{/if}
 </fieldset>

 <fieldset id="message">

  <div id="formatting-help">
   <h3>{#bbcode_help_heading#}</h3>
   <p><a href="#entry-input">{#bbcode_help_skip_link#}</a></p>
{*<!--
This list is read out to generte the default BBCode buttons or displayed if
JavaScript isn't available. 
-->*}
   <dl id="bbcode-instructions">
    <div>
     <dt id="b" title="{#bbcode_bold_label#}" data-icon="bbcode-bold.svg">{#bbcode_bold_title#}</dt>
     <dd>{#bbcode_bold_instruction#}</dd>
    </div>
    <div>
     <dt id="i" title="{#bbcode_italic_label#}" data-icon="bbcode-italic.svg">{#bbcode_italic_title#}</dt>
     <dd>{#bbcode_italic_instruction#}</dd>
    </div>
    <div>
     <dt id="link" title="{#bbcode_link_label#}" data-icon="bbcode-link.svg">{#bbcode_link_title#}</dt>
     <dd>{#bbcode_link_instruction#}</dd>
    </div>
{if $settings.bbcode_color}
    <div>
     <dt id="color" title="{#bbcode_color_label#}" data-icon="bbcode-colour.svg">{#bbcode_color_title#}</dt>
     <dd>{#bbcode_color_instruction#}</dd>
    </div>
{/if}
{if $settings.bbcode_size}
    <div>
     <dt id="size" title="{#bbcode_size_label#}" data-icon="bbcode-textsize.svg">{#bbcode_size_title#}</dt>
     <dd id="small" title="{#bbcode_size_label_small#}">{#bbcode_size_instruction_small#}</dd>
     <dd id="large" title="{#bbcode_size_label_large#}">{#bbcode_size_instruction_large#}</dd>
    </div>
{/if}
    <div>
     <dt id="list" title="{#bbcode_list_label#}" data-icon="bbcode-list.svg">{#bbcode_list_title#}</dt>
     <dd>{#bbcode_list_instruction#}</dd>
    </div>
{if $settings.bbcode_img}
    <div>
     <dt id="img" title="{#bbcode_image_label#}" data-icon="image.svg">{#bbcode_image_title#}</dt>
     <dd title="{#bbcode_image_label_default#}">{#bbcode_image_instr_default#}</dd>
     <dd id="left" title="{#bbcode_image_label_left#}">{#bbcode_image_instr_left#}</dd>
     <dd id="right" title="{#bbcode_image_label_right#}">{#bbcode_image_instr_right#}</dd>
     <dd id="thumbnail" title="{#bbcode_image_label_thumb#}">{#bbcode_image_instr_thumb#}</dd>
     <dd id="thumbnail-left" title="{#bbcode_image_label_thumb_left#}">{#bbcode_image_instr_thumb_left#}</dd>
     <dd id="thumbnail-right" title="{#bbcode_image_label_thumb_right#}">{#bbcode_image_instr_thumb_right#}</dd>
    </div>
{/if}
{if $upload_images}
    <div>
     <dt id="upload" title="{#bbcode_upload_label#}" data-icon="upload.svg">{#bbcode_upload_title#}</dt>
     <dd><a href="index.php?mode=upload_image">{#bbcode_upload_instruction#}</a></dd>
    </div>
{/if}
{if $settings.bbcode_media}
	<div>
     <dt id="media" title="{#bbcode_media_label#}" data-icon="bbcode-media.svg">{#bbcode_media_title#}</dt>
     <dd id="type=video width=560 height=315" title="{#bbcode_media_label_video#}">{#bbcode_media_instruction_video#}</dd>
     <dd id="type=audio" title="{#bbcode_media_label_audio#}">{#bbcode_media_instruction_audio#}</dd>
    </div>
{/if}
{if $settings.bbcode_latex && $settings.bbcode_latex_uri}
    <div>
     <dt id="tex" title="{#bbcode_tex_label#}" data-icon="bbcode-formula.svg">{#bbcode_tex_title#}</dt>
     <dd>{#bbcode_tex_instruction#}</dd>
    </div>
{/if}
{if $settings.bbcode_code}
    <div>
     <dt id="code" title="{#bbcode_code_label#}" data-icon="bbcode-code.svg">{#bbcode_code_title#}</dt>
     <dd id="inlinecode" title="{#bbcode_code_label_inline#}">{#bbcode_code_instruction_inline#}</dd>
     <dd title="{#bbcode_code_label_general#}">{#bbcode_code_instruction_general#}</dd>
{if $code_languages}
{foreach from=$code_languages item=code_language}
     <dd id="{$code_language}" title="{#bbcode_code_label_specific#|replace:"[language]":$code_language}">{#bbcode_code_instruction_spec#|replace:"[language]":$code_language}</dd>
{/foreach}
{/if}
    </div>
{/if}
   </dl>
{/if}

{if $settings.smilies && $smilies}
   <dl id="smiley-instructions">
{foreach name="smilies" from=$smilies item=smiley}
    <div>
     <dt class="{if $smarty.foreach.smilies.index<6}default{else}additional{/if}" title="{#insert_smiley_title#}">{$smiley.code}</dt>
     <dd><img src="images/smilies/{$smiley.file}" alt="{$smiley.code}" /></dd>
    </div>
{/foreach}
   </dl>
{/if}
  </div>

  <div id="entry-input">
   <div id="textarea-label">
    <label for="text" class="textarea">{#message_marking#}</label>
   </div>

   <div>

    <div id="format-bar" hidden>
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

    </div>

    <textarea id="text" cols="80" rows="21" name="text">{if $text}{$text}{/if}</textarea>

   </div>
  </div>
 </fieldset>

{if $signature || $provide_email_notification || $provide_sticky || $terms_of_use_agreement || $data_privacy_agreement}
 <fieldset>
{if $signature}
  <div>
   <input id="show_signature" type="checkbox" name="show_signature" value="1"{if $show_signature && $show_signature==1} checked="checked"{/if} />&nbsp;<label for="show_signature">{#show_signature_marking#}</label>
  </div>
{/if}

{if $provide_email_notification}
  <div>
   <input id="email_notification" type="checkbox" name="email_notification" value="1"{if $email_notification && $email_notification==1} checked="checked"{/if} />&nbsp;<label for="email_notification">{if $id==0}{#email_notific_reply_thread#}{else}{#email_notific_reply_post#}{/if}</label>
  </div>
{/if}

{if $provide_sticky}
  <ul id="sticky-selection">
   <li><input id="sticky_none" type="radio" name="sticky" value="0"{if !$sticky or ($sticky && $sticky==0)} checked="checked"{/if} /><label for="sticky_none">{#sticky_none#}</label></li>
{if $categories}
   <li><input id="sticky_cat" type="radio" name="sticky" value="1"{if $sticky && $sticky==1} checked="checked"{/if} /><label for="sticky_cat">{#sticky_single_cat#}</label></li>
   <li><input id="sticky_all" type="radio" name="sticky" value="2"{if $sticky && $sticky==2} checked="checked"{/if} /><label for="sticky_all">{#sticky_all_cats#}</label></li>
{else}
   <li><input id="sticky_nocat" type="radio" name="sticky" value="1"{if $sticky && $sticky==1} checked="checked"{/if} /><label for="sticky_nocat">{#sticky_thread#}</label></li>
{/if}
  </ul>
{/if}

{if $terms_of_use_agreement}
{assign var=terms_of_use_url value=$settings.terms_of_use_url}
  <div>
   <input id="terms_of_use_agree" type="checkbox" name="terms_of_use_agree" value="1"{if $terms_of_use_agree && $terms_of_use_agree==1} checked="checked"{/if} />&nbsp;<label for="terms_of_use_agree">{if $terms_of_use_url}{#terms_of_use_agreement#|replace:"[[":"<a id=\"terms_of_use\" href=\"$terms_of_use_url\">"|replace:"]]":"</a>"}{else}{#terms_of_use_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label>
  </div>
{/if}
{if $data_privacy_agreement}
{assign var=data_privacy_statement_url value=$settings.data_privacy_statement_url}
  <div>
   <input id="data_privacy_statement_agree" type="checkbox" name="data_privacy_statement_agree" value="1"{if $data_privacy_statement_agree && $data_privacy_statement_agree==1} checked="checked"{/if} />&nbsp;<label for="data_privacy_statement_agree">{if $data_privacy_statement_url}{#data_privacy_agreement#|replace:"[[":"<a id=\"data_privacy_statement\" href=\"$data_privacy_statement_url\">"|replace:"]]":"</a>"}{else}{#data_privacy_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label>
  </div>
{/if}
 </fieldset>
{/if}

{if $captcha}
 <fieldset>
  <legend>{#captcha_marking#}</legend>
{if $captcha.type==2}
  <p><img class="captcha" src="modules/captcha/captcha_image.php?{$session.name}={$session.id}" alt="{#captcha_image_alt#}" width="180" height="40" /><br />
  <label for="captcha_code">{#captcha_expl_image#}</label><br />
  <input id="captcha_code" type="text" name="captcha_code" value="" size="10" /></p>
{else}
  <div>
   <label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label>
   <input id="captcha_code" type="text" name="captcha_code" value="" size="5" maxlength="5" />
  </div>
{/if}
 </fieldset>
{/if}

 <fieldset>
  <div class="buttonbar">
   <button name="save_entry" value="{#message_submit_button#}" title="{#message_submit_title#}">{#message_submit_button#}</button>
   <button name="preview" value="{#message_preview_button#}" title="{#message_preview_title#}">{#message_preview_button#}</button>
   <img id="throbber-submit" src="{$THEMES_DIR}/{$theme}/images/throbber.svg" alt="" width="18" height="18" hidden />
  </div>
 </fieldset>
</form>
{/if}
