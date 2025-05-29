{config_load file=$language_file section="entry"}
{assign var=email_alt value=$smarty.config.email}
{assign var=homepage_alt value=$smarty.config.homepage}
{if $hp && !$email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a>"}
{elseif !$hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"13\" /></a>"}
{elseif $hp && $email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"13\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}
{if $user_type==2}
{assign var=admin_title value=$smarty.config.administrator_title}
{assign var=name value="<span class=\"author-name admin registered_user\" title=\"$admin_title\">$name</span>"}
{elseif $user_type==1}
{assign var=mod_title value=$smarty.config.moderator_title}
{assign var=name value="<span class=\"author-name mod registered_user\" title=\"$mod_title\">$name</span>"}
{elseif $posting_user_id>0}
{assign var=name value="<span class=\"author-name registered_user\">$name</span>"}
{else}
{assign var=name value="<span class=\"author-name\">$name</span>"}
{/if}
{if (($settings.user_area_access == 0 and ($admin or $mod)) or ($settings.user_area_access == 1 and $user) or $settings.user_area_access == 2) && $posting_user_id>0}
{assign var=name value="<a href=\"index.php?mode=user&amp;show_user=$posting_user_id\">$name</a>"}
{/if}
<article class="posting{if $is_read} read{/if}">
<header class="header">{if $spam}<p class="notice spam">{#spam_note#}</p>{/if}
{if $avatar}<img class="avatar" src="{$avatar.image}" alt="{#avatar_img_alt#}" width="{$avatar.width}" height="{$avatar.height}" />{/if}
<h1>{$subject}{if $category_name} <span class="category">({$category_name})</span>{/if}</h1>
<p class="author">{if $location}{#posted_by_location#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[location]":$location}{else}{#posted_by#|replace:"[name]":$name|replace:"[email_hp]":$email_hp}{/if} <time datetime="{$ISO_time}">{*{assign var=formated_time value=$disp_time|date_format:#time_format_full#}*}{$formated_time}</time> <span class="ago">({if $ago.days>1}{#posting_several_days_ago#|replace:"[days]":$ago.days_rounded}{else}{if $ago.days==0 && $ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$ago.minutes}{elseif $ago.days==0 && $ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$ago.hours|replace:"[minutes]":$ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$ago.hours|replace:"[minutes]":$ago.minutes}{/if}{/if})</span>{if $admin && $ip} <span class="ip">({$ip})</span>{/if}{if $pid!=0} <span class="op-link"><a href="index.php?id={$pid}" title="{#original_posting_linktitle#|replace:"[name]":$data.$pid.name}">@ {$data.$pid.name}</a></span>{/if}
{if $edited}{*{assign var=formated_edit_time value=$edit_time|date_format:#time_format_full#}*}<br />
<span class="edited">{#edited_by#|replace:"[name]":$edited_by}<time datetime="{$edit_ISO_time}">{$formated_edit_time}</time></span>{/if}</p>
</header>
<div class="wrapper">
<div class="body">
{if $posting}
{$posting}
{else}
<p>{#no_text#}</p>
{/if}
</div>
{if $signature}
<div class="signature"><p>--<br />
{$signature}</p></div>
{/if}
{if $tags}
<p class="tags">{#tags_marking#}<br />
{foreach name="tags" from=$tags item=tag}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}</p>
{/if}
</div>
<footer class="posting-footer">
<div class="reply">{if $locked==0}<a href="index.php?mode=posting&amp;id={$id}&amp;back=entry" title="{#reply_link_title#}"><img class="icon wd-dependent" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/arrow-bold-horizontal.svg" alt="" width="13" height="13"/><span>{#reply_link#}</span></a>{else}<span class="locked"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-lock-closed.svg" alt="" width="13" height="13"/><span>{#posting_locked#}</span></span>{/if}</div>
{if $views}<div class="views">{if $views==1}{#one_view#}{else}{#several_views#|replace:"[views]":$views}{/if}</div>{/if}
{if $options}
<ul class="options">
<li><a href="#top" title="{#back_to_top_link_title#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/arrow-up.svg" alt="" width="13" height="13"/><span>{#back_to_top_link#}</span></a></li>
{if $options.add_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$id}&amp;back=entry" title="{#add_bookmark_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/bookmark-add.svg" alt="" width="13" height="13"/><span>{#add_bookmark_message_linkname#}</span></a></li>{/if}
{if $options.delete_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$id}&amp;back=entry" title="{#delete_bookmark_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/bookmark-delete.svg" alt="" width="13" height="13"/><span>{#delete_bookmark_message_linkname#}</span></a></li>{/if}
{if $options.edit}<li><a href="index.php?mode=posting&amp;edit={$id}&amp;back=entry" title="{#edit_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/posting-edit.svg" alt="" width="13" height="13"/><span>{#edit_message_linkname#}</span></a></li>{/if}
{if $options.delete}<li><a href="index.php?mode=posting&amp;delete_posting={$id}&amp;csrf_token={$CSRF_TOKEN}&amp;back=entry" title="{#delete_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/delete-cross.svg" alt="" width="13" height="13"/><span>{#delete_message_linkname#}</span></a></li>{/if}
{if $options.move}<li><a href="index.php?mode=posting&amp;move_posting={$id}&amp;back=entry" title="{#move_posting_linktitle#}"><img class="icon wd-dependent" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/arrow-move.svg" alt="" width="13" height="13"/><span>{#move_posting_linkname#}</span></a></li>{/if}
{if $options.report_spam}<li><a href="index.php?mode=posting&amp;report_spam={$id}&amp;back=entry" title="{#report_spam_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/caution.svg" alt="" width="13" height="13"/><span>{#report_spam_linkname#}</span></a></li>{/if}
{if $options.flag_ham}<li><a href="index.php?mode=posting&amp;flag_ham={$id}&amp;back=entry" title="{#flag_ham_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/tick.svg" alt="" width="13" height="13"/><span>{#flag_ham_linkname#}</span></a></li>{/if}
{if $options.lock}
{if $locked==0}
<li><a href="index.php?mode=posting&amp;lock={$id}&amp;back=entry" title="{#lock_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-lock-closed.svg" alt="" width="13" height="13"/><span>{#lock_linkname#}</span></a></li>
{else}
<li><a href="index.php?mode=posting&amp;lock={$id}&amp;back=entry" title="{#unlock_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-lock-open.svg" alt="" width="13" height="13"/><span>{#unlock_linkname#}</span></a></li>
{/if}
{if $thread_locked == 1}
<li><a href="index.php?mode=posting&amp;unlock_thread={$id}&amp;back=entry" title="{#unlock_thread_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-unlock.svg" alt="" width="13" height="13"/><span>{#unlock_thread_linkname#}</span></a></li>
{else}
<li><a href="index.php?mode=posting&amp;lock_thread={$id}&amp;back=entry" title="{#lock_thread_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-lock.svg" alt="" width="13" height="13"/><span>{#lock_thread_linkname#}</span></a></li>
{/if}
{/if}
</ul>
{/if}
</footer>
</article>

<hr class="entryline" />
<div class="complete-thread">
<p class="left"><strong>{#complete_thread_marking#}</strong></p>
<p class="right">&nbsp;{if $settings.rss_feed==1}<a href="index.php?mode=rss&amp;thread={$tid}" title="{#rss_feed_thread_title#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/rss-logo.svg" alt="" width="14" height="14"/><span>{#rss_feed_thread#}</span></a>{/if}</p>
</div>

<ul class="thread openthread">
{function name=tree level=0}
 <li>
  <div class="entry">
{if $data.$element.id!=$id}
{if $data.$element.pid==0&&$data.$element.new}{$iconSrc="images/thread-marker-with-change.svg"}{$wdClass=""}
{elseif $data.$element.pid==0}{$iconSrc="images/thread-marker-no-change.svg"}{$wdClass=""}
{elseif $data.$element.pid!=0&&$data.$element.new}{$iconSrc="images/thread-tree-with-change.svg"}{$wdClass=" wd-dependent"}
{else}{$iconSrc="images/thread-tree-no-change.svg"}{$wdClass=" wd-dependent"}
{/if}
   <img class="icon{$wdClass}" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/{$iconSrc}" alt="" width="14" height="14" />
   <a class="subject{if $data.$element.is_read} read{/if}" href="index.php?id={$data.$element.id}">{$data.$element.subject}</a>
{else}
{if $data.$element.pid==0}
{if $data.$element.new}{$iconSrc="images/thread-marker-with-change.svg"}
{else}{$iconSrc="images/thread-marker-no-change.svg"}
{/if}
{else}
{if $data.$element.new}{$iconSrc="images/thread-tree-no-change.svg"}
{else}{$iconSrc="images/thread-tree-no-change.svg"}
{/if}
{/if}
   <img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/{$iconSrc}" alt="" width="14" height="14" />
   <span class="current">{$data.$element.subject}</span>
{/if}

<span class="metadata">
{if $data.$element.user_type==2}
<span class="author-name admin registered_user" title="{#administrator_title#}">{$data.$element.name}</span>
{elseif $data.$element.user_type==1}
<span class="author-name mod registered_user" title="{#moderator_title#}">{$data.$element.name}</span>
{elseif $data.$element.user_id>0}
<span class="author-name registered_user">{$data.$element.name}</span>
{else}
<span class="author-name">{$data.$element.name}</span>
{/if}

<span id="p{$data.$element.id}" class="tail">
<time datetime="{$data.$element.ISO_time}">{$data.$element.formated_time}</time>
{if $data.$element.no_text} <span><img class="no-text" src="{$THEMES_DIR}/{$theme}/images/posting-no-text.svg" title="{#no_text_title#}" alt="{#no_text_alt#}" width="11" height="11" /></span>{/if}{if $data.$element.pid==0} <a href="index.php?mode=thread&amp;id={$data.$element.id}" title="{#open_whole_thread#}"><img src="{$THEMES_DIR}/{$theme}/images/thread-nested.svg" title="{#open_whole_thread#}" alt="[*]" width="11" height="11" /></a>{/if}{if $admin || $mod} {if $data.$element.not_classified_spam_ham==1}<a><img src="{$THEMES_DIR}/{$theme}/images/keep-eye-on.svg" title="{#unclassified_linktitle#}" alt="[!]" width="13" height="13" /></a>{/if}
 <a id="marklink_{$data.$element.id}" href="index.php?mode=posting&amp;mark={$data.$element.id}&amp;back={$id}" title="{#mark_linktitle#}">{if $data.$element.marked==0}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/marker-empty.svg" title="{#mark_linktitle#}" alt="[○]" width="11" height="11" />{else}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/marker-active.svg" title="{#unmark_linktitle#}" alt="[●]" width="11" height="11" title="{#unmark_linktitle#}" />{/if}</a>
 <a href="index.php?mode=posting&amp;delete_posting={$data.$element.id}&amp;csrf_token={$CSRF_TOKEN}&amp;back=entry" title="{#delete_posting_title#}" onclick="return delete_posting_confirm(this, '{$smarty.config.delete_posting_confirm|escape:"url"}')"><img src="{$THEMES_DIR}/{$theme}/images/delete-cross.svg" alt="[x]" width="11" height="11" /></a>{/if}
</span>
</span>
  </div>
{if is_array($child_array[$element])}
<ul class="{if $level<$settings.deep_reply}reply{elseif $level>=$settings.deep_reply&&$level<$settings.very_deep_reply}deep-reply{else}very-deep-reply{/if}">{foreach from=$child_array[$element] item=child}{tree element=$child level=$level+1}{/foreach}</ul>{/if}</li>
{/function}
{tree element=$tid}
</ul>
