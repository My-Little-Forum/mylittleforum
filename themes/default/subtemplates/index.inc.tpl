{if $tag_cloud || $latest_postings || $admin || $mod}
<div id="sidebar">
<a href="index.php?toggle_sidebar=true" onclick="toggle_sidebar('{$THEMES_DIR}/{$theme}/images/hide_sidebar.png','{$THEMES_DIR}/{$theme}/images/show_sidebar.png'); return false;"><img id="sidebartoggle" src="{$THEMES_DIR}/{$theme}/images/{if $usersettings.sidebar==0}show_sidebar.png{else}hide_sidebar.png{/if}" title="{#toggle_sidebar#}" alt="[+/-]" width="9" height="9" /></a>
<h3 class="sidebar"><a href="index.php?toggle_sidebar=true" title="{#toggle_sidebar#}" onclick="toggle_sidebar('{$THEMES_DIR}/{$theme}/images/hide_sidebar.png','{$THEMES_DIR}/{$theme}/images/show_sidebar.png'); return false;">{#sidebar#}</a></h3>
<div id="sidebarcontent"{if $usersettings.sidebar==0} style="display:none;"{/if}>
{if $latest_postings}
<div id="latest-postings">
<h3>{#latest_postings_hl#}</h3>
<ul id="latest-postings-container">
{foreach from=$latest_postings item=posting}<li><a href="index.php?id={$posting.id}" title="{$posting.name}, {$posting.formated_time}{if $posting.category_name} ({$posting.category_name}){/if}"><span{if $read && in_array($posting.id,$read)} class="read"{/if}>{if $posting.pid==0}<strong>{$posting.subject}</strong>{else}{$posting.subject}{/if}</span><br />
{if $posting.ago.days>1}{#posting_several_days_ago#|replace:"[days]":$posting.ago.days_rounded}{else}{if $posting.ago.days==0 && $posting.ago.hours==0}{#posting_minutes_ago#|replace:"[minutes]":$posting.ago.minutes}{elseif $posting.ago.days==0 && $posting.ago.hours!=0}{#posting_hours_ago#|replace:"[hours]":$posting.ago.hours|replace:"[minutes]":$posting.ago.minutes}{else}{#posting_one_day_ago#|replace:"[hours]":$posting.ago.hours|replace:"[minutes]":$posting.ago.minutes}{/if}{/if}</a></li>{/foreach}
</ul>
</div>
{/if}
{if $tag_cloud}
<div id="tagcloud">
<h3>{#tag_cloud_hl#}</h3>
<p id="tagcloud-container">{foreach from=$tag_cloud item=tag}
{section name=strong_start start=0 loop=$tag.frequency}<strong>{/section}<a href="index.php?mode=search&amp;search={$tag.escaped}&amp;method=tags">{$tag.tag}</a> {section name=strong_end start=0 loop=$tag.frequency}</strong>{/section}
{/foreach}</p>
</div>
{/if}
{if $admin || $mod}
<div id="modmenu">
<h3>{#options#}</h3>
<ul>
<li><a href="index.php?mode=posting&amp;delete_marked=true"><img src="{$THEMES_DIR}/{$theme}/images/marked_link.png" alt="" width="13" height="9" />{#delete_marked_link#}</a></li>
<li><a href="index.php?mode=posting&amp;manage_postings=true"><img src="{$THEMES_DIR}/{$theme}/images/manage_postings.png" alt="" width="13" height="9" />{#manage_postings_link#}</a></li>
{if $show_spam_link}<li><a href="index.php?show_spam=true"><img src="{$THEMES_DIR}/{$theme}/images/spam_link.png" alt="" width="13" height="9" />{$smarty.config.show_spam_link|replace:"[number]":$total_spam}</a></li>{/if}
{if $hide_spam_link}<li><a href="index.php?show_spam=true"><img src="{$THEMES_DIR}/{$theme}/images/spam_link.png" alt="" width="13" height="9" />{$smarty.config.hide_spam_link|replace:"[number]":$total_spam}</a></li>{/if}
{if $delete_spam_link}<li><a href="index.php?mode=posting&amp;delete_spam=true"><img src="{$THEMES_DIR}/{$theme}/images/delete_small.png" alt="" width="13" height="9" />{#delete_spam_link#}</a></li>{/if}
</ul>
</div>{/if}
</div>
</div>
{/if}
{include file="$theme/subtemplates/ajax_preview.inc.tpl"}
<script type="text/javascript">/* <![CDATA[ */
{if $admin || $mod}
function mk(id)
{literal}{{/literal}
mark(id,'{$THEMES_DIR}/{$theme}/images/marked.png','{$THEMES_DIR}/{$theme}/images/unmarked.png','{$THEMES_DIR}/{$theme}/images/mark_process.png','{$smarty.config.mark_linktitle|escape:"url"}','{$smarty.config.unmark_linktitle|escape:"url"}');
{literal}}{/literal}
function dl(ths)
{literal}{{/literal}
return delete_posting_confirm(ths, '{$smarty.config.delete_posting_confirm_admin|escape:"url"}')
{literal}}{/literal}
{/if}
function ap(id,locked)
{literal}{{/literal}
var reply_link = typeof(locked) == 'undefined' || locked == 0 ? 1 : 0;
document.write(' <a href="#" onclick="ajax_preview('+id+','+reply_link+'); return false" title="{#ajax_preview_title#|escape:"quotes"}" onfocus="this.blur()"><img class="ap" src="{$THEMES_DIR}/{$theme}/images/ajax_preview.png" title="{#ajax_preview_title#|escape:"quotes"}" alt="[…]" width="11" height="11" /><\/a>'); 
{literal}}{/literal}
{if $fold_threads==1}
function ft(id,replies)
{literal}{{/literal} 
if(replies > 0) document.write('<a id="expand_link_'+id+'" href="#" onclick="fold_thread('+id+',\'{$THEMES_DIR}/{$theme}/images/expand_thread.png\',\'{$THEMES_DIR}/{$theme}/images/fold_thread.png\'); return false" title="{#expand_fold_thread_linktitle#|escape:"quotes"}" onfocus="this.blur()"><img id="expand_img_'+id+'" src="{$THEMES_DIR}/{$theme}/images/expand_thread.png" title="{#expand_fold_thread_linktitle#|escape:"quotes"}" alt="[+]" width="9" height="11" /><\/a> ');
else document.write('<img id="expand_img_'+id+'" src="{$THEMES_DIR}/{$theme}/images/expand_thread_inactive.png" alt="[+]" width="9" height="11" /> ');
{literal}}{/literal}
{/if}
/* ]]> */</script>

{if $threads}
{foreach from=$threads item=thread}
<ul id="thread-{$thread}" class="thread">
{defun name="tree" element=$thread level=0}
<li>{if $fold_threads==1 && $data.$element.pid==0}<script type="text/javascript">/* <![CDATA[ */ ft({$data.$element.id},{$data.$element.replies}) /* ]]> */</script>{/if}<a class="{if $data.$element.pid==0 && $data.$element.new}{if $data.$element.sticky==1}threadnew-sticky{else}threadnew{/if}{elseif $data.$element.pid==0}{if $data.$element.sticky==1}thread-sticky{else}thread{/if}{elseif $data.$element.pid!=0 && $data.$element.new}replynew{else}reply{/if}{if $read && in_array($data.$element.id,$read)} read{/if}" href="index.php?id={$data.$element.id}"{if $data.$element.spam==1} title="{#spam#}"{/if}>{if $data.$element.spam==1}<span class="spam">{$data.$element.subject}</span>{else}{$data.$element.subject}{/if}</a>{if $data.$element.no_text} <img class="no-text" src="{$THEMES_DIR}/{$theme}/images/no_text.png" title="{#no_text_title#}" alt="{#no_text_alt#}" width="11" height="9" />{/if} - <strong>{if $data.$element.user_type==2}<span class="admin" title="{#administrator_title#}">{$data.$element.name}</span>{elseif $data.$element.user_type==1}<span class="mod" title="{#moderator_title#}">{$data.$element.name}</span>{else}{$data.$element.name}{/if}</strong>, <span>{$data.$element.formated_time}<script type="text/javascript">/* <![CDATA[ */ ap({$data.$element.id}{if $data.$element.locked==1},1{/if}); /* ]]> */</script>{if $data.$element.pid==0} <a href="index.php?mode=thread&amp;id={$data.$element.id}" title="{#open_whole_thread#}"><img src="{$THEMES_DIR}/{$theme}/images/complete_thread.png" title="{#open_whole_thread#}" alt="[*]" width="11" height="11" /></a>{/if}{if $admin || $mod} <a id="marklink_{$data.$element.id}" href="index.php?mode=posting&amp;mark={$data.$element.id}" title="{#mark_linktitle#}" onclick="mk({$data.$element.id}); return false" onfocus="this.blur()">{if $data.$element.marked==0}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/unmarked.png" title="{#mark_linktitle#}" alt="[○]" width="11" height="11" />{else}<img id="markimg_{$data.$element.id}" src="{$THEMES_DIR}/{$theme}/images/marked.png" title="{#unmark_linktitle#}" alt="[●]" width="11" height="11" title="{#unmark_linktitle#}" />{/if}</a> <a href="index.php?mode=posting&amp;delete_posting={$data.$element.id}&amp;back=index" title="{#delete_posting_title#}" onclick="return dl(this)"><img src="{$THEMES_DIR}/{$theme}/images/delete_small_2.png" title="{#delete_posting_title#}" alt="[x]" width="9" height="9" /></a>{/if}
{if $data.$element.pid==0 && $fold_threads==1} <span class="replies" title="{if $data.$element.replies==0}{#no_replies#}{elseif $data.$element.replies==1}{#one_reply#}{else}{$smarty.config.several_replies|replace:"[replies]":$data.$element.replies}{/if}">({$data.$element.replies})</span>{/if}{if $data.$element.category_name && $data.$element.pid==0 && $category<=0} <a href="index.php?mode=index&amp;category={$data.$element.category}" title="{#change_category_link#|replace:"[category]":$data.$element.category_name|escape:"html"}"><span class="category">({$data.$element.category_name|replace:" ":"&nbsp;"})</span></a>{/if}</span>
{if is_array($child_array[$element])}
<ul{if $fold_threads==1} style="display:none;"{/if} class="{if $level<$settings.deep_reply}reply{elseif $level>=$settings.deep_reply&&$level<$settings.very_deep_reply}deep-reply{else}very-deep-reply{/if}">{foreach from=$child_array[$element] item=child}{fun name="tree" element=$child level=$level+1}{/foreach}</ul>{/if}</li>{/defun}
</ul>
{/foreach}
{else}
<p>{if $category!=0}{#no_messages_in_category#}{else}{#no_messages#}{/if}</p>
{/if}

{if $pagination}
<ul class="pagination pagination-index">
{if $pagination.previous}<li><a href="index.php?mode={$mode}&amp;page={$pagination.previous}{if $category}&amp;category={$category}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}&amp;page={$item}{if $category}&amp;category={$category}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}&amp;page={$pagination.next}{if $category}&amp;category={$category}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}  
</ul>
{/if}
