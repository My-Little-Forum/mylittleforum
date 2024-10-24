{config_load file=$language_file section="register"}
{if $captcha}{config_load file=$language_file section="captcha"}{/if}
<p class="normal">{#register_exp#}</p>
{if $errors}
<p class="notice caution">{#error_headline#}</p>
<ul>
{section name=mysec loop=$errors}
{assign var="error" value=$errors[mysec]}
<li>{$smarty.config.$error|replace:"[characters]":$settings.min_pw_length|replace:"[digits]":$settings.min_pw_digits|replace:"[lowercase_letters]":$settings.min_pw_lowercase_letters|replace:"[uppercase_letters]":$settings.min_pw_uppercase_letters|replace:"[special_characters]":$settings.min_pw_special_characters}</li>
{/section}
</ul>
{/if}
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="register" />
<input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}" />
{if $captcha}<input type="hidden" name="{$captcha.session_name}" value="{$captcha.session_id}" />{/if}

 <div>
  <label for="new_user_name" class="main">{#register_username#}</label>
  <input id="new_user_name" class="login" type="text" size="30" name="{$fld_user_name}" value="{$new_user_name|default:''}" maxlength="{$settings.username_maxlength}" autofocus required />
 </div>

 <div>
  <label for="reg_pw" class="main">{#register_pw#}</label>
  <input id="reg_pw" class="login" type="password" spellcheck="false" autocomplete="off" writingsuggestions="false" size="30" name="{$fld_pword}" maxlength="255" required />
 </div>

 <div class="hp">
  <label for="phone" class="main">{#register_honeypot_field#}</label>
  <input id="phone" class="login" type="tel" size="30" name="{$fld_phone}" value="{$honey_pot_phone|default:''}" maxlength="35" tabindex="-1" />
 </div>

 <div>
  <label for="new_user_email" class="main">{#register_user_email#}</label>
  <input id="new_user_email" class="login" type="email" size="30" name="{$fld_user_email}" value="{$new_user_email|default:''}" maxlength="{$settings.email_maxlength}" required />
 </div>

 <div class="hp">
  <label for="repeat_email" class="main">{#register_honeypot_field#}</label>
  <input id="repeat_email" class="login" type="email" size="30" name="{$fld_repeat_email}" value="{$honey_pot_email|default:''}" maxlength="{$settings.email_maxlength}" tabindex="-1" />
 </div>

{if $terms_of_use_agreement}
{assign var=terms_of_use_url value=$settings.terms_of_use_url}
 <div>
  <input id="terms_of_use_agree" type="checkbox" name="terms_of_use_agree" value="1"{if $terms_of_use_agree && $terms_of_use_agree==1} checked="checked"{/if} />&nbsp;<label for="terms_of_use_agree">{if $terms_of_use_url}{#terms_of_use_agreement#|replace:"[[":"<a id=\"terms_of_use\" href=\"$terms_of_use_url\">"|replace:"]]":"</a>"}{else}{#terms_of_use_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label>
 </div>
{/if}
{if $data_privacy_agreement}
{assign var=data_privacy_statement_url value=$settings.data_privacy_statement_url}
 <div>
  <input id="data_privacy_statement_agree" type="checkbox" name="data_privacy_statement_agree" value="1"{if $data_privacy_statement_agree && $data_privacy_statement_agree==1} checked="checked"{/if} />&nbsp;<label for="data_privacy_statement_agree">{if $data_privacy_statement_url}{#data_privacy_agreement#|replace:"[[":"<a id=\"data_priv_declaration\" href=\"$data_privacy_statement_url\">"|replace:"]]":"</a>"}{else}{#data_privacy_agreement#|replace:"[[":""|replace:"]]":""}{/if}</label>
 </div>
{/if}
{if $captcha}
{if $captcha.type==2}
 <div>
  <strong class="label-like">{#captcha_marking#}</span><br />
  <img class="captcha" src="modules/captcha/captcha_image.php?{$captcha.session_name}={$captcha.session_id}" alt="{#captcha_image_alt_reg#}" width="180" height="40" /><br />
  <label for="captcha_code">{#captcha_expl_image#}</label>
  <input id="captcha_code" type="text" name="captcha_code" value="" size="10" required />
 </div>
{else}
 <div>
  <strong>{#captcha_marking#}</strong><br />
  <label for="captcha_code">{#captcha_expl_math#} {$captcha.number_1} + {$captcha.number_2} = </label>
  <input id="captcha_code" type="text" name="captcha_code" value="" size="5" required />
 </div>
{/if}
{/if}
<p><input type="submit" name="register_submit" value="{#submit_button_ok#}" tabindex="8" /></p>
</div>
</form>
