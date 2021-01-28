<?php
namespace Automattic\WooCommerce\Blocks\BlockTypes;

/**
 * ProductCategories class.
 */
class AllCategories extends AbstractDynamicBlock {

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'all-categories';

	/**
	 * Default attribute values, should match what's set in JS `registerBlockType`.
	 *
	 * @var array
	 */
	protected $defaults = array(
		'hasCount'       => true,
		'hasImage'       => false,
		'hasEmpty'       => false,
		'isDropdown'     => false,
		'isHierarchical' => true,
	);

	/**
	 * Get block attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		return array_merge(
			parent::get_attributes(),
			array(
				'align'          => $this->get_schema_align(),
				'className'      => $this->get_schema_string(),
				'hasCount'       => $this->get_schema_boolean( true ),
				'hasImage'       => $this->get_schema_boolean( false ),
				'hasEmpty'       => $this->get_schema_boolean( false ),
				'isDropdown'     => $this->get_schema_boolean( false ),
				'isHierarchical' => $this->get_schema_boolean( true ),
			)
		);
	}

	/**
	 * Render the Product Categories List block.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Rendered block type output.
	 */
	public function render( $attributes = array(), $content = '' ) {
		$uid        = uniqid( 'product-categories-' );
		$categories = $this->get_categories( $attributes );

		if ( empty( $categories ) ) {
			return '';
		}

		if ( ! empty( $content ) ) {
			// Deal with legacy attributes (before this was an SSR block) that differ from defaults.
			if ( strstr( $content, 'data-has-count="false"' ) ) {
				$attributes['hasCount'] = false;
			}
			if ( strstr( $content, 'data-is-dropdown="true"' ) ) {
				$attributes['isDropdown'] = true;
			}
			if ( strstr( $content, 'data-is-hierarchical="false"' ) ) {
				$attributes['isHierarchical'] = false;
			}
			if ( strstr( $content, 'data-has-empty="true"' ) ) {
				$attributes['hasEmpty'] = true;
			}
		}

		$classes = $this->get_container_classes( $attributes );

		$output  = '<div class="' . esc_attr( $classes ) . '">';
		$output .= $this->renderList( $categories, $attributes, $uid );
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get the list of classes to apply to this block.
	 *
	 * @param array $attributes Block attributes. Default empty array.
	 * @return string space-separated list of classes.
	 */
	protected function get_container_classes( $attributes = array() ) {
		$classes = array( 'wc-block-product-categories' );

		if ( isset( $attributes['align'] ) ) {
			$classes[] = "align{$attributes['align']}";
		}

		if ( ! empty( $attributes['className'] ) ) {
			$classes[] = $attributes['className'];
		}

		if ( $attributes['isDropdown'] ) {
			$classes[] = 'is-dropdown';
		} else {
			$classes[] = 'is-list';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Get categories (terms) from the db.
	 *
	 * @param array $attributes Block attributes. Default empty array.
	 * @return array
	 */
	protected function get_categories( $attributes ) {
		$hierarchical = wc_string_to_bool( $attributes['isHierarchical'] );
		$categories   = get_terms(
			'product_cat',
			[
				'hide_empty'   => ! $attributes['hasEmpty'],
				'pad_counts'   => true,
				'hierarchical' => true,
				'exclude'      => array( 15 ),
			]
		);

		if ( ! is_array( $categories ) || empty( $categories ) ) {
			return [];
		}

		return $hierarchical ? $this->build_category_tree( $categories ) : $categories;
	}

	/**
	 * Build hierarchical tree of categories.
	 *
	 * @param array $categories List of terms.
	 * @return array
	 */
	protected function build_category_tree( $categories ) {
		$categories_by_parent = [];

		foreach ( $categories as $category ) {
			if ( ! isset( $categories_by_parent[ 'cat-' . $category->parent ] ) ) {
				$categories_by_parent[ 'cat-' . $category->parent ] = [];
			}
			$categories_by_parent[ 'cat-' . $category->parent ][] = $category;
		}

		$tree = $categories_by_parent['cat-0'];
		unset( $categories_by_parent['cat-0'] );

		foreach ( $tree as $category ) {
			if ( ! empty( $categories_by_parent[ 'cat-' . $category->term_id ] ) ) {
				$category->children = $this->fill_category_children( $categories_by_parent[ 'cat-' . $category->term_id ], $categories_by_parent );
			}
		}

		return $tree;
	}

	/**
	 * Build hierarchical tree of categories by appending children in the tree.
	 *
	 * @param array $categories List of terms.
	 * @param array $categories_by_parent List of terms grouped by parent.
	 * @return array
	 */
	protected function fill_category_children( $categories, $categories_by_parent ) {
		foreach ( $categories as $category ) {
			if ( ! empty( $categories_by_parent[ 'cat-' . $category->term_id ] ) ) {
				$category->children = $this->fill_category_children( $categories_by_parent[ 'cat-' . $category->term_id ], $categories_by_parent );
			}
		}
		return $categories;
	}

	/**
	 * Render the category list as a list.
	 *
	 * @param array $categories List of terms.
	 * @param array $attributes Block attributes. Default empty array.
	 * @param int   $uid Unique ID for the rendered block, used for HTML IDs.
	 * @param int   $depth Current depth.
	 * @return string Rendered output.
	 */
	protected function renderList( $categories, $attributes, $uid, $depth = 0 ) {
		$classes = [
			'wc-block-all-categories-list',
			'wc-block-all-categories-list--depth-' . absint( $depth ),
		];
		$output  = '<ul class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $this->renderListItems( $categories, $attributes, $uid, $depth ) . '</ul>';

		return $output;
	}

	/**
	 * Render a list of terms.
	 *
	 * @param array $categories List of terms.
	 * @param array $attributes Block attributes. Default empty array.
	 * @param int   $uid Unique ID for the rendered block, used for HTML IDs.
	 * @param int   $depth Current depth.
	 * @return string Rendered output.
	 */
	protected function renderListItems( $categories, $attributes, $uid, $depth = 0 ) {
		$output = '';

		foreach ( $categories as $category ) {
			$output .= '
				<li class="wc-block-all-categories-list-item">
					<a href="' . esc_attr( get_term_link( $category->term_id, 'product_cat' ) ) . '">
						' . esc_html( $category->name ) . $this->get_image_html( $category, $attributes ) . '
					</a>
					' . $this->getCount( $category, $attributes ) . '
					' . (
						! empty( $category->children ) ?
						$this->renderList( $category->children, $attributes, $uid, $depth + 1 ) :
						''
					) . '
				</li>
			';
		}

		return $output;
	}

	/**
	 * Returns the category image html
	 *
	 * @param \WP_Term $category Term object.
	 * @param array    $attributes Block attributes. Default empty array.
	 * @param string   $size Image size, defaults to 'woocommerce_thumbnail'.
	 * @return string
	 */
	public function get_image_html( $category, $attributes, $size = 'woocommerce_thumbnail' ) {
		if ( empty( $attributes['hasImage'] ) ) {
			return '';
		}

		$image_id = get_term_meta( $category->term_id, 'thumbnail_id', true );

		if ( ! $image_id ) {
			return '<span class="wc-block-all-categories-list-item__image wc-block-all-categories-list-item__image--placeholder">' . wc_placeholder_img( 'woocommerce_thumbnail' ) . '</span>';
		}

		return '<span class="wc-block-all-categories-list-item__image"><img src="' . wp_get_attachment_url( $image_id ) . '" /><div class="circle-cta static"><div class="decoration"></div><span class="caption">VOIR</span></div></span>';
	}

	/**
	 * Get the count, if displaying.
	 *
	 * @param object $category Term object.
	 * @param array  $attributes Block attributes. Default empty array.
	 * @return string
	 */
	protected function getCount( $category, $attributes ) {
		if ( empty( $attributes['hasCount'] ) ) {
			return '';
		}

		if ( $attributes['isDropdown'] ) {
			return '(' . absint( $category->count ) . ')';
		}

		$screen_reader_text = sprintf(
			/* translators: %s number of products in cart. */
			_n( '%d product', '%d products', absint( $category->count ), 'woo-gutenberg-products-block' ),
			absint( $category->count )
		);

		return '<span class="wc-block-product-categories-list-item-count">'
			. '<span aria-hidden="true">' . absint( $category->count ) . '</span>'
			. '<span class="screen-reader-text">' . esc_html( $screen_reader_text ) . '</span>'
		. '</span>';
	}
}
