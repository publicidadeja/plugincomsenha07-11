<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function gma_criar_tabelas() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $tabela_campanhas = $wpdb->prefix . 'gma_campanhas';
    $tabela_materiais = $wpdb->prefix . 'gma_materiais';
    $tabela_categorias = $wpdb->prefix . 'gma_categorias';
    $tabela_estatisticas = $wpdb->prefix . 'gma_estatisticas';
    $tabela_downloads = $wpdb->prefix . 'gma_downloads';

    // SQL para criar a tabela de categorias
    $sql_categorias = "CREATE TABLE $tabela_categorias (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // SQL para criar a tabela de campanhas
    $sql_campanhas = "CREATE TABLE $tabela_campanhas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        cliente varchar(255) NOT NULL,
        categoria_id mediumint(9) DEFAULT NULL,
        data_criacao datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        tipo_campanha VARCHAR(255) NOT NULL DEFAULT 'marketing',
        status_campanha VARCHAR(255) NOT NULL DEFAULT 'rascunho',
        PRIMARY KEY (id),
        FOREIGN KEY (categoria_id) REFERENCES $tabela_categorias(id) ON DELETE SET NULL
    ) $charset_collate;";

    // SQL para criar a tabela de materiais com a coluna status, feedback e data_criacao
    $sql_materiais = "CREATE TABLE $tabela_materiais (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        imagem_url varchar(255) NOT NULL,
        copy text NOT NULL,
        link_canva varchar(255),
        arquivo_id bigint(20) unsigned DEFAULT NULL,
        status_aprovacao VARCHAR(20) NOT NULL DEFAULT 'pendente',
        feedback TEXT,
        data_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (campanha_id) REFERENCES $tabela_campanhas(id) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL para criar a tabela de estatisticas
    $sql_estatisticas = "CREATE TABLE $tabela_estatisticas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        visualizacoes bigint(20) NOT NULL DEFAULT 0,
        cliques bigint(20) NOT NULL DEFAULT 0,
        conversoes bigint(20) NOT NULL DEFAULT 0,
        data_visualizacao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (campanha_id) REFERENCES $tabela_campanhas(id) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL para criar a tabela de downloads
    $sql_downloads = "CREATE TABLE $tabela_downloads (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        material_id mediumint(9) NOT NULL,
        data_download datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (campanha_id) REFERENCES $tabela_campanhas(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES $tabela_materiais(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_categorias);
    dbDelta($sql_campanhas);
    dbDelta($sql_materiais);
    dbDelta($sql_estatisticas);
    dbDelta($sql_downloads);
}

function gma_create_license_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gma_licenses (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        license_key varchar(255) NOT NULL,
        expiration_date datetime NOT NULL,
        status varchar(20) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'gma_create_license_table');

function gma_atualizar_tabela_materiais() {
    global $wpdb;
    $tabela_materiais = $wpdb->prefix . 'gma_materiais';

    // Verificar se a coluna data_criacao já existe
    $coluna_existe = $wpdb->get_results("SHOW COLUMNS FROM $tabela_materiais LIKE 'data_criacao'");
    if (empty($coluna_existe)) {
        // Adicionar a coluna data_criacao se ela não existir
        $wpdb->query("ALTER TABLE $tabela_materiais ADD COLUMN data_criacao datetime DEFAULT CURRENT_TIMESTAMP");
        
        // Preencher a coluna data_criacao para registros existentes
        $wpdb->query("UPDATE $tabela_materiais SET data_criacao = NOW() WHERE data_criacao IS NULL");
    }
}

function gma_criar_tabela_licencas() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $tabela_licencas = $wpdb->prefix . 'gma_licencas';

    $sql = "CREATE TABLE IF NOT EXISTS $tabela_licencas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        codigo_licenca varchar(32) NOT NULL,
        tipo_licenca varchar(10) NOT NULL DEFAULT 'teste',
        data_ativacao datetime DEFAULT NULL,
        data_expiracao datetime DEFAULT NULL,
        site_url varchar(255) DEFAULT NULL,
        status varchar(20) DEFAULT 'inativo',
        PRIMARY KEY  (id),
        UNIQUE KEY codigo_licenca (codigo_licenca)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Função para verificar e criar tabelas se necessário
function gma_verificar_tabelas() {
    global $wpdb;
    $tabelas = [
        $wpdb->prefix . 'gma_campanhas',
        $wpdb->prefix . 'gma_materiais',
        $wpdb->prefix . 'gma_categorias',
        $wpdb->prefix . 'gma_estatisticas',
        $wpdb->prefix . 'gma_downloads'
    ];

    foreach ($tabelas as $tabela) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$tabela'") != $tabela) {
            gma_criar_tabelas();
            break;
        }
    }
}

// Chamar a função de verificação de tabelas
add_action('plugins_loaded', 'gma_verificar_tabelas');