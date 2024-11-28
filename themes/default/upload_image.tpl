{config_load file=$language_file section="general"}{config_load file=$language_file section="upload_image"}<!DOCTYPE html>
<html lang="{#language#}">
 <head>
  <meta charset="{#charset#}" />
  <title>{$settings.forum_name}{if $page_title} - {$page_title}{elseif $subnav_location} - {$subnav_location}{/if}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style type="text/css">
{literal}
<!--
img           { border:none; }
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
#header {
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
#header > * {
  margin:0;
  padding:0;
}
#wrapper {
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
.caution h2,
.ok h2 {
  font-size: 1em;
  margin: 0 0 0.5em 0;
}
.caution {
  color: #cc0000;
  background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/caution.png);
}
.ok {
  color:green;
  background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/tick.png);
}
img.delete    { max-width:300px; max-height:150px; }
img.uploaded  { max-width:300px; max-height:110px; /*cursor:pointer;*/ }
.deletelink   { font-size:11px; padding-left:13px; background:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/bg_sprite_3.png) no-repeat 0 -47px; }
.small        {
  font-size: 0.86em;
}
code {
  font-family:"courier new", courier, monospace;
  color:#000080;
}
a:link        { color:#0000cc; text-decoration: none; }
a:visited     { color:#0000cc; text-decoration: none; }
a:hover       { color:#0000ff; text-decoration: underline; }
a:active      { color:#ff0000; text-decoration: none; }
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
li > div:last-child {
  align-content:center;
}
img {
  border:none;
  display: block;
}
#imgtab img {
  max-width: 100%;
  margin-inline: auto;
}
#imgtab:not(.shrinked) img {
  cursor: pointer;
}
#imgtab.shrinked img {
  max-width: 50%;
  height: auto;
}
-->
{/literal}
  </style>
  <script type="text/javascript">{literal}/* <![CDATA[ */
function insertCode(image_url) {
 	if (opener && opener.mlfBBCodeButton) {
		var bbcodeButton = opener.mlfBBCodeButton;
		if (!bbcodeButton.canInsert()) 
			return;
		var buttonGroup = bbcodeButton.getButtonGroup();	
		var txtarea = buttonGroup.getTextArea();
		txtarea.insertTextRange( txtarea.getSelection() + "[img]" + image_url + "[/img]" );
	}
	//self.close();
}
/* ]]> */{/literal}</script>
 </head>
 <body>
{if $form}
 <div id="header">
  <h1>{#upload_image_hl#}</h1>
 </div>
 <div id="wrapper">
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
<div>
<input type="hidden" name="mode" value="upload_image" />
<p><input type="file" name="probe" size="17" /></p>
<p><input type="submit" name="" value="{#upload_image_button#}" onclick="document.getElementById('throbber-submit').style.visibility='visible'" /> <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</div>
</form>
<p class="small"><a href="index.php?mode=upload_image&amp;browse_images=1">{#browse_uploaded_images#}</a></p>
 </div>
{elseif $uploaded_file}
 <div id="header">
  <h1>{#upload_image_hl#}</h1>
 </div>
 <div id="wrapper">
  <div class="ok">
   <h2>{#upload_successful#}</h2>
  </div>
{*<script type="text/javascript">/* <![CDATA[ */document.write('<p>{#insert_image_exp#|escape:quotes}<\/p>'); /* ]]> */</script>*}
<noscript><p>{#insert_image_exp_no_js#}</p>
<p><code>[img]images/uploaded/{$uploaded_file}[/img]</code></p></noscript>
{if $image_downsized}<p class="small">{$smarty.config.image_downsized|replace:"[width]":$new_width|replace:"[height]":$new_height|replace:"[filesize]":$new_filesize}</p>{/if}
  <ul id="imgtab" class="shrinked">
   <li><img src="images/uploaded/{$uploaded_file}" title="{#insert_image#}" {*onclick="insertCode('images/uploaded/{$uploaded_file}'); return false;" *}alt="{#insert_image#}" /></li>
  </ul>
 </div>
<script type="text/javascript">/* <![CDATA[ */ insertCode('images/uploaded/{$uploaded_file}'); /* ]]> */</script>
{elseif $browse_images}
 <div id="header">
  <div id="nav-1"><a href="index.php?mode=upload_image">{#back#}</a></div>
  <div id="nav-2">{if $previous}[ <a href="index.php?mode=upload_image&amp;browse_images={$previous}" title="{#previous_page_link_title#}">&laquo;</a> ]{/if}{if $previous && next} {/if}{if $next}[ <a href="index.php?mode=upload_image&amp;browse_images={$next}" title="{#next_page_link_title#}">&raquo;</a> ]{/if}</div>
 </div>
{if $images}
 <div id="wrapper">
  <ul id="imgtab">
{section name=nr loop=$images start=$start max=$images_per_page}
   <li>
    <div><img src="images/uploaded/{$images[nr]}" title="{#insert_image#}" onclick="insertCode('images/uploaded/{$images[nr]}'); self.close();" alt="{#insert_image#}" /></div>
{if $admin || $mod}    <div><a class="deletelink" href="index.php?mode=upload_image&amp;delete={$images[nr]}&amp;current={$current}">{#delete#}</a></div>
{/if}
   </li>
{/section}
  </ul>
 </div>
{else}
<p>{#no_images#}</p>
 <div id="wrapper">
 </div>
{/if}
{elseif $delete_confirm}
<form id="uploadform" action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="upload_image" />
<input type="hidden" name="delete" value="{$delete}" />
{if $current}<input type="hidden" name="current" value="{$current}" />{/if}
<input type="submit" name="delete_confirm" value="{#delete_image_button#}" />
</div>
</form>
 <div id="header">
  <div id="nav-1"><a href="index.php?mode=upload_image&amp;browse_images={$current|default:'1'}">{#back#}</a></div>
 </div>
 <div id="wrapper">
  <div class="caution">
   <h2>{#delete_image_confirm#}</h2>
  </div>
  <ul id="imgtab" class="shrinked">
   <li><img src="images/uploaded/{$delete}" alt="{$delete}" /></li>
  </ul>
 </div>
{else}
 <div id="wrapper">
  <div class="caution">
   <h2>{#image_upload_not_enabled#}</h2>
  </div>
 </div>
{/if}
</body>
</html>
