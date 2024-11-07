<?php
// Não permita acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Registrar a taxonomia gma_campaign_category
function gma_register_campaign_category_taxonomy() {
    $labels = array(
        'name'              => _x('Categorias de Campanha', 'taxonomy general name', 'gma-plugin'),
        'singular_name'     => _x('Categoria de Campanha', 'taxonomy singular name', 'gma-plugin'),
        'search_items'      => __('Buscar Categorias', 'gma-plugin'),
        'all_items'         => __('Todas as Categorias', 'gma-plugin'),
        'parent_item'       => __('Categoria Pai', 'gma-plugin'),
        'parent_item_colon' => __('Categoria Pai:', 'gma-plugin'),
        'edit_item'         => __('Editar Categoria', 'gma-plugin'),
        'update_item'       => __('Atualizar Categoria', 'gma-plugin'),
        'add_new_item'      => __('Adicionar Nova Categoria', 'gma-plugin'),
        'new_item_name'     => __('Nome da Nova Categoria', 'gma-plugin'),
        'menu_name'         => __('Categorias de Campanha', 'gma-plugin'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'categoria-campanha'),
    );

    register_taxonomy('gma_campaign_category', 'gma_campaign', $args);
}
add_action('init', 'gma_register_campaign_category_taxonomy');
function gma_register_campaign_post_type() {
    $labels = array(
        'name'               => _x('Campanhas', 'post type general name', 'gma-plugin'),
        'singular_name'      => _x('Campanha', 'post type singular name', 'gma-plugin'),
        'menu_name'          => _x('Campanhas', 'admin menu', 'gma-plugin'),
        'name_admin_bar'     => _x('Campanha', 'add new on admin bar', 'gma-plugin'),
        'add_new'            => _x('Adicionar Nova', 'campanha', 'gma-plugin'),
        'add_new_item'       => __('Adicionar Nova Campanha', 'gma-plugin'),
        'new_item'           => __('Nova Campanha', 'gma-plugin'),
        'edit_item'          => __('Editar Campanha', 'gma-plugin'),
        'view_item'          => __('Ver Campanha', 'gma-plugin'),
        'all_items'          => __('Todas as Campanhas', 'gma-plugin'),
        'search_items'       => __('Buscar Campanhas', 'gma-plugin'),
        'parent_item_colon'  => __('Campanhas Pai:', 'gma-plugin'),
        'not_found'          => __('Nenhuma campanha encontrada.', 'gma-plugin'),
        'not_found_in_trash' => __('Nenhuma campanha encontrada na lixeira.', 'gma-plugin')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false, // Não mostrar no menu principal do WordPress
        'query_var'          => true,
        'rewrite'            => array('slug' => 'campanha'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
    );

    register_post_type('gma_campaign', $args);
}
add_action('init', 'gma_register_campaign_post_type');