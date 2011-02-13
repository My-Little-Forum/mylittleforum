{config_load file=$language_file section="general"}{config_load file=$language_file section="upload_image"}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{#language#}">
<head>
<title>{$settings.forum_name}{if $page_title} - {$page_title}{elseif $subnav_location} - {$subnav_location}{/if}</title>
<meta http-equiv="content-type" content="text/html; charset={#charset#}" />
<style type="text/css">
{literal}
<!--
body          { color: #000000; background: #ffffff; margin:0; padding:0; font-family: verdana, arial, sans-serif; font-size: 13px; }
img           { border:none; }
#header       { margin:0; padding:0; background:#f9f9f9; border-bottom: 1px solid #bacbdf; height:24px; font-size:13px; line-height:22px; }
#nav-1        { margin:0; padding:0 0 0 5px; float:left; }
#nav-2        { margin:0; padding:0 5px 0 0; float:right; }
#wrapper      { margin:0; padding:20px; }
h1            { font-family: verdana, arial, sans-serif; font-size: 18px; font-weight: bold; }
p             { font-family: verdana, arial, sans-serif; font-size: 13px; line-height: 19px; }
.caution      { padding: 0px 0px 0px 20px; color: red; font-weight: bold; background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/caution.png); background-repeat:no-repeat; background-position: left; }
.ok           { padding: 0px 0px 0px 20px; font-weight:bold; color:red; background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/tick.png); background-repeat:no-repeat; background-position: left; }
img.delete    { max-width:300px; max-height:150px; }
img.uploaded  { max-width:300px; max-height:110px; /*cursor:pointer;*/ }
img.browse    { max-width:320px; cursor:pointer; }
.deletelink   { font-size:11px; padding-left:13px; background:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/bg_sprite_3.png) no-repeat 0 -47px; }
.small        { font-size:11px; line-height:16px; }
code          { font-family:"courier new", courier; color:#000080; }
a:link        { color:#0000cc; text-decoration: none; }
a:visited     { color:#0000cc; text-decoration: none; }
a:hover       { color:#0000ff; text-decoration: underline; }
a:active      { color:#ff0000; text-decoration: none; }
table         { width:100%; margin:5px 0 0 0; padding:0; }
td            { text-align:center; }
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
{if $browse_images}
<script type="text/javascript">{literal}/* <![CDATA[ */
function getMaxWidth()
 {
  if(document.getElementById('imgtab'))
   {
    var maxWidth = document.getElementById('imgtab').offsetWidth-20;
    var obj=getElementsByClassName('browse');
    for(i=0;i<obj.length;i++)
     {
      obj[i].style.maxWidth=maxWidth+'px';
     }
   }
 }
window.onload = getMaxWidth;
window.onresize = getMaxWidth;
/* ]]> */{/literal}</script>
{/if}
</head>
<body>
{if $form}
<div id="wrapper">
<h1>{#upload_image_hl#}</h1>
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
<input type="hidden" name="mode" value="upload_image" />
<p><input type="file" name="probe" size="17" /></p>
<p><input type="submit" name="" value="{#upload_image_button#}" onclick="document.getElementById('throbber-submit').style.visibility='visible'" /> <img id="throbber-submit" style="visibility:hidden;" src="{$THEMES_DIR}/{$theme}/images/throbber_submit.gif" alt="" width="16" height="16" /></p>
</div>
</form>
<p class="small"><a href="index.php?mode=upload_image&amp;browse_images=1">{#browse_uploaded_images#}</a></p>
</div>
{elseif $uploaded_file}
<div id="wrapper">
<h1>{#upload_image_hl#}</h1>
<p class="ok">{#upload_successful#}</p>
{*<script type="text/javascript">/* <![CDATA[ */document.write('<p>{#insert_image_exp#|escape:quotes}<\/p>'); /* ]]> */</script>*}
<noscript><p>{#insert_image_exp_no_js#}</p>
<p><code>[img]images/uploaded/{$uploaded_file}[/img]</code></p></noscript>
<img class="uploaded" src="images/uploaded/{$uploaded_file}" title="{#insert_image#}" {*onclick="insertCode('images/uploaded/{$uploaded_file}'); return false;" *}alt="{#insert_image#}" />
{if $image_downsized}<p class="small">{$smarty.config.image_downsized|replace:"[width]":$new_width|replace:"[height]":$new_height|replace:"[filesize]":$new_filesize}</p>{/if}
</div>
<script type="text/javascript">/* <![CDATA[ */ insertCode('images/uploaded/{$uploaded_file}'); /* ]]> */</script>
{elseif $browse_images}
<div id="header">
<div id="nav-1"><a href="index.php?mode=upload_image">{#back#}</a></div>
<div id="nav-2">{if $previous}[ <a href="index.php?mode=upload_image&amp;browse_images={$previous}" title="{#previous_page_link_title#}">&laquo;</a> ]{/if}{if $previous && next} {/if}{if $next}[ <a href="index.php?mode=upload_image&amp;browse_images={$next}" title="{#next_page_link_title#}">&raquo;</a> ]{/if}</div>
</div>
{if $images}
<table id="imgtab" border="0" cellpadding="5" cellspacing="1">
{section name=nr loop=$images start=$start max=$images_per_page}
{cycle values="odd,even" assign=c}
<tr class="{$c}">
<td><img class="browse" src="images/uploaded/{$images[nr]}" title="{#insert_image#}" onclick="insertCode('images/uploaded/{$images[nr]}'); self.close();" alt="{#insert_image#}" />{if $admin || $mod}<br /><a class="deletelink" href="index.php?mode=upload_image&amp;delete={$images[nr]}&amp;current={$current}">{#delete#}</a>{/if}</td>
</tr>
{/section}
</table>
{else}
<div id="wrapper">
<p>{#no_images#}</p>
</div>
{/if}
{elseif $delete_confirm}
<div id="header">
<div id="nav-1"><a href="index.php?mode=upload_image&amp;browse_images={$current|default:'1'}">{#back#}</a></div>
</div>
<div id="wrapper">
<p class="caution">{#delete_image_confirm#}</p>
<p><img class="delete" src="images/uploaded/{$delete}" alt="{$delete}" /></p>
<form id="uploadform" action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="upload_image" />
<input type="hidden" name="delete" value="{$delete}" />
{if $current}<input type="hidden" name="current" value="{$current}" />{/if}
<input type="submit" name="delete_confirm" value="{#delete_image_button#}" />
</div>
</form>
</div>
{else}
<div id="wrapper">
<p class="caution">{#image_upload_not_enabled#}</p>
</div>
{/if}
</body>
</html>
