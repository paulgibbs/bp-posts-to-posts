<?php

class P2P_Connection_Type {

	public $side;

	public $cardinality;

	public $labels;

	protected $title;

	public function __construct( $args, $sides ) {
		$this->side = $sides;

		$this->set_self_connections( $args );

		$this->set_cardinality( wp_list_pluck( $args, 'cardinality' ) );

		$labels = array();
		foreach ( array( 'from', 'to' ) as $key ) {
			$labels[ $key ] = (array) wp_list_pluck( $args, $key . '_labels' );
		}

		$this->labels = $labels;

		$this->fields = $this->expand_fields( wp_list_pluck( $args, 'fields' ) );

		foreach ( $args as $key => $value ) {
			$this->$key = $value;
		}
	}

	public function get_field( $field, $direction ) {
		$value = $this->$field;

		if ( false === $direction )
			return $value;

		return $value[ $direction ];
	}

	private function set_self_connections( &$args ) {
		$from_side = $this->side['from'];
		$to_side = $this->side['to'];

		if ( !$from_side->is_same_type( $to_side ) ) {
			$args['self_connections'] = true;
		}
	}

	private function expand_fields( $fields ) {
		foreach ( $fields as &$field_args )
		{
			if ( !is_array( $field_args ) )
				$field_args = array( 'title' => $field_args );
		}

		return $fields;
	}

	private function set_cardinality( $cardinality ) {
		$parts = explode( '-', $cardinality );

		$this->cardinality['from'] = $parts[0];
		$this->cardinality['to'] = $parts[2];

		foreach ( $this->cardinality as $key => &$value ) {
			if ( 'one' != $value )
				$value = 'many';
		}
	}

	public function __call( $method, $args ) {
		if ( ! method_exists( 'P2P_Directed_Connection_Type', $method ) ) {
			trigger_error( "Method '$method' does not exist.", E_USER_ERROR );
			return;
		}

		$r = $this->direction_from_item( $args[0] );
		if ( !$r ) {
			trigger_error( sprintf( "Can't determine direction for '%s' type.", $this->name ), E_USER_WARNING );
			return false;
		}

		// replace the first argument with the normalized one, to avoid having to do it again
		list( $direction, $args[0] ) = $r;

		$directed = $this->set_direction( $direction );

		return call_user_func_array( array( $directed, $method ), $args );
	}

	/**
	 * Set the direction.
	 *
	 * @param string $direction Can be 'from', 'to' or 'any'.
	 *
	 * @return object P2P_Directed_Connection_Type instance
	 */
	public function set_direction( $direction, $instantiate = true ) {
		if ( !in_array( $direction, array( 'from', 'to', 'any' ) ) )
			return false;

		if ( $instantiate ) {
			$class = $this->strategy->get_directed_class();

			return new $class( $this, $direction );
		}

		return $direction;
	}

	/**
	 * Attempt to guess direction based on a parameter.
	 *
	 * @param mixed A post type, object or object id.
	 * @param bool Whether to return an instance of P2P_Directed_Connection_Type or just the direction
	 * @param string An object type, such as 'post' or 'user'
	 *
	 * @return bool|object|string False on failure, P2P_Directed_Connection_Type instance or direction on success.
	 */
	public function find_direction( $arg, $instantiate = true, $object_type = null ) {
		if ( $object_type ) {
			$direction = $this->direction_from_object_type( $object_type );
			if ( !$direction )
				return false;

			if ( in_array( $direction, array( 'from', 'to' ) ) )
				return $this->set_direction( $direction, $instantiate );
		}

		$r = $this->direction_from_item( $arg );
		if ( !$r )
			return false;

		list( $direction, $item ) = $r;

		return $this->set_direction( $direction, $instantiate );
	}

	protected function direction_from_item( $arg ) {
		if ( is_array( $arg ) )
			$arg = reset( $arg );

		foreach ( array( 'from', 'to' ) as $direction ) {
			$item = $this->side[ $direction ]->item_recognize( $arg );

			if ( !$item )
				continue;

			return array( $this->strategy->choose_direction( $direction ), $item );
		}

		return false;
	}

	protected function direction_from_object_type( $current ) {
		$from = $this->side['from']->get_object_type();
		$to = $this->side['to']->get_object_type();

		if ( $from == $to && $current == $from )
			return 'any';

		if ( $current == $from )
			return 'to';

		if ( $current == $to )
			return 'from';

		return false;
	}

	public function direction_from_types( $object_type, $post_types = null ) {
		foreach ( array( 'from', 'to' ) as $direction ) {
			if ( $object_type != $this->side[ $direction ]->get_object_type() )
				continue;

			return $this->strategy->choose_direction( $direction );
		}

		return false;
	}
}
