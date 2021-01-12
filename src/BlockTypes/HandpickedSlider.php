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

		$classes  = $this->get_container_classes();
		$output   = implode( '', array_map( array( $this, 'render_product' ), $products ) );
		$total    = count( $products );
		$controls = '<span class="wc-block-slider__controls">
		<span class="wc-block-slider__control wc-block-slider__left"><svg width="23" height="8" viewBox="0 0 23 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.3536 4.35356C22.5488 4.15829 22.5488 3.84171 22.3536 3.64645L19.1716 0.464468C18.9763 0.269206 18.6597 0.269206 18.4645 0.464468C18.2692 0.65973 18.2692 0.976312 18.4645 1.17157L21.2929 4L18.4645 6.82843C18.2692 7.02369 18.2692 7.34027 18.4645 7.53554C18.6597 7.7308 18.9763 7.7308 19.1716 7.53554L22.3536 4.35356ZM-4.37114e-08 4.5L22 4.5L22 3.5L4.37114e-08 3.5L-4.37114e-08 4.5Z" fill="#B82D25"/></svg></span>
		<span class="wc-block-slider__control wc-block-slider__right"><svg width="23" height="8" viewBox="0 0 23 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.3536 4.35356C22.5488 4.15829 22.5488 3.84171 22.3536 3.64645L19.1716 0.464468C18.9763 0.269206 18.6597 0.269206 18.4645 0.464468C18.2692 0.65973 18.2692 0.976312 18.4645 1.17157L21.2929 4L18.4645 6.82843C18.2692 7.02369 18.2692 7.34027 18.4645 7.53554C18.6597 7.7308 18.9763 7.7308 19.1716 7.53554L22.3536 4.35356ZM-4.37114e-08 4.5L22 4.5L22 3.5L4.37114e-08 3.5L-4.37114e-08 4.5Z" fill="#B82D25"/></svg></span>
		</span>';
		$navs     = ( $total > 1 ? '<span class="wc-block-slider__pagination">1-' . $total . '</span>' . $controls : '' );

		return sprintf(
			'<div class="%s alignfull wc-block-slider"><div class="wc-block-slider__products swiper-container">' . $navs . '<div class="swiper-wrapper">%s</div></div></div>',
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
			"<div class=\"wc-block-slider__product swiper-slide\">
				{$data->image}
				<div class=\"wc-block-slider__product-content\">
					{$data->title}
					{$data->badge}
					{$data->price}
					{$data->rating}
					<a href=\"{$data->permalink}\" class=\"wc-block-slider__product-link\">{$data->button}</a>
				</div>
			</div>",
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

	/**
	 * Get the product title.
	 *
	 * @param \WC_Product $product Product.
	 * @return string
	 */
	protected function get_title_html( $product ) {
		if ( empty( $this->attributes['contentVisibility']['title'] ) ) {
			return '';
		}
		return '<div class="wc-block-slider__product-title">' . $product->get_title() . '</div>';
	}

	/**
	 * Get the price.
	 *
	 * @param \WC_Product $product Product.
	 * @return string Rendered product output.
	 */
	protected function get_price_html( $product ) {
		if ( empty( $this->attributes['contentVisibility']['price'] ) ) {
			return '';
		}
		return sprintf(
			'<div class="wc-block-slider__product-price price">%s</div>',
			$product->get_price_html()
		);
	}

	/**
	 * Get the button.
	 *
	 * @param \WC_Product $product Product.
	 * @return string Rendered product output.
	 */
	protected function get_button_html( $product ) {
		$attributes = array(
			'aria-label'       => $product->add_to_cart_description(),
			'data-quantity'    => '1',
			'data-product_id'  => $product->get_id(),
			'data-product_sku' => $product->get_sku(),
			'rel'              => 'nofollow',
			'class'            => 'circle-cta custom-add_to_cart_button',
		);

		if ( $product->supports( 'ajax_add_to_cart' ) ) {
			$attributes['class'] .= ' ajax_add_to_cart';
		}

		return sprintf(
			'<a href="%s" %s><div class="decoration"></div><span class="caption">%s</span></a>',
			esc_url( $product->add_to_cart_url() ),
			wc_implode_html_attributes( $attributes ),
			esc_html( 'AJOUTER' )
		);
	}
}
