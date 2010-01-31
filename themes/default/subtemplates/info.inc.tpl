{if $lang_section}{config_load file=$language_file section=$lang_section}{/if}
{if $custom_message}
<p>{$custom_message}</p>
{elseif $message}
<p>{$smarty.config.$message|replace:"[var]":$var}</p>
{/if}
