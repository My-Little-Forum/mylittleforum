{config_load file=$language_file section="bookmark"}

{if $action == 'bookmark'}
	{if $total_bookmarks > 0}
		<table id="sortable" class="normaltab" border="0" cellpadding="5" cellspacing="1">
			<thead>
				<tr>
					<th>{#bookmark_title#}</th>
					<th>{#bookmark_user_name#}</th>
					<th>{#bookmark_creation_time#}</th>
					<th>{#bookmark_posting_time#}</th>
					<th>Tags</th>
					<th>&#160;</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$bookmarkdata item=row}
					{cycle values="a,b" assign=c}
					<tr id="id_{$row.bid}" class="{$c}">
						<td><a href="index.php?id={$row.id}" title="{$row.subject}"><strong>{$row.subject}</strong></a></td>
						<td>{if $row.user_id > 0}<a href="index.php?mode=user&amp;show_user={$row.user_id}" title="{#show_userdata_linktitle#|replace:"[user]":$row.user_name}">{/if}<strong>{$row.user_name}</strong>{if $row.user_id > 0}</a>{/if}</td>
						<td><span class="small">{$row.bookmark_time}</span></td>
						<td><span class="small">{$row.posting_time}</span></td>
						
						<td><span class="small">
							{foreach name="tags" from=$row.tags item=tag}
								<a title="{#bookmark_filter_linktitle#}" href="index.php?mode=bookmarks&amp;filter={$tag.escaped}">{$tag.display}</a>{if !$smarty.foreach.tags.last}, {/if}
							{/foreach}
						</span></td>
						
						<td><a href="index.php?mode=bookmarks&amp;edit_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/edit.png" title="{#edit#}" alt="{#edit#}" width="16" height="16" /></a> &nbsp; <a href="index.php?mode=bookmarks&amp;delete_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/delete.png" title="{#delete#}" alt="{#delete#}" width="16" height="16"/></a> &nbsp; <a href="index.php?mode=bookmarks&amp;move_up_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_up.png" alt="{#up#}" title="{#up#}" width="16" height="16" /></a>&nbsp;<a href="index.php?mode=bookmarks&amp;move_down_bookmark={$row.bid}"><img class="control" src="{$THEMES_DIR}/{$theme}/images/arrow_down.png" alt="{#down#}" title="{#down#}" width="16" height="16" /></a></td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		{if $filter}
			<p><a href="index.php?mode=bookmarks" title="{#clear_bookmark_filter_linktitle#}">{#clear_bookmark_filter_linkname#}</a></p>
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
	
{elseif $action=='edit_bookmark'}
	{if $errors}
		<p class="caution">{#error_headline#}</p>
		<ul style="margin-bottom:25px;">
		{section name=mysec loop=$errors}
			<li>{assign var="error" value=$errors[mysec]}{$smarty.config.$error|replace:"[word]":$word}</li>
		{/section}
		</ul>
	{/if}

	{if $bookmark}
		<form action="index.php" method="post" class="normalform" accept-charset="{#charset#}">
			<div>
				<input type="hidden" name="mode" value="bookmarks" />
				<input type="hidden" name="id" value="{$bookmark.id}" />
				<label for="bookmark" class="input"><strong>{#edit_bookmark#}</strong></label><br />
				<input type="text" id="bookmark" name="bookmark" value="{$bookmark.title}" maxlength="255" size="25" /><br /><br />
				<label for="tags" class="input"><strong>{#edit_tags#}</strong></label><br />
				<input type="text" id="tags" name="tags" value="{$bookmark.tags}" maxlength="255" size="25" />&nbsp;<span class="xsmall">{#edit_tags_note#}</span><br /><br />
				<input type="submit" name="edit_bookmark_submit" value="{#submit_button_ok#}" />
			</div>
		</form>
	{else}
		<p><em>{#no_bookmarks#}</em></p>
	{/if}
{else}
	<p><em>{#no_bookmarks#}</em></p>
{/if}