<?php

/**
 * Return the endpoint data
 */
function wpgetapi_endpoint( $api_id = '', $endpoint_id = '', $args = array(), $keys = array() ) {

    if( ! $api_id || ! $endpoint_id )
        return false;

    $api = new WpGetApi_Api( $api_id, $endpoint_id, $args, $keys );

    return $api->endpoint_output();

}

/**
 * Return the endpoint data via shortcode
 */
function wpgetapi_endpoint_shortcode( $atts = array() ) {
	
	$a = shortcode_atts( array(
		'api_id' => '',
		'endpoint_id' => '',
		'debug' => false,
		'args' => array(),
		'keys' => array(),
		'endpoint_variables' => array(),
		'query_variables' => array(),
		'format' => '',
		'html_tag' => 'div',
		'html_labels' => 'false',
		'html_url' => '',
		'html_to_link' => '',
		'img_key' => '',
		'img_prepend' => '',
		'on' => '',
		'button_text' => '',
		'hide_button' => '',
		'button_spinner' => '',
		'chain' => '',
	), $atts );

	if( ! isset( $a['api_id'] ) || $a['api_id'] == '' )
		return __( 'api_id shortcode attribute is not set.', 'wpgetapi' );

	if( ! isset( $a['endpoint_id'] ) || $a['endpoint_id'] == '' )
		return __( 'endpoint_id shortcode attribute is not set.', 'wpgetapi' );

	

	// sort out our keys if using them
	if( ! empty( $a['keys'] ) ) {
		// Create our array of values for keys
	    // First, sanitize the data and remove white spaces
	    $no_whitespaces_keys = preg_replace( '/\s*,\s*/', ',', filter_var( $a['keys'], FILTER_SANITIZE_STRING ) );
	    $a['keys'] = explode( ',', $no_whitespaces_keys );
	}

	// sort out our endpoint_variables if using them
	if( ! empty( $a['endpoint_variables'] ) ) {
		// Create our array of values for endpoint_variables
	    // First, sanitize the data and remove white spaces
	    $no_whitespaces_vars = preg_replace( '/\s*,\s*/', ',', filter_var( $a['endpoint_variables'], FILTER_SANITIZE_STRING ) ); 
	    $a['endpoint_variables'] = explode( ',', $no_whitespaces_vars );
	}

	// add our shortcode args to the actual 'args' within the endpoint call
	$a['args']['debug'] = $a['debug'];
	$a['args']['endpoint_variables'] = $a['endpoint_variables'];
	$a['args']['query_variables'] = $a['query_variables'];
	$a['args']['format'] = $a['format'];
	$a['args']['html_tag'] = $a['html_tag'];
	$a['args']['html_labels'] = $a['html_labels'];
	$a['args']['html_url'] = $a['html_url'];
	$a['args']['html_to_link'] = $a['html_to_link'];
	$a['args']['img_key'] = $a['img_key'];
	$a['args']['img_prepend'] = $a['img_prepend'];
	$a['args']['shortcode'] = true;
	$a['args']['on'] = $a['on'];
	$a['args']['button_text'] = $a['button_text'];
	$a['args']['hide_button'] = $a['hide_button'];
	$a['args']['button_spinner'] = $a['button_spinner'];
	$a['args']['chain'] = $a['chain'];


	// if we are doing ajax
	if( isset( $a['args']['on'] ) && $a['args']['on'] == 'ajax' && ! wp_doing_ajax() ) {
		
		if( class_exists( 'WpGetApi_Extras_Extend' ) ) {

			add_action( 'wp_footer', array( 'WpGetApi_Extras_Extend', 'ajax_function' ) );

	        $button_text = isset( $a['args']['button_text'] ) && $a['args']['button_text'] != '' ? $a['args']['button_text'] : 'Call API';
	        
	        ob_start(); ?>

	            <button 
	                class="button btn btn-primary wpgetapi_ajax_request" 
	                data-api="<?php esc_html_e( $a['api_id'] ); ?>" 
	                data-endpoint="<?php esc_html_e( $a['endpoint_id'] ); ?>" 
	                data-args="<?php esc_html_e( json_encode( $a['args'] ) ); ?>" 
	                data-keys="<?php esc_html_e( json_encode( $a['keys'] ) ); ?>">
	                <span class="button_text"><?php esc_html_e( $button_text ); ?></span>
	            </button>
	            <div class="wpgetapi_ajax_output"></div>
	        
	        <?php
	        $result = ob_get_contents();
	        ob_end_clean();

	    } else {

	    	$result = 'You need the <a href="https://wpgetapi.com/downloads/pro-plugin">PRO plugin</a> for this AJAX functionality.';

	    }

	} else {

		// [wpgetapi_endpoint api_id='livelearn' endpoint_id='dezzp1' chain='start' keys='UUID']
		// [wpgetapi_endpoint api_id='livelearn' endpoint_id='dezzp2' chain='end']

		// if we are starting the chain
		if( isset( $a['args']['chain'] ) ) {

			// get our result from this chain
			$result = wpgetapi_endpoint( $a['api_id'], $a['endpoint_id'], $a['args'], $a['keys'] );	

			// set the transient for this chain if not the last chain
			if( $a['args']['chain'] !== 'end' )
				set_transient( 'wpgetapi_chain_' . sanitize_text_field( $a['args']['chain'] ), $result, 10 );
			

		} else {

			// do our standard return
			$result = wpgetapi_endpoint( $a['api_id'], $a['endpoint_id'], $a['args'], $a['keys'] );	

		}

		


	}

	if( is_array( $result ) ) {
		$result = sprintf( 
	    	__( 'The <b>Results Format</b> for this endpoint is set to \'PHP array data\' - shortcodes are unable to output this type of data.<br>Please change the Results Format to \'JSON string\' in the endpoint settings page.<br>Alternatively, please see our tutorial on %1s.', 'wpgetapi' ),
	    	'<a target="_blank" href="https://wpgetapi.com/docs/format-api-data-as-html/?utm_campaign=Shortcode JSON&utm_medium=Admin&utm_source=User">how to format your API data as HTML</a>'
	    );
	}

	if( $a['args']['format'] !== 'no_display' )
		return $result;

}
add_shortcode( 'wpgetapi_endpoint', 'wpgetapi_endpoint_shortcode' );
