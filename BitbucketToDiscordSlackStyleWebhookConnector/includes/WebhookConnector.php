<?php
class WebhookConnector {
	private $url = '';
	private $token = null;
	
	function __construct($url) {
		$this->url = $url;
		$this->updateToken();
	}
	
	public function updateToken() {
		$obj = json_decode(file_get_contents($this->url));
		if ($obj !== false) {
			if (isset($obj['token']))
				$this->token = $obj['token'];
		}
	}
	
	public function send() {
		$url = 'http://server.com/path';
		$data = array('key1' => 'value1', 'key2' => 'value2');
			
		// use key 'http' even if you send the request to https://...
		$options = array(
				'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)
				)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
	}
}
?>