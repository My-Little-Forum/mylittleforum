{config_load file=$language_file section="entry"}
{assign var=email_alt value=$smarty.config.email}
{assign var=homepage_alt value=$smarty.config.homepage}
{if $hp && !$email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a>"}
{elseif !$hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/email.png\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"10\" /></a>"}
{elseif $hp && $email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/email.png\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"10\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}
{if $user_type==2}
{assign var=admin_title value=$smarty.config.administrator_title}
{assign var=name value="<span class=\"admin\" title=\"$admin_title\">$name</span>"}
{elseif $user_type==1}
{assign var=mod_title value=$smarty.config.moderator_title}
{assign var=name value="<span class=\"mod\" title=\"$mod_title\">$name</span>"}
{else}
{assign var=name value="$name"}
{/if}
{if $posting_user_id>0 && ($user || $settings.user_area_public==1)}{assign var=name value="<a href=\"index.php?mode=user&amp;show_user=$posting_user_id\">$name</a>"}{/if}
<div class="posting">{if $spam}<p class="spam-note">{#spam_note#}</p>{/if}
{if $avatar}<img class="avatar" src="{$avatar.image}" alt="{#avatar_img_alt#}" width="{$avatar.width}" height="{$avatar.height}" />{/if}
<h1>{$subject}{if $category_name} <span class="category">({$category_name})</span>{/if}</h1>
<p class="author">{*{assign var=formated_time value=$disp_time|date_format:#time_format_full#}*}{if $location}{#posted_by_location#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[location]":$location|replace:"[time]":$formated_time}{else}{#posted_by#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[time]":$formated_time}{/if} <span class="ago">({if $ago.days>1}{#posting_several_days_ago#|replace:"[days]":$ago.days_rounded}{else}{if $ago.days==0 && $ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$ago.minutes}{elseif $ago.days==0 && $ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$ago.hours|replace:"[minutes]":$ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$ago.hours|replace:"[minutes]":$ago.minutes}{/if}{/if})</span>{if $admin} <span class="ip">({$ip})</span>{/if}{if $pid!=0} <span class="op-link"><a href="index.php?id={$pid}" title="{#original_posting_linktitle#|replace:"[name]":$data.$pid.name}">@ {$data.$pid.name}</a></span>{/if}{if $edited}{*{assign var=formated_edit_time value=$edit_time|date_format:#time_format_full#}*}<br />
<span class="edited">{#edited_by#|replace:"[name]":$edited_by|replace:"[time]":$formated_edit_time}</span>{/if}</p>
{if $posting}
{$posting}
{else}
<p>{#no_text#}</p>
{/if}
{if $signature}
<p class="signature">--<br />
{$signature}</p>
{/if}
{if $tags}
<p class="tags">{#tags_marking#}<br />
{foreach name="tags" from=$tags item=tag}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}</p>
{/if}
</div>
<div class="postingbottom">
<div class="postinganswer">{if $locked==0}<a class="stronglink" href="index.php?mode=posting&amp;id={$id}&amp;back=entry" title="{#reply_link_title#}">{#reply_link#}</a>{else}<span class="locked"><img src="{$THEMES_DIR}/{$theme}/images/lock.png" alt="" width="14" height="12" />{#posting_locked#}</span>{/if}</div>
<div class="postingedit">&nbsp;
{if $views}<span class="xsmall">{if $views==1}{#one_view#}{else}{#several_views#|replace:"[views]":$views}{/if}</span>{/if}
{if $edit_authorization} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;edit={$id}&amp;back=entry" title="{#edit_message_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/edit_small.png" alt="" width="15" height="10" />{#edit_message_linkname#}</a></span>{/if}
{if $delete_authorization} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;delete_posting={$id}&amp;back=entry" title="{#delete_message_linktitle#}" onclick="return delete_posting_confirm(this, '{if $admin||$mod}{$smarty.config.delete_posting_confirm_admin|escape:"url"}{else}{$smarty.config.delete_posting_confirm|escape:"url"}{/if}')"><img src="{$THEMES_DIR}/{$theme}/images/delete_small.png" alt="" width="13" height="9" />{#delete_message_linkname#}</a></span>{/if}
{if $move_posting_link} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;move_posting={$id}&amp;back=entry" title="{#move_posting_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/move_posting.png" alt="" width="14" height="10" />{#move_posting_linkname#}</a></span>{/if}
{if $report_spam_link} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;report_spam={$id}&amp;back=entry" title="{#report_spam_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/spam_link.png" alt="" width="13" height="9" />{#report_spam_linkname#}</a></span>{/if}
{if $flag_ham_link} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;flag_ham={$id}&amp;back=entry" title="{#flag_ham_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/spam_link.png" alt="" width="13" height="9" />{#flag_ham_linkname#}</a></span>{/if}
{if $admin || $mod} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;lock={$id}&amp;back=entry" title="{if $locked==0}{#lock_linktitle#}{else}{#unlock_linktitle#}{/if}"><img src="{$THEMES_DIR}/{$theme}/images/{if $locked==0}lock.png{else}unlock.png{/if}" alt="" width="14" height="12" />{if $locked==0}{#lock_linkname#}{else}{#unlock_linkname#}{/if}</a></span>{if $pid==0} &nbsp;<span class="small"><a href="index.php?mode=posting&amp;lock_thread={$id}&amp;back=entry" title="{#lock_thread_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/lock_thread.png" alt="" width="14" height="12" />{#lock_thread_linkname#}</a></span> &nbsp;<span class="small"><a href="index.php?mode=posting&amp;unlock_thread={$id}&amp;back=entry" title="{#unlock_thread_linktitle#}"><img src="{$THEMES_DIR}/{$theme}/images/unlock_thread.png" alt="" width="14" height="12" />{#unlock_thread_linkname#}</a></span>{/if}{/if}
</div></div>

<hr class="entryline" />
<div class="complete-thread">
<p class="left"><strong>{#complete_thread_marking#}</strong></p><p class="right">&nbsp;{if $settings.rss_feed==1}<a class="rss" href="index.php?mode=rss&amp;thread={$tid}" title="{#rss_feed_thread_title#}">{#rss_feed_thread#}</a>{/if}</p>
</div>

<script type="text/javascript">/* <![CDATA[ */
function ap(id)
{literal}{{/literal}
document.write(' <a href="#" onclick="ajax_preview('+id+'); return false" title="{#ajax_preview_title#|escape:"quotes"}" onfocus="this.blur()"><img class="ap" src="{$THEMES_DIR}/{$theme}/images/ajax_preview.png" title="{#ajax_preview_title#|escape:"quotes"}" alt="[…]" width="11" height="11" /><\/a>');
{literal}}{/literal}
/* ]]> */</script>

<ul class="thread openthread">
{defun name="tree" element=$tid level=0}
<li>{if $data.$element.id!=$id}<a class="{if $data.$element.pid==0&&$newtime&&$newtime<$data.$element.last_reply||$data.$element.pid==0&&$last_visit&&$data.$element.last_reply>$last_visit}threadnew{elseif $data.$element.pid==0}thread{elseif $data.$element.pid!=0&&$newtime&&$newtime<$data.$element.time||$data.$element.pid!=0&&$last_visit&&$data.$element.time>$last_visit}replynew{else}reply{/if}{if $read && in_array($data.$element.id,$read)} read{/if}" href="index.php?id={$data.$element.id}">{$data.$element.subject}</a>{else}<span class="{if $data.$element.pid==0}actthread{else}actreply{/if}">{$data.$element.subject}</span>{/if}{if $data.$element.no_text} <img class="no-text" src="{$THEMES_DIR}/{$theme}/images/no_text.png" title="{#no_text_title#}" alt="[ {#no_text_alt#} ]" width="11" height="9" />{/if} - <strong>{$data.$element.name}</strong>, <span>{$data.$element.formated_time}{if $data.$element.id!=$id}<script type="text/javascript">/* <![CDATA[ */ ap({$data.$element.id}); /* ]]> */</script>{/if}{if $data.$element.pid==0} <a href="index.php?mode=thread&amp;id={$data.$element.id}" title="{#open_whole_thread#}"><img src="{$THEMES_DIR}/{$theme}/images/complete_thread.png" title="{#open_whole_thread#}" alt="[*]" width="11" height="11" /></a>{/if}{if $admin || $mod} <a id="marklink_{$data.$element.id}" href="index.php?mode=posting&amp;mark={$data.$element.id}&amp;back={$id}" title="{#mark_linktitle#}" onclick="mark({$data.$element.id},'{$THEMES_DIR}/{$theme}/images/marked.png','{$THEMES_DIR}/{$theme}/images/unmarked.png','{$THEMES_DIR}/{$theme}/images/mark_process.png','{#mark_linktitle#}','{#unmark_linktitle#}'); return false">{if $data.$element.marked==0}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/unmarked.png" title="{#mark_linktitle#}" alt="[○]" width="11" height="11" />{else}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/marked.png" title="{#unmark_linktitle#}" alt="[●]" width="11" height="11" title="{#unmark_linktitle#}" />{/if}</a> <a href="index.php?mode=posting&amp;delete_posting={$data.$element.id}&amp;back=entry" title="{#delete_posting_title#}" onclick="return delete_posting_confirm(this, '{$smarty.config.delete_posting_confirm|escape:"url"}')"><img src="{$THEMES_DIR}/{$theme}/images/delete_small_2.png" alt="[x]" width="9" height="9" /></a>{/if}</span>
{if is_array($child_array[$element])}
<ul{if $fold_threads==1} style="display:none;"{/if} class="{if $level<$settings.deep_reply}reply{elseif $level>=$settings.deep_reply&&$level<$settings.very_deep_reply}deep-reply{else}very-deep-reply{/if}">{foreach from=$child_array[$element] item=child}{fun name="tree" element=$child level=$level+1}{/foreach}</ul>{/if}</li>{/defun}
</ul>

{include file="$theme/subtemplates/ajax_preview.inc.tpl"}
