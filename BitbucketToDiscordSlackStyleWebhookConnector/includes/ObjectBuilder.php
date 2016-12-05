<?php
class ObjectBuilder {
	private $attributes = array ();
	function __construct() {
		//
	}
	public static function setAttributeWithPath($arr, $object_path, $value) {
		$ret = array ($arr);
		$t = $ret;
		$i = '';
		$first = true;
		$paths = explode ( '/', $object_path );
		foreach ( $paths as $p ) {
			if ($first)
				$first = false;
			else {
				if (!(isset ( $t [$i] )))
					$t [$i] = array ();
				$t = $t [$i];
			}
			$i = $p;
		}
		$t [$i] = $value;
		return $ret;
	}
	public function addAttribute($attribute_path, $routine_name) {
		$this->attributes [$attribute_path] = $routine_name;
	}
	public function build($obj) {
		$ret = array ();
		foreach ( $this->attributes as $k => $v )
			$ret[$k] = $v ( $k, $obj );
		//$ret = ObjectBuilder::setAttributeWithPath ( $ret, $k, $v ( $k, $obj ) );
		return $ret;
	}
}

?>