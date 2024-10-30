<div id="start-again">
	<?php if ($campaign_id) : ?>
	<a href="admin.php?page=mail-shrimp-new-campaign&draft">draft this</a>
	<?php else : ?>
	&nbsp;
	<?php endif; ?>
</div>
<div id="status" class="information"></div>
<div id="test-mail" class="information">
	<p>
		<strong>We need an e-mail address for the test.</strong><br />Please provide a valid e-mail address where we will send you a test for this campaign.
	</p>
	<div class="center">
	<form method="post">
		<input type="text" class="classic" id="test-mail-address" />
		<a class="button-secondary action big" onclick="sendTestMail()">Send the test</a>
	</form>
	</div>
</div>
<div id="send-mail" class="information">
	<p>
		<strong>You're about to send this campaign to <?= $list['data'][0]['stats']['member_count'] ?> subscribers.</strong><br />Do you want to send this campaign right now or set the right moment to send it?
	</p>
	<div class="center">
		<form method="post" id="schedule">
			<input type="text" class="classic" id="schedule-time" value="YYYY-MM-DD HH:II:SS" />
			<a class="button-secondary action big" onclick="sendMail('later')">Schedule this</a>
		</form>
		<div id="send-options">
			<a class="button-secondary action big" onclick="sendMail('now')">Send now</a>
			<a class="button-secondary action big" onclick="jQuery('#send-options').fadeOut(function(){jQuery('#schedule').fadeIn()})">Send later</a>
		</div>
	</div>
</div>
<?php if (!$campaign_id) : ?>
<?php $lists = $sdk->lists(); ?>
<h2>
	First step
	<small>Select a list</small>
</h2>
<ul class="blocks">
<?php foreach ($lists['data'] as $list) : ?>
	<li>
		<a href="admin.php?page=mail-shrimp-new-campaign&list_id=<?= $list['id'] ?>"><?= $list['name'] ?></a>
		<small><?= $list['stats']['member_count'] ?> members</small>
	</li>
<?php endforeach; ?>
</ul>
<?php elseif (!isset($template) || $template == false) : ?>
<?php $templates = $sdk->templates() ?>
<h2>
	Second step
	<small>Select a template</small>
	<small style="float:right;">You are currently editing campaign <strong id="campaign-id">#<?= $campaign['data'][0]['id'] ?></strong></small>
</h2>
<ul class="blocks">
<?php foreach ($templates['user'] as $template) : ?>
	<li>
		<a href="admin.php?page=mail-shrimp-new-campaign&template_id=<?= $template['id'] ?>"><?= $template['name'] ?></a>
		<small><?= $template['layout'] ?></small>
	</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<h2>
	Third step
	<small>Write the e-mail</small>
	<small style="float:right;">You are currently editing campaign <strong id="campaign-id">#<?= $campaign['data'][0]['id'] ?></strong></small>
</h2>
<div id="editor">
	<table width="100%" id="editor-table">
		<tr>
			<td width="50%">
				<label>
					Campaign name
					<input type="text" class="classic" name="title" id="title" value="<?= $campaign['data'][0]['title'] ?>" />
				</label>
			</td>
			<td width="50%">
				<label>
					From (e-mail address)
					<input type="text" class="classic" name="from_email" id="from_email" value="<?= $campaign['data'][0]['from_email'] ?>" />
				</label>
			</td>
		</tr>
		<tr>
			<td width="50%">
				<label>
					Email subject
					<input type="text" class="classic" name="subject" id="subject" value="<?= $campaign['data'][0]['subject'] ?>" />
				</label>
			</td>
			<td width="50%">
				<label>
					From (name)
					<input type="text" class="classic" name="from_name" id="from_name" value="<?= $campaign['data'][0]['from_name'] ?>" />
				</label>
			</td>
		</tr>
	</table>
	<?php $content = $sdk->campaignContent($campaign_id, false); ?>
	<?php wp_editor($content['html'], 'preview'); ?>
</div>
<div id="actions">
	<img src="<?= plugins_url('mailshrimp/images/ajax-loader.gif') ?>" class="loader" />
	<a class="button-secondary action big" onclick="saveCampaign()">Save</a>
	<a class="button-secondary action big" onclick="sendTest()">Send a test</a>
	<a class="button-secondary action big" onclick="showOptions()">Send to <?= $list['data'][0]['stats']['member_count'] ?> subscribers</a>
</div>
<script>
function sendTest() {
	saveCampaign(function(response) {
		jQuery('#test-mail-address').val(jQuery('#from_email').val());
		jQuery('#test-mail').fadeIn();
	});
}

function showOptions() {
	saveCampaign(function(response) {
		jQuery('#send-mail').fadeIn();
	});
}

function sendTestMail() {
	var data = {
		action: 'send_test',
		email: jQuery('#test-mail-address').val()
	};
	jQuery('.loader').show();
	jQuery.post(ajaxurl, data, function(response) {
		jQuery('.loader').hide();
		response = jQuery.parseJSON(response);
		if (response.code != 200) {
			alert("Error " + response.code + " : " + response.error_message);
		} else {
			jQuery('#status').text('Campaign #' + response.campaign_id + ' sent to your e-mail address').fadeIn().delay(2000).fadeOut();
			jQuery('#test-mail').fadeOut();
		}
	});
}

function sendMail(when) {
	var data = {
		action: 'send_campaign',
		when: when,
		schedule: jQuery('#schedule-time').val()
	};
	jQuery('.loader').show();
	jQuery.post(ajaxurl, data, function(response) {
		jQuery('.loader').hide();
		response = jQuery.parseJSON(response);
		if (response.code != 200) {
			alert("Error " + response.code + " : " + response.error_message);
		} else {
			if (when == "now")
				jQuery('#status').text('Campaign #' + response.campaign_id + ' has just been sent ! Thank you for using Mail Schrimp.').fadeIn().delay(2000).fadeOut();
			else
				jQuery('#status').text('Campaign #' + response.campaign_id + ' has just been scheduled ! Thank you for using Mail Schrimp.').fadeIn().delay(2000).fadeOut();
			jQuery('#send-mail').fadeOut();
		}
	});
}

function saveCampaign(callback) {
	tinyMCE.triggerSave();
	var data = {
		action: 'save_campaign',
		from_email: jQuery('#from_email').val(),
		from_name: jQuery('#from_name').val(),
		subject: jQuery('#subject').val(),
		content: jQuery('#preview').val(),
		title: jQuery('#title').val()
	};
	jQuery('.loader').show();
	jQuery.post(ajaxurl, data, function(response) {
		jQuery('.loader').hide();
		response = jQuery.parseJSON(response);
		if (response.code != 200) {
			alert("Error " + response.code + " : " + response.error_message);
		} else {
			jQuery('#status').text('Campaign #' + response.campaign_id + ' has been saved').fadeIn().delay(2000).fadeOut();
			if (typeof callback != "undefined") {
    			callback(response);
  			}
		}
	});
}
</script>
<?php endif; ?>