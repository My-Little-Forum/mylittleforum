{if $show_dps_page}
{assign var="sort_agreement" value="dps_agreement"}
{assign var="agreement_url" value=$settings.data_privacy_statement_url}
{assign var="agreement_description" value={#data_priv_statement_error_reconf#}}
{elseif $show_tou_page}
{assign var="sort_agreement" value="tou_agreement"}
{assign var="agreement_url" value=$settings.terms_of_use_url}
{assign var="agreement_description" value={#terms_of_use_error_reconf#}}
{else}
{assign var="sort_agreement" value=""}
{assign var="agreement_url" value=""}
{assign var="agreement_description" value=""}
{/if}
<form method="post">
<input type="hidden" name="sort_of_agreement" value="{$sort_agreement}" />
<iframe src="{$agreement_url}" width="100%" height="400"></iframe>
<p>{$agreement_description}</p>
<p><input type="submit" name="agreed" value="{#submit_button_agreed#}" /> <input type="submit" name="disagreed" value="{#submit_button_disagreed#}" /></p>
</form>