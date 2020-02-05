/**
 * External dependencies
 */
import {
	insertBlock,
	getEditedPostContent,
	createNewPost,
	switchUserToAdmin,
} from '@wordpress/e2e-test-utils';
import { unsetCheckbox } from '@woocommerce/e2e-tests/utils';

describe( 'All Reviews', () => {
	beforeEach( async () => {
		await switchUserToAdmin();
		await createNewPost();
	} );

	it( 'can be created', async () => {
		await insertBlock( 'All Reviews' );

		// @todo create reviews

		expect( await getEditedPostContent() ).toMatchSnapshot();
	} );

	it( 'can hide product name', async () => {
		await insertBlock( 'All Reviews' );

		// @todo create reviews

		await unsetCheckbox( '.components-form-toggle__input:first-child' );

		expect( await getEditedPostContent() ).toMatchSnapshot();
	} );
} );
