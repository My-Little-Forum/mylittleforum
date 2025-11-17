{config_load file=$language_file section="user_show"}
{if $user_name}
<table class="normaltab wide descript">
<tr>
<td><strong>{#user_name#}</strong></td>
<td><strong>{$user_name}</strong>{if $gender==1} <img src="{$THEMES_DIR}/{$theme}/images/gender-male.svg" alt="{#male#}" width="16" height="16" />{elseif $gender==2} <img src="{$THEMES_DIR}/{$theme}/images/gender-female.svg" alt="{#female#}" width="16" height="16" />{/if} <span class="xsmall">{if $p_user_type==2}({#admin#}){elseif $p_user_type==1}({#mod#}){else}<!--({#user#})-->{/if}</span>{if $user_is_locked} <span class="small user-locked">({#user_locked#})</span>{/if}</td>
</tr>
{if $avatar}
<tr>
<td><strong>{#user_avatar#}</strong></td>
<td><img src="{$avatar.image}" alt="{#avatar_img_alt#}" width="{$avatar.width}" height="{$avatar.height}" /></td>
</tr>
{/if}
{if $user_hp || $user_email}
<tr>
<td><strong>{#user_hp_email#}</strong></td>
<td>{if $user_hp=='' && $user_email==''}-{/if}{if $user_hp!=''}<a href="{$user_hp}" title="{$user_hp}"><img class="sa-icon" src="{$THEMES_DIR}/{$theme}/images/general-homepage.svg" alt="{#homepage#}" width="13" height="13" /></a> &nbsp;{/if}{if $user_email}<a href="index.php?mode=contact&amp;recipient_user_id={$p_user_id}" title="{#mailto_user#|replace:"[user]":$user_name}" rel="nofollow"><img class="sa-icon" src="{$THEMES_DIR}/{$theme}/images/e-mail-envelope.svg" alt="{#email#}" width="13" height="13" /></a>{/if}</td>
</tr>
{/if}
{if $user_real_name}
<tr>
<td><strong>{#user_real_name#}</strong></td>
<td>{$user_real_name|default:'-'}</td>
</tr>
{/if}
{if $birthdate}
<tr>
<td><strong>{#age_birthday#}</strong></td>
<td>{$years} / {$birthdate.year}-{$birthdate.month}-{$birthdate.day}</td>
</tr>
{/if}
{if $user_location}
<tr>
<td><strong>{#user_location#}</strong></td>
<td>{$user_location|default:'-'}</td>
</tr>
{/if}
<tr>
<td><strong>{#user_registered#}</strong></td>
<td>{$user_registered}</td>
</tr>
{if $user_last_login}
<tr>
<td><strong>{#user_last_login#}</strong></td>
<td>{$user_last_login|default:'-'}</td>
</tr>
{/if}
<tr>
<td><strong>{#user_logins#}</strong></td>
<td>{$logins}</td>
</tr>
<tr>
<td><strong>{#logins_per_day#}</strong></td>
<td>{$logins_per_day}</td>
</tr>
<tr>
<td><strong>{#user_postings#}</strong></td>
<td>{$postings} ({$postings_percent}%){if $postings>0} &nbsp;<span class="small">[ <a href="index.php?mode=user&amp;action=show_posts&amp;id={$p_user_id}">{#show_postings_link#}</a> ]</span>{/if}</td>
</tr>
<tr>
<td><strong>{#postings_per_day#}</strong></td>
<td>{$postings_per_day}</td>
</tr>
{if $last_posting_subject}
<tr>
<td><strong>{#last_posting#}</strong></td>
<td>{if $last_posting_subject}{$last_posting_formated_time}: <a id="user-last-posting" href="index.php?mode=entry&amp;id={$last_posting_id}">{$last_posting_subject}</a>{else}-{/if}</td>
</tr>
{/if}
{if $profile}
<tr>
<td><strong>{#user_profile#}</strong></td>
<td>{$profile|default:'<p>-</p>'}</td>
</tr>
{/if}
</table>

{if $mod||$admin}
<ul class="adminmenu">
{if $admin}{if $postings}<li><a href="index.php?mode=admin&amp;user_delete_all_entries={$p_user_id}"><img src="{$THEMES_DIR}/{$theme}/images/postings-delete.svg" alt="" width="16" height="16" /><span>{#user_delete_all_entries#}</span></a></li>{/if}{/if}
{if $p_user_type==0}<li><a href="index.php?mode=user&amp;user_lock={$p_user_id}">{if $user_is_locked}<img class="icon" src="{$THEMES_DIR}/{$theme}/images/user-unlock.svg" alt="" width="18" height="18" /><span>{#user_unlock_account#}</span></a>{else}<img class="icon" src="{$THEMES_DIR}/{$theme}/images/user-lock.svg" alt="" width="18" height="18" /><span>{#user_lock_account#}</span></a>{/if}</li>{/if}
{if $admin}<li><a href="index.php?mode=admin&amp;edit_user={$p_user_id}"><img class="icon" src="{$THEMES_DIR}/{$theme}/images/user-edit.svg" alt="" width="18" height="18" /><span>{#user_edit_account#}</span></a></li>{/if}
{if $admin}<li><a href="index.php?mode=admin&amp;delete_user={$p_user_id}"><img class="icon" src="{$THEMES_DIR}/{$theme}/images/user-delete.svg" alt="" width="18" height="18" /><span>{#user_delete_account#}</span></a></li>{/if}
</ul>
{/if}

{else}
<p class="notice caution">{#user_account_doesnt_exist#}</p>
{/if}
