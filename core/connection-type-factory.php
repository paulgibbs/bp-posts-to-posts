<?php

class P2P_Connection_Type_Factory {
	private static function create_side( &$args, $direction ) {
		$object = wp_list_pluck( $args, $direction );

		if ( in_array( $object, array( 'user', 'attachment' ) ) )
			$object_type = $object;
		else
			$object_type = 'post';

		$query_vars = wp_list_pluck( $args, $direction . '_query_vars' );

		if ( 'post' == $object_type )
			$query_vars['post_type'] = (array) $object;

		$class = 'P2P_Side_' . ucfirst( $object_type );

		return new $class( $query_vars );
	}

	private static function get_direction_strategy( $sides, $reciprocal ) {
		if ( $sides['from']->is_same_type( $sides['to'] ) &&
		     $sides['from']->is_indeterminate( $sides['to'] ) ) {
			if ( $reciprocal )
				$class = 'P2P_Reciprocal_Connection_Type';
			else
				$class = 'P2P_Indeterminate_Connection_Type';
		} else {
			$class = 'P2P_Determinate_Connection_Type';
		}

		return new $class;
	}

	public static function get_instance( $hash ) {
		if ( isset( self::$instances[ $hash ] ) )
			return self::$instances[ $hash ];

		return false;
	}
}
