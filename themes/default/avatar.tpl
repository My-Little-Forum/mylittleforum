{config_load file=$language_file section="general"}{config_load file=$language_file section="avatar"}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{#language#}">
<head>
<title>{$settings.forum_name}{if $page_title} - {$page_title}{elseif $subnav_location} - {$subnav_location}{/if}</title>
<meta http-equiv="content-type" content="text/html; charset={#charset#}" />
<meta http-equiv="cache-control" content="no-cache">
{literal}
<style type="text/css">
<!--
body          { color: #000000; background: #ffffff; margin: 20px; padding: 0px; font-family: verdana, arial, sans-serif; font-size: 13px; }
h1            { font-family: verdana, arial, sans-serif; font-size: 18px; font-weight: bold; }
p             { font-family: verdana, arial, sans-serif; font-size: 13px; line-height: 19px; }
.caution      { padding: 0px 0px 0px 20px; color: red; font-weight: bold; background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/caution.png); background-repeat:no-repeat; background-position: left; }
.ok           { padding: 0px 0px 0px 20px; font-weight:bold; color:red; background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/tick.png); background-repeat:no-repeat; background-position: left; }
img.uploaded  { border: 1px solid #000; cursor:pointer; }
.small        { font-size:11px; line-height:16px; }
.delete a             { text-decoration:none !important; }
.delete a:hover span  { text-decoration:underline; }
.delete a img         { border:none; margin:0px 5px -3px 0px; padding:0px; }
a:link        { color: #0000cc; text-decoration: none; }
a:visited     { color: #0000cc; text-decoration: none; }
a:hover       { color: #0000ff; text-decoration: underline; }
a:active      { color: #ff0000; text-decoration: none; }
-->
</style>

<script type="text/javascript">/* <![CDATA[ */
function setPictureToProfil(src) {
 	if (opener && opener.document.getElementById("avatar_wrapper")) {
		var avatarWrapper = opener.document.getElementById("avatar_wrapper")
		if (src) {
			var img = new Image();
			img.src = src;
			avatarWrapper.innerHTML = '';
			avatarWrapper.appendChild(img);
		}
		else {
			avatarWrapper.innerHTML = '';
		}
	}
};
{/literal}
{if $avatar_uploaded}
setPictureToProfil('{$avatar}');
{elseif $avatar_deleted}
setPictureToProfil('');
{/if}
{literal}
/* ]]> */</script>
{/literal}
</head>
<body>
{if $avatar}
<h1>{#avatar_hl#}</h1>
{if $avatar_uploaded}
<p class="ok">{#upload_successful#}</p>
{/if}
<p><img src="{$avatar}" alt="" /></p>
{if $image_downsized}<p class="small">{$smarty.config.image_downsized|replace:"[width]":$new_width|replace:"[height]":$new_height|replace:"[filesize]":$new_filesize}</p>{/if}
{if $avatar_uploaded}
<script type="text/javascript">/* <![CDATA[ */ document.write('<p><button onclick=\"window.close()\">{#close_window#}</button><\/p>'); /* ]]> */</script>
{else}
<p class="delete"><a href="index.php?mode=avatar&amp;delete=true"><img src="{$THEMES_DIR}/{$settings.theme}/images/delete.png" alt="" width="16" height="16" /><span>{#delete_avatar#}</span></a></p>
{/if}
{elseif $upload}
<h1>{#upload_avatar_hl#}</h1>
<p>{#upload_avatar_notes#|replace:"[width]":$settings.avatar_max_width|replace:"[height]":$settings.avatar_max_width|replace:"[filesize]":$settings.avatar_max_filesize}</p>
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
<li>{assign var="error" value=$errors[mysec]}{$smarty.config.$error|replace:"[width]":$width|replace:"[height]":$height|replace:"[filesize]":$filesize|replace:"[max_width]":$max_width|replace:"[max_height]":$max_height|replace:"[max_filesize]":$max_filesize|replace:"[server_max_filesize]":$server_max_filesize}</li>
{/section}
</ul>
{/if}
<form id="uploadform" action="index.php" method="post" enctype="multipart/form-data" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="avatar" />
<p><input type="file" name="probe" size="17" /></p>
<p><input type="submit" name="" value="{#upload_image_button#}" onclick="document.getElementById('throbber-submit').style.visibility='visible'" /> <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$settings.template}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</div>
</form>
{else}
<p class="caution">{#avatars_disabled#}</p>
{/if}
</body>
</html>
