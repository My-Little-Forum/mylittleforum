{config_load file=$language_file section="entry"}
<div class="thread-wrapper">
{foreach from=$data item=element}
{*{assign var=formated_time value=$element.disp_time|date_format:#time_format_full#}*}
{assign var=email_alt value=$smarty.config.email}
{assign var=homepage_alt value=$smarty.config.homepage}
{assign var=hp value=$element.hp}
{assign var=email value=$element.email}
{assign var=email_id value=$element.id}
{if $hp && !$email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a>"}
{elseif !$hp && $email}
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"13\" /></a>"}
{elseif $hp && $email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/e-mail-envelope.svg\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"13\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}

{if $element.user_type==2}
{assign var=admin_name value=$element.name}
{assign var=admin_title value=$smarty.config.administrator_title}
{assign var=name value="<span class=\"author-name admin registered_user\" title=\"$admin_title\">$admin_name</span>"}
{elseif $element.user_type==1}
{assign var=mod_name value=$element.name}
{assign var=mod_title value=$smarty.config.moderator_title}
{assign var=name value="<span class=\"author-name mod registered_user\" title=\"$mod_title\">$mod_name</span>"}
{elseif $element.user_id>0}
{assign var=user_name value=$element.name}
{assign var=name value="<span class=\"author-name registered_user\">$user_name</span>"}
{else}
{assign var=visitor_name value=$element.name}
{assign var=name value="<span class=\"author-name\">$visitor_name</span>"}
{/if}

{if (($settings.user_area_access == 0 and ($admin or $mod)) or ($settings.user_area_access == 1 and $user) or $settings.user_area_access == 2) &&$element.user_id>0}
{assign var=posting_user_id value=$element.user_id}
{assign var=name value="<a href=\"index.php?mode=user&amp;show_user=$posting_user_id\">$name</a>"}
{/if}
<article class="thread-posting{if $element.new} new{/if}{if $element.is_read} read{/if}" id="p{$element.id}">
<header class="header">
{if $element.avatar}<img id="avatar-{$element.id}" class="avatar" src="{$element.avatar.image}" alt="{#avatar_img_alt#}" width="{$element.avatar.width}" height="{$element.avatar.height}" />{/if}
<h{if $element.pid==0}1{else}2{/if} id="headline-{$element.id}">{$element.subject}{if $element.pid==0 && $category_name} <span class="category">({$category_name})</span>{/if}</h{if $element.pid==0}1{else}2{/if}>
<p class="author">{if $element.location}{#posted_by_location#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[location]":$element.location}{else}{#posted_by#|replace:"[name]":$name|replace:"[email_hp]":$email_hp}{/if}<time datetime="{$element.ISO_time}">{$element.formated_time}</time> <span class="ago">({if $element.ago.days>1}{#posting_several_days_ago#|replace:"[days]":$element.ago.days_rounded}{else}{if $element.ago.days==0 && $element.ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$element.ago.minutes}{elseif $element.ago.days==0 && $element.ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$element.ago.hours|replace:"[minutes]":$element.ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$element.ago.hours|replace:"[minutes]":$element.ago.minutes}{/if}{/if})</span>{if $admin && $element.ip} <span class="ip">({$element.ip})</span>{/if}{if $element.pid!=0}{assign var="parent_posting" value=$element.pid} <span class="op-link"><a href="#p{$element.pid}" title="{#original_posting_linktitle#|replace:"[name]":$data.$parent_posting.name}">@ {$data.$parent_posting.name}</a></span>{/if}
{if $element.edited}<br /><span class="edited">{#edited_by#|replace:"[name]":$element.edited_by}<time datetime="{$element.ISO_edit_time}">{$element.formated_edit_time}</time></span>{/if}</p>
</header>
<div class="wrapper" id="posting-{$element.id}">
<div class="body">
{if $element.posting}
{$element.posting}
{else}
<p>{#no_text#}</p>
{/if}
</div>
{if $element.signature}
<div class="signature"><p>--<br />
{$element.signature}</p></div>
{/if}
{if $element.tags}
<p class="tags">{#tags_marking#}<br />
{foreach name="tags" from=$element.tags item=tag}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}</p>
{/if}
</div>
<footer class="posting-footer">
<div class="reply">{if $element.locked==0}<a class="stronglink" href="index.php?mode=posting&amp;id={$element.id}&amp;back=thread" title="{#reply_link_title#}">{#reply_link#}</a>{else}<span class="locked">{#posting_locked#}</span>{/if}</div>
{if $element.views}<div class="views">{if $element.views==1}{#one_view#}{else}{#several_views#|replace:"[views]":$element.views}{/if}</div>{/if}
{if $element.options}
<ul class="options">
<li><a href="#top" title="{#back_to_top_link_title#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/arrow-up.svg" alt="" width="13" height="13"/><span>{#back_to_top_link#}</span></a></li>
{if $element.options.add_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$element.id}&amp;back=thread" title="{#add_bookmark_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/bookmark-add.svg" alt="" width="13" height="13"/><span>{#add_bookmark_message_linkname#}</span></a></li>{/if}
{if $element.options.delete_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$element.id}&amp;back=thread" title="{#delete_bookmark_message_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/bookmark-delete.svg" alt="" width="13" height="13"/><span>{#delete_bookmark_message_linkname#}</span></a></li>{/if}
{if $element.options.edit}<li><a href="index.php?mode=posting&amp;edit={$element.id}&amp;back=thread" class="edit" title="{#edit_message_linktitle#}">{#edit_message_linkname#}</a></li>{/if}
{if $element.options.delete}<li><a href="index.php?mode=posting&amp;delete_posting={$element.id}&amp;csrf_token={$CSRF_TOKEN}&amp;back=thread" class="delete" title="{#delete_message_linktitle#}">{#delete_message_linkname#}</a></li>{/if}
{if $element.options.move}<li><a href="index.php?mode=posting&amp;move_posting={$element.id}&amp;back=thread" class="move" title="{#move_posting_linktitle#}">{#move_posting_linkname#}</a></li>{/if}
{if $element.options.report_spam}<li><a href="index.php?mode=posting&amp;report_spam={$element.id}&amp;back=thread" title="{#report_spam_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/caution.svg" alt="" width="13" height="13"/><span>{#report_spam_linkname#}</span></a></li>{/if}
{if $element.options.flag_ham}<li><a href="index.php?mode=posting&amp;flag_ham={$element.id}&amp;back=thread" title="{#flag_ham_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/tick.svg" alt="" width="13" height="13"/><span>{#flag_ham_linkname#}</span></a></li>{/if}
{if $element.options.lock}<li><a href="index.php?mode=posting&amp;lock={$element.id}&amp;back=thread" class="{if $element.locked==0}lock{else}unlock{/if}" title="{if $element.locked==0}{#lock_linktitle#}{else}{#unlock_linktitle#}{/if}">{if $element.locked==0}{#lock_linkname#}{else}{#unlock_linkname#}{/if}</a></li>
{if $element.pid==0}
{if $element.thread_locked == 1}
<li><a href="index.php?mode=posting&amp;unlock_thread={$id}&amp;back=entry" class="unlock-thread" title="{#unlock_thread_linktitle#}">{#unlock_thread_linkname#}</a></li>
{else}
<li><a href="index.php?mode=posting&amp;lock_thread={$id}&amp;back=entry" class="lock-thread" title="{#lock_thread_linktitle#}">{#lock_thread_linkname#}</a></li>
{/if}
{/if}
{/if}
</ul>
{/if}
</footer>
</article>
{/foreach}
</div>
{if $settings.rss_feed==1}<div class="complete-thread">
<p class="right"><a href="index.php?mode=rss&amp;thread={$tid}" title="{#rss_feed_thread_title#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/rss-logo.svg" alt="" width="14" height="14"/><span>{#rss_feed_thread#}</span></a></p>
</div>{/if}
