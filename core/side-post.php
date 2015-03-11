<?php

class P2P_Side_Post extends P2P_Side {

	protected $item_type = 'BP_Relations_Item_Post';

	function __construct( $query_vars ) {
		$this->query_vars = $query_vars;
	}

	public function get_object_type() {
		return 'post';
	}

	function translate_qv( $qv ) {
		$map = array(
			'include' => 'post__in',
			'exclude' => 'post__not_in',
			'search' => 's',
			'page' => 'paged',
			'per_page' => 'posts_per_page'
		);

		foreach ( $map as $old => $new )
			if ( isset( $qv["p2p:$old"] ) )
				$qv[$new] = _p2p_pluck( $qv, "p2p:$old" );

		return $qv;
	}

	function get_base_qv( $q ) {
		if ( isset( $q['post_type'] ) && 'any' != $q['post_type'] ) {
			$common = array_intersect( $this->query_vars['post_type'], (array) $q['post_type'] );

			if ( !$common )
				unset( $q['post_type'] );
		}

		return array_merge( $this->query_vars, $q, array(
			'suppress_filters' => false,
			'ignore_sticky_posts' => true,
		) );
	}

	function do_query( $args ) {
		return new WP_Query( $args );
	}

	function capture_query( $args ) {
		$q = new WP_Query;
		$q->_p2p_capture = true;

		$q->query( $args );

		return $q->_p2p_sql;
	}

	function is_indeterminate( $side ) {
		$common = array_intersect(
			$this->query_vars['post_type'],
			$side->query_vars['post_type']
		);

		return !empty( $common );
	}

	protected function recognize( $arg ) {
		if ( is_object( $arg ) && !isset( $arg->post_type ) )
			return false;

		$post = get_post( $arg );

		if ( !is_object( $post ) )
			return false;

		if ( !$this->recognize_post_type( $post->post_type ) )
			return false;

		return $post;
	}

	public function recognize_post_type( $post_type ) {
		if ( !post_type_exists( $post_type ) )
			return false;

		return in_array( $post_type, $this->query_vars['post_type'] );
	}
}


