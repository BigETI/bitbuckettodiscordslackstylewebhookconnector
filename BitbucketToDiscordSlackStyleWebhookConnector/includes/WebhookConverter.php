<?php
include_once './includes/ObjectBuilder.php';
abstract class WebhookConverter {
	private $object_builder = new ObjectBuilder();
	
	function __construct() {
		
	}
	
	protected function getObjectBuilder() {
		return $this->object_builder;
	}
	
	public function convert($obj) {
		return $this->object_builder->build($obj);
	}
}
?>