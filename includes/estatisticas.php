<?php
// includes/estatisticas.php

if (!defined('ABSPATH')) {
    exit;
}

function gma_criar_tabela_estatisticas() {
    global $wpdb;
    $tabela_nome = $wpdb->prefix . 'gma_estatisticas';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tabela_nome (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        visualizacoes bigint(20) NOT NULL DEFAULT 0,
        cliques bigint(20) NOT NULL DEFAULT 0,
        conversoes bigint(20) NOT NULL DEFAULT 0,
        data_visualizacao datetime DEFAULT CURRENT_TIMESTAMP, 
        PRIMARY KEY  (id),
        FOREIGN KEY (campanha_id) REFERENCES {$wpdb->prefix}gma_campanhas(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function gma_atualizar_estatistica($campanha_id, $tipo) {
    global $wpdb;
    $tabela_nome = $wpdb->prefix . 'gma_estatisticas';

    // Verificação de segurança para $campanha_id
    if (!is_numeric($campanha_id) || $campanha_id <= 0) {
        error_log("gma_atualizar_estatistica: ID da campanha inválido ({$campanha_id})");
        return; // Pare a execução se o ID for inválido
    }

    $estatistica = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tabela_nome WHERE campanha_id = %d",
        $campanha_id
    ));

    if ($estatistica) {
        $wpdb->update(
            $tabela_nome,
            array($tipo => $estatistica->$tipo + 1),
            array('campanha_id' => $campanha_id)
        );
    } else {
        $wpdb->insert(
            $tabela_nome,
            array(
                'campanha_id' => $campanha_id,
                $tipo => 1
            )
        );
    }
}

function gma_obter_estatisticas($campanha_id) {
    global $wpdb;
    $tabela_nome = $wpdb->prefix . 'gma_estatisticas';

    $estatisticas = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tabela_nome WHERE campanha_id = %d",
        $campanha_id
    ));

    if (!$estatisticas) {
        $estatisticas = (object) array(
            'visualizacoes' => 0,
            'cliques' => 0,
            'conversoes' => 0
        );
    }

    return $estatisticas;
}

function gma_atualizar_visualizacao_campanha($campanha_id) {
    if (is_numeric($campanha_id) && $campanha_id > 0) { 
        gma_atualizar_estatistica($campanha_id, 'visualizacoes');
    } else {
        error_log("gma_atualizar_visualizacao_campanha: ID da campanha inválido ({$campanha_id})");
    }
}

// Função para obter os dados do relatório de visualizações
function gma_obter_dados_relatorio_visualizacoes($periodo = 'mes') {
    global $wpdb;

    switch ($periodo) {
        case 'semana':
            $inicio = date('Y-m-d', strtotime('last monday'));
            $fim = date('Y-m-d', strtotime('next sunday'));
            break;
        case 'dia':
            $inicio = date('Y-m-d');
            $fim = date('Y-m-d');
            break;
        case 'mes':
        default:
            $inicio = date('Y-m-01');
            $fim = date('Y-m-t');
            break;
    }

    $sql = "
        SELECT c.nome AS nome_campanha, SUM(e.visualizacoes) AS total_visualizacoes
        FROM {$wpdb->prefix}gma_campanhas c
        LEFT JOIN {$wpdb->prefix}gma_estatisticas e ON c.id = e.campanha_id
        WHERE e.data_visualizacao BETWEEN %s AND %s
        GROUP BY c.id
        ORDER BY total_visualizacoes DESC
    ";

    $resultados = $wpdb->get_results($wpdb->prepare($sql, $inicio, $fim));

    return $resultados;
}

// Função para obter os dados do relatório de campanhas
function gma_obter_dados_relatorio_campanhas($periodo = 'mes') {
    global $wpdb;

    switch ($periodo) {
        case 'semana':
            $inicio = date('Y-m-d', strtotime('last monday'));
            $fim = date('Y-m-d', strtotime('next sunday'));
            break;
        case 'dia':
            $inicio = date('Y-m-d');
            $fim = date('Y-m-d');
            break;
        case 'mes':
        default:
            $inicio = date('Y-m-01');
            $fim = date('Y-m-t');
            break;
    }

    $campanhas = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gma_campanhas");

    foreach ($campanhas as &$campanha) {
        $campanha->visualizacoes = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(visualizacoes) FROM {$wpdb->prefix}gma_estatisticas 
            WHERE campanha_id = %d AND data_visualizacao BETWEEN %s AND %s",
            $campanha->id, $inicio, $fim
        ));

        $campanha->cliques = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cliques) FROM {$wpdb->prefix}gma_estatisticas 
            WHERE campanha_id = %d AND data_visualizacao BETWEEN %s AND %s",
            $campanha->id, $inicio, $fim
        ));

        $campanha->downloads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gma_downloads 
            WHERE campanha_id = %d AND data_download BETWEEN %s AND %s",
            $campanha->id, $inicio, $fim
        ));

        $campanha->numero_materiais = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gma_materiais WHERE campanha_id = %d",
            $campanha->id
        ));
    }

    return $campanhas;
}

// Função para obter as estatísticas de um material
function gma_obter_estatisticas_material($material_id, $periodo = 'mes') {
    global $wpdb;

    switch ($periodo) {
        case 'semana':
            $inicio = date('Y-m-d', strtotime('last monday'));
            $fim = date('Y-m-d', strtotime('next sunday'));
            break;
        case 'dia':
            $inicio = date('Y-m-d');
            $fim = date('Y-m-d');
            break;
        case 'mes':
        default:
            $inicio = date('Y-m-01');
            $fim = date('Y-m-t');
            break;
    }

    $visualizacoes = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(e.visualizacoes) FROM {$wpdb->prefix}gma_estatisticas e
        JOIN {$wpdb->prefix}gma_campanhas c ON e.campanha_id = c.id
        JOIN {$wpdb->prefix}gma_materiais m ON c.id = m.campanha_id
        WHERE m.id = %d AND e.data_visualizacao BETWEEN %s AND %s",
        $material_id, $inicio, $fim
    ));

    $cliques = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(e.cliques) FROM {$wpdb->prefix}gma_estatisticas e
        JOIN {$wpdb->prefix}gma_campanhas c ON e.campanha_id = c.id
        JOIN {$wpdb->prefix}gma_materiais m ON c.id = m.campanha_id
        WHERE m.id = %d AND e.data_visualizacao BETWEEN %s AND %s",
        $material_id, $inicio, $fim
    ));

    $downloads = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}gma_downloads 
        WHERE material_id = %d AND data_download BETWEEN %s AND %s",
        $material_id, $inicio, $fim
    ));

    return array(
        'visualizacoes' => $visualizacoes,
        'cliques' => $cliques,
        'downloads' => $downloads,
    );
}