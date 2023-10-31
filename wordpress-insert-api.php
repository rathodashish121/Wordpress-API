<?php
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
    
    $parameters = $request_data->get_params();
    $email    = $parameters['email'];
    $password = $parameters['password'];
    $title     = $parameters['title'];
    $body     = $parameters['body'];
    
    if ( isset($email) && isset($password) ) {
        $userdata = get_user_by( 'email', $email );
        if ( $userdata ) {
            
            $wp_check_password_result = wp_check_password( $password, $userdata->user_pass, $userdata->ID );
           
            if ( $wp_check_password_result ) {
                $data['status'] = 'OK';
                $data['received_data'] = array(
                    'name'     => $email,
                    'password' => $password,
                    //'data'     => $userdata
                );
					
                $post_args = array(
                    'post_title'   => wp_strip_all_tags( $title ),
                    'post_content' => $body,
                    'post_status'  => 'publish',
                    'post_type'    => 'restapi'
                );
                
                $post_var = wp_insert_post( $post_args );
                if( $post_var ) { $data['message'] = 'Post was successful'; }
                
            } else {
                $data['status'] = 'OK';
                $data['message'] = 'You are not authenticated to login!';
            }
        } else {
            $data['status'] = 'OK';
            $data['message'] = 'The current user does not exist!';
        }
    } else {
        $data['status'] = 'Failed';
        $data['message'] = 'Parameters Missing!';
    }
    return $data;
}
