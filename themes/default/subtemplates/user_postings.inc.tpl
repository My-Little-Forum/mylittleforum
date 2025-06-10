{if $user_postings_data}

{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $id}&amp;id={$id}{/if}{if $item>1}&amp;page={$item}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{/if}

<p>{if $user_postings_count>1}{$smarty.config.several_postings_by_user|replace:"[number]":$user_postings_count}{else}{#one_posting_by_user#}{/if}</p>
<ul class="searchresults thread">
{section name=ix loop=$user_postings_data}
 <li>
  <div class="entry">
{if $user_postings_data[ix]==0}{$iconSrc="images/thread-marker-no-change.svg"}{$wdClass=""}
{else}{$iconSrc="images/thread-tree-no-change.svg"}{$wdClass=" wd-dependent"}
{/if}
   <img class="icon{$wdClass}" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/{$iconSrc}" alt="" width="14" height="14" />
   <a class="subject {if $user_postings_data[ix].pid==0}thread-search{else}reply-search{/if} {if $visited && in_array($user_postings_data[ix].id,$visited)} visited{/if}" href="index.php?mode=entry&amp;id={$user_postings_data[ix].id}">{$user_postings_data[ix].subject}</a>
   <span class="metadata">
    <span class="author-name">{$user_postings_data[ix].name}</span>
    <span id="p{$user_postings_data[ix].id}" class="tail">
    {$user_postings_data[ix].disp_time}
     <a href="index.php?mode=thread&amp;id={$user_postings_data[ix].id}" title="{#open_whole_thread#}"><img src="{$THEMES_DIR}/{$theme}/images/complete_thread.png" alt="{#open_whole_thread#}" width="11" height="11" /></a>
    {if $user_postings_data[ix].category}<a href="index.php?mode=index&amp;category={$user_postings_data[ix].category}"><span class="category">({$user_postings_data[ix].category_name})</span></a>{/if}
    </span>
   </span>
  </div>
 </li>
{/section}
</ul>

{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $id}&amp;id={$id}{/if}{if $item>1}&amp;page={$item}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{/if}

{else}
<p>{#no_postings_by_user#}</p>
{/if}
