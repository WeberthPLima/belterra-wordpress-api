<?php

$template_diretorio = get_template_directory();


add_action('init', 'register_portifolio_cpt');
function register_portifolio_cpt() {
    $labels = array(
        'name'                  => 'Portfólios',
        'singular_name'         => 'Portfólio',
        'menu_name'             => 'Portfólios',
        'name_admin_bar'        => 'Portfólio',
        'add_new'               => 'Adicionar novo',
        'add_new_item'          => 'Adicionar novo Portfólio',
        'new_item'              => 'Novo Portfólio',
        'edit_item'             => 'Editar Portfólio',
        'view_item'             => 'Ver Portfólio',
        'all_items'             => 'Todos os Portfólios',
        'search_items'          => 'Pesquisar Portfólios',
        'parent_item_colon'     => 'Portfólio pai:',
        'not_found'             => 'Nenhum Portfólio encontrado',
        'not_found_in_trash'    => 'Nenhum Portfólio encontrado na lixeira',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'rewrite'               => array(
            'slug'       => 'portifolio',
            'with_front' => false,
        ),
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-portfolio',
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'          => true,
        'rest_base'             => 'portifolio',
        'hierarchical'          => false,
    );

    register_post_type('portifolio', $args);
}


// --- Painel Lateral para Ordenação de Seções (Gutenberg) ---
// Registrar o meta field para a REST API
function register_portifolio_meta_rest() {
    register_post_meta('portifolio', '_portifolio_sections_order', array(
        'show_in_rest' => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string',
                ),
            ),
        ),
        'single'       => true,
        'type'         => 'array',
        'auth_callback' => function() { return current_user_can('edit_posts'); }
    ));
}
add_action('init', 'register_portifolio_meta_rest');

// Enfileirar script do editor
function portifolio_enqueue_block_editor_assets() {
    global $post_type;
    if ('portifolio' !== $post_type) {
        return;
    }

    wp_enqueue_script(
        'portifolio-order-sidebar',
        get_template_directory_uri() . '/js/portifolio-order.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose'),
        filemtime(get_template_directory() . '/js/portifolio-order.js'),
        true
    );

    $default_sections = array(
        "banner" => "Banner", 
        "introducao" => "Introdução", 
        "blocos_imagem_texto" => "Blocos Imagem Texto", 
        "proposito" => "Propósito", 
        "frentes" => "Frentes", 
        "texto_livre" => "Texto Livre", 
        "destaque" => "Destaque", 
        "destaque_grande" => "Destaque Grande", 
        "lista_check" => "Lista Check", 
        "cards_movimento" => "Cards Movimento", 
        "cards_beneficios" => "Cards Benefícios", 
        "texto_timeline" => "Texto Timeline", 
        "cards_eixos" => "Cards Eixos"
    );

    wp_localize_script('portifolio-order-sidebar', 'PortifolioData', array(
        'sections' => $default_sections
    ));
}
add_action('enqueue_block_editor_assets', 'portifolio_enqueue_block_editor_assets');

require_once get_template_directory() . '/API/porfifolio.php';
require_once get_template_directory() . '/API/contato.php';
require_once get_template_directory() . '/API/politicas.php';
require_once get_template_directory() . '/API/trabalheconosco.php';
require_once get_template_directory() . '/API/equipe.php';
require_once get_template_directory() . '/API/home.php';

?>