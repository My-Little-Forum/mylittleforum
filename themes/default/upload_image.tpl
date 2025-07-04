{config_load file=$language_file section="general"}{config_load file=$language_file section="upload_image"}<!DOCTYPE html>
<html lang="{#language#}">
 <head>
  <meta charset="{#charset#}" />
  <title>{$settings.forum_name}{if $page_title} - {$page_title}{elseif $subnav_location} - {$subnav_location}{/if}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style type="text/css">
{literal}
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
  padding: 0 0 0 1.75em;
  background-repeat:no-repeat;
  background-position: 0 .025em;
  background-size: 1.45em;
}
.caution h2,
.ok h2 {
  font-size: 1em;
  margin: 0 0 0.5em 0;
}
.caution {
  color: #cc0000;
  background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/general-caution.svg);
}
.ok {
  color:green;
  background-image:url({/literal}{$THEMES_DIR}/{$settings.theme}{literal}/images/general-information.svg);
}
.insert-desc,
.deletelink,
.small {
  font-size: 0.82em;
}
a:has(img.icon) {
  display: flex;
  align-items: center;
  gap: 0.125em;
}
code {
  font-family:"courier new", courier, monospace;
  color:#000080;
}
.insert-desc > * {
  margin-block: 0 0.25em;
}
.insert-desc > *:last-child {
  margin-block: 0;
}
a {
  color:#00c;
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
  min-width: 20em;
  max-width: 40em;
  margin-inline: auto;
}
#del-upload-form {
  text-align:center;
}
#imgtab li {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
  align-items: center;
  width: 100%;
}
li > *:last-child {
  align-content:center;
}
img:not(.buttonbar img) {
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
.buttonbar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3em;
  align-items: center;
}
.buttonbar > * {
  margin-block: 0;
}
.buttonbar img:not([hidden]) {
  display: block;
  width: 1.25em;
  height: 1.25em;
}
.buttonbar button {
  padding: 0.3em;
}
button {
  cursor: pointer;
}
#imgtab li button:has(> img) {
  background: transparent;
  border: none;
  padding: 0;
}
{/literal}
  </style>
  <script>{literal}
/**
 * function for inserting uploaded images into
 * a posting from the uploaded images gallery
 */
function insertCode() {
  const clickedButton = event.target.closest('button');
  if (clickedButton === null)
    return false;
  const imagePath = clickedButton.querySelector('img').getAttribute('src');
  if (opener && opener.mlfBBCodeButton) {
    const bbcodeButton = opener.mlfBBCodeButton;
    if (!bbcodeButton.canInsert())
      return false;
    const buttonGroup = bbcodeButton.getButtonGroup();
    const txtarea = buttonGroup.getTextArea();
    txtarea.insertTextRange( txtarea.getSelection() + "[img]" + imagePath + "[/img]" );
  }
  //self.close();
}
window.addEventListener('DOMContentLoaded', function() {
  if (document.querySelector('#imgtab button')) {
    document.querySelector('#imgtab').addEventListener('click', insertCode);
  }
  if (document.querySelector('button[name="upload_img"]')) {
    document.querySelector('button[name="upload_img"]').addEventListener('click', function() {
      const throbber = document.getElementById('throbber-submit');
      throbber.removeAttribute('hidden');
    });
  }
  if (document.querySelector('div.insert-desc')) {
    const descriptors = document.querySelectorAll('div.insert-desc');
    descriptors.forEach(function (descriptor) {
      const description = document.createElement('p');
      description.textContent = '{/literal}{#insert_image_exp#|escape:quotes}{literal}';
      descriptor.replaceChildren(description);
    });
  }
});
{/literal}</script>
 </head>
 <body>
{if $form}
 <header>
  <h1>{#upload_image_hl#}</h1>
 </header>
 <main>
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
  <form id="upload-form" action="index.php" method="post" enctype="multipart/form-data" accept-charset="{#charset#}">
   <input type="hidden" name="mode" value="upload_image" />
   <div>
    <input type="file" name="probe" size="17" />
   </div>
   <div class="buttonbar">
    <button name="upload_img" value="{#upload_image_button#}">{#upload_image_button#}</button>
    <img id="throbber-submit" src="{$THEMES_DIR}/{$theme}/images/throbber.svg" alt="" width="18" height="18" hidden />
   </div>
  </form>
  <p class="small"><a href="index.php?mode=upload_image&amp;browse_images=1">{#browse_uploaded_images#}</a></p>
 </main>
{elseif $uploaded_file}
 <header>
  <h1>{#upload_image_hl#}</h1>
 </header>
 <main>
  <div class="ok">
   <h2>{#upload_successful#}</h2>
  </div>
  <ul id="imgtab" class="shrinked">
   <li>
    <div>
     <button type="button">
      <img src="images/uploaded/{$uploaded_file}" title="{#insert_image#}" alt="{#insert_image#}" />
     </button>
    </div>
    <div class="insert-desc">
     <p>{#insert_image_exp_no_js#}</p>
     <p><code>[img]images/uploaded/{$uploaded_file}[/img]</code></p>
    </div>
   </li>
  </ul>
{if $image_downsized}  <p class="small">{$smarty.config.image_downsized|replace:"[width]":$new_width|replace:"[height]":$new_height|replace:"[filesize]":$new_filesize}</p>{/if}
  <p class="small"><a href="index.php?mode=upload_image&amp;browse_images=1">{#browse_uploaded_images#}</a></p>
 </main>
{elseif $browse_images}
 <header>
  <div id="nav-1"><a href="index.php?mode=upload_image">{#back#}</a></div>
  <div id="nav-2">{if $previous}[ <a href="index.php?mode=upload_image&amp;browse_images={$previous}" title="{#previous_page_link_title#}">&laquo;</a> ]{/if}{if $previous && next} {/if}{if $next}[ <a href="index.php?mode=upload_image&amp;browse_images={$next}" title="{#next_page_link_title#}">&raquo;</a> ]{/if}</div>
 </header>
{if $images}
 <main>
  <ul id="imgtab">
{section name=nr loop=$images start=$start max=$images_per_page}
   <li>
    <div>
     <button type="button">
      <img src="images/uploaded/{$images[nr]}" title="{#insert_image#}" alt="{#insert_image#}" />
     </button>
    </div>
    <div class="insert-desc">
     <p>{#insert_image_exp_no_js#}</p>
     <p><code>[img]images/uploaded/{$images[nr]}[/img]</code></p>
    </div>
{if $admin || $mod}    <div><a class="deletelink" href="index.php?mode=upload_image&amp;delete={$images[nr]}&amp;current={$current}"><img class="icon" src="{$THEMES_DIR}/{$settings.theme}/images/delete-cross.svg" alt="" width="12" height="12" /><span>{#delete#}</span></a></div>
{/if}
   </li>
{/section}
  </ul>
 </main>
{else}
 <main>
  <p>{#no_images#}</p>
 </main>
{/if}
{elseif $delete_confirm}
 <header>
  <div id="nav-1"><a href="index.php?mode=upload_image&amp;browse_images={$current|default:'1'}">{#back#}</a></div>
 </header>
 <main>
  <div class="caution">
   <h2>{#delete_image_confirm#}</h2>
  </div>
  <ul id="imgtab" class="shrinked">
   <li><img src="images/uploaded/{$delete}" alt="{$delete}" /></li>
  </ul>
  <form id="del-upload-form" action="index.php" method="post" accept-charset="{#charset#}">
   <input type="hidden" name="mode" value="upload_image" />
   <input type="hidden" name="delete" value="{$delete}" />
{if $current}   <input type="hidden" name="current" value="{$current}" />{/if}
   <div>
    <button name="delete_confirm" value="{#delete_image_button#}">{#delete_image_button#}</button>
   </div>
  </form>
 </main>
{else}
 <main>
  <div class="caution">
   <h2>{#image_upload_not_enabled#}</h2>
  </div>
 </main>
{/if}
</body>
</html>
