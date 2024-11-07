<?php 
if (!defined('ABSPATH')) exit;

// Carrega o Media Uploader
wp_enqueue_media();

// Localiza os scripts para AJAX
wp_localize_script('jquery', 'gma_ajax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('gma_copy_suggestions')
));
?>

<div class="gma-create-wrap">
    <div class="gma-create-container">
        <h1 class="gma-create-title">🎨 Criar Novo Material</h1>

        <div class="gma-create-card">
            <form method="post" class="gma-create-form" id="gma-material-form">
                <?php wp_nonce_field('gma_novo_material', 'gma_novo_material_nonce'); ?>
                
                <div class="gma-form-grid">
                    <!-- Seleção de Campanha -->
                    <div class="gma-form-group">
                        <label for="campanha_id">
                            <i class="dashicons dashicons-megaphone"></i> Campanha
                        </label>
                        <select name="campanha_id" id="campanha_id" required>
                            <option value="">Selecione uma campanha</option>
                            <?php 
                            $campanhas = gma_listar_campanhas();
                            foreach ($campanhas as $campanha): 
                                $tipo = esc_attr($campanha->tipo_campanha);
                            ?>
                                <option value="<?php echo esc_attr($campanha->id); ?>" 
                                        data-tipo="<?php echo $tipo; ?>">
                                    <?php echo esc_html($campanha->nome); ?> 
                                    (<?php echo ucfirst($tipo); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Upload de Imagem -->
                    <div class="gma-form-group">
                        <label for="gma-imagem-url">
                            <i class="dashicons dashicons-format-image"></i> Imagem
                        </label>
                        <div class="gma-upload-container">
                            <input type="text" name="imagem_url" id="gma-imagem-url" 
                                   class="gma-input" required readonly>
                            <input type="hidden" name="arquivo_id" id="gma-arquivo-id">
                            <button type="button" id="gma-upload-btn" class="gma-button secondary">
                                <i class="dashicons dashicons-upload"></i> Selecionar
                            </button>
                        </div>
                        <div id="gma-image-preview" class="gma-image-preview"></div>
                    </div>

                    <!-- Copy do Material -->
                    <div class="gma-form-group full-width">
                        <label for="copy">
                            <i class="dashicons dashicons-editor-paste-text"></i> Copy
                        </label>
                        <textarea name="copy" id="copy" rows="5" required></textarea>
                        <div class="gma-character-count">
                            <span id="char-count">0</span> caracteres
                        </div>
                        <div class="gma-form-group full-width">
    <button type="button" id="get-suggestions" class="gma-button secondary">
        <i class="dashicons dashicons-admin-customizer"></i> Obter Sugestões AI
    </button>
    <div id="suggestions-container" style="display: none;">
        <h3>Sugestões da IA</h3>
        <div id="suggestions-content"></div>
    </div>
</div>

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
                    </div>

                    <!-- Link do Canva (apenas para campanhas de marketing) -->
                    <div class="gma-form-group full-width" id="canva-group" style="display: none;">
                        <label for="link_canva">
                            <i class="dashicons dashicons-art"></i> Link do Canva
                        </label>
                        <input type="url" name="link_canva" id="link_canva" 
                               class="gma-input" placeholder="https://www.canva.com/...">
                    </div>
                </div>

                <div class="gma-form-actions">
                    <button type="submit" name="criar_material" class="gma-button primary">
                        <i class="dashicons dashicons-saved"></i> Criar Material
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=gma-materiais'); ?>" 
                       class="gma-button secondary">
                        <i class="dashicons dashicons-arrow-left-alt"></i> Voltar
                    </a>
                  
                </div>
              
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Controle de exibição dos campos baseado no tipo de campanha
    $('#campanha_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var tipoCampanha = selectedOption.data('tipo');
        
        if (tipoCampanha === 'marketing') {
            $('#canva-group').show();
            $('#link_canva').prop('required', false);
        } else {
            $('#canva-group').hide();
            $('#link_canva').prop('required', false);
        }
    });

    // Upload de imagem
    $('#gma-upload-btn').click(function(e) {
        e.preventDefault();
        
        var custom_uploader = wp.media({
            title: 'Selecionar Imagem',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#gma-imagem-url').val(attachment.url);
            $('#gma-arquivo-id').val(attachment.id);
            $('#gma-image-preview').html('<img src="' + attachment.url + '" alt="Preview">');
        });

        custom_uploader.open();
    });

    // Contador de caracteres
    $('#copy').on('input', function() {
        var charCount = $(this).val().length;
        $('#char-count').text(charCount);
    });

    

    // Validação do formulário
    $('#gma-material-form').on('submit', function(e) {
        var isValid = true;
        
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('error').shake();
            } else {
                $(this).removeClass('error');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
        }
    });

    // Efeito shake para campos com erro
    $.fn.shake = function() {
        this.each(function() {
            $(this).css('position', 'relative');
            for(var i = 0; i < 3; i++) {
                $(this).animate({left: -10}, 50)
                       .animate({left: 10}, 50)
                       .animate({left: 0}, 50);
            }
        });
    };
});
  
</script>



<style>
:root {
    --primary-color: #4a90e2;
    --secondary-color: #2ecc71;
    --danger-color: #e74c3c;
    --text-color: #2c3e50;
    --background-color: #f5f6fa;
    --card-background: #ffffff;
    --border-radius: 10px;
    --transition: all 0.3s ease;
}

.gma-create-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-create-container {
    max-width: 800px;
    margin: 0 auto;
}

.gma-create-title {
    font-size: 2.5em;
    color: var(--text-color);
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
}

.gma-create-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 30px;
    animation: slideIn 0.5s ease;
}

.gma-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.gma-form-group {
    margin-bottom: 20px;
}

.gma-form-group.full-width {
    grid-column: 1 / -1;
}

.gma-form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-color);
}

.gma-input, select, textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e1e1;
    border-radius: var(--border-radius);
    font-size: 1em;
    transition: var(--transition);
}

.gma-input:focus, select:focus, textarea:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
}

.gma-upload-container {
    display: flex;
    gap: 10px;
}

.gma-image-preview {
    margin-top: 10px;
    max-width: 300px;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.gma-image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.gma-character-count {
    text-align: right;
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
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
    text-decoration: none;
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

.gma-form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    justify-content: flex-end;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .gma-form-grid {
        grid-template-columns: 1fr;
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
    // Upload de imagem
    $('#gma-upload-btn').click(function(e) {
        e.preventDefault();
        
        var custom_uploader = wp.media({
            title: 'Selecionar Imagem',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#gma-imagem-url').val(attachment.url);
            $('#gma-arquivo-id').val(attachment.id);
            $('#gma-image-preview').html('<img src="' + attachment.url + '" alt="Preview">');
        });

        custom_uploader.open();
    });

    // Contador de caracteres
    $('#copy').on('input', function() {
        var charCount = $(this).val().length;
        $('#char-count').text(charCount);
    });

    // Validação do formulário
    $('#gma-material-form').on('submit', function(e) {
        var isValid = true;
        
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('error').shake();
            } else {
                $(this).removeClass('error');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
        }
    });

    // Efeito shake para campos com erro
    $.fn.shake = function() {
        this.each(function() {
            $(this).css('position', 'relative');
            for(var i = 0; i < 3; i++) {
                $(this).animate({left: -10}, 50)
                       .animate({left: 10}, 50)
                       .animate({left: 0}, 50);
            }
        });
    };
});
</script>