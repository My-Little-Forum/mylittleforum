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
{assign var=email_hp value=" <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/email.png\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"10\" /></a>"}
{elseif $hp && $email}
{assign var=email_hp value=" <a href=\"$hp\"><img src=\"$THEMES_DIR/$theme/images/homepage.png\" title=\"$homepage_alt\" alt=\"⌂\" width=\"13\" height=\"13\" /></a> <a href=\"index.php?mode=contact&amp;id=$email_id\" rel=\"nofollow\"><img src=\"$THEMES_DIR/$theme/images/email.png\" title=\"$email_alt\" alt=\"@\" width=\"13\" height=\"10\" /></a>"}
{else}
{assign var=email_hp value=""}
{/if}

{if $element.user_type==2}
{assign var=admin_name value=$element.name}
{assign var=admin_title value=$smarty.config.administrator_title}
{assign var=name value="<span class=\"admin registered_user\" title=\"$admin_title\">$admin_name</span>"}
{elseif $element.user_type==1}
{assign var=mod_name value=$element.name}
{assign var=mod_title value=$smarty.config.moderator_title}
{assign var=name value="<span class=\"mod registered_user\" title=\"$mod_title\">$mod_name</span>"}
{elseif $element.user_id>0}
{assign var=user_name value=$element.name}
{assign var=name value="<span class=\"registered_user\">$user_name</span>"}
{else}
{assign var=name value=$element.name}
{/if}

{if ($user || $settings.user_area_public==1) && $element.user_id>0}
{assign var=posting_user_id value=$element.user_id}
{assign var=name value="<a href=\"index.php?mode=user&amp;show_user=$posting_user_id\">$name</a>"}
{/if}
<div class="thread-posting{if $element.new} new{/if}{if $element.is_read} read{/if}" id="p{$element.id}">
<div class="header">
{if $element.avatar}<img id="avatar-{$element.id}" class="avatar" src="{$element.avatar.image}" alt="{#avatar_img_alt#}" width="{$element.avatar.width}" height="{$element.avatar.height}" />{/if}
<h{if $element.pid==0}1{else}2{/if} id="headline-{$element.id}">{$element.subject}{if $element.pid==0 && $category_name} <span class="category">({$category_name})</span>{/if}</h{if $element.pid==0}1{else}2{/if}>
<p class="author">{if $element.location}{#posted_by_location#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[location]":$element.location|replace:"[time]":$element.formated_time}{else}{#posted_by#|replace:"[name]":$name|replace:"[email_hp]":$email_hp|replace:"[time]":$element.formated_time}{/if} <span class="ago">({if $element.ago.days>1}{#posting_several_days_ago#|replace:"[days]":$element.ago.days_rounded}{else}{if $element.ago.days==0 && $element.ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$element.ago.minutes}{elseif $element.ago.days==0 && $element.ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$element.ago.hours|replace:"[minutes]":$element.ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$element.ago.hours|replace:"[minutes]":$element.ago.minutes}{/if}{/if})</span>{if $admin && $element.ip} <span class="ip">({$element.ip})</span>{/if}{if $element.pid!=0}{assign var="parent_posting" value=$element.pid} <span class="op-link"><a href="#p{$element.pid}" title="{#original_posting_linktitle#|replace:"[name]":$data.$parent_posting.name}">@ {$data.$parent_posting.name}</a></span>{/if}{if $element.edited}<br />
<span class="edited">{#edited_by#|replace:"[name]":$element.edited_by|replace:"[time]":$element.formated_edit_time}</span>{/if}</p>
</div>
<div class="wrapper" id="posting-{$element.id}">
<div class="body">
{if $element.posting}
{$element.posting}
{else}
<p>{#no_text#}</p>
{/if}
{if $element.signature}
<p class="signature">--<br />
{$element.signature}</p>
{/if}
{if $element.tags}
<p class="tags">{#tags_marking#}<br />
{foreach name="tags" from=$element.tags item=tag}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}</p>
{/if}
</div>
<div class="posting-footer">
<div class="reply">{if $element.locked==0}<a class="stronglink" href="index.php?mode=posting&amp;id={$element.id}&amp;back=thread" title="{#reply_link_title#}">{#reply_link#}</a>{else}<span class="locked">{#posting_locked#}</span>{/if}</div>
<div class="info">&nbsp;
{if $element.views}<span class="views">{if $element.views==1}{#one_view#}{else}{#several_views#|replace:"[views]":$element.views}{/if}</span>{/if}
{if $element.options}
<ul class="options">
{if $element.options.add_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$element.id}&amp;back=thread" class="add-bookmark" title="{#add_bookmark_message_linktitle#}">{#add_bookmark_message_linkname#}</a></li>{/if}
{if $element.options.delete_bookmark}<li><a href="index.php?mode=posting&amp;bookmark={$element.id}&amp;back=thread" class="delete-bookmark" title="{#delete_bookmark_message_linktitle#}">{#delete_bookmark_message_linkname#}</a></li>{/if}
{if $element.options.edit}<li><a href="index.php?mode=posting&amp;edit={$element.id}&amp;back=thread" class="edit" title="{#edit_message_linktitle#}">{#edit_message_linkname#}</a></li>{/if}
{if $element.options.delete}<li><a href="index.php?mode=posting&amp;delete_posting={$element.id}&amp;back=thread" class="delete" title="{#delete_message_linktitle#}">{#delete_message_linkname#}</a></li>{/if}
{if $element.options.move}<li><a href="index.php?mode=posting&amp;move_posting={$element.id}&amp;back=thread" class="move" title="{#move_posting_linktitle#}">{#move_posting_linkname#}</a></li>{/if}
{if $element.options.report_spam}<li><a href="index.php?mode=posting&amp;report_spam={$element.id}&amp;back=thread" class="report" title="{#report_spam_linktitle#}">{#report_spam_linkname#}</a></li>{/if}
{if $element.options.flag_ham}<li><a href="index.php?mode=posting&amp;flag_ham={$element.id}&amp;back=thread" class="report" title="{#flag_ham_linktitle#}">{#flag_ham_linkname#}</a></li>{/if}
{if $element.options.lock}<li><a href="index.php?mode=posting&amp;lock={$element.id}&amp;back=thread" class="{if $element.locked==0}lock{else}unlock{/if}" title="{if $element.locked==0}{#lock_linktitle#}{else}{#unlock_linktitle#}{/if}">{if $element.locked==0}{#lock_linkname#}{else}{#unlock_linkname#}{/if}</a></li>
{if $element.pid==0}<li><a href="index.php?mode=posting&amp;lock_thread={$element.id}&amp;back=thread" class="lock-thread" title="{#lock_thread_linktitle#}">{#lock_thread_linkname#}</a></li>
<li><a href="index.php?mode=posting&amp;unlock_thread={$element.id}&amp;back=thread" class="lock-thread" title="{#unlock_thread_linktitle#}">{#unlock_thread_linkname#}</a></li>{/if}{/if}
</ul>
{/if}
</div>
</div>
</div>
</div>
{/foreach}
</div>
{if $settings.rss_feed==1}<div class="small" style="text-align:right;"><a class="rss" href="index.php?mode=rss&amp;thread={$tid}">{#rss_feed_thread#}</a></div>{/if}
