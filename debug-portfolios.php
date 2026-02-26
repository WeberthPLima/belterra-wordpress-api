<?php
// Carrega o WordPress
define('WP_USE_THEMES', false);
require_once('./wp-load.php');

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG DE PORTFÓLIOS ===\n\n";

// 1. Listar TODOS os tipos de posts distintos no banco para ver se o slug está certo
global $wpdb;
$post_types = $wpdb->get_col("SELECT DISTINCT post_type FROM $wpdb->posts WHERE post_type LIKE '%port%' ORDER BY post_type");
echo "Tipos de post encontrados contendo 'port':\n";
print_r($post_types);
echo "\n";

// 2. Contar quantos posts existem para cada tipo encontrado
if (!empty($post_types)) {
    foreach ($post_types as $type) {
        $count_all = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", $type));
        $count_publish = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish'", $type));
        echo "Post Type: '$type' -> Total: $count_all (Publicados: $count_publish)\n";
        
        // Listar todos os posts desse tipo e seus status
        $all_posts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_status FROM $wpdb->posts WHERE post_type = %s", $type));
        foreach ($all_posts as $p) {
            echo "   -> ID: $p->ID | Status: $p->post_status | Título: $p->post_title\n";
        }
    }
} else {
    echo "Nenhum post type contendo 'port' foi encontrado no banco de dados.\n";
}

echo "\n=== TENTATIVA COM WP_QUERY (portifolio) ===\n";
$args = array(
    'post_type'      => 'portifolio',
    'post_status'    => 'any',
    'posts_per_page' => -1,
);
$query = new WP_Query($args);
echo "Query SQL Gerada:\n" . $query->request . "\n\n";
echo "Encontrados: " . $query->found_posts . "\n";

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        echo "- ID: " . get_the_ID() . " | Título: " . get_the_title() . " | Status: " . get_post_status() . "\n";
    }
} else {
    echo "Nenhum post encontrado via WP_Query com 'portifolio'.\n";
}

echo "\n=== TENTATIVA COM WP_QUERY (portfolio - inglês) ===\n";
$args = array(
    'post_type'      => 'portfolio',
    'post_status'    => 'any',
    'posts_per_page' => -1,
);
$query = new WP_Query($args);
echo "Query SQL Gerada:\n" . $query->request . "\n\n";
echo "Encontrados: " . $query->found_posts . "\n";

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        echo "- ID: " . get_the_ID() . " | Título: " . get_the_title() . " | Status: " . get_post_status() . "\n";
    }
} else {
    echo "Nenhum post encontrado via WP_Query com 'portfolio'.\n";
}
