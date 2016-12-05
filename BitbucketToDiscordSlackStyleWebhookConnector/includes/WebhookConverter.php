<?php
include_once './includes/ObjectBuilder.php';
abstract class WebhookConverter {
	private $object_builder = null;
	
	function __construct() {
		$this->object_builder = new ObjectBuilder();
	}
	
	protected function getObjectBuilder() {
		return $this->object_builder;
	}
	
	public function convert($obj) {
		return $this->object_builder->build($obj);
	}
}
?>