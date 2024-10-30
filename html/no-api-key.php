<div class="information">
	<p>
		<strong>We currently don't have access to your MailChimp account.</strong><br />Please provide your API key,
		<a href="https://us1.admin.mailchimp.com/account/api/" target="_blank">click here to obtain it</a>.
	</p>
	<div class="center">
	<form method="post">
		<input type="text" class="classic" name="api-key" value="<?= (isset($api_key))?($api_key):('') ?>" />
		<input type="submit" value="Update" class="button-secondary action big" />
	</form>
	</div>
</div>