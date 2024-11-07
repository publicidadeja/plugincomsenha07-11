<?php
// Funções relacionadas a materiais

function gma_criar_material($campanha_id, $imagem_url, $copy, $link_canva, $arquivo_id = null) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';

    // Inicia a transação
    $wpdb->query('START TRANSACTION');

    try {
        // Verifica se o material já existe
        if (gma_verificar_material_existente($campanha_id, $imagem_url, $copy, $link_canva)) {
            $wpdb->query('ROLLBACK');
            return false; // Material já existe
        }

        $wpdb->insert(
            $tabela,
            array(
                'campanha_id' => $campanha_id,
                'imagem_url' => $imagem_url,
                'copy' => $copy,
                'link_canva' => $link_canva,
                'arquivo_id' => $arquivo_id,
                'status_aprovacao' => 'pendente',
            )
        );

        $insert_id = $wpdb->insert_id;

        if ($insert_id) {
            $wpdb->query('COMMIT');
            return $insert_id;
        } else {
            $wpdb->query('ROLLBACK');
            return false;
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('Erro ao criar material: ' . $e->getMessage());
        return false;
    }
}

function gma_verificar_material_existente($campanha_id, $imagem_url, $copy, $link_canva) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    
    $material = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $tabela WHERE campanha_id = %d AND imagem_url = %s AND copy = %s AND link_canva = %s",
            $campanha_id,
            $imagem_url,
            $copy,
            $link_canva
        )
    );

    return $material ? true : false;
}

function gma_obter_material($id) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabela WHERE id = %d", $id));
}

function gma_listar_materiais($campanha_id = null) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    if ($campanha_id) {
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $tabela WHERE campanha_id = %d", $campanha_id));
    }
    return $wpdb->get_results("SELECT * FROM $tabela");
}


function gma_atualizar_material($material_id, $dados) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    
    $resultado = $wpdb->update(
        $tabela,
        $dados,
        array('id' => $material_id),
        null,
        array('%d')
    );
    
    if ($resultado === false) {
        error_log("Erro no MySQL ao atualizar material: " . $wpdb->last_error);
        return false;
    } elseif ($resultado === 0) {
        error_log("Nenhuma linha foi atualizada para o material ID: $material_id");
        return true; // Retorna true porque nenhum erro ocorreu, mas nenhuma alteração foi necessária
    }
    
    return true;
}

function gma_handle_atualizar_material() {
    if (isset($_POST['submit']) && isset($_POST['gma_nonce']) && wp_verify_nonce($_POST['gma_nonce'], 'gma_atualizar_material')) {
        $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
        $imagem_url = isset($_POST['imagem_url']) ? sanitize_url($_POST['imagem_url']) : '';
        $copy = isset($_POST['copy']) ? sanitize_textarea_field($_POST['copy']) : '';
        $link_canva = isset($_POST['link_canva']) ? sanitize_url($_POST['link_canva']) : '';

        $dados_atualizados = array(
            'imagem_url' => $imagem_url,
            'copy' => $copy,
            'link_canva' => $link_canva
        );

        $resultado = gma_atualizar_material($material_id, $dados_atualizados);

        // Início da mudança
        if ($resultado) {
            $url = add_query_arg('message', 'updated', wp_get_referer());
        } else {
            $url = add_query_arg('message', 'error', wp_get_referer());
        }

        wp_safe_redirect($url);
        exit;
        // Fim da mudança

    } else {
        error_log("Falha na verificação do nonce ou dados do formulário ausentes.");
    }
}
// Registre a função para lidar com o POST
add_action('admin_post_gma_atualizar_material', 'gma_handle_atualizar_material');
add_action('admin_post_nopriv_gma_atualizar_material', 'gma_handle_atualizar_material');

function gma_excluir_material($material_id) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    $result = $wpdb->delete($tabela, array('id' => $material_id), array('%d'));
    if ($result === false) {
        error_log("Erro ao excluir material: " . $wpdb->last_error); // Log de erro
    }
    return $result;
}

function gma_handle_excluir_material() {
    // Verificar o nonce para segurança
    if (!isset($_GET['gma_nonce']) || !wp_verify_nonce($_GET['gma_nonce'], 'gma_excluir_material_' . $_GET['id'])) {
        wp_die('Ação não autorizada.');
    }

    // Verificar se o ID do material foi fornecido
    if (!isset($_GET['id'])) {
        wp_die('ID do material não fornecido.');
    }

    $material_id = intval($_GET['id']);

    // Excluir o material
    $resultado = gma_excluir_material($material_id);

    if ($resultado) {
        // Redirecionar de volta para a página de listagem com uma mensagem de sucesso
        wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=gma-materiais')));
        exit;
    } else {
        // Redirecionar de volta para a página de listagem com uma mensagem de erro
        wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=gma-materiais')));
        exit;
    }
}

function gma_listar_materiais_por_tipo_campanha($tipo) {
    global $wpdb;
    $tabela_materiais = $wpdb->prefix . 'gma_materiais';
    $tabela_campanhas = $wpdb->prefix . 'gma_campanhas';
    return $wpdb->get_results($wpdb->prepare("SELECT m.* FROM $tabela_materiais m JOIN $tabela_campanhas c ON m.campanha_id = c.id WHERE c.tipo_campanha = %s", $tipo));
}
function gma_obter_materiais_por_status($status = 'todos') {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';

    if ($status === 'todos') {
        return $wpdb->get_results("SELECT * FROM $tabela ORDER BY id DESC");
    } else {
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $tabela WHERE status_aprovacao = %s ORDER BY id DESC", $status));
    }
}

function gma_registrar_download($campanha_id, $material_id) {
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'gma_downloads',
        array(
            'campanha_id' => $campanha_id,
            'material_id' => $material_id,
        )
    );
}

function gma_exibir_materiais_campanha($campanha_id) {
    $materiais = gma_listar_materiais($campanha_id);
    if ($materiais) {
        echo '<div class="gma-materiais">';
        foreach ($materiais as $material) {
            echo '<div class="gma-material">';
            if (!empty($material->imagem_url)) {
                echo '<img src="' . esc_url($material->imagem_url) . '" alt="Material">';
            }
            if (!empty($material->copy)) {
                echo '<p>' . wp_kses_post($material->copy) . '</p>';
            }
            if (!empty($material->link_canva)) {
                echo '<a href="' . esc_url($material->link_canva) . '" target="_blank" class="button">Ver no Canva</a>';
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>Nenhum material encontrado para esta campanha.</p>';
    }
}

// Funções de manipulação de imagens
function gma_handle_image_upload() {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    $uploadedfile = $_FILES['file'];
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        echo json_encode($movefile);
    } else {
        echo json_encode(array('error' => $movefile['error']));
    }
    wp_die();
}
add_action('wp_ajax_gma_upload_image', 'gma_handle_image_upload');

// Funções AJAX
function gma_aprovar_material() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;

    if ($material_id) {
        $resultado = gma_atualizar_material($material_id, array('status_aprovacao' => 'aprovado'));

        if ($resultado) {
            // Disparar ação para notificar o administrador
            do_action('gma_material_status_updated', $material_id, 'aprovado');
          

            wp_send_json_success(array('message' => 'Material aprovado com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao aprovar o material.'));
        }
    } else {
        wp_send_json_error(array('message' => 'ID do material inválido.'));
    }
    wp_die();
}
function gma_reprovar_material() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;

    if ($material_id) {
        $resultado = gma_atualizar_material($material_id, array('status_aprovacao' => 'reprovado'));

        if ($resultado) {
            // Disparar ação para notificar o administrador
            do_action('gma_material_status_updated', $material_id, 'reprovado');

            wp_send_json_success(array('message' => 'Material reprovado com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao reprovar o material.'));
        }
    } else {
        wp_send_json_error(array('message' => 'ID do material inválido.'));
    }
    wp_die();
}

function gma_editar_material() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
    $alteracao_arte = isset($_POST['alteracao_arte']) ? sanitize_textarea_field($_POST['alteracao_arte']) : '';
    $nova_copy = isset($_POST['nova_copy']) ? sanitize_textarea_field($_POST['nova_copy']) : '';

    if ($material_id) {
        $resultado = gma_atualizar_material($material_id, array(
            'feedback' => $alteracao_arte,
            'copy' => $nova_copy,
            'status_aprovacao' => 'pendente' 
        ));

        if ($resultado) {
            // Disparar ação para notificar o administrador
            do_action('gma_material_status_updated', $material_id, 'pendente'); 

            wp_send_json_success(array('message' => 'Material editado com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao editar o material.'));
        }
    } else {
        wp_send_json_error(array('message' => 'ID do material inválido.'));
    }
    wp_die();
}

function gma_atualizar_material_aprovacao($material_id, $status, $feedback, $copy, $imagem_url = null) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    
    $dados = array(
        'status_aprovacao' => $status,
        'feedback' => $feedback,
        'copy' => $copy
    );

    if ($imagem_url !== null) {
        $dados['imagem_url'] = $imagem_url;
    }

    return $wpdb->update(
        $tabela,
        $dados,
        array('id' => $material_id),
        array('%s', '%s', '%s', '%s'),
        array('%d')
    );
}
function gma_atualizar_material_marketing($material_id, $copy, $link_canva, $image_id = null) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    
    $dados = array(
        'copy' => $copy,
        'link_canva' => $link_canva
    );
    
    $formatos = array('%s', '%s');

    // Adiciona os campos de imagem se houver um image_id
    if ($image_id !== null) {
        $dados['image_id'] = $image_id;
        $dados['imagem_url'] = wp_get_attachment_url($image_id);
        $formatos[] = '%d';
        $formatos[] = '%s';
    }

    $resultado = $wpdb->update(
        $tabela,
        $dados,
        array('id' => $material_id),
        $formatos,
        array('%d')
    );

    if ($resultado === false) {
        error_log("Erro ao atualizar material marketing: " . $wpdb->last_error);
        return false;
    }
    return true;
}
function gma_salvar_feedback() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
    $feedback = isset($_POST['feedback']) ? sanitize_textarea_field($_POST['feedback']) : '';

    if ($material_id && $feedback) {
        $resultado = gma_atualizar_material($material_id, array('feedback' => $feedback));
        if ($resultado) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('error' => 'Erro ao salvar feedback.'));
        }
    } else {
        wp_send_json_error(array('error' => 'ID ou feedback inválido.'));
    }
    wp_die();
}

function gma_obter_material_ajax() {
    check_ajax_referer('gma_ajax_nonce', 'nonce');
    $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
    if ($material_id) {
        $material = gma_obter_material($material_id);
        if ($material) {
            wp_send_json_success(array('data' => $material));
        } else {
            wp_send_json_error(array('error' => 'Material não encontrado.'));
        }
    } else {
        wp_send_json_error(array('error' => 'ID do material inválido.'));
    }
    wp_die();
}
function gma_obter_materiais_campanha($campanha_id) {
    global $wpdb;
    $tabela = $wpdb->prefix . 'gma_materiais';
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $tabela WHERE campanha_id = %d ORDER BY data_criacao DESC",
        $campanha_id
    ));
}

function gma_get_copy_suggestions($copy) {
    $api_key = get_option('gma_openai_api_key');
    if (empty($api_key)) {
        return 'Configure sua chave API OpenAI nas configurações do plugin.';
    }

    $url = 'https://api.openai.com/v1/chat/completions';
    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    );

    $body = array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array(
                'role' => 'system',
                'content' => 'Você é um especialista em marketing. Analise o texto e sugira melhorias para clareza, engajamento e impacto.'
            ),
            array(
                'role' => 'user',
                'content' => $copy
            )
        )
    );

    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($body),
        'timeout' => 30
    ));

    if (is_wp_error($response)) {
        return 'Erro ao conectar com OpenAI: ' . $response->get_error_message();
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    if (isset($body->choices[0]->message->content)) {
        return $body->choices[0]->message->content;
    }

    return 'Erro ao obter sugestões.';
}

// Adicionar AJAX handler
add_action('wp_ajax_gma_get_copy_suggestions', 'gma_ajax_get_copy_suggestions');
function gma_ajax_get_copy_suggestions() {
    check_ajax_referer('gma_copy_suggestions', 'nonce');
    $copy = sanitize_textarea_field($_POST['copy']);
    $suggestions = gma_get_copy_suggestions($copy);
    wp_send_json_success(array('suggestions' => $suggestions));
}

function gma_notificar_admin_mudanca_status($material_id, $novo_status) {
    $material = gma_obter_material($material_id);
    $campanha = gma_obter_campanha($material->campanha_id);
    $admin_email = get_option('admin_email');

    // Email template with inline CSS
    $titulo = "Gerenciador de Marketing: Atualização de Status";
    
    $mensagem = "
    <div style='background-color: #f6f6f6; padding: 20px; font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #333; margin: 0;'>Atualização de Status</h1>
                <p style='color: #666; margin-top: 10px;'>Material da campanha '{$campanha->nome}'</p>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                <p style='margin: 0; color: #444;'>
                    <strong>Material:</strong> {$material->copy}<br>
                    <strong>ID:</strong> {$material_id}<br>
                    <strong>Novo Status:</strong> <span style='color: #28a745;'>{$novo_status}</span>
                </p>
            </div>
            
            <div style='text-align: center;'>
                <a href='" . admin_url('admin.php?page=gma-editar-material&id=' . $material->id) . "' 
                   style='display: inline-block; padding: 12px 24px; background-color: #007bff; color: #ffffff; 
                          text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    Visualizar Material
                </a>
            </div>
            
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px; text-align: center;'>
                <p>Este é um email automático do Gerenciador de Marketing Avançado.</p>
            </div>
        </div>
    </div>";

    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($admin_email, $titulo, $mensagem, $headers);

    // Admin notification with status-specific styling
    $status_classes = array(
        'aprovado' => 'success',
        'reprovado' => 'error',
        'pendente' => 'warning'
    );
    
    $status_class = isset($status_classes[$novo_status]) ? $status_classes[$novo_status] : 'info';
    
    $mensagem_notificacao = "
        <div class='gma-notification {$status_class} animate-notification'>
            <div class='gma-notification-icon'>
                " . ($status_class === 'success' ? '✓' : ($status_class === 'error' ? '✕' : 'ℹ')) . "
            </div>
            <div class='gma-notification-content'>
                <h4>Atualização de Status</h4>
                <p>O material de ID <strong>{$material_id}</strong> da campanha <strong>'{$campanha->nome}'</strong> 
                   foi alterado para: <strong>{$novo_status}</strong></p>
            </div>
        </div>";

    gma_exibir_notificacao_admin($mensagem_notificacao, $status_class);
}

// Função para exibir a notificação no painel do admin
function gma_exibir_notificacao_admin($mensagem, $tipo = 'success') {
    // Adicionar CSS para as notificações popup
    add_action('admin_head', function() {
        ?>
        <style>
            .gma-popup-notification {
                position: fixed;
                top: 32px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                max-width: 400px;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(120%);
                transition: transform 0.3s ease-out;
            }

            .gma-popup-notification.show {
                transform: translateX(0);
            }

            .gma-popup-notification.success {
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
            }

            .gma-popup-notification.error {
                background: linear-gradient(135deg, #dc3545, #c82333);
                color: white;
            }

            .gma-popup-notification.warning {
                background: linear-gradient(135deg, #ffc107, #ffb006);
                color: #333;
            }

            .gma-popup-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .gma-popup-close {
                cursor: pointer;
                font-size: 18px;
                opacity: 0.8;
            }

            .gma-popup-close:hover {
                opacity: 1;
            }

            .gma-progress-bar {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(255,255,255,0.7);
                width: 100%;
                transform-origin: left;
                animation: progress 10s linear forwards;
            }

            @keyframes progress {
                from { transform: scaleX(1); }
                to { transform: scaleX(0); }
            }
        </style>
        <?php
    });

    // Adicionar JavaScript para controlar o popup
    add_action('admin_footer', function() {
        ?>
        <script>
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `gma-popup-notification ${type}`;
            notification.innerHTML = `
                <div class="gma-popup-header">
                    <h4 style="margin:0">Notificação</h4>
                    <span class="gma-popup-close">&times;</span>
                </div>
                <div class="gma-popup-content">${message}</div>
                <div class="gma-progress-bar"></div>
            `;

            document.body.appendChild(notification);
            
            // Mostrar o popup
            setTimeout(() => notification.classList.add('show'), 100);

            // Configurar o fechamento do popup
            const closePopup = () => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            };

            // Fechar ao clicar no X
            notification.querySelector('.gma-popup-close').addEventListener('click', closePopup);

            // Fechar automaticamente após 10 segundos
            setTimeout(closePopup, 10000);
        }
        </script>
        <?php
    });

    // Armazenar a notificação para exibição
    set_transient('gma_notificacao_admin', 
        array(
            'mensagem' => $mensagem,
            'tipo' => $tipo
        ), 
        15 // Tempo um pouco maior que o timeout do JavaScript
    );

    // Trigger imediato da notificação via JavaScript
    add_action('admin_footer', function() use ($mensagem, $tipo) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification(" . json_encode($mensagem) . ", " . json_encode($tipo) . ");
            });
        </script>";
    });
}

// Adicionar hook para chamar a função de notificação
add_action('gma_material_status_updated', 'gma_notificar_admin_mudanca_status', 10, 2);

// Registrar as ações AJAX
add_action('wp_ajax_gma_aprovar_material', 'gma_aprovar_material');
add_action('wp_ajax_gma_reprovar_material', 'gma_reprovar_material');
add_action('wp_ajax_gma_editar_material', 'gma_editar_material');
add_action('wp_ajax_gma_salvar_feedback', 'gma_salvar_feedback');
add_action('wp_ajax_gma_obter_material', 'gma_obter_material_ajax');
add_action('admin_post_gma_excluir_material', 'gma_handle_excluir_material');

// Adicione estas linhas para permitir acesso a usuários não logados
add_action('wp_ajax_nopriv_gma_aprovar_material', 'gma_aprovar_material');
add_action('wp_ajax_nopriv_gma_reprovar_material', 'gma_reprovar_material');
add_action('wp_ajax_nopriv_gma_editar_material', 'gma_editar_material');
add_action('wp_ajax_nopriv_gma_salvar_feedback', 'gma_salvar_feedback');
add_action('wp_ajax_nopriv_gma_obter_material', 'gma_obter_material_ajax');

?>