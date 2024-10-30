<?php
/*
Plugin Name: Mail Shrimp
Plugin URI: http://www.lifesgreat.fr/
Description: Send your MailChimp campaigns from your Wordpress
Version: 1.0
Author: Benjamin Netter
Author URI: http://www.twitter.com/benjaminnetter
*/

add_action('admin_menu', 'load_mailshrimp');
add_action('wp_ajax_save_campaign', 'mailshrimp_save_campaign');
add_action('wp_ajax_send_test', 'mailshrimp_send_test');
add_action('wp_ajax_send_campaign', 'mailshrimp_send_campaign');

function load_mailshrimp() {
	// Get path
	$icon_path = plugins_url('mailshrimp/images/icon.png');

	// Adding MailShrimp to Wordpress Admin
	add_utility_page('Mail Shrimp', 'Mail Shrimp', 'manage_options', 'mail-shrimp', 'mailshrimp_index', $icon_path);
	$campaign_id = get_option('mailchimp-current-campaign');
	if (!$campaign_id)
		add_submenu_page('mail-shrimp', 'New campaign — Mail Shrimp', 'New campaign', 'manage_options', 'mail-shrimp-new-campaign', 'mailshrimp_newcampaign');
	else
		add_submenu_page('mail-shrimp', 'Campaign #' . $campaign_id . ' — Mail Shrimp', 'Edit campaign', 'manage_options', 'mail-shrimp-new-campaign', 'mailshrimp_newcampaign');
	add_submenu_page('mail-shrimp', 'Settings — Mail Shrimp', 'Settings', 'manage_options', 'mail-shrimp-settings', 'mailshrimp_settings');
}

function mailshrimp_index($new_campaign = false) {
	$api_key = mailshrimp_header();

	if (!$api_key || $api_key == "")
		include('html/no-api-key.php');
	else {
		require_once('MCAPI.class.php');
		$sdk = new MCAPI($api_key);
		if ($sdk->ping() == false)
			include('html/bad-api-key.php');
		else {
			if ($new_campaign) {
				if (isset($_GET['campaign_id'])) {
					update_option('mailchimp-current-campaign', $_GET['campaign_id']);
					update_option('mailchimp-current-template', true);
				}
				if (isset($_GET['draft']))
					mailshrimp_reset_campaign();
				$campaign_id = get_option('mailchimp-current-campaign');
				if (!$campaign_id && isset($_GET['list_id'])) {
					$campaign_id = create_default_campaign($sdk, $_GET['list_id']);
					update_option('mailchimp-current-campaign', $campaign_id);
				}
				if ($campaign_id) {
					if (isset($_GET['template_id'])) {
						update_option('mailchimp-current-template', $_GET['template_id']);
						$template = $sdk->templateInfo($_GET['template_id']);
						$sdk->campaignUpdate($campaign_id, 'content', array('html' => $template['source']));
					}
					$campaign = $sdk->campaigns(array('campaign_id' => $campaign_id));
					if ($campaign) {
						$list = $sdk->lists(array('list_id' => $campaign['data'][0]['list_id']));
						$template_option = get_option('mailchimp-current-template');
						if (!isset($template) && $template_option) {
							$template = true;
						}
					} else {
						$campaign_id = mailshrimp_reset_campaign();
					}
				}
				include('html/new-campaign.php');
			}
			else
				include('html/show-campaigns.php');
		}
	}
	mailshrimp_footer();
}

function mailshrimp_reset_campaign() {
	delete_option('mailchimp-current-campaign');
	delete_option('mailchimp-current-template');
	return false;
}

function mailshrimp_newcampaign() {
	mailshrimp_index(true);
}

function mailshrimp_settings() {
	$api_key = mailshrimp_header();
	if (isset($_POST['google_analytics']))
		update_option('mailshrimp_google_analytics', $_POST['google_analytics']);
	$google_analytics = get_option('mailshrimp_google_analytics');
	if (!$google_analytics || empty($google_analytics))
		$google_analytics = 'off';
	include('html/settings.php');
	mailshrimp_footer();
}

function mailshrimp_header() {
	mailshrimp_security_check();
	load_mailshrimp_css();
	include('html/header.php');

	if (isset($_POST['api-key'])) {
		update_option('mailchimp-api-key', $_POST['api-key']);
		include('html/new-api-key.php');
	}
	return get_option('mailchimp-api-key');
}

function mailshrimp_footer() {
	include('html/footer.php');
}

function mailshrimp_security_check() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
}

function mailshrimp_save_campaign() {
	require_once('MCAPI.class.php');
	$api_key = get_option('mailchimp-api-key');
	$campaign_id = get_option('mailchimp-current-campaign');
	$json = array();
	if ($api_key) {
		$json['code'] = 200;
		$sdk = new MCAPI($api_key);
		$fields = array('subject', 'from_email', 'from_name', 'title');
		foreach ($fields as $field) {
			$valid = $sdk->campaignUpdate($campaign_id, $field, $_POST[$field]);
			if (!$valid)
				break;
		}
		if ($valid)
			$sdk->campaignUpdate($campaign_id, 'content', array('html' => stripslashes($_POST['content'])));
		if (!$valid){
			$json['code'] = 100;
			$json['error_message'] = $sdk->errorMessage;
		} else {
			$json['code'] = 200;
			$json['campaign_id'] = $campaign_id;
		}
	} else {
		$json['code'] = 100;
		$json['error_message'] = "Your API key is not valid";
	}
	echo json_encode($json);
	die();
}

function mailshrimp_send_campaign() {
	require_once('MCAPI.class.php');
	$api_key = get_option('mailchimp-api-key');
	$campaign_id = get_option('mailchimp-current-campaign');
	$json = array();
	if ($api_key) {
		$json['code'] = 200;
		$sdk = new MCAPI($api_key);
		if ($_POST['when'] == 'now') {
			$code = $sdk->campaignSendNow($campaign_id);
		} else {
			$code = $sdk->campaignSchedule($campaign_id, $_POST['schedule']);
		}
		if (!$code){
			$json['code'] = 100;
			$json['error_message'] = $sdk->errorMessage;
		} else {
			$json['code'] = 200;
			$json['campaign_id'] = $campaign_id;
		}
	} else {
		$json['code'] = 100;
		$json['error_message'] = "Your API key is not valid";
	}
	echo json_encode($json);
	die();
}

function mailshrimp_send_test() {
	require_once('MCAPI.class.php');
	$api_key = get_option('mailchimp-api-key');
	$campaign_id = get_option('mailchimp-current-campaign');
	$json = array();
	if ($api_key) {
		$json['code'] = 200;
		$sdk = new MCAPI($api_key);
		$code = $sdk->campaignSendTest($campaign_id, array($_POST['email']), 'html');
		if (!$code){
			$json['code'] = 100;
			$json['error_message'] = $sdk->errorMessage;
		} else {
			$json['code'] = 200;
			$json['campaign_id'] = $campaign_id;
		}
	} else {
		$json['code'] = 100;
		$json['error_message'] = "Your API key is not valid";
	}
	echo json_encode($json);
	die();
}

function create_default_campaign(&$sdk, $list_id) {
	$list = $sdk->lists(array('list_id' => $list_id));
	$opts['list_id'] = $list_id;
	$opts['subject'] = "No subject";
	$opts['from_email'] = $list['data'][0]['default_from_email'];
	$opts['from_name'] = $list['data'][0]['default_from_name'];
	$google_key = get_option('mailshrimp_google_analytics');
	if (isset($google_key) && $google_key == "on")
		$opts['analytics'] = array('google'=>$google_key);
	$campaign_id = $sdk->campaignCreate('regular', $opts, "");

	return $campaign_id;
}

function load_mailshrimp_css() {
	// Path
	$style_path = plugins_url('mailshrimp/style.css');

	// Loading the CSS stylesheet
	wp_register_style('css-stylesheet', $style_path);
    wp_enqueue_style('css-stylesheet');
}