<?php
// includes/admin-menu.php

if (!defined('ABSPATH')) {
    exit;
}

function gma_verificar_acesso_admin() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para acessar esta página.'));
    }

    $licenca_ativa = get_option('gma_licenca_ativa');
    if (!$licenca_ativa || !gma_verificar_licenca($licenca_ativa)) {
        wp_redirect(add_query_arg(
            array(
                'page' => 'gma-ativacao',
                'error' => 'licenca_invalida'
            ),
            admin_url('admin.php')
        ));
        exit;
    }
}

add_action('admin_menu', 'gma_criar_menu_admin');
function gma_exibir_pagina_ativacao() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para acessar esta página.'));
    }
    
    include GMA_PLUGIN_DIR . 'templates/pagina-ativacao.php';
}

function gma_criar_menu_admin() {
    // Adicionando a página principal do plugin
    add_menu_page(
        'Gerenciador de Marketing Avançado', // Título da página
        'BrandAI', // Nome do menu
        'manage_options', // Capacidade de acesso
        'gma-plugin', // Slug da página
        'gma_pagina_principal', // Função a ser chamada
        'dashicons-image-filter', // Ícone do menu
        30 // Posição do menu
    );

    // Criando submenus
    $submenus = array(
        array('Campanhas', 'gma-campanhas', 'gma_pagina_campanhas'),
        array('Editar Campanha', 'gma-editar-campanha', 'gma_pagina_editar_campanha'),
        array('Materiais', 'gma-materiais', 'gma_pagina_listar_materiais'),
        array('Novo Material', 'gma-novo-material', 'gma_pagina_novo_material'),
        array('Editar Material', 'gma-editar-material', 'gma_pagina_editar_material'),
        array('Categorias', 'gma-criar-categoria', 'gma_pagina_criar_categoria'),
        array('Relatório', 'gma-relatorio-campanhas', 'gma_pagina_relatorio_campanhas'),
    );

    foreach ($submenus as $submenu) {
        add_submenu_page(
            'gma-plugin',
            $submenu[0],
            $submenu[0],
            'manage_options',
            $submenu[1],
            $submenu[2]
        );
    }
}

// Funções para exibir as páginas
function gma_pagina_principal() {
  gma_verificar_acesso_admin(); // Adicione esta linha
    $notificacao = get_transient('gma_notificacao_admin');
  // Verificar se existe uma notificação
    $notificacao = get_transient('gma_notificacao_admin');

    if ($notificacao) {
        // Exibir a notificação
        echo '<div class="notice notice-' . $notificacao['tipo'] . ' is-dismissible"><p>' . $notificacao['mensagem'] . '</p></div>';
        
        // Apagar a notificação para que ela não seja exibida novamente
        delete_transient('gma_notificacao_admin'); 
    }
    include GMA_PLUGIN_DIR . 'templates/pagina-principal.php';
}

function gma_pagina_campanhas() {
  gma_verificar_acesso_admin();
    global $wpdb;
    if (isset($_POST['criar_campanha'])) {
        $nome = sanitize_text_field($_POST['nome_campanha']);
        $cliente = sanitize_text_field($_POST['cliente_campanha']);
        $categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
        $tipo_campanha = isset($_POST['tipo_campanha']) ? sanitize_text_field($_POST['tipo_campanha']) : 'marketing';
        $campanha_id = gma_criar_campanha($nome, $cliente, $categoria_id, $tipo_campanha);
        wp_redirect(admin_url('admin.php?page=gma-campanhas'));
        exit;
    }

    $campanhas = isset($_GET['categoria']) ? 
        $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gma_campanhas WHERE categoria_id = %d ORDER BY data_criacao DESC", intval($_GET['categoria']))) :
        gma_listar_campanhas();

    include GMA_PLUGIN_DIR . 'templates/pagina-campanhas.php';
}

function gma_pagina_nova_campanha() {
  gma_verificar_acesso_admin();
    // Implementar lógica para criar nova campanha
    include GMA_PLUGIN_DIR . 'templates/pagina-nova-campanha.php';
}

function gma_pagina_editar_campanha() {
    if (isset($_GET['campanha_id'])) {
        $campanha_id = intval($_GET['campanha_id']);
        $campanha = gma_obter_campanha($campanha_id);

        if ($campanha) {
            include GMA_PLUGIN_DIR . 'templates/pagina-editar-campanha.php';
        } else {
            echo '<div class="notice notice-error"><p>Campanha não encontrada.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>ID da campanha não fornecido.</p></div>';
    }
}

function gma_pagina_listar_materiais() {
  gma_verificar_acesso_admin();
  // Verificar se existe uma notificação
    $notificacao = get_transient('gma_notificacao_admin');

    if ($notificacao) {
        // Exibir a notificação
        echo '<div class="notice notice-' . $notificacao['tipo'] . ' is-dismissible"><p>' . $notificacao['mensagem'] . '</p></div>';
        
        // Apagar a notificação para que ela não seja exibida novamente
        delete_transient('gma_notificacao_admin'); 
    }
    global $wpdb;
    $tabela_materiais = $wpdb->prefix . 'gma_materiais';
    $tabela_campanhas = $wpdb->prefix . 'gma_campanhas';

    $periodo = isset($_GET['periodo']) ? sanitize_text_field($_GET['periodo']) : 'mes';

    $materiais = $wpdb->get_results("
    SELECT m.*, c.nome AS nome_campanha, c.tipo_campanha
    FROM $tabela_materiais m
    LEFT JOIN $tabela_campanhas c ON m.campanha_id = c.id
    ORDER BY m.data_criacao DESC
    ");

    include GMA_PLUGIN_DIR . 'templates/pagina-listar-materiais.php';
}

function gma_pagina_novo_material() {
  gma_verificar_acesso_admin();
    if (!session_id()) {
        session_start();
    }

    require_once GMA_PLUGIN_DIR . 'includes/materiais.php';

    $messages = array();

    if (isset($_POST['criar_material']) && wp_verify_nonce($_POST['gma_novo_material_nonce'], 'gma_novo_material')) {
        if (!isset($_SESSION['material_criado'])) {
            $campanha_id = intval($_POST['campanha_id']);
            $imagem_url = esc_url_raw($_POST['imagem_url']);
            $copy = wp_kses_post($_POST['copy']);
            $link_canva = esc_url_raw($_POST['link_canva']);
            $arquivo_id = isset($_POST['arquivo_id']) ? intval($_POST['arquivo_id']) : null;

            $resultado = gma_criar_material($campanha_id, $imagem_url, $copy, $link_canva, $arquivo_id);

            if ($resultado) {
                $_SESSION['material_criado'] = true;
                $messages[] = array('type' => 'success', 'message' => 'Material criado com sucesso!');
            } else {
                $messages[] = array('type' => 'error', 'message' => 'Erro ao criar o material. Por favor, tente novamente. Verifique o log de erros.');
            }
        } else {
            $messages[] = array('type' => 'warning', 'message' => 'Material já criado. Atualize a página.');
        }
    }

    unset($_SESSION['material_criado']);

    foreach ($messages as $message) {
        echo '<div class="notice notice-' . $message['type'] . ' is-dismissible"><p>' . $message['message'] . '</p></div>';
    }

    $campanhas = gma_listar_campanhas();
    include GMA_PLUGIN_DIR . 'templates/pagina-novo-material.php';
}

function gma_pagina_editar_material() {
  gma_verificar_acesso_admin();
    if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
        wp_die('Parâmetros inválidos.');
    }

    $material_id = intval($_GET['id']);
    $tipo_campanha = sanitize_text_field($_GET['tipo']);

    $material = gma_obter_material($material_id);

    if (!$material) {
        wp_die('Material não encontrado.');
    }

    $campanha = gma_obter_campanha($material->campanha_id);
    if ($campanha->tipo_campanha !== $tipo_campanha) {
        wp_die('Tipo de campanha não corresponde ao material.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tipo_campanha === 'aprovacao' && isset($_POST['atualizar_material_aprovacao'])) {
        check_admin_referer('editar_material_aprovacao', 'gma_nonce');
        $status = sanitize_text_field($_POST['status_aprovacao']);
        $feedback = sanitize_textarea_field($_POST['feedback']);
        $copy = isset($_POST['copy']) ? sanitize_textarea_field($_POST['copy']) : ''; // Adicione esta linha
        $imagem_url = isset($_POST['imagem_url']) ? esc_url_raw($_POST['imagem_url']) : $material->imagem_url;
        
        $resultado = gma_atualizar_material_aprovacao($material_id, $status, $feedback, $copy, $imagem_url);

        if ($resultado) {
            add_settings_error('gma_messages', 'gma_message', 'Material de aprovação atualizado com sucesso.', 'updated');
            $material = gma_obter_material($material_id);
        } else {
            add_settings_error('gma_messages', 'gma_message', 'Erro ao atualizar o material de aprovação.', 'error');
        }
        } elseif ($tipo_campanha === 'marketing' && isset($_POST['atualizar_material_marketing'])) {
            check_admin_referer('editar_material_marketing', 'gma_nonce');
            $copy = sanitize_textarea_field($_POST['copy']);
            $link_canva = esc_url_raw($_POST['link_canva']);
            $resultado = gma_atualizar_material_marketing($material_id, $copy, $link_canva);
            if ($resultado) {
                add_settings_error('gma_messages', 'gma_message', 'Material de marketing atualizado com sucesso.', 'updated');
            } else {
                add_settings_error('gma_messages', 'gma_message', 'Erro ao atualizar o material de marketing.', 'error');
            }
        }
        
        $material = gma_obter_material($material_id);
    }

    settings_errors('gma_messages');

    if ($tipo_campanha === 'aprovacao') {
        include GMA_PLUGIN_DIR . 'templates/editar-material-aprovacao.php';
    } else {
        include GMA_PLUGIN_DIR . 'templates/editar-material-marketing.php';
    }
}

function gma_pagina_listar_categorias() {
  gma_verificar_acesso_admin();
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para acessar esta página.'));
    }

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && isset($_POST['category_id'])) {
            check_admin_referer('delete_category', 'gma_nonce');
            $category_id = intval($_POST['category_id']);
            if (gma_excluir_categoria($category_id)) {
                wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=gma-categorias')));
                exit;
            } else {
                add_settings_error('gma_messages', 'category_delete_error', 'Erro ao excluir a categoria.', 'error');
            }
        } elseif ($_POST['action'] === 'edit' && isset($_POST['category_id']) && isset($_POST['new_name'])) {
            check_admin_referer('edit_category', 'gma_nonce');
            $category_id = intval($_POST['category_id']);
            $new_name = sanitize_text_field($_POST['new_name']);
            if (gma_atualizar_categoria($category_id, $new_name)) {
                wp_redirect(add_query_arg('message', 'updated', admin_url('admin.php?page=gma-categorias')));
                exit;
            } else {
                add_settings_error('gma_messages', 'category_update_error', 'Erro ao atualizar a categoria.', 'error');
            }
        }
    }

    include GMA_PLUGIN_DIR . 'templates/pagina-listar-categoria.php';
}

function gma_pagina_criar_categoria() {
  gma_verificar_acesso_admin();
    include GMA_PLUGIN_DIR . 'templates/pagina-criar-categoria.php';
}

function gma_pagina_relatorio_campanhas() {
  gma_verificar_acesso_admin();
    include GMA_PLUGIN_DIR . 'templates/pagina-relatorio-campanhas.php';
}

function gma_atualizar_categoria($id, $novo_nome) {
  gma_verificar_acesso_admin();
    global $wpdb;
    $table_name = $wpdb->prefix . 'gma_categorias';
    
    $result = $wpdb->update(
        $table_name,
        array('nome' => $novo_nome),
        array('id' => $id),
        array('%s'),
        array('%d')
    );

    if ($result === false) {
        error_log("Erro ao atualizar categoria: " . $wpdb->last_error);
        return false;
    }

    return true;
}

function gma_excluir_categoria($id) {
  gma_verificar_acesso_admin();
    global $wpdb;
    $table_name = $wpdb->prefix . 'gma_categorias';
    
    $result = $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );

    if ($result === false) {
        error_log("Erro ao excluir categoria: " . $wpdb->last_error);
        return false;
    }

    return true;
}