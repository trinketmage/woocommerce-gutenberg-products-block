<?php
/**
 * Term Schema.
 *
 * @package WooCommerce/Blocks
 */

namespace Automattic\WooCommerce\Blocks\StoreApi\Schemas;

defined( 'ABSPATH' ) || exit;

/**
 * TermSchema class.
 *
 * @since 2.5.0
 */
class TermSchema extends AbstractSchema {
	/**
	 * The schema item name.
	 *
	 * @var string
	 */
	protected $title = 'term';

	/**
	 * Term properties.
	 *
	 * @return array
	 */
	public function get_properties() {
		return [
			'id'          => array(
				'description' => __( 'Unique identifier for the resource.', 'woo-gutenberg-products-block' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'name'        => array(
				'description' => __( 'Term name.', 'woo-gutenberg-products-block' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'slug'        => array(
				'description' => __( 'String based identifier for the term.', 'woo-gutenberg-products-block' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'description' => array(
				'description' => __( 'Term description.', 'woo-gutenberg-products-block' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'parent'      => array(
				'description' => __( 'Parent term ID, if applicable.', 'woo-gutenberg-products-block' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'count'       => array(
				'description' => __( 'Number of objects (posts of any type) assigned to the term.', 'woo-gutenberg-products-block' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		];
	}

	/**
	 * Convert a term object into an object suitable for the response.
	 *
	 * @param \WP_Term         $term Term object.
	 * @param \WP_REST_Request $request Request object.
	 * @return array
	 */
	public function get_item_response( $term, \WP_REST_Request $request = null ) {
		return [
			'id'          => (int) $term->term_id,
			'name'        => $this->prepare_html_response( $term->name ),
			'slug'        => $term->slug,
			'description' => $this->prepare_html_response( $term->description ),
			'parent'      => (int) $term->parent,
			'count'       => (int) $term->count,
		];
	}
}