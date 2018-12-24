{config_load file=$language_file section="user_edit"}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[profile_length]":$profil_length|replace:"[profile_maxlength]":$settings.profile_maxlength|replace:"[signature_length]":$signature_length|replace:"[signature_maxlength]":$settings.signature_maxlength|replace:"[word]":$word|replace:"[not_accepted_word]":$not_accepted_word|replace:"[not_accepted_words]":$not_accepted_words}</li>
{/section}
</ul>
{/if}
{if $msg}<p class="ok">{$smarty.config.$msg}</p>{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
<input type="hidden" name="mode" value="user" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<td class="c"><strong>{#edit_user_name#}</strong></td>
<td class="d"><strong>{$user_name}</strong></td>
</tr>
{if $settings.avatars>0}
<tr>
<td class="c"><strong>{#edit_avatar#}</strong></td>
<td class="d">
<div id="avatar_wrapper">{if $avatar}<img src="{$avatar.image}" alt="{#edit_avatar_link_title#}" width="{$avatar.width}" height="{$avatar.height}" />{/if}</div>
<span class="small">[ <a id="edit_avatar" href="index.php?mode=avatar">{#edit_avatar_link#}</a> ]</span>
</td>
</tr>
{/if}
<tr>
<td class="c"><strong>{#edit_user_pw#}</strong></td>
<td class="d"><span class="small">[ <a href="index.php?mode=user&amp;action=edit_pw">{#edit_pw#}</a> ]</span></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_email#}</strong></td>
<td class="d"><!--<a href="mailto:{$user_email}">-->{$user_email}<!--</a>--> &nbsp;<span class="small">[ <a href="index.php?mode=user&amp;action=edit_email">{#edit_email#}</a> ]</span><br />
<span class="small"><input id="email_contact" type="checkbox" name="email_contact" value="1"{if $email_contact==1} checked="checked"{/if} /><label for="email_contact">{#edit_user_email_contact#}</label></span></td>
</tr>
<tr>
<td class="c"><label for="user_hp"><strong>{#edit_user_hp#}</strong></label></td>
<td class="d"><input id="user_hp" type="text" size="40" name="user_hp" value="{$user_hp}" maxlength="{$settings.hp_maxlength}" /></td>
</tr>
<tr>
<td class="c"><label for="user_real_name"><strong>{#edit_user_real_name#}</strong></label></td>
<td class="d"><input id="user_real_name" type="text" size="40" name="user_real_name" value="{$user_real_name}" maxlength="{$settings.name_maxlength}" /></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_gender#}</strong></td>
<td class="d">
<input id="no-gender" type="radio" name="user_gender" value="0"{if $user_gender=="0"} checked="checked"{/if} /><label for="no-gender">{#gender_not_specified#}</label><br />
<input id="male" type="radio" name="user_gender" value="1"{if $user_gender=="1"} checked="checked"{/if} /><label for="male">{#male#}</label><br />
<input id="female" type="radio" name="user_gender" value="2"{if $user_gender=="2"} checked="checked"{/if} /><label for="female">{#female#}</label></td>
</tr>
<tr>
<td class="c"><label for="user_birthday"><strong>{#edit_user_birthday#}</strong></label></td>
<td class="d"><input id="user_birthday" type="text" size="40" name="user_birthday" value="{$user_birthday}" /> <span class="small">({#birthday_format#})</span></td>
</tr>
<tr>
<td class="c"><label for="user_location"><strong>{#edit_user_location#}</strong></label></td>
<td class="d"><input id="user_location" type="text" size="40" name="user_location" value="{$user_location}" maxlength="{$settings.location_maxlength}" /></td>
</tr>
<tr>
<td class="c"><label for="profile"><strong>{#edit_user_profile#}</strong></label></td>
<td class="d"><textarea id="profile" cols="65" rows="12" name="profile">{$profile}</textarea></td>
</tr>
<tr>
<td class="c"><label for="signature"><strong>{#edit_user_signature#}</strong></label></td>
<td class="d"><textarea id="signature" cols="65" rows="4" name="signature">{$signature}</textarea></td>
</tr>
{if $categories}
<tr>
<td class="c"><strong>{#edit_user_cat_selection#}</strong></td>
<td class="d">
{*<select id="category_selection" name="category_selection[]" multiple="multiple" size="{if $number_of_categories>10}10{else}{$number_of_categories}{/if}">
{foreach key=key item=val from=$categories}
{if $key!=0}<option value="{$key}"{if isset($category_selection) && in_array($key,$category_selection)} selected="selected"{/if}>{$val}</option>{/if}
{/foreach}
</select>*}
<ul class="checkboxlist">
{foreach key=key item=val from=$categories}
{if $key!=0}<li><input id="category_{$key}" type="checkbox" name="category_selection[]" value="{$key}"{if isset($category_selection) && in_array($key,$category_selection)} checked="checked"{/if} /><label for="category_{$key}">{$val}</label></li>{/if}
{/foreach}
</ul>
</td>
</tr>
{/if}

{if $languages}
<tr>
<td class="c"><strong><label for="user_language">{#edit_user_language#}</label></strong></td>
<td class="d">
<select id="user_language" name="user_language" size="1">
<option value=""{if $user_language==''} selected="selected"{/if}>{#edit_user_default_language#|replace:"[default_language]":$default_language}</option>
{foreach from=$languages item=l}
<option value="{$l.identifier}"{if $l.identifier==$user_language} selected="selected"{/if}>{$l.title}</option>
{/foreach}
</select>
</td>
</tr>
{/if}

<tr>
<td class="c"><strong>{if $time_zones}<label for="user_time_zone">{#edit_user_time_zone#}</label>{else}{#edit_user_time_zone#}{/if}</strong></td>
<td class="d">
{if $time_zones}
<p>
<select id="user_time_zone" name="user_time_zone" size="1">
<option value=""{if $user_time_zone==''} selected="selected"{/if}>{if $default_time_zone}{#edit_user_default_time_zone#|replace:"[default_time_zone]":$default_time_zone}{else}{#edit_user_default_time_zone_svr#}{/if}</option>
{foreach from=$time_zones item=tz}
<option value="{$tz}"{if $tz==$user_time_zone} selected="selected"{/if}>{$tz}</option>
{/foreach}
</select>
</p>
{/if}
<p><span class="small"><label for="user_time_difference">{#edit_user_time_difference#}</label></span><br /><input id="user_time_difference" type="text" size="6" name="user_time_difference" value="{$user_time_difference}" maxlength="6" /></p>
</td>
</tr>

{if $themes}
<tr>
<td class="c"><strong><label for="user_theme">{#edit_user_theme#}</label></strong></td>
<td class="d">
<select id="user_theme" name="user_theme" size="1">
<option value=""{if $user_theme==''} selected="selected"{/if}>{#edit_user_default_theme#|replace:"[default_theme]":$default_theme}</option>
{foreach from=$themes item=t}
<option value="{$t.identifier}"{if $t.identifier==$user_theme} selected="selected"{/if}>{$t.title}</option>
{/foreach}
</select>
</td>
</tr>
{/if}

{if $settings.autologin==1}
<tr>
<td class="c"><strong>{#edit_user_auto_login#}</strong></td>
<td class="d"><input id="auto_login" type="checkbox" name="auto_login" value="1"{if $auto_login==1} checked="checked"{/if} /><label for="auto_login">{#enable_auto_login#}</label></td>
</tr>
{/if}

<tr>
<td class="c"><strong class="caution">{#remove_user_account#}</strong></td>
<td class="d"><span class="small">[ <a href="index.php?mode=user&amp;action=remove_account">{#remove_user_account_link#}</a> ]</span></td>
</tr>

{if $mod||$admin}
<tr>
<td class="c"><strong>{#edit_user_notification#}</strong></td>
<td class="d"><input id="new_posting_notification" type="checkbox" name="new_posting_notification" value="1"{if $new_posting_notification=="1"} checked="checked"{/if} /><label for="new_posting_notification">{#admin_mod_notif_posting#}</label><br />
<input id="new_user_notification" type="checkbox" name="new_user_notification" value="1"{if $new_user_notification=="1"} checked="checked"{/if} /><label for="new_user_notification">{#admin_mod_notif_register#}</label></td>
</tr>
{/if}
<tr>
<td class="c">&nbsp;</td>
<td class="d"><input type="submit" name="edit_user_submit" value="{#userdata_submit_button#}" /></td>
</tr>
</table>
</div>
</form>
