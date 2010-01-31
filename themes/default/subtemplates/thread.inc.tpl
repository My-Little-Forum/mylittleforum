{config_load file=$language_file section="entry"}
<script type="text/javascript">/* <![CDATA[ */
function hp(id)
{literal}{{/literal}
hide_posting(id,'{$THEMES_DIR}/{$theme}/images/show_posting.png','{$THEMES_DIR}/{$theme}/images/hide_posting.png');
{literal}}{/literal}
function hpt(id)
{literal}{{/literal}
document.getElementById('headline-'+id).title='{#show_hide_posting_title#|escape:"quotes"}';
document.getElementById('headline-'+id).style.cursor='pointer';
{literal}}{/literal}
function dl(ths)
{literal}{{/literal}
return delete_posting_confirm(ths, '{if $admin||$mod}{$smarty.config.delete_posting_confirm_admin|escape:"url"}{else}{$smarty.config.delete_posting_confirm|escape:"url"}{/if}')
{literal}}{/literal}
/* ]]> */</script>

{defun name="tree" element=$tid level=0}
<div class="{if $level==0}ct-thread{elseif $level>0&&$level<$settings.deep_reply}ct-reply{elseif $level>=$settings.deep_reply&&$level<$settings.very_deep_reply}ct-deep-reply{else}ct-very-deep-reply{/if}">
{*{assign var=formated_time value=$data.$element.disp_time|date_format:#time_format_full#}*}
{assign var=email_alt value=$smarty.config.email}
{assign var=homepage_alt value=$smarty.config.homepage}
{assign var=hp value=$data.$element.hp}
{assign var=email value=$data.$element.email}
{assign var=email_id value=$data.$element.id}
{if $hp && !$email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a>"}
{elseif !$hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/email.png\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"10\" /></a>"}
{elseif $hp && $email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/email.png\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"10\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}

{if $data.$element.user_type==2}
{assign var=admin_name value=$data.$element.name}
{assign var=admin_title value=$smarty.config.administrator_title}
{assign var=name value="<span class=\"admin\" title=\"$admin_title\">$admin_name</span>"}
{elseif $data.$element.user_type==1}
{assign var=mod_name value=$data.$element.name}
{assign var=mod_title value=$smarty.config.moderator_title}
{assign var=name value="<span class=\"mod\" title=\"$mod_title\">$mod_name</span>"}
{else}
{assign var=name value=$data.$element.name}
{/if}

{if ($user || $settings.user_area_public==1) && $data.$element.user_id>0}
{assign var=posting_user_id value=$data.$element.user_id}
{assign var=name value="<a href=\"index.php?mode=user&amp;show_user=$posting_user_id\">$name</a>"}
{/if}
<div class="ct-posting{if $last_visit&&$data.$element.time>$last_visit||$newtime&&$data.$element.time>$newtime} new{/if}" id="p{$data.$element.id}">
<div class="ct-postinghead">
{if $data.$element.avatar}<img id="avatar-{$data.$element.id}" class="avatar" src="{$data.$element.avatar.image}" alt="{#avatar_img_alt#}" width="{$data.$element.avatar.width}" height="{$data.$element.avatar.height}" />{/if}
<h{if $data.$element.pid==0}1{else}2{/if} id="headline-{$data.$element.id}" onmouseover="this.style.color='#000080'" onmouseout="this.style.color='#000'" onclick="hp({$data.$element.id})">{$data.$element.subject}{if $data.$element.pid==0 && $category_name} <span class="category">({$category_name})</span>{/if}</h{if $data.$element.pid==0}1{else}2{/if}><script type="text/javascript">/* <![CDATA[ */ hpt({$data.$element.id}); /* ]]> */</script>
<p class="ct-author">{if $data.$element.location}{#posted_by_location#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[location]":$data.$element.location|replace:"[time]":$data.$element.formated_time}{else}{#posted_by#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[time]":$data.$element.formated_time}{/if} <span class="ago">({if $data.$element.ago.days>1}{#posting_several_days_ago#|replace:"[days]":$data.$element.ago.days_rounded}{else}{if $data.$element.ago.days==0 && $data.$element.ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$data.$element.ago.minutes}{elseif $data.$element.ago.days==0 && $data.$element.ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$data.$element.ago.hours|replace:"[minutes]":$data.$element.ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$data.$element.ago.hours|replace:"[minutes]":$data.$element.ago.minutes}{/if}{/if})</span>{if $admin} <span class="ip">({$data.$element.ip})</span>{/if}{if $data.$element.pid!=0}{assign var="parent_posting" value=$data.$element.pid} <span class="op-link"><a href="#p{$data.$element.pid}" title="{#original_posting_linktitle#|replace:"[name]":$data.$parent_posting.name}">@ {$data.$parent_posting.name}</a></span>{/if}{if $data.$element.edited}<br />
<span class="edited">{#edited_by#|replace:"[name]":$data.$element.edited_by|replace:"[time]":$data.$element.formated_edit_time}</span>{/if}</p>
</div>
<div class="postingcontainer" id="posting-{$data.$element.id}">
<div class="ct-postingbody">
{if $data.$element.posting}
{$data.$element.posting}
{else}
<p>{#no_text#}</p>
{/if}
{if $data.$element.signature}
<p class="signature">--<br />
{$data.$element.signature}</p>
{/if}
{if $data.$element.tags}
<p class="tags">{#tags_marking#}<br />
{foreach name="tags" from=$data.$element.tags item=tag}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}</p>
{/if}
</div>
<div class="ct-postingfooter">
<div class="postinganswer">{if $data.$element.locked==0}<a class="stronglink" href="index.php?mode=posting&amp;id={$data.$element.id}&amp;back=thread" title="{#reply_link_title#}">{#reply_link#}</a>{else}<span class="locked"><img src="{$THEMES_DIR}/{$theme}/images/lock.png" alt="" width="14" height="12" />{#posting_locked#}</span>{/if}</div>
<div class="postingedit">&nbsp;
{if $data.$element.views}<span class="xsmall">{if $data.$element.views==1}{#one_view#}{else}{#several_views#|replace:"[views]":$data.$element.views}{/if}</span>{/if}
{if $data.$element.edit_authorization} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;edit={$data.$element.id}&amp;back=thread" title="{#edit_message_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/edit_small.png" alt="" width="15" height="10" />{#edit_message_linkname#}</a></span>{/if}
{if $data.$element.delete_authorization} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;delete_posting={$data.$element.id}&amp;back=thread" title="{#delete_message_linktitle#}" onclick="return dl(this)"><img src="{$THEMES_DIR}/{$theme}/images/delete_small.png" alt="" width="13" height="9" />{#delete_message_linkname#}</a></span>{/if}
{if $data.$element.move_posting_link} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;move_posting={$data.$element.id}&amp;back=thread" title="{#move_posting_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/move_posting.png" alt="" width="14" height="10" />{#move_posting_linkname#}</a></span>{/if}
{if $data.$element.report_spam_link} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;report_spam={$data.$element.id}&amp;back=thread" title="{#report_spam_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/spam_link.png" alt="" width="13" height="9" />{#report_spam_linkname#}</a></span>{/if}
{if $data.$element.flag_ham_link} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;flag_ham={$data.$element.id}&amp;back=thread" title="{#flag_ham_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/spam_link.png" alt="" width="13" height="9" />{#flag_ham_linkname#}</a></span>{/if}
{if $admin || $mod} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;lock={$data.$element.id}&amp;back=thread" title="{if $data.$element.locked==0}{#lock_linktitle#}{else}{#unlock_linktitle#}{/if}"><img src="{$THEMES_DIR}/{$theme}/images/{if $data.$element.locked==0}lock.png{else}unlock.png{/if}" alt="" width="14" height="12" />{if $data.$element.locked==0}{#lock_linkname#}{else}{#unlock_linkname#}{/if}</a></span>{if $data.$element.pid==0} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;lock_thread={$data.$element.id}&amp;back=thread" title="{#lock_thread_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/lock_thread.png" alt="" width="14" height="12" />{#lock_thread_linkname#}</a></span> &nbsp;<span class="small"><a href="index.php?mode=posting&amp;unlock_thread={$data.$element.id}&amp;back=thread" title="{#unlock_thread_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/unlock_thread.png" alt="" width="14" height="12" />{#unlock_thread_linkname#}</a></span>{/if}{/if}
</div>
</div>
</div>
</div>
{if is_array($child_array[$element])}
{foreach from=$child_array[$element] item=child}{fun name="tree" element=$child level=$level+1}{/foreach}
{/if}
</div>
{/defun}
{if $settings.rss_feed==1}<div class="small" style="text-align:right;"><a class="rss" href="index.php?mode=rss&amp;thread={$tid}" title="{#rss_feed_thread_title#}">{#rss_feed_thread#}</a></div>{/if}
