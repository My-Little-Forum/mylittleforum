{config_load file=$language_file section="bookmark"}

{if $action == 'bookmark'}
	{if $total_bookmarks > 0}
	<table id="sortable" class="normaltab" border="0" cellpadding="5" cellspacing="1">
		<thead>
			<tr>
				<th><a href="index.php?mode=bookmarks&amp;order=subject&amp;descasc={if $descasc=="ASC" && $order=="subject"}DESC{else}ASC{/if}&amp;bl={$bookmark_limit}">Subject</a>{if $order=="subject" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="subject" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
				<th><a href="index.php?mode=bookmarks&amp;order=user_name&amp;descasc={if $descasc=="ASC" && $order=="user_name"}DESC{else}ASC{/if}&amp;bl={$bookmark_limit}">Nutzer</a>{if $order=="user_name" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="user_name" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
				<th><a href="index.php?mode=bookmarks&amp;order=time&amp;descasc={if $descasc=="ASC" && $order=="time"}DESC{else}ASC{/if}&amp;bl={$bookmark_limit}">Erstellt am</a>{if $order=="time" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="time" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
				<th><a href="index.php?mode=bookmarks&amp;order=reply_time&amp;descasc={if $descasc=="ASC" && $order=="reply_time"}DESC{else}ASC{/if}&amp;bl={$bookmark_limit}">Letzte Antwort</a>{if $order=="reply_time" && $descasc=="ASC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/asc.png" alt="[asc]" width="5" height="9" />{elseif $order=="reply_time" && $descasc=="DESC"}&nbsp;<img src="{$THEMES_DIR}/{$theme}/images/desc.png" alt="[desc]" width="5" height="9" />{/if}</th>
				<th>&#160;</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$bookmarkdata item=row}
				{cycle values="a,b" assign=c}
				<tr class="{$c}">
					<td><a href="index.php?id={$row.id}" title="{#show_userdata_linktitle#|replace:"[user]":$row.user_name}"><strong>{$row.subject}</strong></a></td>
					<td>{if $row.user_id > 0}<a href="index.php?mode=user&amp;show_user={$row.user_id}" title="{#show_userdata_linktitle#|replace:"[user]":$row.user_name}">{/if}<strong>{$row.user_name}</strong>{if $row.user_id > 0}</a>{/if}</td>
					<td><span class="small">{$row.posting_time}</span></td>
					<td><span class="small">{$row.reply_time}</span></td>
					<td><a href="index.php?mode=bookmarks&amp;delete_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete#}" alt="{#delete#}" width="16" height="16"/></a> &nbsp; <a href="index.php?mode=bookmarks&amp;move_up_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_up.png" alt="{#up#}" title="{#up#}" width="16" height="16" /></a>&nbsp;<a href="index.php?mode=bookmarks&amp;move_down_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_down.png" alt="{#down#}" title="{#down#}" width="16" height="16" /></a></td>
				</tr>
			{/foreach}
		</tbody>
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
	
	{else}
		<p><em>{#no_bookmarks#}</em></p>
	{/if}
	
{elseif $action=='delete_bookmark'}
	{if $bookmark}
		<p class="caution">{#caution#}</p>
		<p>{#delete_bookmark_confirm#}</p>
		<p><strong>{$bookmark.subject}</strong></p>
		<form action="index.php" method="post" accept-charset="{#charset#}">
			<div>
				<input type="hidden" name="mode" value="bookmarks" />
				<input type="hidden" name="id" value="{$bookmark.id}" />
				<input type="submit" name="delete_bookmark_submit" value="{#delete_bookmark_submit#}" />
			</div>
		</form>
	{else}
		<p><em>{#no_bookmarks#}</em></p>
	{/if}
{/if}