<?php
class ObjectBuilder {
	private $attributes = array ();
	function __construct() {
		//
	}
	public static function &getReferenceFromPath($obj, $object_path, $create_if_not_exists = false) {
		$ret &= $obj;
		$paths = explode ( '/', $object_path );
		foreach ( $paths as $p ) {
			if (is_array ( $ret )) {
				if (isset ( $ret [$p] ))
					$ret &= $ret [$p];
				else {
					if ($create_if_not_exists)
						$ret [$p] = array ();
					else {
						$ret = null;
						break;
					}
				}
			}
		}
		return $ret;
	}
	public function addAttribute($attribute_path, $routine_name) {
		$this->attributes [$attribute_path] = $routine_name;
	}
	public function build($obj) {
		$ret = array ();
		foreach ( $this->attributes as $k => $v )
			ObjectBuilder::getReferenceFromPath ( $ret, $k, true ) = $v ( $k, $obj );
		return $ret;
	}
}

?>