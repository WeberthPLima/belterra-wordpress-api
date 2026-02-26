<?php

function register_politicas_api_route() {
    register_rest_route('api', '/politicas', array(
        'methods'  => 'GET',
        'callback' => 'get_politicas_page_data',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_politicas_api_route');

function get_politicas_page_data($request) {
    $post_id = 196;
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('no_page', 'Página não encontrada', array('status' => 404));
    }

    $acf_fields = function_exists('get_fields') ? get_fields($post_id) : null;

    $item = array(
        'id'       => $post->ID,
        'title'    => get_the_title($post_id),
        'slug'     => $post->post_name,
        'content'  => $post->post_content,
        'acf'      => $acf_fields ? $acf_fields : new stdClass(),
    );

    if (has_post_thumbnail($post_id)) {
        $item['featured_image'] = get_the_post_thumbnail_url($post_id, 'full');
    } else {
        $item['featured_image'] = null;
    }

    return new WP_REST_Response($item, 200);
}
