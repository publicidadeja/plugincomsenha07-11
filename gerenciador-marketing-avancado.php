<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
Plugin Name: BrandAI - Gerenciador de Marketing Avançado
Plugin URI: https://publicidadeja.com.br
Description: Um plugin avançado para gerenciar campanhas de marketing e materiais.
Version: 1.2.5
Author: Publicidade Já
Author URI: https://publicidadeja.com.br/
Text Domain: gma-plugin
*/

// Ativar relatório de erros (apenas para desenvolvimento)
if (defined('WP_DEBUG') && WP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('GMA_VERSION', '1.2.1');
define('GMA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GMA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir arquivos necessários
require_once GMA_PLUGIN_DIR . 'includes/database.php';
require_once GMA_PLUGIN_DIR . 'includes/admin-menu.php';
require_once GMA_PLUGIN_DIR . 'includes/openai-config.php';
require_once GMA_PLUGIN_DIR . 'includes/campanhas.php';
require_once GMA_PLUGIN_DIR . 'includes/materiais.php';
require_once GMA_PLUGIN_DIR . 'includes/estatisticas.php';
require_once GMA_PLUGIN_DIR . 'includes/admin-editar-material.php';
require_once GMA_PLUGIN_DIR . 'includes/taxonomies.php';


register_activation_hook(__FILE__, 'gma_ativar_plugin');


function gma_ativar_plugin() {
    gma_criar_tabelas();
    gma_criar_tabela_estatisticas();
    create_logs_table();
    gma_criar_tabela_licencas();

    global $wpdb;
    $tabela_campanhas = $wpdb->prefix . 'gma_campanhas';
    $coluna_existe = $wpdb->get_results("SHOW COLUMNS FROM $tabela_campanhas LIKE 'tipo_campanha'");
    if (empty($coluna_existe)) {
        $wpdb->query("ALTER TABLE $tabela_campanhas ADD COLUMN tipo_campanha VARCHAR(255) NOT NULL DEFAULT 'marketing'");
    }

    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'gma_desativar_plugin');

function gma_desativar_plugin() {
    flush_rewrite_rules();
}


function gma_enqueue_admin_assets($hook) {
    $gma_pages = array(
        'toplevel_page_gma-plugin',
        'marketing_page_gma-editar-campanha',
        'marketing_page_gma-editar-material',
        'marketing_page_gma-novo-material',
        'marketing_page_gma-listar-materiais',
        'marketing_page_gma-estatisticas'
    );

    if (in_array($hook, $gma_pages)) {
        // CSS Admin
        wp_enqueue_style(
            'gma-admin-style',
            plugins_url('/gerenciador-marketing-avancado/assets/css/admin-style.css'),
            array(),
            GMA_VERSION
        );

      
      
        // JS Admin
        wp_enqueue_script(
            'gma-admin-script',
            plugins_url('/gerenciador-marketing-avancado/assets/js/admin-script.js'),
            array('jquery'),
            GMA_VERSION,
            true
        );

        // Adicione este bloco para localizar o script
        wp_localize_script('gma-admin-script', 'gmaData', array(
            'pluginUrl' => GMA_PLUGIN_URL,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gma_ajax_nonce'),
            'wpMediaTitle' => __('Escolha ou faça upload de uma imagem', 'gma-plugin'),
            'wpMediaButton' => __('Usar esta imagem', 'gma-plugin'),
            'soundUrl' => GMA_PLUGIN_URL . 'assets/sounds/notification.mp3'
        ));
    }
}
add_action('admin_enqueue_scripts', 'gma_enqueue_admin_assets');


// Adicionar variáveis de consulta personalizadas
function gma_query_vars($query_vars) {
    $query_vars[] = 'campanha_id';
    $query_vars[] = 'campanha_aprovacao';
    return $query_vars;
}
add_filter('query_vars', 'gma_query_vars');

// Regras de reescrita
function gma_rewrite_rules() {
    add_rewrite_rule('^campanha/([0-9]+)/?$', 'index.php?campanha_id=$matches[1]', 'top');
    add_rewrite_rule('^campanha-aprovacao/([0-9]+)/?$', 'index.php?campanha_id=$matches[1]&campanha_aprovacao=1', 'top');
}
add_action('init', 'gma_rewrite_rules');

// Enfileirar estilos e scripts para o frontend
function gma_enqueue_frontend_assets() {
    wp_enqueue_style('gma-frontend-style', GMA_PLUGIN_URL . 'assets/css/frontend-style.css', array(), GMA_VERSION);
    wp_enqueue_style('gma-public-style', GMA_PLUGIN_URL . 'assets/css/public.css', array(), GMA_VERSION);
    
    wp_enqueue_script('gma-frontend-script', GMA_PLUGIN_URL . 'assets/js/frontend-script.js', array('jquery'), GMA_VERSION, true);
    wp_enqueue_script('gma-public', GMA_PLUGIN_URL . 'assets/js/public.js', array('jquery'), GMA_VERSION, true);
    
    if (get_query_var('campanha_id')) {
        wp_enqueue_style('single-campanha-style', GMA_PLUGIN_URL . 'assets/css/single-campanha-style.css', array(), GMA_VERSION);
        wp_enqueue_script('gma-single-campanha', GMA_PLUGIN_URL . 'assets/js/gma-single-campanha.js', array('jquery'), GMA_VERSION, true);
    }

    wp_enqueue_script('gma-ajax', GMA_PLUGIN_URL . 'assets/js/gma-ajax.js', array('jquery'), GMA_VERSION, true);
    wp_localize_script('gma-ajax', 'gmaAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gma_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'gma_enqueue_frontend_assets');



// Usar template personalizado para exibição de campanha
function gma_custom_template($template) {
    $campanha_id = get_query_var('campanha_id');
    if ($campanha_id !== '' && $campanha_id !== false) {
        $campanha = gma_obter_campanha($campanha_id);

        if ($campanha && isset($campanha->tipo_campanha)) {
            $template_name = $campanha->tipo_campanha === 'aprovacao' ? 'single-campanha-aprovacao.php' : 'single-campanha.php';
            $new_template = GMA_PLUGIN_DIR . 'templates/' . $template_name;
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
    }
    return $template;
}
add_filter('template_include', 'gma_custom_template', 99);

// Funções AJAX
function gma_atualizar_clique() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    $campanha_id = isset($_POST['campanha_id']) ? intval($_POST['campanha_id']) : 0;
    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;

    if ($campanha_id > 0 && $material_id > 0) {
        gma_atualizar_estatistica($campanha_id, 'cliques');
        wp_send_json_success(array('message' => 'Clique registrado com sucesso!'));
    } else {
        wp_send_json_error(array('message' => 'ID da campanha ou do material inválido.'));
    }
}
add_action('wp_ajax_gma_atualizar_clique', 'gma_atualizar_clique');
add_action('wp_ajax_nopriv_gma_atualizar_clique', 'gma_atualizar_clique');

// Função para salvar feedback e edição de materiais
function gma_salvar_feedback_e_edicao() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');

    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
    $feedback_arte = isset($_POST['feedback_arte']) ? sanitize_textarea_field($_POST['feedback_arte']) : '';
    $copy_editada = isset($_POST['copy_editada']) ? sanitize_textarea_field($_POST['copy_editada']) : '';

    if ($material_id > 0 && ($feedback_arte !== '' || $copy_editada !== '')) {
        global $wpdb;
        $tabela = $wpdb->prefix . 'gma_materiais';

        $dados_atualizacao = array(
            'feedback' => $feedback_arte,
            'copy' => $copy_editada,
            'status_aprovacao' => 'pendente'
        );

        $resultado = $wpdb->update($tabela, $dados_atualizacao, array('id' => $material_id), array('%s', '%s', '%s'), array('%d'));

        if ($resultado !== false) {
            wp_send_json_success(array('message' => 'Feedback e edição salvos com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao salvar feedback e edição.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Dados inválidos.'));
    }
}
add_action('wp_ajax_gma_salvar_feedback_e_edicao', 'gma_salvar_feedback_e_edicao');
add_action('wp_ajax_nopriv_gma_salvar_feedback_e_edicao', 'gma_salvar_feedback_e_edicao');

// Ações AJAX adicionais
add_action('wp_ajax_gma_atualizar_status_material', 'gma_atualizar_status_material');
add_action('wp_ajax_nopriv_gma_atualizar_status_material', 'gma_atualizar_status_material');
add_action('wp_ajax_gma_salvar_feedback', 'gma_salvar_feedback');
add_action('wp_ajax_nopriv_gma_salvar_feedback', 'gma_salvar_feedback');

// Inicializar o plugin
function gma_init_plugin() {
    load_plugin_textdomain('gma-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'gma_init_plugin');

// Handler para a ação de exclusão de material
function gma_excluir_material_handler() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para realizar esta ação.'));
    }

    $material_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (!$material_id) {
        wp_die(__('ID de material inválido.'));
    }

    check_admin_referer('gma_excluir_material_' . $material_id, 'gma_nonce');

    if (gma_excluir_material($material_id)) {
        wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=gma-listar-materiais')));
    } else {
        wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=gma-listar-materiais')));
    }
    exit;
}
add_action('admin_post_gma_excluir_material', 'gma_excluir_material_handler');

// Registrar mudança de status de uma campanha
add_action('transition_post_status', 'monitor_campaign_status', 10, 3);

function monitor_campaign_status($new_status, $old_status, $post) {
    if ($post->post_type == 'campaign') {
        if ($new_status === 'approved' && $old_status !== 'approved') {
            log_event('Campanha aprovada: ' . $post->ID);
        } elseif ($new_status === 'rejected' && $old_status !== 'rejected') {
            log_event('Campanha reprovada: ' . $post->ID);
        } elseif ($new_status !== $old_status) {
            log_event('Campanha editada: ' . $post->ID);
        }
    }
}

// Lógica para salvar eventos de log
if (!function_exists('log_event')) {
    function log_event($message) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaign_logs';

        $wpdb->insert(
            $table_name,
            array(
                'event' => $message
            )
        );

        if ($wpdb->last_error) {
            error_log('DB Log Error: ' . $wpdb->last_error);
        } else {
            error_log('Log inserido com sucesso: ' . $message);
        }
    }
}


// Função para processar as sugestões da IA
add_action('wp_ajax_gma_get_copy_suggestions', 'gma_get_copy_suggestions_callback');
function gma_get_copy_suggestions_callback() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    
    $copy = sanitize_textarea_field($_POST['copy']);
    
    // Aqui você deve implementar a chamada para sua API de IA
    // Este é apenas um exemplo de resposta
    $response = array(
        'success' => true,
        'data' => array(
            'suggestions' => 'Aqui virão as sugestões da IA para o texto: ' . $copy
        )
    );
    
    wp_send_json($response);
}

// Adiciona um widget personalizado ao dashboard do WordPress
add_action('wp_dashboard_setup', 'add_custom_dashboard_widget');

function add_custom_dashboard_widget() {
    wp_add_dashboard_widget(
        'custom_notifications',
        'Notificações de Campanha',
        'display_campaign_notifications'
    );
}

if (!function_exists('display_campaign_notifications')) {
    function display_campaign_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaign_logs';
        
        $logs = $wpdb->get_results("SELECT time, event FROM $table_name ORDER BY time DESC LIMIT 20");

        if (!empty($logs)) {
    echo '<ul>';
    foreach ($logs as $log) {
        echo '<li>' . esc_html($log->event) . ' em ' . esc_html($log->time) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>Sem notificações no momento</p>';
}
    }
}

// Função para criar tabela ao ativar o plugin
function create_logs_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'campaign_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        event text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Configuração do cron para limpar logs antigos diariamente
add_action('init', 'setup_cleanup_schedule');

function setup_cleanup_schedule() {
    if (!wp_next_scheduled('cleanup_old_logs')) {
        wp_schedule_event(time(), 'daily', 'cleanup_old_logs');
    }
}

add_action('cleanup_old_logs', 'remove_old_logs');

function remove_old_logs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'campaign_logs';

    $wpdb->query(
        "DELETE FROM $table_name WHERE time < NOW() - INTERVAL 30 DAY"
    );
}

// Shortcode para exibir campanhas
function gma_campanha_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'gma_campanha');

    $campanha_id = intval($atts['id']);
    if ($campanha_id > 0) {
        ob_start();
        include(GMA_PLUGIN_DIR . 'templates/shortcode-campanha.php');
        return ob_get_clean();
    }
    return '';
}
add_shortcode('gma_campanha', 'gma_campanha_shortcode');

// Função para registrar os scripts e estilos globalmente
function gma_register_global_assets() {
    wp_register_style('gma-global-style', GMA_PLUGIN_URL . 'assets/css/global-style.css', array(), GMA_VERSION);
    wp_register_script('gma-global-script', GMA_PLUGIN_URL . 'assets/js/global-script.js', array('jquery'), GMA_VERSION, true);
}
add_action('init', 'gma_register_global_assets');

// Função para enfileirar os assets globais quando necessário
function gma_enqueue_global_assets() {
    wp_enqueue_style('gma-global-style');
    wp_enqueue_script('gma-global-script');
}

// Adicionar suporte para thumbnails se ainda não estiver ativado
function gma_add_thumbnail_support() {
    if (!current_theme_supports('post-thumbnails')) {
        add_theme_support('post-thumbnails');
    }
}
add_action('after_setup_theme', 'gma_add_thumbnail_support');

// Adicionar meta box para informações adicionais da campanha
function gma_add_campaign_meta_box() {
    add_meta_box(
        'gma_campaign_info',
        __('Informações da Campanha', 'gma-plugin'),
        'gma_campaign_meta_box_callback',
        'gma_campaign',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'gma_add_campaign_meta_box');

function gma_campaign_meta_box_callback($post) {
    wp_nonce_field('gma_save_campaign_meta', 'gma_campaign_meta_nonce');
    $campaign_type = get_post_meta($post->ID, '_gma_campaign_type', true);
    ?>
    <p>
        <label for="gma_campaign_type"><?php _e('Tipo de Campanha:', 'gma-plugin'); ?></label>
        <select name="gma_campaign_type" id="gma_campaign_type">
            <option value="marketing" <?php selected($campaign_type, 'marketing'); ?>><?php _e('Marketing', 'gma-plugin'); ?></option>
            <option value="aprovacao" <?php selected($campaign_type, 'aprovacao'); ?>><?php _e('Aprovação', 'gma-plugin'); ?></option>
        </select>
    </p>
    <?php
}

function gma_save_campaign_meta($post_id) {
    if (!isset($_POST['gma_campaign_meta_nonce']) || !wp_verify_nonce($_POST['gma_campaign_meta_nonce'], 'gma_save_campaign_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['gma_campaign_type'])) {
        update_post_meta($post_id, '_gma_campaign_type', sanitize_text_field($_POST['gma_campaign_type']));
    }
}
add_action('save_post_gma_campaign', 'gma_save_campaign_meta');

// Adicionar página de configurações do plugin
function gma_add_settings_page() {
    add_submenu_page(
        'gma-plugin',
        __('Configurações do GMA', 'gma-plugin'),
        __('Configurações', 'gma-plugin'),
        'manage_options',
        'gma-settings',
        'gma_settings_page_callback'
    );
}
add_action('admin_menu', 'gma_add_settings_page');

function gma_settings_page_callback() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('gma_settings');
            do_settings_sections('gma-settings');
            submit_button('Salvar Configurações');
            ?>
        </form>
    </div>
    <?php
}

// Registrar configurações
function gma_register_settings() {
    register_setting('gma_settings', 'gma_default_campaign_type');

    add_settings_section(
        'gma_general_settings',
        __('Configurações Gerais', 'gma-plugin'),
        'gma_general_settings_callback',
        'gma-settings'
    );

    add_settings_field(
        'gma_default_campaign_type',
        __('Tipo de Campanha Padrão', 'gma-plugin'),
        'gma_default_campaign_type_callback',
        'gma-settings',
        'gma_general_settings'
    );
}
add_action('admin_init', 'gma_register_settings');

function gma_general_settings_callback() {
    echo '<p>' . __('Configurações gerais para o Gerenciador de Marketing Avançado.', 'gma-plugin') . '</p>';
}

function gma_default_campaign_type_callback() {
    $option = get_option('gma_default_campaign_type');
    ?>
    <select name="gma_default_campaign_type">
        <option value="marketing" <?php selected($option, 'marketing'); ?>><?php _e('Marketing', 'gma-plugin'); ?></option>
        <option value="aprovacao" <?php selected($option, 'aprovacao'); ?>><?php _e('Aprovação', 'gma-plugin'); ?></option>
    </select>
    <?php
}


// Fim do arquivo

function gma_adicionar_pagina_licenca() {
    add_submenu_page(
        'gma-plugin',
        'Ativação do Plugin',
        'Ativação',
        'manage_options',
        'gma-ativacao',
        'gma_exibir_pagina_ativacao'
    );
}
add_action('admin_menu', 'gma_adicionar_pagina_licenca');



function gma_verificar_licenca($codigo) {
    global $wpdb;
    $tabela_licencas = $wpdb->prefix . 'gma_licencas';
    $site_atual = get_site_url();
    
    $licenca = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tabela_licencas WHERE codigo_licenca = %s AND status = 'ativo'",
        $codigo
    ));

    if (!$licenca) {
        return false;
    }

    // Verifica se está no site correto
    if ($licenca->site_url !== $site_atual) {
        return false;
    }

    // Verifica se não expirou
    if (strtotime($licenca->data_expiracao) < time()) {
        $wpdb->update(
            $tabela_licencas,
            array('status' => 'expirado'),
            array('codigo_licenca' => $codigo)
        );
        return false;
    }

    return true;
}

function gma_criar_licenca_teste() {
    global $wpdb;
    $tabela_licencas = $wpdb->prefix . 'gma_licencas';
    
    $codigo = 'TESTE-' . strtoupper(substr(md5(uniqid()), 0, 8));
    
    $wpdb->insert(
        $tabela_licencas,
        array(
            'codigo_licenca' => $codigo,
            'tipo_licenca' => 'teste',
            'status' => 'inativo'
        )
    );
    
    return $codigo;
}

function gma_criar_licenca_pro() {
    global $wpdb;
    $tabela_licencas = $wpdb->prefix . 'gma_licencas';
    
    $codigo = 'PRO-' . strtoupper(substr(md5(uniqid()), 0, 12));
    
    $wpdb->insert(
        $tabela_licencas,
        array(
            'codigo_licenca' => $codigo,
            'tipo_licenca' => 'pro',
            'status' => 'inativo'
        )
    );
    
    return $codigo;
}

function gma_ativar_licenca($codigo) {
    global $wpdb;
    $tabela_licencas = $wpdb->prefix . 'gma_licencas';
    $site_atual = get_site_url();
    
    // Verifica se a licença existe
    $licenca = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tabela_licencas WHERE codigo_licenca = %s",
        $codigo
    ));

    if (!$licenca) {
        return false;
    }

    // Define a duração baseada no tipo de licença
    $duracao = ($licenca->tipo_licenca === 'pro') ? '+1 year' : '+1 day';
    
    // Atualiza a licença
    $wpdb->update(
        $tabela_licencas,
        array(
            'site_url' => $site_atual,
            'status' => 'ativo',
            'data_ativacao' => current_time('mysql'),
            'data_expiracao' => date('Y-m-d H:i:s', strtotime($duracao))
        ),
        array('codigo_licenca' => $codigo)
    );

    return true;
}

function gma_verificar_acesso() {
    $licenca_ativa = get_option('gma_licenca_ativa');
    if (!$licenca_ativa || !gma_verificar_licenca($licenca_ativa)) {
        return false;
    }
    return true;
}

add_action('admin_post_gma_ativar_licenca', 'gma_processar_ativacao_licenca');

function gma_processar_ativacao_licenca() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para realizar esta ação.'));
    }

    check_admin_referer('gma_ativar_licenca', 'gma_licenca_nonce');

    $codigo_licenca = isset($_POST['codigo_licenca']) ? sanitize_text_field($_POST['codigo_licenca']) : '';

    if (empty($codigo_licenca)) {
        wp_redirect(add_query_arg(
            array(
                'message' => 'Por favor, insira um código de licença válido.',
                'type' => 'error'
            ),
            admin_url('admin.php?page=gma-ativacao')
        ));
        exit;
    }

    if (gma_ativar_licenca($codigo_licenca)) {
        update_option('gma_licenca_ativa', $codigo_licenca);
        wp_redirect(add_query_arg(
            array(
                'message' => 'Licença ativada com sucesso!',
                'type' => 'success'
            ),
            admin_url('admin.php?page=gma-ativacao')
        ));
    } else {
        wp_redirect(add_query_arg(
            array(
                'message' => 'Código de licença inválido.',
                'type' => 'error'
            ),
            admin_url('admin.php?page=gma-ativacao')
        ));
    }
    exit;
}


function gma_verificar_licenca_global() {
    global $pagenow;
    $pagina_atual = isset($_GET['page']) ? $_GET['page'] : '';
    
    // Lista de páginas que não precisam de verificação
    $paginas_excluidas = array(
        'gma-ativacao'
    );
    
    // Verifica se está em uma página do plugin e não é uma página excluída
    if (strpos($pagina_atual, 'gma-') === 0 && !in_array($pagina_atual, $paginas_excluidas)) {
        gma_verificar_acesso_admin();
    }
}



function gma_dias_restantes_licenca() {
    $data_expiracao = get_option('gma_data_expiracao_licenca');
    if (!$data_expiracao) return false;

    $hoje = new DateTime();
    $expiracao = new DateTime($data_expiracao);
    $diferenca = $hoje->diff($expiracao);

    return $diferenca->invert ? 0 : $diferenca->days;
}

function gma_generate_license_key() {
    $prefix = 'GMA';
    $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 12);
    return $prefix . '-' . chunk_split($random, 4, '-');
}

function gma_validate_license($license_key) {
    global $wpdb;
    
    $license = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gma_licenses 
        WHERE license_key = %s AND status = 'active'",
        $license_key
    ));
    
    if (!$license) {
        return false;
    }
    
    // Check if license has expired
    if (strtotime($license->expiration_date) < time()) {
        $wpdb->update(
            $wpdb->prefix . 'gma_licenses',
            array('status' => 'expired'),
            array('id' => $license->id)
        );
        return false;
    }
    
    return true;
}



add_action('woocommerce_order_status_completed', 'gma_create_license_on_order_complete');

function gma_create_license_on_order_complete($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    
    // Generate license key
    $license_key = gma_generate_license_key();
    
    // Set expiration date (1 year from now)
    $expiration_date = date('Y-m-d H:i:s', strtotime('+1 year'));
    
    // Store license in database
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'gma_licenses',
        array(
            'user_id' => $user_id,
            'license_key' => $license_key,
            'expiration_date' => $expiration_date,
            'status' => 'active'
        )
    );
    
    // Send email to customer with license key
    $to = $order->get_billing_email();
    $subject = 'Sua licença do BrandAI - Gerenciador de Marketing Avançado PRO';
    $message = "Sua chave de licença é: " . $license_key;
    wp_mail($to, $subject, $message);
}

// Adicione isto no seu arquivo de funções principais
add_action('admin_init', function() {
    // Verifica se há uma URL de redirecionamento salva após ativação bem-sucedida
    if (isset($_GET['activation_success']) && $_GET['activation_success'] == 1) {
        $redirect_url = get_option('gma_redirect_after_activation');
        if ($redirect_url) {
            delete_option('gma_redirect_after_activation');
            wp_redirect($redirect_url);
            exit;
        }
    }
});


