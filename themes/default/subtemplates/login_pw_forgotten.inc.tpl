{config_load file=$language_file section="pw_forgotten"}
<p>{#pw_forgotten_exp#}</p>
<form action="index.php" method="post" accept-charset="{#charset#}">
<div>
<input type="hidden" name="mode" value="login" />
<p><label for="pwf_email" class="main">{#pwf_email#}</label><br />
<input id="pwf_email" type="text" name="pwf_email" size="30" /> <input type="submit" name="pwf_submit" value="{#submit_button_ok#}" /></p>
</div>
</form>
