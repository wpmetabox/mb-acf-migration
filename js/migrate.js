( function ( document, i18n ) {
	'use strict';

	const status = document.querySelector( '#status' ),
		button = document.querySelector( '#process' );

	button.addEventListener( 'click', async () => {
		printMessage( i18n.start );

		await resetCounter();
		await migrate( 'field_groups' );

		await resetCounter();
		await migrate( 'posts' );

		await resetCounter();
		await migrate( 'terms' );

		await resetCounter();
		await migrate( 'users' );

		await resetCounter();
		await migrate( 'settings_pages' );

		printMessage( i18n.done );
	} );

	const resetCounter = () => get( `${ajaxurl}?action=mbacf_reset_counter` );

	async function migrate( type ) {
		const response = await get( `${ajaxurl}?action=mbacf_migrate&type=${type}` );
		if ( response.data.type == 'continue' ) {
			printMessage( response.data.message );
			await migrate( type );
		}
	}

	async function get( url ) {
		const response = await fetch( url );
	    const json = await response.json();
		if ( ! response.ok ) {
	       	throw Error( json.data );
	    }
		return json;
	}

	const printMessage = text => status.innerHTML += `<p>${text}</p>`;
} )( document, MbAcf );
