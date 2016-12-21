<?php
//error_reporting ( E_ALL );
//ini_set ( 'display_errors', 1 );
include_once './includes/BitbucketToDiscordSlackStyleWebhookConverter.php';
include_once './includes/WebhookConnector.php';
$content = null;
$all_headers = getallheaders ();
$event_type = 'unknown';
if (isset ( $all_headers ['X-Event-Key'] ))
	$event_type = $all_headers ['X-Event-Key'];
$result = "{\n}";
$content = file_get_contents ( 'php://input' );
$webhook_id = null;
$webhook_token = null;
if (isset ( $_GET ['id'] ))
	$webhook_id = $_GET ['id'];
if (isset ( $_GET ['token'] ))
	$webhook_token = $_GET ['token'];
if (($content !== null) && ($webhook_id !== null) && ($webhook_token !== null)) {
	$content_obj = json_decode ( $content );
	$btdsswc = new BitbucketToDiscordSlackStyleWebhookConverter ( $event_type );
	$result = json_encode ( $btdsswc->convert ( $content_obj ) );
	// $result = WebhookConnector::send ( 'https://discordapp.com/api/webhooks/' . $webhook_id . '/' . $webhook_token . '/slack', $btdsswc->convert ( $content_obj ) );
	unset ( $content_obj );
}
echo $result;
unset ( $content );
unset ( $result );
?>