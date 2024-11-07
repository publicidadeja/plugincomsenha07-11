<?php

function gma_exibir_campanha($campanha_id) {
    global $wpdb; // Certifique-se que a variável global está sendo utilizada
    error_log("gma_exibir_campanha: Iniciando a exibição da campanha {$campanha_id}"); 

    $campanha = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gma_campanhas WHERE id = %d", $campanha_id));
    $materiais = gma_listar_materiais($campanha_id);

    if (!$campanha) {
        error_log("gma_exibir_campanha: Campanha {$campanha_id} não encontrada.");
        return;
    }

    ob_start();
    ?>
    <div class="gma-campanha">
        <h1><?php echo esc_html($campanha->nome); ?></h1>
        <p>Cliente/Projeto: <?php echo esc_html($campanha->cliente); ?></p>
        
        <?php if (!empty($materiais)): ?>
            <?php foreach ($materiais as $material): ?>
            <div class="gma-material">
                <img src="<?php echo esc_url($material->imagem_url); ?>" alt="Material de Marketing">
                <div class="copy"><?php echo wp_kses_post($material->copy); ?></div>
                <?php if (!empty($material->link_canva)): ?>
                <a href="<?php echo esc_url($material->link_canva); ?>" class="edit-button" target="_blank">Editar no Canva</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum material encontrado para esta campanha.</p>
        <?php endif; ?>
    </div>
    <?php
    error_log("gma_exibir_campanha: Campanha {$campanha_id} exibida");
    return ob_get_clean();
}

function gma_shortcode_campanha($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '',
        ),
        $atts
    );

    $campanha_id = intval($atts['id']);

    if ($campanha_id) {
        return gma_exibir_campanha($campanha_id);
    } else {
        return 'Campanha não encontrada';
    }
}

add_shortcode('gma_campanha', 'gma_shortcode_campanha');