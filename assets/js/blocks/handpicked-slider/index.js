/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { DEFAULT_COLUMNS } from '@woocommerce/block-settings';
import { Icon, widgets } from '@woocommerce/icons';

/**
 * Internal dependencies
 */
import './editor.scss';
import Block from './block';
import { deprecatedConvertToShortcode } from '../../utils/deprecations';

registerBlockType( 'woocommerce/handpicked-slider', {
	title: __( 'Hand-picked Slider', 'woo-gutenberg-products-block' ),
	icon: {
		src: <Icon srcElement={ widgets } />,
		foreground: '#96588a',
	},
	category: 'woocommerce',
	keywords: [
		__( 'Handpicked Slider', 'woo-gutenberg-products-block' ),
		__( 'WooCommerce', 'woo-gutenberg-products-block' ),
	],
	description: __(
		'Display a selection of hand-picked products in a slider.',
		'woo-gutenberg-products-block'
	),
	supports: {
		align: [ 'wide', 'full' ],
		html: false,
	},
	example: {
		attributes: {
			isPreview: true,
		},
	},
	attributes: {
		/**
		 * Alignment of product grid
		 */
		align: {
			type: 'string',
		},

		/**
		 * Number of columns.
		 */
		columns: {
			type: 'number',
			default: DEFAULT_COLUMNS,
		},

		/**
		 * Toggle for edit mode in the block preview.
		 */
		editMode: {
			type: 'boolean',
			default: true,
		},

		/**
		 * Content visibility setting
		 */
		contentVisibility: {
			type: 'object',
			default: {
				title: true,
				price: true,
				rating: true,
				button: true,
			},
		},

		/**
		 * How to order the products: 'date', 'popularity', 'price_asc', 'price_desc' 'rating', 'title'.
		 */
		orderby: {
			type: 'string',
			default: 'date',
		},

		/**
		 * The list of product IDs to display
		 */
		products: {
			type: 'array',
			default: [],
		},

		/**
		 * How to align cart buttons.
		 */
		alignButtons: {
			type: 'boolean',
			default: false,
		},

		/**
		 * Are we previewing?
		 */
		isPreview: {
			type: 'boolean',
			default: false,
		},
	},

	deprecated: [
		{
			// Deprecate shortcode save method in favor of dynamic rendering.
			attributes: {
				align: {
					type: 'string',
				},
				columns: {
					type: 'number',
					default: DEFAULT_COLUMNS,
				},
				editMode: {
					type: 'boolean',
					default: true,
				},
				contentVisibility: {
					type: 'object',
					default: {
						title: true,
						price: true,
						rating: true,
						button: true,
					},
				},
				orderby: {
					type: 'string',
					default: 'date',
				},
				products: {
					type: 'array',
					default: [],
				},
			},
			save: deprecatedConvertToShortcode(
				'woocommerce/handpicked-products-slider'
			),
		},
	],

	/**
	 * Renders and manages the block.
	 *
	 * @param {Object} props Props to pass to block.
	 */
	edit( props ) {
		return <Block { ...props } />;
	},

	save() {
		return null;
	},
} );
