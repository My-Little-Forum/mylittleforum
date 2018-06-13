{config_load file=$language_file section="general"}{if $subnav_location && $subnav_location_var}{assign var="subnav_location" value=$smarty.config.$subnav_location|replace:"[var]":$subnav_location_var}{elseif $subnav_location}{assign var='subnav_location' value=$smarty.config.$subnav_location}{/if}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{#language#}" dir="{#dir#}">
<head>
<meta http-equiv="content-type" content="text/html; charset={#charset#}" />
<title>{if $page_title}{$page_title} - {elseif $subnav_location}{$subnav_location} - {/if}{$settings.forum_name|escape:"html"}</title>
<meta name="description" content="{$settings.forum_description|escape:"html"}" />
{if $keywords}<meta name="keywords" content="{$keywords}" />{/if}
{if $mode=='posting'}
<meta name="robots" content="noindex" />
{/if}
<meta name="referrer" content="origin" />
<meta name="referrer" content="same-origin" />
<meta name="generator" content="my little forum {$settings.version}" />
<link rel="stylesheet" type="text/css" href="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/style.min.css" media="all" />
{if $settings.rss_feed==1}<link rel="alternate" type="application/rss+xml" title="RSS" href="index.php?mode=rss" />{/if}
{if !$top}
<link rel="top" href="./" />
{/if}
{if $link_rel_first}
<link rel="first" href="{$link_rel_first}" />
{/if}
{if $link_rel_prev}
<link rel="prev" href="{$link_rel_prev}" />
{/if}
{* if $link_rel_next}
<link rel="next" href="{$link_rel_next}" />
{/if *}
{if $link_rel_last}
<link rel="last" href="{$link_rel_last}" />
{/if}
<link rel="search" href="index.php?mode=search" />
<link rel="shortcut icon" href="{$FORUM_ADDRESS}/{$THEMES_DIR}/{$theme}/images/favicon.ico" />
{if $mode=='entry'}<link rel="canonical" href="{$settings.forum_address}index.php?mode=thread&amp;id={$tid}" />{/if}
<script src="{$FORUM_ADDRESS}/index.php?mode=js_defaults&amp;t={$settings.last_changes}{if $user}&amp;user_type={if $mod}1{elseif $admin}2{else}0{/if}{/if}" type="text/javascript" charset="utf-8"></script>
<script src="{$FORUM_ADDRESS}/js/main.min.js" type="text/javascript" charset="utf-8"></script>
{if $mode=='posting'}
<script src="{$FORUM_ADDRESS}/js/posting.min.js" type="text/javascript" charset="utf-8"></script>
{/if}
{if $mode=='admin'}
<script src="{$FORUM_ADDRESS}/js/admin.min.js" type="text/javascript" charset="utf-8"></script>
{/if}
{if $settings.bbcode_latex && $settings.bbcode_latex_uri}
<script type="text/javascript" async src="{$settings.bbcode_latex_uri}"></script>
<script type="text/x-mathjax-config">/*<![CDATA[*/MathJax.Hub.Config({
    tex2jax: {
        inlineMath: [ ["$","$"], ["\\(","\\)"] ],
        displayMath: [ ["$$","$$"], ["\\[","\\]"] ],
		ignoreClass: "tex2jax_ignore",
		processClass: "tex2jax_process",
        processEscapes: true
    },

    TeX: {
        equationNumbers: { autoNumber: "AMS" }
    }
});
/*!]]>*/</script>
{/if}
</head>

<body class="tex2jax_ignore">
<!--[if IE]><div id="ie"><![endif]-->

<div id="top">

<div id="logo">
{if $settings.home_linkname}<p class="home"><a href="{$settings.home_linkaddress}">{$settings.home_linkname}</a></p>{/if}
<h1><a href="./" title="{#forum_index_link_title#}">{$settings.forum_name|escape:"html"}</a></h1>
</div>

<div id="nav">
<ul id="usermenu">
{if $user}<li><a href="index.php?mode=user&amp;action=edit_profile" title="{#profile_link_title#}"><strong>{$user}</strong></a></li><li><a href="index.php?mode=user&amp;action=show_posts&amp;id={$user_id}">{#show_all_postings_link#}</a></li><li><a href="index.php?mode=bookmarks">{#show_bookmarks_link#}</a></li><li><a href="index.php?mode=user" title="{#user_area_link_title#}">{#user_area_link#}</a></li>{if $admin}<li><a href="index.php?mode=admin" title="{#admin_area_link_title#}">{#admin_area_link#}</a></li>{/if}<li><a href="index.php?mode=login" title="{#log_out_link_title#}">{#log_out_link#}</a></li>{else}<li><a href="index.php?mode=login" title="{#log_in_link_title#}">{#log_in_link#}</a></li>{if $settings.register_mode!=2}<li><a href="index.php?mode=register" title="{#register_link_title#}">{#register_link#}</a></li>{/if}{if $settings.user_area_public}<li><a href="index.php?mode=user" title="{#user_area_link_title#}">{#user_area_link#}</a></li>{/if}{/if}
{if $menu}
{foreach $menu as $item}<li><a href="index.php?mode=page&amp;id={$item.id}">{$item.linkname}</a></li>{/foreach}
{/if}
</ul>
<form id="topsearch" action="index.php" method="get" title="{#search_title#}" accept-charset="{#charset#}"><div><input type="hidden" name="mode" value="search" /><label for="search-input">{#search_marking#}</label>&nbsp;<input id="search-input" type="text" name="search" value="{#search_default_value#}" /><!--&nbsp;<input type="image" src="templates/{$settings.template}/images/submit.png" alt="[&raquo;]" />--></div></form></div>
</div>

<div id="subnav">
<div id="subnav-1">{include file="$theme/subtemplates/subnavigation_1.inc.tpl"}</div>
<div id="subnav-2">{include file="$theme/subtemplates/subnavigation_2.inc.tpl"}</div>
</div>

<div id="content">
{if $subtemplate}
{include file="$theme/subtemplates/$subtemplate"}
{else}
{$content|default:""}
{/if}
</div>

<div id="footer">
<div id="footer-1">{if $total_users_online}{#counter_users_online#|replace:"[total_postings]":$total_postings|replace:"[total_threads]":$total_threads|replace:"[registered_users]":$registered_users|replace:"[total_users_online]":$total_users_online|replace:"[registered_users_online]":$registered_users_online|replace:"[unregistered_users_online]":$unregistered_users_online}{else}{#counter#|replace:"[total_postings]":$total_postings|replace:"[total_threads]":$total_threads|replace:"[registered_users]":$registered_users}{/if}<br />
{if $forum_time_zone}{#forum_time_with_time_zone#|replace:'[time]':$forum_time|replace:'[time_zone]':$forum_time_zone}{else}{#forum_time#|replace:'[time]':$forum_time}{/if}</div>
<div id="footer-2">
<ul id="footermenu">
{if $settings.rss_feed==1}<li><a class="rss" href="index.php?mode=rss" title="{#rss_feed_postings_title#}">{#rss_feed_postings#}</a> &nbsp;<a class="rss" href="index.php?mode=rss&amp;items=thread_starts" title="{#rss_feed_new_threads_title#}">{#rss_feed_new_threads#}</a></li>{/if}<li><a href="index.php?mode=contact" title="{#contact_linktitle#}" rel="nofollow">{#contact_link#}</a></li>
</ul></div>
</div>

{*
Please donate if you want to remove this link:
https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=1922497
*}
<div id="pbmlf"><a href="https://mylittleforum.net/">powered by my little forum</a></div>

<!--[if IE]></div><![endif]-->

</body>
</html>
