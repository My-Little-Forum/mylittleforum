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
{include file="$theme/subtemplates/ajax_preview.inc.tpl"}
<p class="reply-to">{#reply_to_posting_marking#|replace:"[name]":$name_repl_subnav}<script type="text/javascript">/* <![CDATA[ */document.write(' <a href="#" onclick="ajax_preview({$id}); return false" title="{#ajax_preview_title#|escape:"quotes"}"><img class="ap" src="{$THEMES_DIR}/{$theme}/images/ajax_preview.png" alt="{#ajax_preview_title#|escape:"quotes"}" width="11" height="11" /><\/a>')/* ]]> */</script></p>
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
<input type="hidden" name="back" value="{$back}" />
<input type="hidden" name="mode" value="{$mode}" />
<input type="hidden" name="id" value="{$id}" />
<input type="hidden" name="uniqid" value="{$uniqid}" />
<input type="hidden" name="posting_mode" value="{$posting_mode}" />
{if $session}<input type="hidden" name="{$session.name}" value="{$session.id}" />{/if}

<table border="0" cellpadding="0" cellspacing="5">
{if $form_type==0}
<tr>
<td><label for="name" class="main">{#name_marking#}</label></td><td><input id="name" type="text" size="40" name="name" value="{if $name}{$name}{/if}" maxlength="{$settings.username_maxlength}"  tabindex="1" /></td>
</tr>
<tr>
<td><label for="email" class="main">{#email_marking#}</label></td><td><input id="email" type="text" size="40" name="email" value="{if $email}{$email}{/if}" maxlength="{$settings.email_maxlength}" tabindex="2" />&nbsp;<span class="xsmall">{#optional_email#}</span></td>
</tr>
<tr>
<td><label for="hp" class="main">{#hp_marking#}</label></td><td><input id="hp" type="text" size="40" name="hp" value="{if $hp}{$hp}{/if}" maxlength="{$settings.hp_maxlength}" tabindex="3" />&nbsp;<span class="xsmall">{#optional#}</span></td>
</tr>
<tr>
<td><label for="location" class="main">{#location_marking#}</label></td><td><input id="location" type="text" size="40" name="location" value="{if $location}{$location}{/if}" maxlength="{$settings.location_maxlength}" tabindex="4" />&nbsp;<span class="xsmall">{#optional#}</span></td>
</tr>
{if $settings.remember_userdata == 1 && $posting_mode==0 && !$user}
<tr>
<td>&nbsp;</td><td><input id="setcookie" type="checkbox" name="setcookie" value="1"{if $setcookie} checked="checked"{/if} />&nbsp;<label for="setcookie">{#remember_userdata_marking#}</label>{if $cookie} &nbsp;<span id="delete_cookie"><a href="index.php?mode=delete_cookie" onclick="delete_cookie('{$smarty.config.deleting_cookie|escape:"url"}'); return false;" ><img src="{$THEMES_DIR}/{$theme}/images/delete_small.png" alt="" width="12" height="9" />{#delete_cookie_linkname#}</a></span>{/if}</td>
</tr>
{/if}
<tr>
<td colspan="2">&nbsp;</td>
</tr>
{/if}
{if $categories}
<tr>
<td><label for="p_category" class="main">{#category_marking#}</label></td>
<td><select id="p_category" size="1" name="p_category" tabindex="5"{if $posting_mode==0 && $id>0 || $posting_mode==1 && $pid>0} disabled="disabled"{/if}>
{foreach key=key item=val from=$categories}
{if $key!=0}<option value="{$key}"{if $key==$p_category} selected="selected"{/if}>{$val}</option>{/if}
{/foreach}
</select></td>
</tr>
{/if}
<tr>
<td><label for="subject" class="main">{#subject_marking#}</label></td><td><input id="subject" type="text" size="50" name="subject" value="{if $subject}{$subject}{/if}" maxlength="{$settings.subject_maxlength}" tabindex="6" /></td>
</tr>
{if ($admin ||$mod) && $settings.tags>0}
<tr>
<td><label for="tags" class="main">{#tags_marking#}</label></td><td><input id="tags" type="text" size="50" name="tags" value="{$tags|default:""}" maxlength="253" />&nbsp;<span class="xsmall">{#tags_note#}</span></td>
</tr>
{/if}
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2"><label for="text" class="main">{#message_marking#}</label>{if $hide_quote} &nbsp;<span class="small"><a href="#" onclick="insert_quote(); return false" id="insert_quote_link" style="visibility:hidden;"><img src="{$THEMES_DIR}/{$theme}/images/quote_message.png" alt="" width="14" height="9" />{#quote_message#}</a></span>{/if}</td>
</tr>
<tr>
<td colspan="2">
<table class="normal" border="0" cellpadding="0" cellspacing="0">
<tr>
<td valign="top">
<textarea id="text" cols="80" rows="21" name="text" tabindex="7">{if $text}{$text}{/if}</textarea>{if $hide_quote}<script type="text/javascript">/* <![CDATA[ */ hide_quote(); /* ]]> */</script>{/if}</td><td valign="top" style="padding: 0px 0px 0px 5px;">
<div id="jsbuttons" style="display:none;">
{if $settings.bbcode}
<input class="bbcode-button" style="font-weight: bold;" type="button" name="bold" value="{#bbcode_bold_marking#}" title="{#bbcode_bold_title#}" onclick="bbcode('text','b');" /><br />
<input class="bbcode-button" style="font-style: italic;" type="button" name="italic" value="{#bbcode_italic_marking#}" title="{#bbcode_italic_title#}" onclick="bbcode('text','i');" /><br />
<input class="bbcode-button" style="color: #0000ff; text-decoration: underline;" type="button" name="link" value="{#bbcode_link_marking#}" title="{#bbcode_link_title#}" onclick="insert_link('text','{#bbcode_link_linktext#|escape:"url"}','{#bbcode_link_url#|escape:"url"}');" /><br />
{if $settings.bbcode_color}<input class="bbcode-button" style="color: red;" type="button" name="color" value="{#bbcode_color#}" title="{#bbcode_color_title#}" onclick="show_box('colorpicker',30,-30);" /><br />{/if}
{if $settings.bbcode_size}<input class="bbcode-button" type="button" name="size" value="{#bbcode_font_size#}" title="{#bbcode_font_size_title#}" onclick="show_box('sizepicker',30,-20);" /><br />{/if}
<input class="bbcode-button" type="button" name="list" value="{#bbcode_list_marking#}" title="{#bbcode_list_title#}" onclick="insert('text','[list]\n[*]...\n[*]...\n[/list]');" /><br />
{if $settings.bbcode_img}<input class="bbcode-button" type="button" name="image" value="{#bbcode_image_marking#}" title="{#bbcode_image_title#}" onclick="insert_image('text','{#bbcode_image_url#|escape:"url"}');" /><br />{/if}
{if $upload_images}<input class="bbcode-button" type="button" name="imgupload" value="{#upload_image_marking#}" title="{#upload_image_title#}" onclick="popup('index.php?mode=upload_image',350,340);" /><br />{/if}
{if $settings.bbcode_flash}<input class="bbcode-button" type="button" name="image" value="{#bbcode_flash_marking#}" title="{#bbcode_flash_title#}" onclick="popup('index.php?mode=insert_flash',400,440);" /><br />{/if}
{if $settings.bbcode_code}<input class="bbcode-button" type="button" name="size" value="{#bbcode_code_marking#}" title="{#bbcode_code_title#}" onclick="show_box('codepicker',30,-20);" /><br />{/if}<br />
{/if}
{if $settings.smilies == 1 && $smilies}
{section name=smiley start=0 step=2 max="3" loop=$smilies}
{section name=smiley_row start=$smarty.section.smiley.index loop=$smarty.section.smiley.index+1}
{if $smilies[smiley_row].file}<button class="smiley-button" name="smiley" type="button" value="{$smilies[smiley_row].code}" title="Insert smiley" onclick="insert('text','{$smilies[smiley_row].code} ')"><img src="images/smilies/{$smilies[smiley_row].file}" alt="{$smilies[smiley_row].code}" /></button>{else}&nbsp;{/if}
{/section}
{section name=smiley_row start=$smarty.section.smiley.index+1 loop=$smarty.section.smiley.index+2}
{if $smilies[smiley_row].file}<button class="smiley-button" name="smiley" type="button" value="{$smilies[smiley_row].code}" title="Insert smiley" onclick="insert('text','{$smilies[smiley_row].code} ')"><img src="images/smilies/{$smilies[smiley_row].file}" alt="{$smilies[smiley_row].code}" /></button><br />{else}<br />{/if}
{/section}
{/section}
{if $smilies_count>6}<span class="small"><a href="#" onclick="show_box('more-smilies',10,-120); return false" title="{#more_smilies_title#}">{#more_smilies_link#}</a></span>{/if}
{/if}
</div>
<script type="text/javascript">/* <![CDATA[ */document.getElementById('jsbuttons').style.display="block"; /* ]]> */</script>
<noscript><p class="small">
{if $settings.bbcode}
{#bbcode_bold_title#}<br />
{#bbcode_italic_title#}<br />
{#bbcode_link_title#}<br />
{if $settings.bbcode_color}{#bbcode_color_title#}<br />{/if}
{if $settings.bbcode_size}{#bbcode_font_size_title#}<br />{/if}
{#bbcode_list_title#}<br />
{if $settings.bbcode_img}{#bbcode_image_title#}<br />{/if}
{if $upload_images}<a href="index.php?mode=upload_image">{#upload_image_title#}</a><br />{/if}
{/if}
</p></noscript>
</td></tr></table>
</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
{if $signature}
<tr>
<td colspan="2"><input id="show_signature" type="checkbox" name="show_signature" value="1"{if $show_signature && $show_signature==1} checked="checked"{/if} />&nbsp;<label for="show_signature">{#show_signature_marking#}</label></td>
</tr>
{/if}
{if $provide_email_notification}
<tr>
<td colspan="2"><input id="email_notification" type="checkbox" name="email_notification" value="1"{if $email_notification && $email_notification==1} checked="checked"{/if} />&nbsp;<label for="email_notification">{if $id==0}{#email_notific_reply_thread#}{else}{#email_notific_reply_post#}{/if}</label></td>
</tr>
{/if}
{if $provide_sticky}
<tr>
<td colspan="2"><input id="sticky" type="checkbox" name="sticky" value="1"{if $sticky && $sticky==1} checked="checked"{/if} />&nbsp;<label for="sticky">{#sticky_thread#}</label></td>
</tr>
{/if}
{if $terms_of_use_agreement}
{assign var=terms_of_use_url value=$settings.terms_of_use_url}
<tr>
<td colspan="2"><input id="terms_of_use_agree" type="checkbox" name="terms_of_use_agree" value="1"{if $terms_of_use_agree && $terms_of_use_agree==1} checked="checked"{/if} />&nbsp;<label for="terms_of_use_agree">{if $terms_of_use_url}{#terms_of_use_agreement#|replace:"[[":"<a href=\"$terms_of_use_url\" onclick=\"popup('$terms_of_use_url',640,480); return false\">"|replace:"]]":"</a>"}{else}{#terms_of_use_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label></td>
</tr>
{/if}
{if $signature || $provide_email_notification || $provide_sticky || $terms_of_use_agreement}
<tr>
<td colspan="2">&nbsp;</td>
</tr>
{/if}
{if $captcha}
<tr>
<td colspan="2">
<div id="captcha">
{if $captcha.type==2}
<p><strong>{#captcha_marking#}</strong><br />
<img class="captcha" src="modules/captcha/captcha_image.php?{$captcha.session_name}={$captcha.session_id}" alt="{#captcha_image_alt#}" width="180" height="40" /><br />
<label for="captcha_code">{#captcha_expl_image#}</label> <input id="captcha_code" type="text" name="captcha_code" value="" size="10" /></p>
{else}
<p><strong>{#captcha_marking#}</strong><br />
<label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label><input id="captcha_code" type="text" name="captcha_code" value="" size="5" maxlength="5" /></p>
{/if}
</div>
</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
{/if}
<tr>
<td colspan="2"><input type="submit" name="save_entry" value="{#message_submit_button#}" title="{#message_submit_title#}" tabindex="8" onclick="return is_postingform_complete('{$smarty.config.error_no_name|escape:"url"}','{$smarty.config.error_no_subject|escape:"url"}','{if $settings.empty_postings_possible==0}{$smarty.config.error_no_text|escape:"url"}{/if}'{if $terms_of_use_agreement},'{$smarty.config.terms_of_use_agree_error_posting|escape:"url"}'{/if})" />&nbsp;<input type="submit" name="preview" value="{#message_preview_button#}" title="{#message_preview_title#}" tabindex="9" onclick="return is_postingform_complete('{$smarty.config.error_no_name|escape:"url"}','{$smarty.config.error_no_subject|escape:"url"}','{if $settings.empty_postings_possible==0}{$smarty.config.error_no_text|escape:"url"}{/if}'{if $terms_of_use_agreement},'{$smarty.config.terms_of_use_agree_error_posting|escape:"url"}'{/if})" /> <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></td>
</tr>
</table>
</div>
</form>
{if !$user}<p class="xsmall" style="margin-top: 30px;">{#email_exp#}</p>{else}<p>&nbsp;</p>{/if}
{/if}
{if $settings.smilies == 1 && $smilies}
<div id="more-smilies">
<a href="#" onclick="hide_element('more-smilies'); return false"><img class="close" src="{$THEMES_DIR}/{$theme}/images/close.png" alt="[x]" title="{#close#}" width="14" height="14" /></a>
<div id="more-smilies-body">
<div id="more-smilies-content">
<p>{section name=smiley loop=$smilies}
{if $smilies[smiley].file}<a href="#" onclick="insert('text','{$smilies[smiley].code} '); return false;"><img src="images/smilies/{$smilies[smiley].file}" alt="{$smilies[smiley].code}" /></a>{/if}
{/section}
</p>
</div>
</div>
</div>
{/if}
{if $settings.bbcode_color}{include file="$theme/subtemplates/colorpicker.inc.tpl"}{/if}
{if $settings.bbcode_size}
<div id="sizepicker">
<p style="font-size:x-small;"><a href="#" onclick="bbcode('text','size','small'); hide_element('sizepicker'); return false">{#bbcode_font_size_small#}</a></p>
<p style="font-size:large;"><a href="#" onclick="bbcode('text','size','large'); hide_element('sizepicker'); return false">{#bbcode_font_size_large#}</a></p>
</div>
{/if}
{if $settings.bbcode_code}
<div id="codepicker">
<ul>
<li><a href="#" onclick="bbcode('text','inlinecode'); hide_element('codepicker'); return false">{#bbcode_code_inline#}</a></li>
<li><a href="#" onclick="bbcode('text','code'); hide_element('codepicker'); return false">{#bbcode_code_block#}</a></li>
{if $code_languages}
{foreach from=$code_languages item=code_language}
<li><a href="#" onclick="bbcode('text','code','{$code_language}'); hide_element('codepicker'); return false">{#bbcode_code_block_lang#|replace:"[language]":$code_language}</a></li>
{/foreach}
{/if}
</ul>
</div>
{/if}
