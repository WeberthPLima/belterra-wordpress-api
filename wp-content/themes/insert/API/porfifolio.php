<?php

function register_portfolio_api_routes() {
    register_rest_route('api', '/interna/portifolios/all', array(
        'methods'  => 'GET',
        'callback' => 'get_all_portfolios',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('api', '/interna/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods'  => 'GET',
        'callback' => 'get_portfolio_by_slug',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_portfolio_api_routes');

function get_portfolio_by_slug($request) {
    $slug = $request['slug'];

    $args = array(
        'name'           => $slug,
        'post_type'      => 'portifolio',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return new WP_Error('no_portfolio', 'Portfólio não encontrado', array('status' => 404));
    }

    $query->the_post();
    
    $item = format_portfolio_item();

    wp_reset_postdata();

    return new WP_REST_Response($item, 200);
}

function get_all_portfolios($request) {
    $args = array(
        'post_type'      => 'portifolio',
        'post_status'    => 'publish',
        'posts_per_page' => -1, 
    );

    $query = new WP_Query($args);
    $portfolios = [];

    if (!$query->have_posts()) {
        $args['post_type'] = 'portfolio';
        $query = new WP_Query($args);
    }

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $portfolios[] = format_portfolio_item();
        }
    }
    
    wp_reset_postdata();

    return new WP_REST_Response($portfolios, 200);
}

function format_portfolio_item() {
    $post_id = get_the_ID();
    
    $acf_fields = function_exists('get_fields') ? get_fields($post_id) : null;
    
    $sections_order = get_post_meta($post_id, '_portifolio_sections_order', true);

    if ( ! is_array( $sections_order ) ) {
        $sections_order = array();
    }

    $sections_order = array_values( array_diff( $sections_order, array( 'banner' ) ) );
    array_unshift( $sections_order, 'banner' );

    $item = array(
        'id'       => $post_id,
        'title'    => get_the_title(),
        'slug'     => get_post_field('post_name', $post_id),
        'content'  => get_the_content(),
        'excerpt'  => get_the_excerpt(),
        'date'     => get_the_date('Y-m-d H:i:s'),
        'acf'      => $acf_fields ? $acf_fields : new stdClass(),
        'sections_order' => $sections_order ? $sections_order : array(),
    );

    if (has_post_thumbnail()) {
        $item['featured_image'] = get_the_post_thumbnail_url($post_id, 'full');
    } else {
        $item['featured_image'] = null;
    }
    
    return $item;
}
