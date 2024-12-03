{config_load file=$language_file section="general"}{config_load file=$language_file section="avatar"}<!DOCTYPE html>
<html lang="{#language#}">
 <head>
  <meta charset="{#charset#}" />
  <title>{$settings.forum_name}{if $page_title} - {$page_title}{elseif $subnav_location} - {$subnav_location}{/if}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="cache-control" content="no-cache">
  <style type="text/css">
{literal}
<!--
*,
::before,
::after {
  box-sizing: border-box;
}
body {
  color: #000;
  background: #fff;
  margin: 0;
  padding: 0;
  font-family: verdana, arial, sans-serif;
  font-size: 1em;
  font-size: 1rem;
  line-height: 1.5;
}
h1 {
  font-size: 1em;
  font-weight: bold;
}
.caution,
.ok {
  margin-block: 0.5em;
  padding: 0 0 0 24px;
  background-repeat:no-repeat;
  background-position: 2px 3px;
}
.caution {
  color: #cc0000;
  font-weight: bold;
  background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/caution.png);
}
.ok {
  font-weight:bold;
  color:green;
  background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/tick.png);
}
img.uploaded {
  border: 1px solid #000;
  cursor:pointer;
}
.small {
  font-size: 0.82em;
}
.delete a {
  text-decoration:none !important;
}
.delete a:hover span  {
  text-decoration:underline;
}
.delete a img {
  border:none;
  margin:0px 5px -3px 0px;
  padding:0px;
}
a {
  color: #00c;
  text-decoration: none;
}
a:focus,
a:hover {
  color:#00f;
  text-decoration: underline dotted 9% #45f;
}
a:active {
  color:#f00;
  text-decoration: underline solid 7% #d00;
}
-->
{/literal}
  </style>

  <script type="text/javascript">{literal}
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
{/literal}</script>
 </head>
 <body>
{if $avatar}
 <header>
  <h1>{#avatar_hl#}</h1>
 </header>
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
 <header>
  <h1>{#upload_avatar_hl#}</h1>
 </header>
 <main>
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
   <input type="hidden" name="mode" value="avatar" />
   <div>
    <input type="file" name="probe" size="17" />
   </div>
   <div class="buttonbar">
    <button value="{#upload_image_button#}" onclick="document.getElementById('throbber-submit').style.visibility='visible'">{#upload_image_button#}</button>
    <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" />
   </div>
  </form>
 </main>
{else}
 <main>
  <div class="caution">
   <h2>{#avatars_disabled#}</h2>
  </div>
 </main>
{/if}
</body>
</html>
