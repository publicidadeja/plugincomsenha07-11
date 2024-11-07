<?php
if (!defined('ABSPATH')) exit;

// Verificar se há um ID de material válido
$material_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$material_id) {
    wp_die('Material não encontrado.');
}

// Obter dados do material
$material = gma_obter_material($material_id);
if (!$material) {
    wp_die('Material não encontrado.');
}

if (isset($_POST['atualizar_material_aprovacao']) && isset($_POST['gma_nonce']) && wp_verify_nonce($_POST['gma_nonce'], 'editar_material_aprovacao')) {
    $status = sanitize_text_field($_POST['status_aprovacao']);
    $feedback = sanitize_textarea_field($_POST['feedback']);
    $copy = sanitize_textarea_field($_POST['copy']);
    $imagem_url = isset($_POST['imagem_url']) ? sanitize_url($_POST['imagem_url']) : $material->imagem_url;

    if (gma_atualizar_material_aprovacao($material_id, $status, $feedback, $copy, $imagem_url)) {
        echo '<div class="gma-notice success">
                <i class="dashicons dashicons-yes-alt"></i> 
                Material atualizado com sucesso!
              </div>';
        $material = gma_obter_material($material_id);
    } else {
        echo '<div class="gma-notice error">
                <i class="dashicons dashicons-warning"></i> 
                Erro ao atualizar material.
              </div>';
    }
}
?>

<div class="gma-approval-wrap">
    <h1 class="gma-approval-title">Editar Material de Aprovação</h1>
    
    <div class="gma-approval-card">
        <form id="gma-approval-form" method="post" action="">
          
            <?php wp_nonce_field('editar_material_aprovacao', 'gma_nonce'); ?>
            <input type="hidden" name="material_id" value="<?php echo esc_attr($material->id); ?>">
            <input type="hidden" name="imagem_url" id="gma-imagem-url" value="<?php echo esc_attr($material->imagem_url); ?>">
            
            <div class="gma-material-preview">
                <div id="gma-image-preview">
                    <?php if (!empty($material->imagem_url)): ?>
                        <img src="<?php echo esc_url($material->imagem_url); ?>" alt="Preview do Material">
                    <?php endif; ?>
                </div>
                <button type="button" id="gma-upload-btn" class="gma-button secondary">
                    <i class="dashicons dashicons-upload"></i>
                    Atualizar Imagem
                </button>
            </div>
          
          
 <div class="gma-form-group">
                <label for="copy">
                    <i class="dashicons dashicons-edit"></i>
                    Copy do Material
                </label>
                <div class="gma-copy-wrapper">
                    <textarea name="copy" id="copy" rows="5"><?php echo esc_textarea($material->copy ?? ''); ?></textarea>
                    <button type="button" id="gma-ai-suggestion" class="gma-button ai-button">
                        <i class="dashicons dashicons-admin-generic"></i>
                      
<button type="button" id="get-suggestions" class="gma-button secondary">
        <i class="dashicons dashicons-admin-customizer"></i> Obter Sugestões AI
    </button>
    <div id="suggestions-container" style="display: none;">
        <h3>Sugestões da IA</h3>
        <div id="suggestions-content"></div>
    </div>

                </div>
            </div>
          
          
            <div class="gma-form-group">
                <label for="status_aprovacao">
                    <i class="dashicons dashicons-flag"></i>
                    Status de Aprovação
                </label>
                <select name="status_aprovacao" id="status_aprovacao" required>
                    <option value="pendente" <?php selected($material->status_aprovacao, 'pendente'); ?>>Pendente</option>
                    <option value="aprovado" <?php selected($material->status_aprovacao, 'aprovado'); ?>>Aprovado</option>
                    <option value="reprovado" <?php selected($material->status_aprovacao, 'reprovado'); ?>>Reprovado</option>
                </select>
            </div>

            <div class="gma-form-group">
                <label for="feedback">
                    <i class="dashicons dashicons-admin-comments"></i>
                    Feedback
                </label>
                <div class="gma-feedback-wrapper">
                    <textarea name="feedback" id="feedback" rows="5" required><?php echo esc_textarea($material->feedback); ?></textarea>
                    <div class="gma-character-count">
                        Caracteres: <span id="feedback-count">0</span>
                    </div>
                </div>
            </div>

            <div class="gma-form-actions">
                <button type="submit" name="atualizar_material_aprovacao" class="gma-button primary">
                    <i class="dashicons dashicons-yes-alt"></i>
                    Atualizar Material
                </button>
                <a href="javascript:history.back()" class="gma-button secondary">
                    <i class="dashicons dashicons-dismiss"></i>
                    Cancelar
                </a>
            </div>
          
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var mediaUploader;
    
    $('#gma-upload-btn').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Escolha ou faça upload de uma imagem',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#gma-imagem-url').val(attachment.url);
            $('#gma-image-preview').html('<img src="' + attachment.url + '" alt="Preview do Material">');
        });

        mediaUploader.open();
    });

    function updateFeedbackCount() {
        var count = $('#feedback').val().length;
        $('#feedback-count').text(count);
    }

    $('#feedback').on('input', updateFeedbackCount);
    updateFeedbackCount();
});
</script>

<?php
wp_enqueue_media();
wp_enqueue_style('dashicons');
?>
<style>
:root {
    --primary-color: #4a90e2;
    --secondary-color: #2ecc71;
    --danger-color: #e74c3c;
    --text-color: #2c3e50;
    --background-color: #f5f6fa;
    --card-background: #ffffff;
    --border-radius: 12px;
    --transition: all 0.3s ease;
}

.gma-approval-wrap {
    padding: 30px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-approval-title {
    font-size: 2.5em;
    color: var(--text-color);
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
}

.gma-approval-card {
    max-width: 800px;
    margin: 0 auto;
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    padding: 30px;
    animation: slideUp 0.5s ease;
}

.gma-material-preview {
    text-align: center;
    margin-bottom: 30px;
}

.gma-material-preview img {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: var(--transition);
    margin-bottom: 15px;
}

.gma-form-group {
    margin-bottom: 25px;
}

.gma-form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-weight: 600;
    color: var(--text-color);
}

select, textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e1e1;
    border-radius: var(--border-radius);
    font-size: 1em;
    transition: var(--transition);
}

.gma-button {
    padding: 12px 24px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.gma-button.primary {
    background: var(--primary-color);
    color: white;
}

.gma-button.secondary {
    background: var(--secondary-color);
    color: white;
}

.gma-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .gma-approval-wrap {
        padding: 15px;
    }
    
    .gma-form-actions {
        flex-direction: column;
    }
    
    .gma-button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Inicialização do Media Uploader
    var mediaUploader;
    
    $('#gma-upload-btn').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Escolha ou faça upload de uma imagem',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#gma-imagem-url').val(attachment.url);
            $('#gma-image-preview').html('<img src="' + attachment.url + '" alt="Preview do Material">');
        });

        mediaUploader.open();
    });

    // Contador de caracteres para o feedback
    function updateFeedbackCount() {
        var count = $('#feedback').val().length;
        $('#feedback-count').text(count);
    }

    $('#feedback').on('input', updateFeedbackCount);
    updateFeedbackCount();

    // Validação do formulário
    $('#gma-approval-form').on('submit', function(e) {
        var isValid = true;
        
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
        }
    });
});
<?php
wp_enqueue_media();
wp_enqueue_script('jquery');
?>
</script>
  
  <script>
jQuery(document).ready(function($) {
    $('#get-suggestions').on('click', function() {
        const copy = $('#copy').val();
        const button = $(this);
        
        if (!copy) {
            alert('Por favor, insira algum texto primeiro.');
            return;
        }
        
        button.prop('disabled', true).text('Obtendo sugestões...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'gma_get_copy_suggestions',
                nonce: '<?php echo wp_create_nonce("gma_copy_suggestions"); ?>',
                copy: copy
            },
            success: function(response) {
                if (response.success) {
                    $('#suggestions-content').html(response.data.suggestions);
                    $('#suggestions-container').slideDown();
                } else {
                    alert('Falha ao obter sugestões. Tente novamente.');
                }
            },
            error: function() {
                alert('Erro ao conectar com o servidor.');
            },
            complete: function() {
                button.prop('disabled', false).text('Obter Sugestões AI');
            }
        });
    });
});
</script>

<style>
#suggestions-container {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid #4a90e2;
}

#suggestions-content {
    white-space: pre-line;
    line-height: 1.5;
}

#get-suggestions {
    margin-top: 10px;
}
</style>