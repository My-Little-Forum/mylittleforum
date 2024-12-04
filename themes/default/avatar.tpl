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
body > * {
  padding-block: 0;
  padding-inline: 0.5em;
}
header {
  margin: 0;
  background: #f9f9f9;
  border-bottom: 1px solid #bacbdf;
  display: flex;
  justify-content: space-between;
  position: sticky;
  top: 0;
  left: 0;
  width: 100%;
}
header > * {
  margin:0;
  padding:0;
}
main {
  margin-inline: 0;
  margin-block: 0.5em;
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
form > div:not(:last-child) {
  margin-block-end: .75em;
}
ul {
  list-style:none;
  margin-block:0.5em;
  padding: 0;
}
#imgtab {
  display:flex;
  flex-direction:column;
  gap:0.75em;
}
#imgtab li {
  text-align:center;
}
-->
{/literal}
  </style>

  <script type="text/javascript">{literal}
function setPictureToProfile(src) {
  if (opener && opener.document.getElementById("avatar_wrapper")) {
    const avatarWrapper = opener.document.getElementById("avatar_wrapper")
    if (src) {
      const img = new Image();
      img.src = src;
      avatarWrapper.innerHTML = '';
      avatarWrapper.appendChild(img);
    } else {
      avatarWrapper.innerHTML = '';
    }
  }
};
{/literal}
{if $avatar_uploaded}
setPictureToProfile('{$avatar}');
{elseif $avatar_deleted}
setPictureToProfile('');
{/if}
{literal}
{/literal}</script>

window.addEventListener('DOMContentLoaded', function() {
  if (document.querySelector('button[name="close-form"]')) {
    document.querySelector('button[name="close-form"]').addEventListener('click', function() {
      window.close();
    });
  }
});
 </head>
 <body>
{if $avatar}
 <header>
  <h1>{#avatar_hl#}</h1>
 </header>
 <main>
{if $avatar_uploaded}
  <div class="ok">
   <h2>{#upload_successful#}</h2>
  </div>
{/if}
  <ul id="imgtab" class="shrinked">
   <li><img src="{$avatar}" alt="" /></li>
  </ul>
{if $image_downsized}  <p class="small">{$smarty.config.image_downsized|replace:"[width]":$new_width|replace:"[height]":$new_height|replace:"[filesize]":$new_filesize}</p>{/if}
  <form id="del-upload-form" action="index.php" method="post" accept-charset="{#charset#}">
   <input type="hidden" name="mode" value="avatar" />
{if $avatar_uploaded}
   <div>
    <button type="button" name="close-form">{#close_window#}</button>
   </div>
{else}
   <div>
    <button name="delete" value="{#delete_avatar#}">{#delete_avatar#}</button>
    <button type="button" name="close-form">{#close_window#}</button>
   </div>
{/if}
  </form>
 </main>
{elseif $upload}
 <header>
  <h1>{#upload_avatar_hl#}</h1>
 </header>
 <main>
  <p class="instruction">{#upload_avatar_notes#|replace:"[width]":$settings.avatar_max_width|replace:"[height]":$settings.avatar_max_width|replace:"[filesize]":$settings.avatar_max_filesize}</p>
{if $errors}
  <div class="caution">
   <h2>{#error_headline#}</h2>
   <ul>
{section name=mysec loop=$errors}
    <li>{assign var="error" value=$errors[mysec]}{$smarty.config.$error|replace:"[width]":$width|replace:"[height]":$height|replace:"[filesize]":$filesize|replace:"[max_width]":$max_width|replace:"[max_height]":$max_height|replace:"[max_filesize]":$max_filesize|replace:"[server_max_filesize]":$server_max_filesize}</li>
{/section}
   </ul>
  </div>
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
