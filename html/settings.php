<form method="post" id="settings" class="information">
	<label>
		MailChimp API key <small><a href="https://us1.admin.mailchimp.com/account/api/" target="_blank">get it here</a></small>
		<input type="text" class="classic" name="api-key" value="<?= $api_key ?>" />
	</label>
	<div style="clear:both">&nbsp;</div>
	<label>
		Google Analytics
		<?php if ($google_analytics == "on") : ?>
		<small id="analytics">ON <a onclick="setAnalytics('off')">OFF</a></small>
		<?php else : ?>
		<small id="analytics"><a onclick="setAnalytics('on')">ON</a> OFF</small>
		<?php endif; ?>
		<input type="hidden" class="classic" name="google_analytics" id="google_analytics" value="<?= $google_analytics ?>" />
	</label>
	<div class="right">
		<input type="submit" value="Update" class="button-secondary action big" />
	</div>
</form>
<script>
function setAnalytics(status) {
	var text = [];
	text['on'] = 'ON <a onclick="setAnalytics(\'off\')">OFF</a>';
	text['off'] = '<a onclick="setAnalytics(\'on\')">ON</a> OFF';

	jQuery('#analytics').html(text[status]);
	jQuery('#google_analytics').val(status);
}
</script>