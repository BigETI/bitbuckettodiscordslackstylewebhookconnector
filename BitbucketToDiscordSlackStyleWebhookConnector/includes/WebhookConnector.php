<?php
class WebhookConnector {
	public static function send($url, $obj) {
		return file_get_contents($url, false, stream_context_create(array(
				'http' => array(
						'header'  => '',
						'method'  => 'POST',
						'content' => http_build_query(array ('payload' => json_encode ( $obj )))
				)
		)));
	}
}
?>