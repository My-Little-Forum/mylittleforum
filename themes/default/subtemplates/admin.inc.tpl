{config_load file=$language_file section="admin"}
{if $action=='settings'}
{if $saved}<p class="ok">{#settings_saved#}</p>{/if}
<form id="settings" action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<td class="c"><strong>{#forum_name#}</strong><br /><span class="small">{#forum_name_desc#}</span></td>
<td class="d"><input type="text" name="forum_name" value="{$settings.forum_name|escape}" size="40" /></td>
</tr>
<tr>
<td class="c"><strong>{#forum_description#}</strong><br /><span class="small">{#forum_description_desc#}</span></td>
<td class="d"><input type="text" name="forum_description" value="{$settings.forum_description|escape}" size="40" /></td>
</tr>
<tr>
<td class="c"><strong>{#forum_address#}</strong><br /><span class="small">{#forum_address_desc#}</span></td>
<td class="d"><input type="text" name="forum_address" value="{$settings.forum_address|escape}" size="40" /></td>
</tr>
<tr>
<td class="c"><strong>{#forum_email#}</strong><br /><span class="small">{#forum_email_desc#}</span></td>
<td class="d"><input type="text" name="forum_email" value="{$settings.forum_email|escape}" size="40" /></td>
</tr>

{if $languages}
<tr>
<td class="c"><strong>{#default_language#}</strong><br /><span class="small">{#default_language_desc#}</span></td>
<td class="d"><select name="language_file" size="1">
{foreach from=$languages item=l}
<option value="{$l.identifier}"{if $l.identifier==$settings.language_file} selected="selected"{/if}>{$l.title}</option>
{/foreach}
</select></td>
</tr>
{/if}

<tr>
<td class="c"><strong>{#default_time_zone#}</strong><br /><span class="small">{#default_time_zone_desc#}</span></td>
<td class="d">
{if $time_zones}
<p>
<select id="time_zone" name="time_zone" size="1">
<option value=""{if $settings.time_zone==''} selected="selected"{/if}></option>
{foreach from=$time_zones item=tz}
<option value="{$tz}"{if $tz==$settings.time_zone} selected="selected"{/if}>{$tz}</option>
{/foreach}
</select>
</p>
{else}
<p><label for="time_difference">{#default_time_difference#}</label><br /><input id="time_difference" type="text" name="time_difference" value="{$settings.time_difference}" size="5" /></p>
{/if}
</td>
</tr>

{if $themes}
<tr>
<td class="c"><strong>{#default_theme#}</strong><br /><span class="small">{#default_theme_desc#}</span></td>
<td class="d"><select name="theme" size="1">
{foreach from=$themes item=t}
<option value="{$t.identifier}"{if $t.identifier==$settings.theme} selected="selected"{/if}>{$t.title}</option>
{/foreach}
</select></td>
</tr>
{/if}

<tr>
<td class="c"><strong>{#home_link#}</strong><br /><span class="small">{#home_link_desc#}</span></td>
<td class="d"><input type="text" name="home_linkaddress" value="{$settings.home_linkaddress|escape}" size="40" /></td>
</tr>
<tr>
<td class="c"><strong>{#home_link_name#}</strong><br /><span class="small">{#home_link_name_desc#}</span></td>
<td class="d"><input type="text" name="home_linkname" value="{$settings.home_linkname|escape}" size="40" /></td>
</tr>

<tr>
<td class="c"><strong>{#terms_of_use_agreement#}</strong><br /><span class="small">{#terms_of_use_agreement_desc#}</span></td>
<td class="d"><p><input id="terms_of_use_agreement" type="checkbox" name="terms_of_use_agreement" value="1"{if $settings.terms_of_use_agreement==1} checked="checked"{/if} /><label id="terms_of_use_agreement_label" for="terms_of_use_agreement" class="{if $settings.terms_of_use_agreement==1}active{else}inactive{/if}">{#terms_of_use_agreement_enabled#}</label></p>
<p><label id="terms_of_use_url_label" for="terms_of_use_url" class="{if $settings.terms_of_use_agreement==1}active{else}inactive{/if}">{#terms_of_use_url#}</label><br /><input id="terms_of_use_url" type="text" name="terms_of_use_url" value="{$settings.terms_of_use_url|escape}" size="40" /></p></td>
</tr>

<tr>
<td class="c"><strong>{#accession#}</strong><br /><span class="small">{#accession_desc#}</span></td>
<td class="d"><input id="access_for_all" type="radio" name="access_for_users_only" value="0"{if $settings.access_for_users_only==0} checked="checked"{/if} /><label id="access_for_all_label" for="access_for_all" class="{if $settings.access_for_users_only==0}active{else}inactive{/if}">{#all_users#}</label><br />
<input id="access_for_users_only" type="radio" name="access_for_users_only" value="1"{if $settings.access_for_users_only==1} checked="checked"{/if} /><label id="access_for_users_only_label" for="access_for_users_only" class="{if $settings.access_for_users_only==1}active{else}inactive{/if}">{#only_registered_users#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#post_permission#}</strong><br /><span class="small">{#post_permission_desc#}</span></td>
<td class="d"><input id="entries_by_all" type="radio" name="entries_by_users_only" value="0"{if $settings.entries_by_users_only==0} checked="checked"{/if} /><label id="entries_by_all_label" for="entries_by_all" class="{if $settings.entries_by_users_only==0}active{else}inactive{/if}">{#all_users#}</label><br />
<input id="entries_by_users" type="radio" name="entries_by_users_only" value="1"{if $settings.entries_by_users_only==1} checked="checked"{/if} /><label id="entries_by_users_label" for="entries_by_users" class="{if $settings.entries_by_users_only==1}active{else}inactive{/if}">{#only_registered_users#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#register_permission#}</strong><br /><span class="small">{#register_permission_desc#}</span></td>
<td class="d"><input id="register_mode_0" type="radio" name="register_mode" value="0"{if $settings.register_mode==0} checked="checked"{/if} /><label id="register_mode_0_label" for="register_mode_0" class="{if $settings.register_mode==0}active{else}inactive{/if}">{#register_self#}</label><br />
<input id="register_mode_1" type="radio" name="register_mode" value="1"{if $settings.register_mode==1} checked="checked"{/if} /><label id="register_mode_1_label" for="register_mode_1" class="{if $settings.register_mode==1}active{else}inactive{/if}">{#register_self_locked#}</label><br />
<input id="register_mode_2" type="radio" name="register_mode" value="2"{if $settings.register_mode==2} checked="checked"{/if} /><label id="register_mode_2_label" for="register_mode_2" class="{if $settings.register_mode==2}active{else}inactive{/if}">{#register_only_admin#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#user_area#}</strong><br /><span class="small">{#user_area_desc#}</span></td>
<td class="d"><input id="public" type="radio" name="user_area_public" value="1"{if $settings.user_area_public==1} checked="checked"{/if} /><label id="public_label" for="public" class="{if $settings.user_area_public==1}active{else}inactive{/if}">{#public_accessible#}</label><br />
<input id="not_public" type="radio" name="user_area_public" value="0"{if $settings.user_area_public==0} checked="checked"{/if} /><label id="not_public_label" for="not_public" class="{if $settings.user_area_public==0}active{else}inactive{/if}">{#accessible_reg_users_only#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#latest_postings#}</strong><br /><span class="small">{#latest_postings_desc#}</span></td>
<td class="d"><input type="text" name="latest_postings" value="{$settings.latest_postings|escape}" size="5" /></td>
</tr>
<tr>
<td class="c"><strong>{#tag_cloud#}</strong><br /><span class="small">{#tag_cloud_desc#}</span></td>
<td class="d"><input id="tag_cloud" type="checkbox" name="tag_cloud" value="1"{if $settings.tag_cloud==1} checked="checked"{/if} /><label id="tag_cloud_label" for="tag_cloud" class="{if $settings.tag_cloud==1}active{else}inactive{/if}">{#enable_tag_cloud#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#edit_postings#}</strong><br /><span class="small">{#edit_postings_desc#}</span></td>
<td class="d">
{assign var="settings_edit_delay" value=$settings.edit_delay}
{assign var="input_edit_delay" value="<input type=\"text\" name=\"edit_delay\" value=\"$settings_edit_delay\" size=\"3\" />"}

<p><input id="show_if_edited" type="checkbox" name="show_if_edited" value="1"{if $settings.show_if_edited==1} checked="checked"{/if} /><label id="show_if_edited_label" for="show_if_edited" class="{if $settings.show_if_edited==1}active{else}inactive{/if}">{#show_if_edited#|replace:"[minutes]":$input_edit_delay}</label><br />
<input id="dont_reg_edit_by_admin" type="checkbox" name="dont_reg_edit_by_admin" value="1"{if $settings.dont_reg_edit_by_admin==1} checked="checked"{/if} /><label id="dont_reg_edit_by_admin_label" for="dont_reg_edit_by_admin" class="{if $settings.dont_reg_edit_by_admin==1}active{else}inactive{/if}">{#dont_show_edit_by_admin#}</label><br />
<input id="dont_reg_edit_by_mod" type="checkbox" name="dont_reg_edit_by_mod" value="1"{if $settings.dont_reg_edit_by_mod==1} checked="checked"{/if} /><label id="dont_reg_edit_by_mod_label" for="dont_reg_edit_by_mod" class="{if $settings.dont_reg_edit_by_mod==1}active{else}inactive{/if}">{#dont_show_edit_by_mod#}</label></p>

<p><em>{#edit_own_postings#}</em></p>
<p><input id="edit_own_postings_all" type="radio" name="user_edit" value="2"{if $settings.user_edit==2} checked="checked"{/if} /><label id="edit_own_postings_all_label" for="edit_own_postings_all" class="{if $settings.user_edit==2}active{else}inactive{/if}">{#edit_own_postings_all#}</label><br />
<input id="edit_own_postings_users" type="radio" name="user_edit" value="1"{if $settings.user_edit==1} checked="checked"{/if} /><label id="edit_own_postings_users_label" for="edit_own_postings_users" class="{if $settings.user_edit==1}active{else}inactive{/if}">{#edit_own_postings_users#}</label><br />
<input id="edit_own_postings_disabled" type="radio" name="user_edit" value="0"{if $settings.user_edit==0} checked="checked"{/if} /><label id="edit_own_postings_disabled_label" for="edit_own_postings_disabled" class="{if $settings.user_edit==0}active{else}inactive{/if}">{#edit_own_postings_disabled#}</label></p>

<fieldset id="user_edit_details" class="{if $settings.user_edit==0}inactive{else}active{/if}">
{assign var="settings_edit_max_time_period" value=$settings.edit_max_time_period}
{assign var="input_edit_max_time_period" value="<input type=\"text\" id=\"edit_max_time_period\" name=\"edit_max_time_period\" value=\"$settings_edit_max_time_period\" size=\"3\" />"}
<p><label id="edit_max_time_period_label" for="edit_max_time_period">{#edit_max_time_period#|replace:"[minutes]":$input_edit_max_time_period}</label></p>
{assign var="settings_edit_min_time_period" value=$settings.edit_min_time_period}
{assign var="input_edit_min_time_period" value="<input type=\"text\" name=\"edit_min_time_period\" value=\"$settings_edit_min_time_period\" size=\"3\" />"}
<p><input id="user_edit_if_no_replies" type="checkbox" name="user_edit_if_no_replies" value="1"{if $settings.user_edit_if_no_replies==1} checked="checked"{/if} /><label id="user_edit_if_no_replies_label" for="user_edit_if_no_replies" class="{if $settings.user_edit_if_no_replies==1}active{else}inactive{/if}">{#user_edit_if_no_replies#|replace:"[minutes]":$input_edit_min_time_period}</label></p>
</fieldset>

</td>
</tr>
<tr>
<td class="c"><strong>{#bbcode#}</strong><br /><span class="small">{#bbcode_desc#}</span></td>
<td class="d"><input id="bbcode" type="checkbox" name="bbcode" value="1"{if $settings.bbcode==1} checked="checked"{/if} /><label id="bbcode_label" for="bbcode" class="{if $settings.bbcode==1}active{else}inactive{/if}">{#bbcodes_enabled#}</label><br />
<input id="bbcode_img" type="checkbox" name="bbcode_img" value="1"{if $settings.bbcode_img==1} checked="checked"{/if} /><label id="bbcode_img_label" for="bbcode_img" class="{if $settings.bbcode_img==1}active{else}inactive{/if}">{#bbcodes_img_enabled#}</label><br />
<input id="bbcode_flash" type="checkbox" name="bbcode_flash" value="1"{if $settings.bbcode_flash==1} checked="checked"{/if} /><label id="bbcode_flash_label" for="bbcode_flash" class="{if $settings.bbcode_flash==1}active{else}inactive{/if}">{#bbcodes_flash_enabled#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#smilies#}</strong><br /><span class="small">{#smilies_desc#}</span></td>
<td class="d"><input id="smilies" type="checkbox" name="smilies" value="1"{if $settings.smilies==1} checked="checked"{/if} /><label id="smilies_label" for="smilies" class="{if $settings.smilies==1}active{else}inactive{/if}">{#smilies_enabled#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#enamble_avatars#}</strong><br /><span class="small">{#enamble_avatars_desc#}</span></td>
<td class="d"><p><input id="avatars_profiles_postings" type="radio" name="avatars" value="2"{if $settings.avatars==2} checked="checked"{/if} /><label id="avatars_profiles_postings_label" for="avatars_profiles_postings" class="{if $settings.avatars==2}active{else}inactive{/if}">{#avatars_profiles_postings#}</label><br />
<input id="avatars_profiles" type="radio" name="avatars" value="1"{if $settings.avatars==1} checked="checked"{/if} /><label id="avatars_profiles_label" for="avatars_profiles" class="{if $settings.avatars==1}active{else}inactive{/if}">{#avatars_profiles#}</label><br />
<input id="avatars_disabled" type="radio" name="avatars" value="0"{if $settings.avatars==0} checked="checked"{/if} /><label id="avatars_disabled_label" for="avatars_disabled" class="{if $settings.avatars==0}active{else}inactive{/if}">{#disabled#}</label></p>

{assign var="settings_avatar_max_width" value=$settings.avatar_max_width}
{assign var="input_avatar_max_width" value="<input id=\"avatar_max_width\" type=\"text\" name=\"avatar_max_width\" value=\"$settings_avatar_max_width\" size=\"3\" />"}
{assign var="settings_avatar_max_height" value=$settings.avatar_max_height}
{assign var="input_avatar_max_height" value="<input type=\"text\" name=\"avatar_max_height\" value=\"$settings_avatar_max_height\" size=\"3\" />"}
{assign var="settings_avatar_max_filesize" value=$settings.avatar_max_filesize}
{assign var="input_avatar_max_filesize" value="<input type=\"text\" name=\"avatar_max_filesize\" value=\"$settings_avatar_max_filesize\" size=\"3\" />"}
<p><label id="max_avatar_size_label" for="avatar_max_width" class="{if $settings.avatars==0}inactive{else}active{/if}">{#max_avatar_size#|replace:"[width]":$input_avatar_max_width|replace:"[height]":$input_avatar_max_height|replace:"[filesize]":$input_avatar_max_filesize}</label></p></td>
</tr>
<tr>
<td class="c"><strong>{#upload_images#}</strong><br /><span class="small">{#upload_images_desc#}</span></td>
<td class="d"><p><input id="upload_images_all" type="radio" name="upload_images" value="3"{if $settings.upload_images==3} checked="checked"{/if} /><label id="upload_images_all_label" for="upload_images_all" class="{if $settings.upload_images==3}active{else}inactive{/if}">{#upload_enabled_all#}</label><br />
<input id="upload_images_users" type="radio" name="upload_images" value="2"{if $settings.upload_images==2} checked="checked"{/if} /><label id="upload_images_users_label" for="upload_images_users" class="{if $settings.upload_images==2}active{else}inactive{/if}">{#upload_enabled_users#}</label><br />
<input id="upload_images_admins_mods" type="radio" name="upload_images" value="1"{if $settings.upload_images==1} checked="checked"{/if} /><label id="upload_images_admins_mods_label" for="upload_images_admins_mods" class="{if $settings.upload_images==1}active{else}inactive{/if}">{#upload_enabled_admins_mods#}</label><br />
<input id="upload_images_disabled" type="radio" name="upload_images" value="0"{if $settings.upload_images==0} checked="checked"{/if} /><label id="upload_images_disabled_label" for="upload_images_disabled" class="{if $settings.upload_images==0}active{else}inactive{/if}">{#disabled#}</label></p>
{assign var="settings_upload_max_width" value=$settings.upload_max_img_width}
{assign var="input_upload_max_width" value="<input id=\"upload_max_img_width\" type=\"text\" name=\"upload_max_img_width\" value=\"$settings_upload_max_width\" size=\"3\" />"}
{assign var="settings_upload_max_height" value=$settings.upload_max_img_height}
{assign var="input_upload_max_height" value="<input type=\"text\" name=\"upload_max_img_height\" value=\"$settings_upload_max_height\" size=\"3\" />"}
{assign var="settings_upload_max_img_size" value=$settings.upload_max_img_size}
{assign var="input_upload_max_filesize" value="<input type=\"text\" name=\"upload_max_img_size\" value=\"$settings_upload_max_img_size\" size=\"3\" />"}
<p><label id="max_upload_size_label" for="upload_max_img_width" class="{if $settings.upload_images==0}inactive{else}active{/if}">{#max_upload_size#|replace:"[width]":$input_upload_max_width|replace:"[height]":$input_upload_max_height|replace:"[filesize]":$input_upload_max_filesize}</label></p></td>
</tr>
<tr>
<td class="c"><strong>{#autolink#}</strong><br /><span class="small">{#autolink_desc#}</span></td>
<td class="d"><input id="autolink" type="checkbox" name="autolink" value="1"{if $settings.autolink==1} checked="checked"{/if} /><label id="autolink_label" for="autolink" class="{if $settings.autolink==1}active{else}inactive{/if}">{#autolink_enabled#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#count_views#}</strong><br /><span class="small">{#count_views_desc#}</span></td>
<td class="d"><input id="count_views" type="checkbox" name="count_views" value="1"{if $settings.count_views==1} checked="checked"{/if} /><label id="count_views_label" for="count_views" class="{if $settings.count_views==1}active{else}inactive{/if}">{#views_counter_enabled#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#rss_feed#}</strong><br /><span class="small">{#rss_feed_desc#}</span></td>
<td class="d"><input id="rss_feed" type="checkbox" name="rss_feed" value="1"{if $settings.rss_feed==1} checked="checked"{/if} /><label id="rss_feed_label" for="rss_feed" class="{if $settings.rss_feed==1}active{else}inactive{/if}">{#rss_feed_enabled#}</label></td>
</tr>

<tr>
<td class="c"><strong>{#threads_per_page#}</strong><br /><span class="small">{#threads_per_page_desc#}</span></td>
<td class="d"><input type="text" name="threads_per_page" value="{$settings.threads_per_page|escape}" size="5" /></td>
</tr>

<tr>
<td class="c"><strong>{#auto_lock_old_threads#}</strong><br /><span class="small">{#auto_lock_old_threads_desc#}</span></td>
<td class="d"><input type="text" name="auto_lock_old_threads" value="{$settings.auto_lock_old_threads|escape}" size="5" /></td>
</tr>

<tr>
<td class="c"><strong>{#count_users_online#}</strong><br /><span class="small">{#count_users_online_desc#}</span></td>
<td class="d"><input type="text" name="count_users_online" value="{$settings.count_users_online|escape}" size="5" /></td>
</tr>

<tr>
<td class="c"><strong>{#forum_enabled_marking#}</strong><br /><span class="small">{#forum_enabled_desc#}</span></td>
<td class="d"><p><input id="forum_enabled" type="checkbox" name="forum_enabled" value="1"{if $settings.forum_enabled==1} checked="checked"{/if} /><label id="forum_enabled_label" for="forum_enabled" class="{if $settings.forum_enabled==1}active{else}inactive{/if}">{#forum_enabled#}</label></p>
<p><label id="forum_disabled_message_label" for="forum_disabled_message" class="{if $settings.forum_enabled==1}inactive{else}active{/if}">{#forum_disabled_message#}</label><br /><input id="forum_disabled_message" type="text" name="forum_disabled_message" value="{$settings.forum_disabled_message|escape}" size="40" /></p></td>
</tr>
<tr>
<td class="c">&nbsp;</td>
<td class="d"><p class="small"><input id="clear_chache" type="checkbox" name="clear_cache" value="1" /><label for="clear_chache">{#clear_chache#}</label></p>
<p><input type="submit" name="settings_submit" value="{#settings_submit_button#}" /></p></td>
</tr>
</table>
</div>
</form>
<p style="margin-top:10px;"><a class="stronglink" href="index.php?mode=admin&amp;action=advanced_settings">{#advanced_settings_link#}</a></p>
{elseif $action=='advanced_settings'}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
{section name=nr loop=$settings_sorted}
<tr>
<td class="c"><strong>{$settings_sorted[nr].key}</strong></td>
<td class="d"><input type="text" name="{$settings_sorted[nr].key}" value="{$settings_sorted[nr].val|escape}" /></td>
</tr>
{/section}
<tr>
<td class="c">&nbsp;</td>
<td class="d"><input type="submit" name="settings_submit" value="{#settings_submit_button#}" /></td>
</tr>
</table>
</div>
</form>
{elseif $action=='categories'}
{if $entries_in_not_existing_categories>0}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div style="margin:0px 0px 20px 0px; padding:10px; border:1px dotted red;">
<input type="hidden" name="mode" value="admin" />
<p>{#entries_in_not_ex_cat#}</p>
<p><input type="radio" name="entry_action" value="delete" checked="checked" />{#entries_in_not_ex_cat_del#}<br />
<input type="radio" name="entry_action" value="move" />{#entries_in_not_ex_cat_mov#}
<select class="kat" size="1" name="move_category">
{foreach key=key item=val from=$categories}
{if $key!=0}<option value="{$key}">{$val}</option>{/if}
{/foreach}
</select>
</p>
<p><input type="submit" name="entries_in_not_existing_categories_submit" value="{#submit_button_ok#}"></p>
</div>
</form>
{/if}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
{if $categories_count>0}
<table id="sortable" class="normaltab" cellspacing="1" cellpadding="5">
<thead>
<tr>
<th>{#category_name#}</th>
<th>{#category_accession#}</th>
<th>{#category_topics#}</th>
<th>{#category_entries#}</th>
<th>&#160;</th>
</tr>
</thead>
<tbody id="items">
{section name=row loop=$categories_list}
{cycle values="a,b" assign=c}
<tr id="id_{$categories_list[row].id}" class="{$c}">
<td><strong>{$categories_list[row].name}</strong></td>
<td>{if $categories_list[row].accession==2}{#cat_accessible_admin_mod#}{elseif $categories_list[row].accession==1}{#cat_accessible_reg_users#}{else}{#cat_accessible_all#}{/if}</td>
<td>{$categories_list[row].threads_in_category}</td>
<td>{$categories_list[row].postings_in_category}</td>
<td><a href="index.php?mode=admin&amp;edit_category={$categories_list[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/edit.png" title="{#edit#}" alt="{#edit#}" width="16" height="16" /></a> &nbsp; <a href="index.php?mode=admin&amp;delete_category={$categories_list[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete#}" alt="{#delete#}" width="16" height="16"/></a> &nbsp; <a href="index.php?mode=admin&amp;move_up_category={$categories_list[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_up.png" alt="{#up#}" title="{#up#}" width="16" height="16" /></a>&nbsp;<a href="index.php?mode=admin&amp;move_down_category={$categories_list[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_down.png" alt="{#down#}" title="{#down#}" width="16" height="16" /></a></td>
</tr>
{/section}
</tbody>
</table>
{else}
<p>{#no_categories#}</p>
{/if}
<br />
<form action="index.php" method="post" class="normalform" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<label for="new_category" class="main">{#new_category#}</label><br />
<input id="new_category" type="text" name="new_category" size="25" value="{if $new_category}{$new_category}{/if}" /><br /><br />
<strong>{#category_accessible_by#}</strong><br />
<input id="cat_accessible_all" type="radio" name="accession" value="0"{if !$accession || acession==0} checked="checked"{/if} /><label for="cat_accessible_all">{#cat_accessible_all#}</label><br />
<input id="cat_accessible_reg_users" type="radio" name="accession" value="1"{if acession==1} checked="checked"{/if} /><label for="cat_accessible_reg_users">{#cat_accessible_reg_users#}</label><br />
<input id="cat_accessible_admin_mod" type="radio" name="accession" value="2"{if acession==2} checked="checked"{/if} /><label for="cat_accessible_admin_mod">{#cat_accessible_admin_mod#}</label><br /><br />
<input type="submit" value="{#submit_button_ok#}" />
</div>
</form>
{elseif $action=='edit_category'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" class="normalform" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="id" value="{$category_id}" />
<strong>{#edit_category#}</strong><br />
<input type="text" name="category" value="{$edit_category}" size="25" /><br /><br />
<strong>{#category_accessible_by#}</strong><br />
<input id="cat_accessible_all" type="radio" name="accession" value="0"{if $edit_accession==0} checked="checked"{/if} /><label for="cat_accessible_all">{#cat_accessible_all#}</label><br />
<input id="cat_accessible_reg_users" type="radio" name="accession" value="1"{if $edit_accession==1} checked="checked"{/if} /><label for="cat_accessible_reg_users">{#cat_accessible_reg_users#}</label><br />
<input id="cat_accessible_admin_mod" type="radio" name="accession" value="2"{if $edit_accession==2} checked="checked"{/if} /><label for="cat_accessible_admin_mod">{#cat_accessible_admin_mod#}</label><br /><br />
<input type="submit" name="edit_category_submit" value="{#submit_button_ok#}" />
</div>
</form>
{elseif $action=='delete_category'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
<h2>{#delete_category_hl#|replace:"[category]":$category_name}</h2>
<p class="caution">{#caution#}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="category_id" value="{$category_id}" />
<p><input type="radio" name="delete_mode" value="complete" checked="checked" /> {#delete_category_compl#}</p></td>
<p><input type="radio" name="delete_mode" value="keep_entries" /> {if $categories_count <= 1}{#del_cat_keep_entries#}
{else}{#del_cat_move_entries#}
<select class="kat" size="1" name="move_category">
<option value="0">&nbsp;</option>
{foreach key=key item=val from=$move_categories}
{if $key!=0}<option value="{$key}">{$val}</option>{/if}
{/foreach}
</select>
{/if}
<p><input type="submit" name="delete_category_submit" value="{#delete_category_submit#}" /></p>
</div>
</form>
{elseif $action=='user'}
{if $new_user && !$send_error}<p class="ok">{#new_user_registered#|replace:"[name]":$new_user}</p>{elseif $new_user && $send_error}<p class="caution">{#new_user_reg_send_error#|replace:"[name]":$new_user}</p>{/if}
{*<p>{#num_registerd_users#|replace:"[number]":$total_users}</p>*}

<div id="usernav">
<div id="usersearch">
<label for="search-user">{#search_user#}</label><form action="index.php" method="get" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="action" value="user" />
<input id="search-user" type="text" name="search_user" value="{if $search_user}{$search_user}{else}{#search_user_default_value#}{/if}" size="25" alt="{#search_user_default_value#}" />{*&nbsp;<input type="image" src="{$THEMES_DIR}/{$theme}/images/submit.png" alt="[&raquo;]" />*}
</div>
</form>
</div>
<div id="userpagination">
{if $pagination}
<ul class="pagination pagination-index">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$item}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{else}
&nbsp;
{/if}
</div>
</div>

{if $result_count > 0}
{if $no_users_in_selection}<p class="caution">{#no_users_in_sel#}</p>{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<th>&nbsp;</th>
<!--<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_id&amp;descasc={if $descasc=="ASC" && $order=="user_id"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_id#}</a>{if $order=="user_id" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_id" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>-->
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_name&amp;descasc={if $descasc=="ASC" && $order=="user_name"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_name#}</a>{if $order=="user_name" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_name" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_email&amp;descasc={if $descasc=="ASC" && $order=="user_email"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_email#}</a>{if $order=="user_email" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_email" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_type&amp;descasc={if $descasc=="ASC" && $order=="user_type"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_type#}</a>{if $order=="user_type" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_type" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=registered&amp;descasc={if $descasc=="ASC" && $order=="registered"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_registered#}</a>{if $order=="registered" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="registered" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=logins&amp;descasc={if $descasc=="ASC" && $order=="logins"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_logins#}</a>{if $order=="logins" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="logins" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=last_login&amp;descasc={if $descasc=="ASC" && $order=="last_login"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#last_login#}</a>{if $order=="last_login" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="last_login" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=admin&amp;action=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_lock&amp;descasc={if $descasc=="ASC" && $order=="user_lock"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#lock#}</a>{if $order=="user_lock" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_lock" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th colspan="2">&nbsp;</th>
</tr>
{section name=row loop=$userdata}
{cycle values="a,b" assign=c}
<tr class="{$c}">
<td style="width:10px;"><input type="checkbox" name="selected[]" value="{$userdata[row].user_id}" /></td>
<td>{if $userdata[row].inactive}<span title="{#user_inactive#}" style="font-weight:bold;color:red;">{$userdata[row].user_name}</span>{else}<a href="index.php?mode=user&amp;show_user={$userdata[row].user_id}" title="{#show_userdata_linktitle#|replace:"[user]":$userdata[row].user_name}"><strong>{$userdata[row].user_name}</strong></a>{/if}</td>
<td><span class="small"><a href="mailto:{$userdata[row].user_email}" title="{#mailto_user_lt#|replace:"[user]":$userdata[row].user_name}">{$userdata[row].user_email}</a></span></td>
<td><span class="small">{if $userdata[row].user_type==2}{#admin#}{elseif $userdata[row].user_type==1}{#mod#}{else}{#user#}{/if}</span></td>
<td><span class="small">{$userdata[row].registered_time|date_format:#time_format#}</span></td>
<td><span class="small">{$userdata[row].logins}</span></td>
<td><span class="small">{if $userdata[row].logins > 0}{$userdata[row].last_login_time|date_format:#time_format#}{else}&nbsp;{/if}</span></td>
<td><span class="small">{if $userdata[row].user_type>0}{if $userdata[row].user_lock==0}{#unlocked#}{else}{#locked#}{/if}{elseif $userdata[row].user_lock==0}<a href="index.php?mode=admin&amp;user_lock={$userdata[row].user_id}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;page={$page}&amp;order={$order}&amp;descasc={$descasc}" title="{#lock_title#}">{#unlocked#}</a>{else}<a style="color:red;" href="index.php?mode=admin&amp;user_lock={$userdata[row].user_id}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;page={$page}&amp;order={$order}&amp;descasc={$descasc}" title="{#unlock_title#}">{#locked#}</a>{/if}</span></td>
<td><a href="index.php?mode=admin&amp;edit_user={$userdata[row].user_id}"><img src="{$THEMES_DIR}/{$theme}/images/edit.png" title="{#edit#}" alt="{#edit#}" width="16" height="16" /></a></td>
<td><a href="index.php?mode=admin&amp;delete_user={$userdata[row].user_id}"><img src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete#}" alt="{#delete#}" width="16" height="16" /></a></td>
</tr>
{/section}
</table>

<div id="admin-usernav-bottom">

<div id="selectioncontrols">
<img id="arrow-selected" src="{$THEMES_DIR}/{$theme}/images/arrow_selected.png" alt="&#x2191;" width="24" height="14" /> <input type="submit" name="delete_selected_users" value="{#delete_selected_users#}" title="{#delete_users_sb_title#}" />
</div>

<div id="userpagination">
{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$item}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{else}
&nbsp;
{/if}
</div>

</div>
</div>
</form>
{else}
<p><em>{#no_users#}</em></p>
{/if}
<ul class="adminmenu">
<li><a href="index.php?mode=admin&amp;action=register"><img src="{$THEMES_DIR}/{$theme}/images/add_user.png" alt="" width="16" height="16" /><span>{#add_user#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=email_list"><img src="{$THEMES_DIR}/{$theme}/images/email_list.png" alt="" width="16" height="16" /><span>{#email_list#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=clear_userdata"><img src="{$THEMES_DIR}/{$theme}/images/delete.png" alt="" width="16" height="16" /><span>{#clear_userdata#}</span></a></li>
</ul>
{elseif $action=='edit_user'}
{config_load file=$language_file section="user_edit"}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[profile_length]":$profil_length|replace:"[profile_maxlength]":$settings.profile_maxlength|replace:"[signature_length]":$signature_length|replace:"[signature_maxlength]":$settings.signature_maxlength|replace:"[word]":$word}</li>
{/section}
</ul>
{/if}
{if $inactive}<p class="caution">{#caution#}</p><p>{#activate_note#} <a href="index.php?mode=admin&amp;activate={$edit_user_id}">{#activate_link#}</a></p>{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="edit_user_id" value="{$edit_user_id}" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<td class="c"><strong>{#edit_user_name#}</strong></td>
<td class="d"><input type="text" size="40" name="edit_user_name" value="{$edit_user_name}" maxlength="{$settings.name_maxlength}" /></td>
</tr>
{if $avatar}
<tr>
<td class="c"><p class="userdata"><strong>{#edit_user_avatar#}</strong></p></td>
<td class="d"><p class="userdata"><img src="{$avatar.image}" alt="{#avatar_img_alt#}" width="{$avatar.width}" height="{$avatar.height}" /><br />
<input id="delete_avatar" type="checkbox" name="delete_avatar" value="1"{if $delete_avatar=="1"} checked="checked"{/if} /><label for="delete_avatar">{#delete_avatar#}</label></p>
</td>
</tr>
{/if}
<tr>
<td class="c"><strong>{#edit_user_type#}</strong></td>
<td class="d"><input id="edit_user_type_0" type="radio" name="edit_user_type" value="0"{if $edit_user_type==0} checked="checked"{/if} /><label for="edit_user_type_0">{#user#}</label><br /><input id="edit_user_type_1" type="radio" name="edit_user_type" value="1"{if $edit_user_type==1} checked="checked"{/if} /><label for="edit_user_type_1">{#mod#}</label><br /><input id="edit_user_type_2" type="radio" name="edit_user_type" value="2"{if $edit_user_type==2} checked="checked"{/if} /><label for="edit_user_type_2">{#admin#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_email#}</strong></td>
<td class="d"><input type="text" size="40" name="user_email" value="{$user_email}" /><br />
<span class="small"><input id="email_contact" type="checkbox" name="email_contact" value="1"{if $email_contact==1} checked="checked"{/if} /><label for="email_contact">{#edit_user_email_contact#}</label></span></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_hp#}</strong></td>
<td class="d"><input type="text" size="40" name="user_hp" value="{$user_hp}" maxlength="{$settings.hp_maxlength}" /></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_real_name#}</strong></td>
<td class="d"><input type="text" size="40" name="user_real_name" value="{$user_real_name}" maxlength="{$settings.name_maxlength}" /></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_gender#}</strong></td>
<td class="d">
<input id="user_gender_1" type="radio" name="user_gender" value="1"{if $user_gender=="1"} checked="checked"{/if} /><label for="user_gender_1">{#male#}</label><br />
<input id="user_gender_2" type="radio" name="user_gender" value="2"{if $user_gender=="2"} checked="checked"{/if} /><label for="user_gender_2">{#female#}</label>
</td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_birthday#}</strong></td>
<td class="d"><input type="text" size="40" name="user_birthday" value="{$user_birthday}" /> <span class="small">({#birthday_format#})</span></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_location#}</strong></td>
<td class="d"><input type="text" size="40" name="user_location" value="{$user_location}" maxlength="{$settings.location_maxlength}" /></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_profile#}</strong></td>
<td class="d"><textarea cols="65" rows="4" name="profile">{$profile}</textarea></td>
</tr>
<tr>
<td class="c"><strong>{#edit_user_signature#}</strong></td>
<td class="d"><textarea cols="65" rows="4" name="signature">{$signature}</textarea></td>
</tr>

{if $languages}
<tr>
<td class="c"><strong><label for="user_language">{#edit_user_language#}</label></strong></td>
<td class="d">
<select id="user_language" name="user_language" size="1">
<option value=""{if $user_language==''} selected="selected"{/if}></option>
{foreach from=$languages item=l}
<option value="{$l.identifier}"{if $l.identifier==$user_language} selected="selected"{/if}>{$l.title}</option>
{/foreach}
</select>
</td>
</tr>
{/if}

<tr>
<td class="c"><strong>{#edit_user_time_zone#}</strong></td>
<td class="d">
{if $time_zones}
<p>
<select id="user_time_zone" name="user_time_zone" size="1">
<option value=""{if $user_time_zone==''} selected="selected"{/if}></option>
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
<option value=""{if $user_theme==''} selected="selected"{/if}></option>
{foreach from=$themes item=t}
<option value="{$t.identifier}"{if $t.identifier==$user_theme} selected="selected"{/if}>{$t.title}</option>
{/foreach}
</select>
</td>
</tr>
{/if}

{if $edit_user_type==2 || $edit_user_type==1}
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
{elseif $action=='delete_users'}
<p class="caution">{#caution#}</p>
<p>{if $selected_users_count>1}{#delete_users_confirmation#}{else}{#delete_user_confirmation#}{/if}</p>
<ul>
{section name=nr loop=$selected_users}
<li><a href="index.php?mode=user&amp;show_user={$selected_users[nr].id}"><strong>{$selected_users[nr].name}</strong></a></li>
{/section}
</ul>
<br />
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
{section name=nr loop=$selected_users}
<input type="hidden" name="selected_confirmed[]" value="{$selected_users[nr].id}" />
{/section}
<input type="submit" name="delete_confirmed" value="{#delete_submit#}" />
</div>
</form>
{elseif $action=='user_delete_entries'}
<p class="caution">{#caution#}</p>
<p>{#delete_entries_of_user_confirm#|replace:"[user]":$user_delete_entries['user']}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="user_delete_entries" value="{$user_delete_entries.id}" />
<input type="submit" name="delete_confirmed" value="{#delete_submit#}" />
</div>
</form>
{elseif $action=='register'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<p><label for="ar_username" class="main">{#register_username#}</label><br />
<input id="ar_username" type="text" size="25" name="ar_username" value="{$ar_username|default:''}" maxlength="{$settings.name_maxlength}" /></p>
<p><label for="ar_email" class="main">{#register_email#}</label><br />
<input id="ar_email" type="text" size="25" name="ar_email" value="{$ar_email|default:''}" maxlength="{$settings.email_maxlength}" /></p>
<p><label for="ar_pw" class="main">{#register_pw#}</label><br />
<input id="ar_pw" type="password" size="25" name="ar_pw" maxlength="50" /></p>
<p><label for="ar_pw_conf" class="main">{#register_pw_conf#}</label><br />
<input id="ar_pw_conf" type="password" size="25" name="ar_pw_conf" maxlength="50" /></p>
<p><input id="ar_send_userdata" type="checkbox" name="ar_send_userdata" value="true"{if $ar_send_userdata} checked="checked"{/if} /> <label for="ar_send_userdata">{#register_send_userdata#}</label></p>
<p><input type="submit" name="register_submit" value="{#submit_button_ok#}" /></p>
</div>
</form>
<p class="small">{#register_exp#}</p>
{elseif $action=='smilies'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
{if $settings.smilies==1}
<table id="sortable" class="normaltab" border="0" cellpadding="5" cellspacing="1">
<thead>
<tr>
<th>{#smiley_image#}</th>
<th>{#smiley_codes#}</th>
<th>{#smiley_title#}</th>
<th>&#160;</th>
</tr>
</thead>
<tbody id="items">
{section name=row loop=$smilies}
{cycle values="a,b" assign=c}
<tr id="id_{$smilies[row].id}" class="{$c}">
<td><img src="images/smilies/{$smilies[row].file}" alt="{$smilies[row].code_1}" /></td>
<td>{$smilies[row].codes}</td>
<td>{$smilies[row].title}</td>
<td>

<a href="index.php?mode=admin&amp;edit_smiley={$smilies[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/edit.png" title="{#edit#}" alt="{#edit#}" width="16" height="16" /></a> &nbsp; <a href="index.php?mode=admin&amp;delete_smiley={$smilies[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete#}" alt="{#delete#}" width="16" height="16"/></a> &nbsp; <a href="index.php?mode=admin&amp;move_up_smiley={$smilies[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_up.png" alt="{#move_up#}" title="{#move_up#}" width="16" height="16" /></a><a href="index.php?mode=admin&amp;move_down_smiley={$smilies[row].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_down.png" alt="{#move_down#}" title="{#move_down#}" width="16" height="16" /></a>

</td>

</tr>
{/section}
</tbody>
</table>
{if $smiley_files}
<form action="index.php" method="post" class="normalform" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin">
<table style="margin-top:20px;">
<tr>
<td><label for="add_smiley">{#add_smiley#}</label></td>
<td><label for="smiley_code">{#add_smiley_code#}</label></td>
<td>&nbsp;</td>
</tr>
<tr>
<td>
<select id="add_smiley" name="add_smiley" size="1">
{section name=nr loop=$smiley_files}
<option value="{$smiley_files[nr]}">{$smiley_files[nr]}</option>
{/section}
</select>
</select></td>
<td><input id="smiley_code" type="text" name="smiley_code" size="10" /></td>
<td><input type="submit" value="{#submit_button_ok#}" /></td>
</tr>
</table>
</div>
</form>
{else}
<p style="margin-top:20px;"><em>{#no_other_smilies_in_folder#}</em></p>
{/if}
{else}
<p><em>{#smilies_disabled#}</em></p>
{/if}
<ul class="adminmenu">
<li>{if $settings.smilies==1}<a href="index.php?mode=admin&amp;disable_smilies=true"><img src="{$THEMES_DIR}/{$theme}/images/smilies_disable.png" alt="" width="16" height="16" /><span>{#disable_smilies#}</span></a>{else}<a href="index.php?mode=admin&amp;enable_smilies=true"><img src="{$THEMES_DIR}/{$theme}/images/smilies.png" alt="" width="16" height="16" /><span>{#enable_smilies#}</span></a>{/if}</li>
</ul>
{elseif $action=='spam_protection'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
{if $saved}<p class="ok">{#spam_protection_saved#}</p>{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">

<tr>
<td class="c" style="width:30%;"><strong>{#captcha#}</strong><br /><span class="small">{#captcha_desc#}{if !$graphical_captcha_available || !$font_available}<br />{#captcha_graphical_desc#}{/if}</span></td>
<td class="d">
 <table border="0" cellpadding="0" cellspacing="3">
  <tr>
   <td><strong>{#captcha_posting#}</strong></td>
   <td><input id="captcha_posting_0" type="radio" name="captcha_posting" value="0"{if $captcha_posting==0} checked="checked"{/if} /><label for="captcha_posting_0">{#captcha_disabled#}</label></td>
   <td><input id="captcha_posting_1" type="radio" name="captcha_posting" value="1"{if $captcha_posting==1} checked="checked"{/if} /><label for="captcha_posting_1">{#captcha_mathematical#}</label></td>
   <td><input id="captcha_posting_2" type="radio" name="captcha_posting" value="2"{if $captcha_posting==2} checked="checked"{/if}{if !$graphical_captcha_available} disabled="disabled"{/if} /><label for="captcha_posting_2"{if !$graphical_captcha_available} class="unavailable"{/if}>{#captcha_graphical#}{if !$graphical_captcha_available || !$font_available}<sup>*</sup>{/if}</label></td>
  </tr>
  <tr>
   <td><strong>{#captcha_email#}</strong></td>
   <td><input id="captcha_email_0" type="radio" name="captcha_email" value="0"{if $captcha_email==0} checked="checked"{/if} /><label for="captcha_email_0">{#captcha_disabled#}</label></td>
   <td><input id="captcha_email_1" type="radio" name="captcha_email" value="1"{if $captcha_email==1} checked="checked"{/if} /><label for="captcha_email_1">{#captcha_mathematical#}</label></td>
   <td><input id="captcha_email_2" type="radio" name="captcha_email" value="2"{if $captcha_email==2} checked="checked"{/if}{if !$graphical_captcha_available} disabled="disabled"{/if} /><label for="captcha_email_2"{if !$graphical_captcha_available} class="unavailable"{/if}>{#captcha_graphical#}{if !$graphical_captcha_available || !$font_available}<sup>*</sup>{/if}</label></td>
  </tr>
  <tr>
   <td><strong>{#captcha_register#}</strong></td>
   <td><input id="captcha_register_0" type="radio" name="captcha_register" value="0"{if $captcha_register==0} checked="checked"{/if} /><label for="captcha_register_0">{#captcha_disabled#}</label></td>
   <td><input id="captcha_register_1" type="radio" name="captcha_register" value="1"{if $captcha_register==1} checked="checked"{/if} /><label for="captcha_register_1">{#captcha_mathematical#}</label></td>
   <td><input id="captcha_register_2" type="radio" name="captcha_register" value="2"{if $captcha_register==2} checked="checked"{/if}{if !$graphical_captcha_available} disabled="disabled"{/if} /><label for="captcha_register_2"{if !$graphical_captcha_available} class="unavailable"{/if}>{#captcha_graphical#}{if !$graphical_captcha_available || !$font_available}<sup>*</sup>{/if}</label></td>
  </tr>
 </table>
 {if !$graphical_captcha_available || !$font_available}
 <p class="xsmall"><sup>*</sup> {if !$graphical_captcha_available}{#gr_captcha_not_available#}{elseif !$font_available}{#gr_captcha_no_font#}{/if}</p>
 {/if}
</td>
</tr>

<tr>
<td class="c"><strong>{#bad_behavior#}</strong><br /><span class="small">{#bad_behavior_desc#}</span></td>
<td class="d"><input id="bad_behavior" type="checkbox" name="bad_behavior" value="1"{if $bad_behavior==1} checked="checked"{/if} /><label for="bad_behavior">{#bad_behavior_enable#}</label></td>
</tr>
<tr>
<td class="c"><strong>{#akismet#}</strong><br /><span class="small">{#akismet_desc#}</span></td>
<td class="d"><p>{#akismet_key#}<br />
<input type="text" name="akismet_key" value="{$akismet_key}" size="25" /></p>
<p><input id="akismet_entry_check" type="checkbox" name="akismet_entry_check" value="1"{if $akismet_entry_check==1} checked="checked"{/if} /><label for="akismet_entry_check">{#akismet_entry#}</label><br />
<input id="akismet_mail_check" type="checkbox" name="akismet_mail_check" value="1"{if $akismet_mail_check==1} checked="checked"{/if} /><label for="akismet_mail_check">{#akismet_mail#}</label><br />
<input id="akismet_check_registered" type="checkbox" name="akismet_check_registered" value="1"{if $akismet_check_registered==1} checked="checked"{/if} /><label for="akismet_check_registered">{#akismet_registered#}</label></p>
<p>{#akismet_save_spam#} <input type="radio" name="save_spam" value="1"{if $save_spam==1} checked="checked"{/if} />{#yes#} <input type="radio" name="save_spam" value="0"{if $save_spam==0} checked="checked"{/if} />{#no#}<br />
{#akismet_auto_delete_spam#} <input type="text" name="auto_delete_spam" value="{$auto_delete_spam}" size="5" /></p></td>
</tr>
<tr>
<td class="c"><strong>{#not_accepted_words#}</strong><br /><span class="small">{#not_accepted_words_desc#}</span></td>
<td class="d"><textarea name="not_accepted_words" cols="35" rows="10">{$not_accepted_words}</textarea></td>
</tr>
<tr>
<td class="c"><strong>{#banned_ips#}</strong><br /><span class="small">{#banned_ips_desc#}</span></td>
<td class="d"><textarea name="banned_ips" cols="35" rows="5">{$banned_ips}</textarea></td>
</tr>
<tr>
<td class="c"><strong>{#banned_user_agents#}</strong><br /><span class="small">{#banned_user_agents_desc#}</span></td>
<td class="d"><textarea name="banned_user_agents" cols="35" rows="5">{$banned_user_agents}</textarea></td>
</tr>
<tr>
<td class="c">&nbsp;</td>
<td class="d"><input type="submit" name="spam_protection_submit" value="{#spam_protection_submit#}" /></td>
</tr>
</table>
</div>
</form>
{elseif $action=='reset_uninstall'}
<p class="caution">{#caution#}</p>
{if $errors}{include file="$theme/subtemplates/errors.inc.tpl"}{/if}

<h2>{#reset_forum#}</h2>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<p><input id="delete_postings" type="checkbox" name="delete_postings" value="true" /><label for="delete_postings"> {#delete_postings#}</label></p>
<p><input id="delete_userdata" type="checkbox" name="delete_userdata" value="true" /><label for="delete_userdata"> {#delete_userdata#}</label></p>
<p>{#admin_confirm_password#}<br />
<input type="password" size="20" name="confirm_pw" /> <input type="submit" name="reset_forum_confirmed" value="{#reset_forum_submit#}" /></p>
</div>
</form>

<hr style="margin:20px 0px 20px 0px; border-top: 1px dotted #808080; border-left: 0; border-right: 0; border-bottom: 0; height: 1px;"/>

<h2>{#uninstall_forum#}</h2>
<p>{#uninstall_forum_exp#}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<p>{#reset_uninstall_conf_pw#}<br />
<input type="password" size="20" name="confirm_pw" /> <input type="submit" name="uninstall_forum_confirmed" value="{#uninstall_forum_submit#}" /></p>
</div>
</form>
{elseif $action=='backup'}
{if $errors}{include file="$theme/subtemplates/errors.inc.tpl"}{/if}
{if $message}<p class="ok">{$smarty.config.$message}</p>{/if}
{if $backup_files}
<form id="selectform" action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="delete_backup_files_confirm" value="" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<th>&#160;</th>
<th>{#backup_file#}</th>
<th>{#backup_date#}</th>
<th>{#backup_size#}</th>
<th>&#160;</th>
</tr>
{foreach from=$backup_files item=file}
{cycle values="a,b" assign=c}
<tr class="{$c}">
<td style="width:10px;"><input type="checkbox" name="delete_backup_files[]" value="{$file.file}" /></td>
<td>{$file.file}</td>
<td>{$file.date|date_format:#time_format#}</td>
<td>{$file.size}</td>
<td><a href="index.php?mode=admin&amp;download_backup_file={$file.file}"><img src="{$THEMES_DIR}/{$theme}/images/disk.png" title="{#download_backup_file#}" alt="{#download_backup_file#}" width="16" height="16" /></a> &#160; <a href="index.php?mode=admin&amp;restore={$file.file}"><img src="{$THEMES_DIR}/{$theme}/images/restore.png" title="{#restore#}" alt="{#restore#}" width="16" height="16" /></a> &#160; <a href="index.php?mode=admin&amp;delete_backup_files[]={$file.file}" onclick="return delete_backup_confirm(this, '{$smarty.config.delete_backup_file_confirm|escape:"url"}')"><img src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete_backup_file#}" alt="{#delete_backup_file#}" width="16" height="16" /></a></td>
</tr>
{/foreach}
</table>
<div id="selectioncontrols"><img id="arrow-selected" src="{$THEMES_DIR}/{$theme}/images/arrow_selected.png" alt="" width="24" height="14" /> <input type="submit" name="delete_selected_backup_files" value="{#delete_selected#}" /></div>
</div>
</form>
{else}
<p class="caution">{#caution#}</p>
<p>{#backup_note#}</p>
<!--<p><em>No backup files available.</em></p>-->
{/if}
<ul class="adminmenu">
<li><a href="index.php?mode=admin&amp;create_backup=0"><img src="{$THEMES_DIR}/{$theme}/images/backup.png" alt="" width="16" height="16" /><span>{#create_backup_complete#}</span></a></li>
<li><span class="small">{#only_create_backup_of#} <a href="index.php?mode=admin&amp;create_backup=1"><span>{#backup_entries#}</span></a>, <a href="index.php?mode=admin&amp;create_backup=2"><span>{#backup_userdata#}</span></a>, <a href="index.php?mode=admin&amp;create_backup=3"><span>{#backup_settings#}</span></a>, <a href="index.php?mode=admin&amp;create_backup=4"><span>{#backup_categories#}</span></a>, <a href="index.php?mode=admin&amp;create_backup=5"><span>{#backup_pages#}</span></a>, <a href="index.php?mode=admin&amp;create_backup=6"><span>{#backup_smilies#}</span></a>, <a href="index.php?mode=admin&amp;create_backup=7"><span>{#backup_banlists#}</span></a></span></li>
</ul>
{elseif $action=='delete_backup_files_confirm'}
<p class="caution">{#caution#}</p>
<p>{if $file_number==1}{#delete_backup_file_confirm#}{else}{#delete_backup_files_confirm#}{/if}</p>
<ul>
{section name=nr loop=$delete_backup_files}
<li>{$delete_backup_files[nr]}</li>
{/section}
</ul>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
{section name=nr loop=$delete_backup_files}
<input type="hidden" name="delete_backup_files[]" value="{$delete_backup_files[nr]}" />
{/section}
<input type="submit" name="delete_backup_files_confirm" value="{#delete_backup_submit#}" />
</div>
</form>
{elseif $action=='restore'}
<p class="caution">{#caution#}</p>
<p>{#restore_confirm#}</p>
<p><strong>{$backup_file}</strong> - {$backup_file_date|date_format:#time_format#}</p>
{if $safe_mode_warning}<p class="caution">{#restore_safe_mode_warning#}</p>
<p style="color:red;">{#restore_safe_mode_note#}</p>{/if}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=error loop=$errors}
{assign var="error" value=$errors[error]}
<li>{$smarty.config.$error|replace:"[mysql_error]":$mysql_error}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="backup_file" value="{$backup_file}" />
<p>{#admin_confirm_password#}<br /><input type="password" name="restore_password" size="25"/></p>
<p><input type="submit" name="restore_submit" value="{#restore_submit#}" onclick="document.getElementById('throbber-submit').style.visibility = 'visible';" /> <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</div>
</form>
{elseif $action=='update'}
<p style="margin-bottom:25px;"><span style="background:yellow; padding:5px;">{#update_current_version#|replace:"[version]":$settings.version}</span></p>

<h3>{#update_instructions_hl#}</h3>
<ul>
{foreach from=$smarty.config.update_instructions item=instruction}
<li>{$instruction}</li>
{/foreach}
</ul>

{if $errors}{include file="$theme/subtemplates/errors.inc.tpl"}{/if}
{if $message}<p class="ok">{$smarty.config.$message}</p>{/if}
{if $update_files}
<h3>{#update_available_files#}</h3>
<ul>
{foreach from=$update_files item=file}
<li><a href="index.php?mode=admin&amp;run_update={$file.filename}" title="{#update_file_title#}">{$file.filename}</a></li>
{/foreach}
</ul>
{else}
<p><em>{#update_no_files_available#}</em></p>
{/if}
{elseif $action=='run_update'}
<p class="caution">{#caution#}</p>
<p>{#update_confirm#}</p>
<p><strong>{$update_file}</strong>{if $update_from_version && $update_to_version} {#update_file_details#|replace:"[update_from_version]":$update_from_version|replace:"[update_to_version]":$update_to_version}{/if}</p>
<p style="color:red;font-weight:bold;">{#update_note#}</p>
{if $errors}{include file="$theme/subtemplates/errors.inc.tpl"}{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="update_file_submit" value="{$update_file}" />
<p>{#admin_confirm_password#}<br /><input type="password" name="update_password" size="25"/></p>
<p><input type="submit" name="update_submit" value="{#update_submit#}" onclick="document.getElementById('throbber-submit').style.visibility = 'visible';" /> <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</div>
</form>
{elseif $action=='update_done'}
{if $update_errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$update_errors}
{assign var="error" value=$update_errors[mysec]}
<li>{$error}</li>
{/section}
</ul>
{else}
<p class="ok">{#update_successful#}</p>
{/if}
{if $update_items}
<p>{#update_items_note#|replace:"[version]":$update_new_version}</p>
<ul class="filelist">
{foreach from=$update_items item=item}
<li><img src="{$THEMES_DIR}/{$theme}/images/{if $item.type==0}folder.png{else}file.png{/if}" alt="[{if $item.type==0}{#folder_alt#}{else}{#file_alt#}{/if}]" width="16" height="16" />{$item.name}</li>
{/foreach}
</ul>
{/if}
{if $update_download_url}<p class="small">{#update_download#|replace:"[[":"<a href=\"$update_download_url\">"|replace:"]]":"</a>"}</p>{/if}
{if $update_message}{$update_message}{/if}
{elseif $action == 'email_list'}
<textarea onfocus="this.select()" onclick="this.select()" readonly="readonly" cols="60" rows="15">{$email_list}</textarea>
{elseif $action == 'clear_userdata'}
{if $no_users_in_selection}<p class="caution">{#no_users_in_selection#}</p>{/if}
{assign var="input_logins" value="<input type=\"text\" name=\"logins\" value=\"$logins\" size=\"4\" />"}
{assign var="input_days" value="<input type=\"text\" name=\"days\" value=\"$days\" size=\"4\" />"}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<p>{$smarty.config.clear_userdata_condition|replace:"[logins]":$input_logins|replace:"[days]":$input_days} <input type="submit" name="clear_userdata" value="{#submit_button_ok#}" /></p>
</div>
</form>
<p class="small">{#clear_userdata_note#}</p>
{elseif $action == 'edit_smiley'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="id" value="{$id}" />
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<td class="c"><strong>{#edit_smilies_smiley#}</strong></td>
<td class="d"><select name="file" size="1">
{section name=nr loop=$smiley_files}
<option value="{$smiley_files[nr]}"{if $file==$smiley_files[nr]} selected="selected"{/if}>{$smiley_files[nr]}</option>
{/section}
</select></td>
</tr>
<tr>
<td class="c"><strong>{#edit_smilies_codes#}</strong></td>
<td class="d"><input type="text" name="code_1" size="7" value="{$code_1}" /> <input type="text" name="code_2" size="7" value="{$code_2}" /> <input type="text" name="code_3" size="7" value="{$code_3}" /> <input type="text" name="code_4" size="7" value="{$code_4}" /> <input type="text" name="code_5" size="7" value="{$code_5}" /></td>
</tr>
<tr>
<td class="c"><strong>{#edit_smilies_title#}</strong></td>
<td class="d"><input type="text" name="title" size="25" value="{$title}" /></td>
</tr>
<tr>
<td class="c">&nbsp;</td>
<td class="d"><input type="submit" name="edit_smiley_submit" value="{#submit_button_ok#}" /></td>
</tr>
</table>
</div>
</form>
{elseif $action=='pages'}
{if $pages}
<table id="sortable" class="normaltab" cellspacing="1" cellpadding="5">
<thead>
<tr>
<th>{#page_title#}</th>
<th>{#page_menu_linkname#}</th>
<th>{#page_access#}</th>
<th>&#160;</th>
</tr>
</thead>
<tbody id="items">
{section name=page loop=$pages}
{cycle values="a,b" assign=c}
<tr id="id_{$pages[page].id}" class="{$c}">
<td><a href="index.php?mode=page&amp;id={$pages[page].id}" title="{$pages[page].title}"><strong class="control">{$pages[page].title}</strong></a></td>
<td><span class="small">{if $pages[page].menu_linkname!=''}{$pages[page].menu_linkname}{else}&nbsp;{/if}</span></td>
<td><span class="small">{if $pages[page].access==1}{#page_access_reg_users#}{elseif $pages[page].access==0}{#page_access_public#}{/if}</span></td>
<td><a href="index.php?mode=admin&amp;edit_page={$pages[page].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/edit.png" title="{#edit#}" alt="{#edit#}" width="16" height="16" /></a> &#160; <a href="index.php?mode=admin&amp;delete_page={$pages[page].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete#}" alt="{#delete#}" width="16" height="16"/></a> &nbsp; <a href="index.php?mode=admin&amp;move_up_page={$pages[page].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_up.png" alt="{#move_up#}" title="{#move_up#}" width="16" height="16" /></a>&nbsp;<a href="index.php?mode=admin&amp;move_down_page={$pages[page].id}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_down.png" alt="{#move_down#}" title="{#move_down#}" width="16" height="16" /></a></td>
</tr>
{/section}
</tbody>
</table>
{else}
<p>{#no_pages#}</p>
{/if}
<ul class="adminmenu"><li><a href="index.php?mode=admin&amp;action=edit_page"><img src="{$THEMES_DIR}/{$theme}/images/add_page.png" alt="" width="16" height="16" /><span>{#add_page_link#}</span></a></li></ul>
{elseif $action=='edit_page'}
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
{if $id}<input type="hidden" name="id" value="{$id}" />{/if}
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<td class="c"><strong>{#page_title#}</strong></td>
<td class="d"><input type="text" name="title" value="{$title|default:""}" size="50" /></td>
</tr>
<tr>
<td class="c"><strong>{#page_content#}</strong><br /><span class="small">{#page_content_desc#}</span></td>
<td class="d"><textarea name="content" cols="70" rows="20">{$content|default:""}</textarea></td>
</tr>
<tr>
<td class="c"><strong>{#page_menu_linkname#}</strong><br /><span class="small">{#page_menu_linkname_desc#}</span></td>
<td class="d"><input type="text" name="menu_linkname" value="{$menu_linkname|default:""}" size="50" /></td>
</tr>
<tr>
<td class="c"><strong>{#page_access#}</strong></td>
<td class="d"><input type="radio" name="access" value="0"{if $access==0} checked="checked"{/if} /><span class="small">{#page_access_public#}</span> <input type="radio" name="access" value="1"{if $access==1} checked="checked"{/if} /><span class="small">{#page_access_reg_users#}</span></td>
</tr>

<tr>
<td class="c">&nbsp;</td>
<td class="d"><input type="submit" name="edit_page_submit" value="{#edit_page_submit#}" /></td>
</tr>
</table>
</div>
</form>
{elseif $action=='delete_page'}
{if $page}
<p class="caution">{#caution#}</p>
<p>{#delete_page_confirm#}</p>
<p><strong>{$page.title}</strong></p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="admin" />
<input type="hidden" name="id" value="{$page.id}" />
<input type="submit" name="delete_page_submit" value="{#delete_page_submit#}" />
</div>
</form>
{else}
<p>{#page_doesnt_exist#}</p>
{/if}
{else}
<ul class="adminmenu">
<li><a href="index.php?mode=admin&amp;action=settings"><img src="{$THEMES_DIR}/{$theme}/images/settings.png" alt="" width="16" height="16" /><span>{#forum_settings_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=user"><img src="{$THEMES_DIR}/{$theme}/images/user.png" alt="" width="16" height="16" /><span>{#user_administr_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=categories"><img src="{$THEMES_DIR}/{$theme}/images/categories.png" alt="" width="16" height="16" /><span>{#category_administr_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=smilies"><img src="{$THEMES_DIR}/{$theme}/images/smilies.png" alt="" width="16" height="16" /><span>{#smilies_administr_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=pages"><img src="{$THEMES_DIR}/{$theme}/images/pages.png" alt="" width="16" height="16" /><span>{#pages_administr_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=spam_protection"><img src="{$THEMES_DIR}/{$theme}/images/spam_protection.png" alt="" width="16" height="16" /><span>{#spam_protection_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=backup"><img src="{$THEMES_DIR}/{$theme}/images/backup.png" alt="" width="16" height="16" /><span>{#backup_restore_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=update"><img src="{$THEMES_DIR}/{$theme}/images/update.png" alt="" width="16" height="16" /><span>{#update_link#}</span></a></li>
<li><a href="index.php?mode=admin&amp;action=reset_uninstall"><img src="{$THEMES_DIR}/{$theme}/images/delete.png" alt="" width="16" height="16" /><span>{#reset_uninstall_link#}</span></a></li>
</ul>
{/if}
