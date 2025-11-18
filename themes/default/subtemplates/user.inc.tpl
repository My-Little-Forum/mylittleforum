{config_load file=$language_file section="user"}
<h2 id="admin_header">{#user_list_header#}</h2>
<div id="usersearch">
<form action="index.php" method="get" accept-charset="{#charset#}">
<input type="hidden" name="mode" value="user" />
<div>
<label for="search-user">{#search_user#}</label>
<input id="search-user" type="search" name="search_user" value="{if $search_user}{$search_user}{/if}" placeholder="{#search_user_default_value#}" size="35" />
&nbsp;<button>{#go#}</button>
</div>
</form>
</div>
{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $item>1}&amp;page={$item}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{/if}

{if $total_users > 0}
{if $descasc=="ASC"}
{assign var=sorting_icon value="<img class=\"sa-icon\" src=\"{$THEMES_DIR}/{$theme}/images/order-asc.svg\" alt=\"[desc]\" width=\"11\" height=\"11\" />"}
{else}
{assign var=sorting_icon value="<img class=\"sa-icon\" src=\"{$THEMES_DIR}/{$theme}/images/order-desc.svg\" alt=\"[desc]\" width=\"11\" height=\"11\" />"}
{/if}
<table class="normaltab">
<thead>
<tr>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_name&amp;descasc={if $descasc=="ASC" && $order=="user_name"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_name#}</a>
{if $order=="user_name"}{$sorting_icon}{/if}</th>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_type&amp;descasc={if $descasc=="ASC" && $order=="user_type"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_type#}</a>
{if $order=="user_type"}{$sorting_icon}{/if}</th>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_hp&amp;descasc={if $descasc=="ASC" && $order=="user_hp"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_hp#}</a>
{if $order=="user_hp"}{$sorting_icon}{/if}</th>
<th>{#user_email#}</th>
{if $mod || $admin}<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_lock&amp;descasc={if $descasc=="ASC" && $order=="user_lock"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_blockage#}</a>{if $order=="user_lock" && $descasc=="ASC"}&nbsp;<img class="sa-icon" src="{$THEMES_DIR}/{$theme}/images/order-asc.svg" alt="[asc]" width="11" height="11" />{elseif $order=="user_lock" && $descasc=="DESC"}&nbsp;<img class="sa-icon" src="{$THEMES_DIR}/{$theme}/images/order-desc.svg" alt="[desc]" width="11" height="11" />{/if}
{if $order=="user_lock"}{$sorting_icon}{/if}</th>{/if}
</tr>
</thead>
<tbody>
{foreach from=$userdata item=row}
<tr>
<td><a href="index.php?mode=user&amp;show_user={$row.user_id}" title="{#show_userdata_linktitle#|replace:"[user]":$row.user_name}"><span class="author-name">{$row.user_name}</span></a></td>
<td>{if $row.user_type==2}{#admin#}{elseif $row.user_type==1}{#mod#}{else}{#user#}{/if}</td>
<td>{if $row.user_hp!=''}<a href="{$row.user_hp}" title="{$row.user_hp}"><img class="sa-icon" src="{$THEMES_DIR}/{$theme}/images/general-homepage.svg" alt="{#homepage#}" width="13" height="13" /></a>{/if}</td>
<td>{if $row.user_email}<a href="index.php?mode=contact&amp;recipient_user_id={$row.user_id}" title="{#mailto_user#|replace:"[user]":$row.user_name}"><img class="sa-icon" src="{$THEMES_DIR}/{$theme}/images/e-mail-envelope.svg" alt="{#email#}" width="13" height="13" /></a>{/if}</td>
{if $mod || $admin}<td><span class="small">{if $row.user_type>0}{if $row.user_lock==0}{#unlocked#}{else}{#locked#}{/if}{elseif $row.user_lock==0}<a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;user_lock={$row.user_id}&amp;page={$page}&amp;order={$order}&amp;descasc={$descasc}" title="{#lock_title#}">{#unlocked#}</a>{else}<a class="user-locked" href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;user_lock={$row.user_id}&amp;page={$page}&amp;order={$order}&amp;descasc={$descasc}" title="{#unlock_title#}">{#user_locked#}</a>{/if}</span></td>{/if}
</tr>
{/foreach}
</tbody>
</table>

{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $item>1}&amp;page={$item}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{/if}

{if $users_online}
<aside id="usersonline">
 <div>
  <h3>{#currently_online#}</h3>
  <p>{foreach name="users_online" from=$users_online item=user}<a href="index.php?mode=user&amp;show_user={$user.id}">{$user.name}</a>{if !$smarty.foreach.users_online.last}, {/if}{/foreach}</p>
 </div>
</aside>
{/if}

{else}
<p><em>{#no_users#}</em></p>
{/if}