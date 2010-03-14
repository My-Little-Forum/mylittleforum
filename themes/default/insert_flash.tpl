{config_load file=$language_file section="general"}{config_load file=$language_file section="insert_flash"}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{#language#}">
<head>
<title>{$settings.forum_name}{if $page_title} - {$page_title}{elseif $subnav_location} - {$subnav_location}{/if}</title>
<meta http-equiv="content-type" content="text/html; charset={#charset#}" />
{literal}
<style type="text/css">
<!--
body          { color: #000000; background: #ffffff; margin: 20px; padding: 0px; font-family: verdana, arial, sans-serif; font-size: 13px; }
h1            { font-family: verdana, arial, sans-serif; font-size: 18px; font-weight: bold; }
p             { font-family: verdana, arial, sans-serif; font-size: 13px; line-height: 19px; }
fieldset      { border:1px solid #c0c0c0; }
.caution      { padding: 0px 0px 0px 20px; color: red; font-weight: bold; background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/caution.png); background-repeat:no-repeat; background-position: left; }
.ok           { padding: 0px 0px 0px 20px; font-weight:bold; color:red; background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/tick.png); background-repeat:no-repeat; background-position: left; }
img.uploaded  { border: 1px solid #000; cursor:pointer; }
.small        { font-size:11px; line-height:16px; }
code          { font-family:"courier new", courier; color:#000080; }
a:link        { color: #0000cc; text-decoration: none; }
a:visited     { color: #0000cc; text-decoration: none; }
a:hover       { color: #0000ff; text-decoration: underline; }
a:active      { color: #ff0000; text-decoration: none; }
-->
</style>
<script src="js/main.js" type="text/javascript"></script>
<script type="text/javascript">/* <![CDATA[ */
function insertCode(f) {
	if (f && opener && opener.mlfBBCodeButton) {
		var bbcodeButton = opener.mlfBBCodeButton;
		if (!bbcodeButton.canInsert()) 
			return;
		
		var buttonGroup = bbcodeButton.getButtonGroup();	
		var txtarea = buttonGroup.getTextArea();
 
		var flash_code = f.elements["flash_code"].value;
 
		var flash_url    = f.elements["flash_url"].value;
		var flash_width  = f.elements["flash_width"].value;
		var flash_height = f.elements["flash_height"].value;
 
		if (flash_code!='') {
			if (flash_code.search(/vimeo\.com.+/i)!=-1) {
				var flash_url_pattern = /<embed.*?src=\"(.*?)\"/;
				flash_url_pattern.exec(flash_code);
				flash_url = RegExp.$1;
				var flash_width_pattern = /<object.*?width=\"(\d+)\"/;
				flash_width_pattern.exec(flash_code);
				flash_width = RegExp.$1;
				var flash_height_pattern = /<object.*?height=\"(\d+)\"/;
				flash_height_pattern.exec(flash_code);
				flash_height = RegExp.$1;
			}
			else if (flash_code.search(/myvideo\.de.+/i)!=-1) {
				var flash_url_pattern = /<param.*?value=\'(.*?)\'/;
				flash_url_pattern.exec(flash_code);
				flash_url = RegExp.$1;
				var flash_width_pattern = /width:(\d+)px/;
				flash_width_pattern.exec(flash_code);
				flash_width = RegExp.$1;
				var flash_height_pattern = /height:(\d+)px/;
				flash_height_pattern.exec(flash_code);
				flash_height = RegExp.$1;
			}
			else {
				var flash_url_pattern = /<param.*?value=\"(.*?)\"/;
				flash_url_pattern.exec(flash_code);
				flash_url = RegExp.$1;
				var flash_width_pattern = /<object.*?width=\"(\d+)\"/;
				flash_width_pattern.exec(flash_code);
				flash_width = RegExp.$1;
				var flash_height_pattern = /<object.*?height=\"(\d+)\"/;
				flash_height_pattern.exec(flash_code);
				flash_height = RegExp.$1;
			}
 
			txtarea.insertTextRange( txtarea.getSelection() + "[flash width="+flash_width+" height="+flash_height+"]"+flash_url+"[/flash]");
		}
		else if (flash_url!='') {
			txtarea.insertTextRange( txtarea.getSelection() + "[flash width="+flash_width+" height="+flash_height+"]"+flash_url+"[/flash]");
		}
	}
	self.close();
}
/* ]]> */</script>
{/literal}
</head>
<body>

<h1>{#insert_flash_hl#}</h1>

<form id="film" action="./">

<fieldset>
<legend>{#insert_flash_code#}</legend>
<p><label for="flash_code">{#flash_code#}</label><br />
<input type="text" id="flash_code" name="flash_code" size="40" /></p>
</fieldset>

<p><strong>{#select_flash_or#}</strong></p>

<fieldset>
<legend>{#insert_flash_custom#}</legend>
<p><label for="flash_url">{#flash_url#}</label><br />
<input type="text" id="flash_url" name="flash_url" size="40" /></p>
<p><label for="flash_width">{#flash_size#}</label><br />
<input type="text" id="flash_width" name="flash_width" value="{$settings.flash_default_width}" size="5" /> x <input type="text" id="flash_height" name="flash_height" value="{$settings.flash_default_height}" size="5" /> px</p>
</fieldset>

<p><input class="format-button" type="button" value="{#insert_flash_button#}" onclick="insertCode(this.form)" /></p>

</form>


</body>
</html>
