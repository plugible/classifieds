<?php

namespace Plugible\Classifieds;

Class Form {

	private $shortcode = 'classified-form';

	public function __construct() {
		$this->hook();
	}

	private function hook() {
		add_shortcode( $this->shortcode, [ $this, 'output' ] );
	}

	public function output() {

		/**
		 * Verify if login is required.
		 */
		$require_login = apply_filters( 'pl_claassifieds_require_login', false );
		if ( $require_login ) {
			return apply_filters( 'pl_claassifieds_required_login', __( 'Error: Login Required', 'classifieds-by-plugible' ) );
		}

		/**
		 * Generate form.
		 */
		$locations = [];
		$this->getHierarchicalTerms( 'pl_classified_location', $locations );
		$categories = [];
		$this->getHierarchicalTerms( 'pl_classified_category', $categories );
		return $this->form( ''
			. $this->input( 'title', __( 'Classified Title', 'classifieds-by-plugible' ) )
			. $this->select( 'category', __( 'Category', 'classifieds-by-plugible' ), $categories )
			. $this->select( 'location', __( 'Location', 'classifieds-by-plugible' ), $locations )
			. $this->wpEditor( 'description', __( 'Classified Description', 'classifieds-by-plugible' ) )
		);
	}

	private function input( $name, $title, $type = 'text' ) {
		$format = apply_filters( 'pl_classifieds_form_input_format', '<p><label for="%1$s">%2$s<br><input type="%3$s" id="%1$s" name="%1$s" /></label></p>' );
		return apply_filters( 'pl_classifieds_form_input', sprintf( $format, $name, $title, $type ), $name, $title, $type );
	}

	private function textArea( $name, $title ) {
		$format = apply_filters( 'pl_classifieds_form_textarea_format', '<p><label for="%1$s">%2$s<br><textarea id="%1$s" name="%1$s" cols="40" rows="5"></textarea></label></p>' );
		return sprintf( $format, $name, $title );
	}

	private function wpEditor( $name, $title ) {
		$format = apply_filters( 'pl_classifieds_form_wpeditor_format', '<p><label for="%1$s">%2$s<br>%3$s</label></p>' );

		ob_start();
		wp_editor( '', $name );
		$editor = ob_get_clean();

		return sprintf( $format, $name, $title, $editor );
	}

	public function select( $name, $title, $options ) {

		$format = apply_filters( 'pl_classifieds_form_select_format', '<p><label for="%1$s">%2$s<br><select id="%1$s" name="%1$s">%3$s</select></label></p>' );

		$options_html = '';
		array_walk( $options, function( $value, $index ) use( &$options_html ) {
			$options_html .= sprintf( '<option value="%1$s">%2$s</option>', $index, $value );
		} );

		return sprintf( $format, $name, $title, $options_html );
	} 

	private function form( $contents ) {
		$format = apply_filters( 'pl_classifieds_form_format', '<form method="post"/>%1$s</form>' );
		return sprintf( $format,  $contents );
	}

	private function getHierarchicalTerms( $taxonomy, &$ret, $parent = 0 ) {

		static $level = 0;

		$terms = get_terms( $taxonomy, [
			'hide_empty' => false,
			'parent' => $parent,
		] );

		foreach ( $terms  as $term ) {
			$ret[ $term->term_id ] = str_repeat( '&mdash;', $level ) . ' ' . $term->name;
			$child_terms = get_terms( $taxonomy, [
				'hide_empty' => false,
				'parent' => $term->term_id,
			] );
			if ( $child_terms ) {
				$level++;
				$this->getHierarchicalTerms( $taxonomy, $ret, $term->term_id );
				$level--;
			}
		}
	}
}
