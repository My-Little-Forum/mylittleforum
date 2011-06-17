{config_load file=$language_file section="user"}
<div id="usernav">
<div id="usersearch">
<label for="search-user">{#search_user#}</label><form action="index.php" method="get" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="user" />
<input id="search-user" type="text" name="search_user" value="{if $search_user}{$search_user}{else}{#search_user_default_value#}{/if}" size="25" alt="{#search_user_default_value#}" />{*&nbsp;<input type="image" src="{$THEMES_DIR}/{$theme}/images/submit.png" alt="[&raquo;]" />*}
</div>
</form>
</div>
<div id="userpagination">
{if $pagination}
<ul class="pagination">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $item>1}&amp;page={$item}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{else}
&nbsp;
{/if}
</div>
</div>

{if $total_users > 0}
<table class="normaltab" border="0" cellpadding="5" cellspacing="1">
<tr>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_name&amp;descasc={if $descasc=="ASC" && $order=="user_name"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_name#}</a>{if $order=="user_name" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_name" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_type&amp;descasc={if $descasc=="ASC" && $order=="user_type"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_type#}</a>{if $order=="user_type" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_type" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_hp&amp;descasc={if $descasc=="ASC" && $order=="user_hp"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_hp#}</a>{if $order=="user_hp" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_hp" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=email_contact&amp;descasc={if $descasc=="ASC" && $order=="email_contact"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_email#}</a>{if $order=="email_contact" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="email_contact" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
{*<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=postings&amp;descasc={if $descasc=="ASC" && $order=="postings"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_postings#}</a>{if $order=="postings" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="postings" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>*}
{*{if $settings.count_users_online>0}<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=online&amp;descasc={if $descasc=="ASC" && $order=="online"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_online#}</a>{if $order=="online" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="online" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>{/if}*}
{if $mod || $admin}<th><a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;order=user_lock&amp;descasc={if $descasc=="ASC" && $order=="user_lock"}DESC{else}ASC{/if}&amp;ul={$ul}" title="{#order_linktitle#}">{#user_blockage#}</a>{if $order=="user_lock" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_lock" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>{/if}
</tr>
{foreach from=$userdata item=row}
{cycle values="a,b" assign=c}
<tr class="{$c}">
<td><a href="index.php?mode=user&amp;show_user={$row.user_id}" title="{#show_userdata_linktitle#|replace:"[user]":$row.user_name}"><strong>{$row.user_name}</strong></a></td>
<td><span class="small">{if $row.user_type==2}{#admin#}{elseif $row.user_type==1}{#mod#}{else}{#user#}{/if}</span></td>
<td><span class="small">{if $row.user_hp!=''}<a href="{$row.user_hp}" title="{$row.user_hp}"><img src="{$THEMES_DIR}/{$theme}/images/homepage.png" alt="{#homepage#}" width="13" height="13" /></a>{else}&nbsp;{/if}</span></td>
<td><span class="small">{if $row.user_email}<a href="index.php?mode=contact&amp;user_id={$row.user_id}" title="{#mailto_user#|replace:"[user]":$row.user_name}"><img src="{$THEMES_DIR}/{$theme}/images/email.png" alt="{#email#}" width="13" height="10" /></a>{else}&nbsp;{/if}</span></td>
{*<td><span class="small">{if $row.postings>0}<a href="index.php?mode=user&amp;action=show_posts&amp;id={$row.user_id}">{$row.postings}</a>{else}{$row.postings}{/if}</span></td>*}
{*{if $settings.count_users_online>0}<td class="{$c}"><span class="small">{if $row.online}<span style="color:red;">{#online#}</span>{else}&nbsp;{/if}</span></td>{/if}*}
{if $mod || $admin}<td><span class="small">{if $row.user_type>0}{if $row.user_lock==0}{#unlocked#}{else}{#locked#}{/if}{elseif $row.user_lock==0}<a href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;user_lock={$row.user_id}&amp;page={$page}&amp;order={$order}&amp;descasc={$descasc}" title="{#lock_title#}">{#unlocked#}</a>{else}<a class="user-locked" href="index.php?mode=user{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}&amp;user_lock={$row.user_id}&amp;page={$page}&amp;order={$order}&amp;descasc={$descasc}" title="{#unlock_title#}">{#user_locked#}</a>{/if}</span></td>{/if}
</tr>
{/foreach}
</table>

{if $pagination}
<ul class="pagination pagination-index-table">
{if $pagination.previous}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $pagination.previous>1}&amp;page={$pagination.previous}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#previous_page_link_title#}">{#previous_page_link#}</a></li>{/if}
{foreach from=$pagination.items item=item}
{if $item==0}<li>&hellip;</li>{elseif $item==$pagination.current}<li><span class="current">{$item}</span></li>{else}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}{if $item>1}&amp;page={$item}{/if}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}">{$item}</a></li>{/if}
{/foreach}
{if $pagination.next}<li><a href="index.php?mode={$mode}{if $action}&amp;action={$action}{/if}{if $search_user_encoded}&amp;search_user={$search_user_encoded}{/if}{if $method && $method!='fulltext'}&amp;method={$method}{/if}{if $id}&amp;id={$id}{/if}&amp;page={$pagination.next}{if $p_category && $p_category>0}&amp;p_category={$p_category}{/if}{if $order}&amp;order={$order}{/if}{if $descasc}&amp;descasc={$descasc}{/if}" title="{#next_page_link_title#}">{#next_page_link#}</a></li>{/if}
</ul>
{/if}

{if $users_online}
<div id="usersonline">
<h3>{#currently_online#}</h3>
<p>{foreach name="users_online" from=$users_online item=user}<a href="index.php?mode=user&amp;show_user={$user.id}">{$user.name}</a>{if !$smarty.foreach.users_online.last}, {/if}{/foreach}</p>
</div>
{/if}

{else}
<p><em>{#no_users#}</em></p>
{/if}
