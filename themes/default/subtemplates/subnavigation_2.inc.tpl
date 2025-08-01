{if $mode=='index'}
 <ul id="subnavmenu">
  <li><a href="index.php?refresh=1&amp;category={$category}" title="{#refresh_linktitle#}" rel="nofollow"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-refresh-two-arrows.svg" alt="" width="11" height="11" /><span>{#refresh_link#}</span></a></li>
{if $thread_order==0}
{assign var=order_class value="order-1"}
{assign var=order_param value="thread_order=1"}
{assign var=order_title value={#order_link_title_1#}}
{else}
{assign var=order_class value="order-2"}
{assign var=order_param value="thread_order=0"}
{assign var=order_title value={#order_link_title_2#}}
{/if}
  <li><a class="{$order_class}" href="index.php?mode=index&amp;{$order_param}" title="{$order_title}" rel="nofollow"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/general-order-two-arrows.svg" alt="" width="11" height="11" /><span>{#order_link#}</span></a></li>
  <li>{if $usersettings.fold_threads==0}<a href="index.php?fold_threads=1" title="{#fold_threads_linktitle#}"><img class="icon wd-dependent" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-list-fold.svg" alt="" width="11" height="11" /><span>{#fold_threads#}</span></a>{else}<a href="index.php?fold_threads=0" title="{#expand_threads_linktitle#}"><img class="icon wd-dependent" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-list-nested.svg" alt="" width="11" height="11" /><span>{#expand_threads#}</span></a>{/if}</li>
  <li>{if $usersettings.user_view==0}<a href="index.php?toggle_view=1" title="{#table_view_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-list-tabularised.svg" alt="" width="11" height="11" /><span>{#table_view#}</span></a>{else}<a href="index.php?toggle_view=0" title="{#thread_view_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-list-nested.svg" alt="" width="11" height="11" /><span>{#thread_view#}</span></a>{/if}</li>
 </ul>
{elseif $mode=='entry'}
 <ul id="subnavmenu">
  <li><a href="index.php?mode=thread&amp;id={$tid}#p{$id}" title="{#open_in_thread_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-nested.svg" alt="" width="11" height="11" /><span>{#open_in_thread_link#}</span></a></li>
 </ul>
{elseif $mode=='thread'}
 <ul id="subnavmenu">
  <li>{if $usersettings.thread_display==0}<a href="index.php?mode=thread&amp;id={$id}&amp;toggle_thread_display=1" title="{#thread_linear_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-flat.svg" alt="" width="11" height="11" /><span>{#thread_linear#}</span></a>{else}<a class="hierarchic" href="index.php?mode=thread&amp;id={$id}&amp;toggle_thread_display=0" title="{#thread_hierarchical_linktitle#}"><img class="icon" src="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/thread-nested.svg" alt="" width="11" height="11" /><span>{#thread_hierarchical#}</span></a>{/if}</li>
 </ul>
{/if}
{if $categories && $mode=='index'}
 <form action="index.php" method="get" accept-charset="{#charset#}">
  <input type="hidden" name="mode" value="{$mode}" />
  <div>&nbsp;<select class="small" size="1" name="category" title="{#category_title#}">
<option value="0"{if $category==0} selected="selected"{/if}>{#all_categories#}</option>
{if $category_selection}<option value="-1"{if $category==-1} selected="selected"{/if}>{#my_category_selection#}</option>{/if}
{foreach key=key item=val from=$categories}
{if $key!=0}<option value="{$key}"{if $key==$category} selected="selected"{/if}>{$val}</option>{/if}
{/foreach}
</select><noscript><div class="inline"><input class="small" type="submit" value="&raquo;" title="{#go#}" /></div></noscript></div></form>{/if}
{if $pagination_top}
 <div id="subnav-pagination">
{if $pagination_top.previous}  <a href="index.php?mode={$mode}&amp;page={$pagination_top.previous}{if $category}&amp;category={$category}{/if}"><img src="{$THEMES_DIR}/{$theme}/images/triangle-full-left.svg" alt="[&laquo;]" title="{#previous_page_link_title#}" width="11" height="11" /></a>
{/if}
  <form action="index.php" method="get">
   <input type="hidden" name="mode" value="{$mode}" />
{if $order}   <input type="hidden" name="order" value="{$order}" />{/if}
{if $category}   <input type="hidden" name="category" value="{$category}" />{/if}
   <div class="inline">
    <select class="small" size="1" name="page" title="{#browse_page_title#}">
{foreach from=$pagination_top.items item=item}
{if $item!=0}     <option value="{$item}"{if $item==$page} selected="selected"{/if}>{$item}</option>
{/if}
{/foreach}
    </select>
    <noscript>
     <div class="inline"><input class="small" type="submit" value="&raquo;" title="{#go#}" /></div>
    </noscript>
   </div>
  </form>
{if $pagination_top.next}  <a href="index.php?mode={$mode}&amp;page={$pagination_top.next}{if $category}&amp;category={$category}{/if}"><img src="{$THEMES_DIR}/{$theme}/images/triangle-full-right.svg" alt="[&raquo;]" title="{#next_page_link_title#}" width="11" height="11" /></a>
{/if}
 </div>
{/if}
