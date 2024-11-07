<?php

// includes/campanhas.php

// ... (outros includes, se houver)

function gma_criar_campanha($nome, $cliente, $categoria_id = null, $tipo_campanha = 'marketing', $status_campanha = 'rascunho') {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_campanhas';

    // Preparar os dados
    $dados = array(
        'nome' => $nome,
        'cliente' => $cliente,
        'tipo_campanha' => $tipo_campanha,
        'status_campanha' => $status_campanha
    );

    // Preparar os formatos
    $formatos = array('%s', '%s', '%s', '%s');

    // Adicionar categoria_id apenas se não for nulo
    if ($categoria_id !== null && $categoria_id !== '') {
        $dados['categoria_id'] = $categoria_id;
        $formatos[] = '%d';
    }

    $resultado = $wpdb->insert(
        $tabela,
        $dados,
        $formatos
    );

    if ($resultado === false) {
        error_log("Erro ao inserir campanha: " . $wpdb->last_error);
        return false;
    }

    return $wpdb->insert_id;
}

function gma_listar_campanhas($categoria_filtro = null) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_campanhas';
    $query = "SELECT * FROM $tabela";
    if ($categoria_filtro !== null) {
        $query .= " WHERE categoria_id = $categoria_filtro";
    }
    $query .= " ORDER BY data_criacao DESC";
    return $wpdb->get_results($query);
}

function gma_obter_campanha($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gma_campanhas';
    $campanha = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    return $campanha;
}

function gma_atualizar_campanha($id, $nome, $cliente, $categoria_id, $tipo_campanha) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_campanhas';
    
    $dados = array(
        'nome' => $nome,
        'cliente' => $cliente,
        'tipo_campanha' => $tipo_campanha
    );
    
    $tipos = array('%s', '%s', '%s');
    
    if ($categoria_id !== null) {
        $dados['categoria_id'] = $categoria_id;
        $tipos[] = '%d';
    } else {
        $dados['categoria_id'] = null;
        $tipos[] = null;
    }
    
    $resultado = $wpdb->update(
        $tabela,
        $dados,
        array('id' => $id),
        $tipos,
        array('%d')
    );
    
    if ($resultado === false) {
        error_log("Erro ao atualizar campanha: " . $wpdb->last_error);
        return false;
    }
    
    return true;
}
// --- Funções para gerenciar categorias ---

function gma_criar_categoria($nome) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_categorias';
    $nome = sanitize_text_field($nome);
    $categoria_existente = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabela WHERE nome = %s", $nome));
    if ($categoria_existente) {
        return false;
    }
    $wpdb->insert($tabela, array('nome' => $nome));
    return $wpdb->insert_id;
}

function gma_editar_categoria($id, $nome) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_categorias';
    
    // Verificar se já existe uma categoria com este nome
    $categoria_existente = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tabela WHERE nome = %s AND id != %d",
        $nome,
        $id
    ));
    
    if ($categoria_existente) {
        return false; // Já existe uma categoria com este nome
    }
    
    $resultado = $wpdb->update(
        $tabela,
        array('nome' => $nome),
        array('id' => $id),
        array('%s'),
        array('%d')
    );
    
    return $resultado !== false;
}



function gma_listar_categorias() {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_categorias';
    return $wpdb->get_results("SELECT * FROM $tabela ORDER BY nome ASC");
}

function gma_obter_categoria($id) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_categorias';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabela WHERE id = %d", $id));
}
// --- Fim das funções para gerenciar categorias ---

// Função para atualizar o status do material
function gma_atualizar_status_material() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');

    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
    $novo_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    if ($material_id && $novo_status) {
        global $wpdb;
        $tabela = $wpdb->prefix . 'gma_materiais';

        $resultado = $wpdb->update(
            $tabela,
            array('status_aprovacao' => $novo_status),
            array('id' => $material_id)
        );

        if ($resultado !== false) {
            wp_send_json_success(array('message' => 'Status atualizado com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao atualizar o status.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Dados inválidos.'));
    }

    wp_die();
}


function gma_listar_campanhas_por_tipo($tipo) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_campanhas';
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM $tabela WHERE tipo_campanha = %s ORDER BY data_criacao DESC", $tipo));
}
// Função para gerar nonce para a atualização do status
function gma_criar_nonce() {
    // Gera um nonce
    $nonce = wp_create_nonce('gma_ajax_nonce');
    return $nonce;
}

// Registra a ação para processar requisições AJAX
add_action('wp_ajax_gma_atualizar_status_material', 'gma_atualizar_status_material');
add_action('wp_ajax_nopriv_gma_atualizar_status_material', 'gma_atualizar_status_material'); // Se necessário

// Função para excluir uma campanha
function gma_excluir_campanha() {
    global $wpdb;

    // Verificar o nonce
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'gma_excluir_campanha')) {
        wp_die('Erro de segurança: nonce inválido!');
    }

    // Obter o ID da campanha da URL
    $campanha_id = isset($_GET['campanha_id']) ? intval($_GET['campanha_id']) : 0;

    // Excluir a campanha do banco de dados usando o ID correto
    $wpdb->delete($wpdb->prefix . 'gma_campanhas', array('id' => $campanha_id));

    // Redirecionar de volta para a página de listagem de campanhas com o parâmetro success=true
    wp_redirect(admin_url('admin.php?page=gma-campanhas&success=true'));
    exit;
}



// Registrar a ação 'admin_post_gma_excluir_campanha'
add_action('admin_post_gma_excluir_campanha', 'gma_excluir_campanha');

?>