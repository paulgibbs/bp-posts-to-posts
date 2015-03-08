<?php

/**
 * A uniform wrapper for various types of WP objects, i.e. posts or users.
 */
abstract class P2P_Item {

	protected $item;

	function __construct( $item ) {
		$this->item = $item;
	}

	function __isset( $key ) {
		return isset( $this->item->$key );
	}

	function __get( $key ) {
		return $this->item->$key;
	}

	function __set( $key, $value ) {
		$this->item->$key = $value;
	}

	function get_object() {
		return $this->item;
	}

	function get_id() {
		return $this->item->ID;
	}

	abstract function get_permalink();
}

class P2P_Item_User extends P2P_Item {

	function get_permalink() {
		return get_author_posts_url( $this->item->ID );
	}

	function get_editlink() {
		return get_edit_user_link( $this->item->ID );
	}
}

class P2P_Item_Any extends P2P_Item {

	function __construct() {}

	function get_permalink() {}

	function get_object() {
		return 'any';
	}

	function get_id() {
		return false;
	}
}

