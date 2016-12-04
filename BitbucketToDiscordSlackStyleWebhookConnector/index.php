<?php
include_once './includes/BitbucketToDiscordSlackStyleWebhookConverter.php';
function toClassName($str) {
	return ucfirst ( strtolower ( trim ( $str ) ) );
}

$content = null;
$result = '{\n}';
if (isset ( $_POST ['Content-Type'] )) {
	if ($_POST ['Content-Type'] == 'application/json')
		$content = stream_get_contents ( STDIN );
} else if (isset ( $_POST ['payload'] ))
	$content = $_POST ['payload'];
if ($content !== null) {
	$content_obj = json_decode ( $content, TRUE );
	$btdsswc = new BitbucketToDiscordSlackStyleWebhookConverter ();
	$result = json_encode ( $btdsswc->convert ( $obj ) );
}
echo $result;
unset ( $content );
unset ( $result );
// if (isset ( $_GET ['from'] ) && isset ( $_GET ['to'] ) && isset ( $_GET ['provider'] ) && isset ( $_POST ['body'] )) {
// $class_name = toClassName ( $_GET ['from'] ) . 'To' . toClassName ( $_GET ['to'] ) . 'Converter';
// $provider = $_GET['provider'];
// switch (strtolower(trim($provider))) {
// case 'discord':
// $url = 'https://discordapp.com/api/webhooks/' . $webhook_id . '/' . $webhook_key;

// break;
// }
// if (class_exists ( $class_name )) {
// $converter = new $class_name ($webhook_id, $webhook_key);
// $result = json_encode ( $converter->convert ( json_decode ( $_POST ['body'] ) ) );
// } else {
// $result = '{\n\t\"error\": \"Unknown class name.\"\n}';
// }
// } else {
// $result = '{\n\t\"error\": \"Invalid request.\"\n}';
// }
// echo $result;
?>