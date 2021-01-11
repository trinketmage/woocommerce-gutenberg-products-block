<?php
namespace Automattic\WooCommerce\Blocks\BlockTypes;

/**
 * HandpickedProducts class.
 */
class HandpickedSlider extends AbstractProductGrid {
	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'handpicked-slider';

	/**
	 * Set args specific to this block
	 *
	 * @param array $query_args Query args.
	 */
	protected function set_block_query_args( &$query_args ) {
		$ids = array_map( 'absint', $this->attributes['products'] );

		$query_args['post__in']       = $ids;
		$query_args['posts_per_page'] = count( $ids );
	}

	/**
	 * Set visibility query args. Handpicked products will show hidden products if chosen.
	 *
	 * @param array $query_args Query args.
	 */
	protected function set_visibility_query_args( &$query_args ) {
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$product_visibility_terms  = wc_get_product_visibility_term_ids();
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => array( $product_visibility_terms['outofstock'] ),
				'operator' => 'NOT IN',
			);
		}
	}

	/**
	 * Get block attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		return array(
			'align'             => $this->get_schema_align(),
			'alignButtons'      => $this->get_schema_boolean( false ),
			'className'         => $this->get_schema_string(),
			'columns'           => $this->get_schema_number( wc_get_theme_support( 'product_blocks::default_columns', 3 ) ),
			'editMode'          => $this->get_schema_boolean( true ),
			'orderby'           => $this->get_schema_orderby(),
			'products'          => $this->get_schema_list_ids(),
			'contentVisibility' => $this->get_schema_content_visibility(),
			'isPreview'         => $this->get_schema_boolean( false ),
		);
	}

	/**
	 * Include and render the dynamic block.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Rendered block type output.
	 */
	public function render( $attributes = array(), $content = '' ) {
		$this->attributes = $this->parse_attributes( $attributes );
		$this->content    = $content;
		$this->query_args = $this->parse_query_args();
		$products         = $this->get_products();

		if ( ! $products ) {
			return '';
		}

		$classes = $this->get_container_classes();
		$output  = implode( '', array_map( array( $this, 'render_product' ), $products ) );

		return sprintf(
			'<div class="%s alignfull">
			<span class="wc-block-slider__index">1-' . count( $products ) . '</span>
			<ul class="wc-block-slider__products">%s</ul>
			</div>',
			esc_attr( $classes ),
			$output
		);
	}

	/**
	 * Render a single products.
	 *
	 * @param int $id Product ID.
	 * @return string Rendered product output.
	 */
	public function render_product( $id ) {
		$product = wc_get_product( $id );

		if ( ! $product ) {
			return '';
		}

		$data = (object) array(
			'permalink' => esc_url( $product->get_permalink() ),
			'image'     => $this->get_image_html( $product ),
			'title'     => $this->get_title_html( $product ),
			'rating'    => $this->get_rating_html( $product ),
			'price'     => $this->get_price_html( $product ),
			'badge'     => $this->get_sale_badge_html( $product ),
			'button'    => $this->get_button_html( $product ),
		);

		return apply_filters(
			'woocommerce_blocks_product_grid_item_html',
			"<li class=\"wc-block-slider__product\">
				{$data->image}
				{$data->title}
				{$data->badge}
				{$data->price}
				{$data->rating}
				<a href=\"{$data->permalink}\" class=\"wc-block-slider__product-link\">
				  {$data->button}
				</a>
			</li>",
			$data,
			$product
		);
	}

	/**
	 * Get the product image.
	 *
	 * @param \WC_Product $product Product.
	 * @return string
	 */
	protected function get_image_html( $product ) {
		return '<div class="wc-block-slider__product-image"><div class="wc-block-slider__product-image-holder" style="background-image: url(' . wp_get_attachment_url( $product->get_image_id() ) . ');"></div></div>';
	}
}
