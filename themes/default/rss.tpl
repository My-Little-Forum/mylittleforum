{config_load file=$settings.language_file section="general"}<?xml version="1.0" encoding="{#charset#}"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/"{if $thread_starts} xmlns:wfw="http://wellformedweb.org/CommentAPI/"{/if}>
<channel>
<title>{$settings.forum_name|escape:"html"}{if $thread && $rss_items[0].title} - {$rss_items[0].title}{/if}</title>
<link>{$settings.forum_address}</link>
<description>{$settings.forum_description|escape:"html"}</description>
<language>{#language#}</language>
{if $rss_items}
{foreach from=$rss_items item=item}
<item>
<title>{if $replies}{$item.name}: {/if}{$item.title}{if $item.reply && !$replies} {#rss_reply_marking#}{/if}</title>
<content:encoded><![CDATA[{*<p><em>{if $item.reply}{$smarty.config.rss_reply_by|replace:"[name]":$item.name|replace:"[time]":$item.formated_time}{else}{$smarty.config.rss_posting_by|replace:"[name]":$item.name|replace:"[time]":$item.formated_time}{/if}</em></p>*}{if $item.text!=''}{$item.text}{else}{#no_text#}{/if}]]></content:encoded>
<link>{$item.link}</link>
<guid>{$item.link}</guid>
<pubDate>{$item.pubdate}</pubDate>
{if $item.category}<category>{$item.category}</category>{/if}
{if $thread_starts}<wfw:commentRss>{$item.commentRss}</wfw:commentRss>{/if}
<dc:creator>{$item.name}</dc:creator>
</item>
{/foreach}
{/if}
</channel>
</rss>
