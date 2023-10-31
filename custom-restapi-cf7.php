<?php
// Created the CPT "restapi" and contact form with fields ("post-name" | "post-description") and created the post type via REST API

add_action( 'rest_api_init', 'restapi_add_callback_url_endpoint' );
function restapi_add_callback_url_endpoint(){
    register_rest_route(
        'custom-rest-api/v1/', // Namespace
        'receive-callback', // Endpoint
        array(
            'methods'  => 'POST',
            'callback' => 'restapi_receive_callback'
        )
    );
}

function restapi_receive_callback( $request_data ) {
    $data = array();
    
    $parameters = json_decode( $request_data->get_body(), true );
    $title     = $parameters['title'];
    $body     = $parameters['body'];

	$post_args = array(
		'post_title'   => wp_strip_all_tags( $title ),
		'post_content' => $body,
		'post_status'  => 'publish',
		'post_type'    => 'restapi'
	);
	if( $post_args ) {
        error_log('Post was inserted successfully.');
		wp_insert_post( $post_args );
        $data['message'] = 'Post was successful';
    } else {
        error_log('Failed to insert the post.');
    }
    return $data;
}

add_action( 'wpcf7_before_send_mail', 'sendingDataToJava', 10, 4 );
   function sendingDataToJava( $contact_form ) {
	$wpcf7 = WPCF7_ContactForm::get_current();
	$submission = WPCF7_Submission::get_instance();
	$data = $submission->get_posted_data();

	
    if ($wpcf7->id == 12) {
		$name = $data['post-name'];
		$description = $data['post-description'];
		$body = array(
			'title' => $name,
			'body' => $description
		);
		$fields = json_encode($body);
		
		$response = wp_remote_post( site_url().'/wp-json/custom-rest-api/v1/receive-callback', array( 'body' => $fields ) );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		} else {
				return $response;
		}
    }
	return $data;	
}
