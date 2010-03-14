{config_load file=$language_file section="register"}
{if $captcha}{config_load file=$language_file section="captcha"}{/if}
<p class="normal">{#register_exp#}</p>
{if $errors}
<p class="caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[characters]":$settings.min_pw_length}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="register" />
{if $captcha}<input type="hidden" name="{$captcha.session_name}" value="{$captcha.session_id}" />{/if}
<p><label for="new_user_name" class="main">{#register_username#}</label><br />
<input id="new_user_name" class="login" type="text" size="30" name="new_user_name" value="{$new_user_name|default:''}" maxlength="{$settings.username_maxlength}" /></p>
<p><label for="new_user_email" class="main">{#register_user_email#}</label><br />
<input id="new_user_email" class="login" type="text" size="30" name="new_user_email" value="{$new_user_email|default:''}" maxlength="{$settings.email_maxlength}" /></p>
<p><label for="reg_pw" class="main">{#register_pw#}</label><br />
<input id="reg_pw" class="login" type="password" size="30" name="reg_pw" maxlength="255" /></p>
<p><label for="reg_pw_conf" class="main">{#register_pw_conf#}</label><br />
<input id="reg_pw_conf" class="login" type="password" size="30" name="reg_pw_conf" maxlength="255" /></p>
{if $terms_of_use_agreement}
{assign var=terms_of_use_url value=$settings.terms_of_use_url}
<p><input id="terms_of_use_agree" type="checkbox" name="terms_of_use_agree" value="1"{if $terms_of_use_agree && $terms_of_use_agree==1} checked="checked"{/if} />&nbsp;<label for="terms_of_use_agree">{if $terms_of_use_url}{#terms_of_use_agreement#|replace:"[[":"<a id=\"terms_of_use\" href=\"$terms_of_use_url\">"|replace:"]]":"</a>"}{else}{#terms_of_use_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label></p>
{/if}
{if $captcha}
{if $captcha.type==2}
<p><strong>{#captcha_marking#}</strong><br />
<img class="captcha" src="modules/captcha/captcha_image.php?{$captcha.session_name}={$captcha.session_id}" alt="{#captcha_image_alt_reg#}" width="180" height="40" /><br />
<label for="captcha_code">{#captcha_expl_image#}</label><br />
<input id="captcha_code" type="text" name="captcha_code" value="" size="10" /></p>
{else}
<p><strong>{#captcha_marking#}</strong><br />
<label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label><input id="captcha_code" type="text" name="captcha_code" value="" size="5" /></p>
{/if}
{/if}
<p><input type="submit" name="register_submit" value="{#submit_button_ok#}" /></p>
</div>
</form>
