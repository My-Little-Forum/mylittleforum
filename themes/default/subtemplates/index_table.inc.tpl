{if $threads}
<div id="main-grid" class="table">
<table id="threadlist" class="normaltab" border="0" cellpadding="5" cellspacing="1">
<thead>
<tr>
{*{if $fold_threads==1}<th style="width:10px;">&nbsp;</th>{/if}*}
<th>{#subject#}</th>
<th>{#author#}</th>
<th>{#date#}</th>
{if $settings.count_views}<th>{#views#}</th>{/if}
<th>{#replies#}</th>
{if $categories && $category<=0}<th>{#category#}</th>{/if}
</tr>
</thead>
<tbody>
{foreach from=$threads item=thread}
{cycle values="a,b" assign=c}
<tr class="{$c}">
{*{if $fold_threads==1}<td class="fold"></td>{/if}*}
<td class="subject">
<ul id="thread-{$thread}" class="thread {if $fold_threads==1}folded{else}expanded{/if}">
{function name=tree level=0}
 <li>
  <div class="entry">
{if $data.$element.pid==0 && $data.$element.new}
{if $data.$element.sticky>0 && $data.$element.locked==1}{$iconSrc="images/thread-marker-locked-pinned-with-change.svg"}{$wdClass=""}
{elseif $data.$element.sticky>0}{$iconSrc="images/thread-marker-pinned-with-change.svg"}{$wdClass=""}
{elseif $data.$element.locked==1}{$iconSrc="images/thread-marker-locked-with-change.svg"}{$wdClass=""}
{else}{$iconSrc="images/thread-marker-with-change.svg"}{$wdClass=""}
{/if}
{elseif $data.$element.pid==0}
{if $data.$element.sticky>0 && $data.$element.locked==1}{$iconSrc="images/thread-marker-locked-pinned-no-change.svg"}{$wdClass=""}
{elseif $data.$element.sticky>0}{$iconSrc="images/thread-marker-pinned-no-change.svg"}{$wdClass=""}
{elseif $data.$element.locked==1}{$iconSrc="images/thread-marker-locked-no-change.svg"}{$wdClass=""}
{else}{$iconSrc="images/thread-marker-no-change.svg"}{$wdClass=""}
{/if}
{elseif $data.$element.pid!=0 && $data.$element.new}{$iconSrc="images/thread-tree-with-change.svg"}{$wdClass=" wd-dependent"}
{else}{$iconSrc="images/thread-tree-no-change.svg"}{$wdClass=" wd-dependent"}
{/if}
   <img class="icon{$wdClass}" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/{$iconSrc}" alt="" width="13" height="13" />
   <a class="subject {if $data.$element.pid==0 && $data.$element.new}{if $data.$element.sticky>0 && $data.$element.locked==1}threadnew-sticky-locked{elseif $data.$element.sticky>0}threadnew-sticky{elseif $data.$element.locked==1}threadnew-locked{else}threadnew{/if}{elseif $data.$element.pid==0}{if $data.$element.sticky>0 && $data.$element.locked==1}thread-sticky-locked{elseif $data.$element.sticky>0}thread-sticky{elseif $data.$element.locked==1}thread-locked{else}thread{/if}{elseif $data.$element.pid!=0 && $data.$element.new}replynew{else}reply{/if}{if $data.$element.is_read} read{/if}" href="index.php?mode=thread&amp;id={$data.$element.tid}{if $data.$element.pid!=0}#p{$data.$element.id}{/if}" title="{$data.$element.name}, {$data.$element.formated_time}">{if $data.$element.spam==1}<span class="spam">{$data.$element.subject}</span>{else}{$data.$element.subject}{/if}</a>

<span class="metadata">
<span id="p{$data.$element.id}" class="tail">
{if $data.$element.no_text}<span><img class="no-text" src="{$THEMES_DIR}/{$theme}/images/posting-no-text.svg" title="{#no_text_title#}" alt="{#no_text_alt#}" width="11" height="11" /></span>{/if}{if $admin || $mod} {if $data.$element.not_classified_spam_ham==1}<a><img src="{$THEMES_DIR}/{$theme}/images/keep-eye-on.svg" title="{#unclassified_linktitle#}" alt="[!]" width="13" height="13" /></a>{/if} <a id="marklink_{$data.$element.id}" href="index.php?mode=posting&amp;mark={$data.$element.id}" title="{#mark_linktitle#}">{if $data.$element.marked==0}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/marker-empty.svg" title="{#mark_linktitle#}" alt="[○]" width="11" height="11" />{else}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/marker-active.svg" title="{#unmark_linktitle#}" alt="[●]" width="11" height="11" />{/if}</a> <a href="index.php?mode=posting&amp;delete_posting={$data.$element.id}&amp;csrf_token={$CSRF_TOKEN}&amp;back=index" title="{#delete_posting_title#}"><img src="{$THEMES_DIR}/{$theme}/images/delete-cross.svg" title="{#delete_posting_title#}" alt="[x]" width="11" height="11" /></a>{/if}</span>
</span>
</div>
{if is_array($child_array[$element])}
<ul class="{if $level<$settings.deep_reply}reply{elseif $level>=$settings.deep_reply&&$level<$settings.very_deep_reply}deep-reply{else}very-deep-reply{/if}{if $fold_threads==1} js-display-none{/if}">{foreach from=$child_array[$element] item=child}{tree element=$child level=$level+1}{/foreach}</ul>{/if}</li>
{/function}
{tree element=$thread}
</ul>
</td>
<td><span class="small nowrap">{if $data.$thread.user_type==2}<span class="author-name admin registered_user" title="{#administrator_title#}">{$data.$thread.name}</span>{elseif $data.$thread.user_type==1}<span class="author-name mod registered_user" title="{#moderator_title#}">{$data.$thread.name}</span>{elseif $data.$thread.user_id>0}<span class="author-name registered_user">{$data.$thread.name}</span>{else}<span class="author-name">{$data.$thread.name}</span>{/if}</span></td>
<td><span class="small nowrap"><time datetime="{$data.$thread.ISO_time}">{$data.$thread.formated_time}</time></span></td>
{if $settings.count_views}<td><span class="small">{$total_views.$thread}</span></td>{/if}
<td><span class="small">{$replies.$thread}</span></td>
{if $categories && $category<=0}<td>{if $data.$thread.category_name}<a href="index.php?mode=index&amp;category={$data.$thread.category}" title="{#change_category_link#|replace:"[category]":$data.$thread.category_name|escape:"html"}"><span class="category nowrap">{$data.$thread.category_name}</span></a>{else}&nbsp;{/if}</td>{/if}
</tr>
{/foreach}
</tbody>
</table>
{else}<p>{if $category!=0}{#no_messages_in_category#}{else}{#no_messages#}{/if}</p>{/if}

{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}&amp;page={$pagination.previous}{if $category}&amp;category={$category}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}&amp;page={$item}{if $category}&amp;category={$category}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}&amp;page={$pagination.next}{if $category}&amp;category={$category}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{/if}

{if $tag_cloud || $latest_postings || $admin || $mod}
<div id="sidebar"{if $usersettings.sidebar==0} class="js-display-fold"{/if}>
<h2 class="sidebar"><a href="index.php?toggle_sidebar=true" title="{#toggle_sidebar#}">{#sidebar#}</a></h2>
<div id="sidebarcontent">
{if $latest_postings}
<div id="latest-postings">
<h3>{#latest_postings_hl#}</h3>
<ul id="latest-postings-container">
{foreach from=$latest_postings item=posting}<li><a{if $posting.is_read} class="read"{/if} href="index.php?mode=thread&amp;id={$posting.tid}{if $posting.pid!=0}#p{$posting.id}{/if}" title="{$posting.name}, {$posting.formated_time} {if $posting.category_name}({$posting.category_name}){/if}"><span class="entry-title">{if $posting.pid==0}<strong>{$posting.subject}</strong>{else}{$posting.subject}{/if}</span><br /><span class="entry-date">{if $posting.ago.days>1}{#posting_several_days_ago#|replace:"[days]":$posting.ago.days_rounded}{else}{if $posting.ago.days==0 && $posting.ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$posting.ago.minutes}{elseif $posting.ago.days==0 && $posting.ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$posting.ago.hours|replace:"[minutes]":$posting.ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$posting.ago.hours|replace:"[minutes]":$posting.ago.minutes}{/if}{/if}<span></a></li>{/foreach}
</ul>
</div>
{/if}
{if $tag_cloud}
<div id="tagcloud">
<h3>{#tag_cloud_hl#}</h3>
<p class="tagcloud">{foreach from=$tag_cloud item=tag}
{section name=strong_start start=0 loop=$tag.frequency}<strong>{/section}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.tag}</a> {section name=strong_end start=0 loop=$tag.frequency}</strong>{/section}
{/foreach}</p>
</div>
{/if}
{if $admin || $mod}
<div id="modmenu">
	<h3>{#options#}</h3>
	<ul id="mod-options">
		{if $number_of_non_activated_users}<li><a href="index.php?mode=user" class="non-activated-users"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/user-add.svg" alt="" width="12" height="12"/><span>{#non_activated_users_link#|replace:'[counter]':$number_of_non_activated_users}</span></a></li>{/if}
		<li><a href="index.php?mode=posting&amp;delete_marked=true" class="delete-marked"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/marker-active.svg" alt="" width="12" height="12"/><span>{#delete_marked_link#}</span></a></li>
		<li><a href="index.php?mode=posting&amp;manage_postings=true" class="manage"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/manage-marked-postings.svg" alt="" width="12" height="12"/><span>{#manage_postings_link#}</span></a></li>
		{if $show_spam_link}<li><a href="index.php?show_spam=true" class="report"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-caution.svg" alt="" width="12" height="12"/><span>{$smarty.config.show_spam_link|replace:"[number]":$total_spam}</span></a></li>{/if}
		{if $hide_spam_link}<li><a href="index.php?show_spam=true" class="report"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-caution.svg" alt="" width="12" height="12"/><span>{$smarty.config.hide_spam_link|replace:"[number]":$total_spam}</span></a></li>{/if}
		{if $delete_spam_link}<li><a href="index.php?mode=posting&amp;delete_spam=true" class="delete-spam"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/delete-cross.svg" alt="" width="12" height="12"/><span>{#delete_spam_link#}</span></a></li>{/if}
	</ul>
</div>{/if}
</div>
</div>
{/if}
{if $threads}
</div>
{/if}
