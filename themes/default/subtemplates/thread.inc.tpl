{config_load file=$language_file section="entry"}
{function name=tree level=0}
<div class="{if $level==0}thread-wrapper{elseif $level>0&&$level<$settings.deep_reply}reply-wrapper{elseif $level>=$settings.deep_reply&&$level<$settings.very_deep_reply}deep-reply-wrapper{else}very-deep-reply-wrapper{/if}">
{*{assign var=formated_time value=$data.$element.disp_time|date_format:#time_format_full#}*}
{assign var=email_alt value=$smarty.config.email}
{assign var=homepage_alt value=$smarty.config.homepage}
{assign var=hp value=$data.$element.hp}
{assign var=email value=$data.$element.email}
{assign var=email_id value=$data.$element.id}
{if $hp && !$email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a>"}
{elseif !$hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"13\" /></a>"}
{elseif $hp && $email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"13\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}

{if $data.$element.user_type==2}
{assign var=admin_name value=$data.$element.name}
{assign var=admin_title value=$smarty.config.administrator_title}
{assign var=name value="<span class=\"author-name admin registered_user\" title=\"$admin_title\">$admin_name</span>"}
{elseif $data.$element.user_type==1}
{assign var=mod_name value=$data.$element.name}
{assign var=mod_title value=$smarty.config.moderator_title}
{assign var=name value="<span class=\"author-name mod registered_user\" title=\"$mod_title\">$mod_name</span>"}
{elseif $data.$element.user_id>0}
{assign var=user_name value=$data.$element.name}
{assign var=name value="<span class=\"author-name registered_user\">$user_name</span>"}
{else}
{assign var=visitor_name value=$data.$element.name}
{assign var=name value="<span class=\"author-name\">$visitor_name</span>"}
{/if}

{if (($settings.user_area_access == 0 and ($admin or $mod)) or ($settings.user_area_access == 1 and $user) or $settings.user_area_access == 2) && $data.$element.user_id>0}
{assign var=posting_user_id value=$data.$element.user_id}
{assign var=name value="<a href=\"index.php?mode=user&amp;show_user=$posting_user_id\">$name</a>"}
{/if}
<article class="thread-posting{if $data.$element.new} new{/if}{if $data.$element.is_read} read{/if}" id="p{$data.$element.id}">
<header class="header">
{if $data.$element.avatar}<img id="avatar-{$data.$element.id}" class="avatar" src="{$data.$element.avatar.image}" alt="{#avatar_img_alt#}" width="{$data.$element.avatar.width}" height="{$data.$element.avatar.height}" />{/if}
<h{if $data.$element.pid==0}1{else}2{/if} id="headline-{$data.$element.id}">{$data.$element.subject}{if $data.$element.pid==0 && $category_name} <span class="category">({$category_name})</span>{/if}</h{if $data.$element.pid==0}1{else}2{/if}>
<p class="author">{if $data.$element.location}{#posted_by_location#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[location]":$data.$element.location}{else}{#posted_by#|replace:"[name]":$name|replace:"[email_hp]":$email_hp}{/if}<time datetime="{$data.$element.ISO_time}">{$data.$element.formated_time}</time> <span class="ago">({if $data.$element.ago.days>1}{#posting_several_days_ago#|replace:"[days]":$data.$element.ago.days_rounded}{else}{if $data.$element.ago.days==0 && $data.$element.ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$data.$element.ago.minutes}{elseif $data.$element.ago.days==0 && $data.$element.ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$data.$element.ago.hours|replace:"[minutes]":$data.$element.ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$data.$element.ago.hours|replace:"[minutes]":$data.$element.ago.minutes}{/if}{/if})</span>{if $admin && $data.$element.ip} <span class="ip">({$data.$element.ip})</span>{/if}{if $data.$element.pid!=0}{assign var="parent_posting" value=$data.$element.pid} <span class="op-link"><a href="#p{$data.$element.pid}" title="{#original_posting_linktitle#|replace:"[name]":$data.$parent_posting.name}">@ {$data.$parent_posting.name}</a></span>{/if}
{if $data.$element.edited}<br /><span class="edited">{#edited_by#|replace:"[name]":$data.$element.edited_by}<time datetime="{$data.$element.ISO_edit_time}">{$data.$element.formated_edit_time}</time></span>{/if}</p>
</header>
<div class="wrapper" id="posting-{$data.$element.id}">
<div class="body">
{if $data.$element.posting}
{$data.$element.posting}
{else}
<p>{#no_text#}</p>
{/if}
</div>
{if $data.$element.signature}
<div class="signature"><p>--<br />
{$data.$element.signature}</p></div>
{/if}
{if $data.$element.tags}
<p class="tags">{#tags_marking#}<br />
{foreach name="tags" from=$data.$element.tags item=tag}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}</p>
{/if}
</div>
<footer class="posting-footer">
<div class="reply">{if $data.$element.locked==0}<a class="stronglink" href="index.php?mode=posting&amp;id={$data.$element.id}&amp;back=thread" title="{#reply_link_title#}">{#reply_link#}</a>{else}<span class="locked"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-lock-closed.svg" alt="" width="13" height="13"/><span>{#posting_locked#}</span></span>{/if}</div>
{if $data.$element.views}<div class="views">{if $data.$element.views==1}{#one_view#}{else}{#several_views#|replace:"[views]":$data.$element.views}{/if}</div>{/if}
{if $data.$element.options}
<ul class="options">
<li><a href="#top" title="{#back_to_top_link_title#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/arrow-up.svg" alt="" width="13" height="13"/><span>{#back_to_top_link#}</span></a></li>
{if $data.$element.options.add_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$data.$element.id}&amp;back=thread" title="{#add_bookmark_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/bookmark-add.svg" alt="" width="13" height="13"/><span>{#add_bookmark_message_linkname#}</span></a></li>{/if}
{if $data.$element.options.delete_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$data.$element.id}&amp;back=thread" title="{#delete_bookmark_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/bookmark-delete.svg" alt="" width="13" height="13"/><span>{#delete_bookmark_message_linkname#}</span></a></li>{/if}
{if $data.$element.options.edit}<li><a href="index.php?mode=posting&amp;edit={$data.$element.id}&amp;back=thread" class="edit" title="{#edit_message_linktitle#}">{#edit_message_linkname#}</a></li>{/if}
{if $data.$element.options.delete}<li><a href="index.php?mode=posting&amp;delete_posting={$data.$element.id}&amp;csrf_token={$CSRF_TOKEN}&amp;back=thread" class="delete" title="{#delete_message_linktitle#}">{#delete_message_linkname#}</a></li>{/if}
{if $data.$element.options.move}<li><a href="index.php?mode=posting&amp;move_posting={$data.$element.id}&amp;back=thread" title="{#move_posting_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/{if #dir# == 'rtl'}arrow-move-rtl.svg{else}arrow-move-ltr.svg{/if}" alt="" width="13" height="13"/><span>{#move_posting_linkname#}</span></a></li>{/if}
{if $data.$element.options.report_spam}<li><a href="index.php?mode=posting&amp;report_spam={$data.$element.id}&amp;back=thread" title="{#report_spam_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/caution.svg" alt="" width="13" height="13"/><span>{#report_spam_linkname#}</span></a></li>{/if}
{if $data.$element.options.flag_ham}<li><a href="index.php?mode=posting&amp;flag_ham={$data.$element.id}&amp;back=thread" title="{#flag_ham_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/tick.svg" alt="" width="13" height="13"/><span>{#flag_ham_linkname#}</span></a></li>{/if}
{if $data.$element.options.lock}
{if $data.$element.locked==0}
<li><a href="index.php?mode=posting&amp;lock={$data.$element.id}&amp;back=thread" title="{#lock_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-lock-closed.svg" alt="" width="13" height="13"/><span>{#lock_linkname#}</span></a></li>
{else}
<li><a href="index.php?mode=posting&amp;lock={$data.$element.id}&amp;back=thread" title="{#unlock_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-lock-open.svg" alt="" width="13" height="13"/><span>{#unlock_linkname#}</span></a></li>
{/if}
{if $data.$element.pid==0}
<li><a href="index.php?mode=posting&amp;unlock_thread={$data.$element.id}&amp;back=thread" class="unlock-thread" title="{#unlock_thread_linktitle#}">{#unlock_thread_linkname#}</a></li>
{else}
<li><a href="index.php?mode=posting&amp;lock_thread={$data.$element.id}&amp;back=thread" class="lock-thread" title="{#lock_thread_linktitle#}">{#lock_thread_linkname#}</a></li>
{/if}
{/if}
{/if}
</ul>
{/if}
</footer>
</article>
{if is_array($child_array[$element])}
{foreach from=$child_array[$element] item=child}{tree element=$child level=$level+1}{/foreach}
{/if}
</div>
{/function}
{tree element=$tid}
{if $settings.rss_feed==1}<div class="complete-thread">
<p class="right"><a href="index.php?mode=rss&amp;thread={$tid}" title="{#rss_feed_thread_title#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/rss-logo.svg" alt="" width="14" height="14"/><span>{#rss_feed_thread#}</span></a></p>
</div>{/if}
