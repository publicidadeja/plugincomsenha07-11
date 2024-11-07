<?php if (!defined('ABSPATH')) exit; ?>

<div class="gma-edit-wrap">
    <div class="gma-edit-container">
        <h1 class="gma-edit-title">✏️ Editar Campanha</h1>

        <?php 
        if (isset($_POST['atualizar_campanha'])) {
            $nome = sanitize_text_field($_POST['nome_campanha']);
            $cliente = sanitize_text_field($_POST['cliente_campanha']);
            $categoria_id = isset($_POST['categoria_id']) && !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
            $tipo_campanha = isset($_POST['tipo_campanha']) ? sanitize_text_field($_POST['tipo_campanha']) : 'marketing';

            if (gma_atualizar_campanha($campanha->id, $nome, $cliente, $categoria_id, $tipo_campanha)) {
                echo '<div class="gma-notice success"><p>✅ Campanha atualizada com sucesso!</p></div>';
                $campanha = gma_obter_campanha($campanha->id);
            } else {
                echo '<div class="gma-notice error"><p>❌ Erro ao atualizar a campanha. Tente novamente.</p></div>';
            }
        }
        ?>

        <div class="gma-edit-card">
            <div class="gma-edit-card-header">
                <h2>Informações da Campanha</h2>
            </div>
            <div class="gma-edit-card-body">
                <form method="post" class="gma-edit-form">
                    <input type="hidden" name="campanha_id" value="<?php echo esc_attr($campanha->id); ?>">
                    
                    <div class="gma-form-group">
                        <label for="nome_campanha">Nome da Campanha</label>
                        <input type="text" 
                               id="nome_campanha"
                               name="nome_campanha" 
                               class="gma-input" 
                               value="<?php echo esc_attr($campanha->nome); ?>" 
                               required>
                    </div>

                    <div class="gma-form-group">
                        <label for="cliente_campanha">Cliente/Projeto</label>
                        <input type="text" 
                               id="cliente_campanha"
                               name="cliente_campanha" 
                               class="gma-input" 
                               value="<?php echo esc_attr($campanha->cliente); ?>" 
                               required>
                    </div>

                    <div class="gma-form-group">
                        <label for="categoria_id">Categoria</label>
                        <select name="categoria_id" id="categoria_id" class="gma-select">
                            <option value="">Selecione uma categoria (opcional)</option>
                            <?php foreach (gma_listar_categorias() as $categoria) : ?>
                                <option value="<?php echo esc_attr($categoria->id); ?>" 
                                        <?php selected($campanha->categoria_id, $categoria->id); ?>>
                                    <?php echo esc_html($categoria->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gma-form-group">
                        <label for="tipo_campanha">Tipo de Campanha</label>
                        <select id="tipo_campanha" name="tipo_campanha" class="gma-select">
                            <option value="marketing" <?php selected($campanha->tipo_campanha, 'marketing'); ?>>
                                📈 Campanha de Marketing
                            </option>
                            <option value="aprovacao" <?php selected($campanha->tipo_campanha, 'aprovacao'); ?>>
                                ✅ Aprovação de Conteúdo
                            </option>
                        </select>
                    </div>

                    <div class="gma-form-actions">
                        <button type="submit" name="atualizar_campanha" class="gma-button primary">
                            💾 Salvar Alterações
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=gma-campanhas'); ?>" class="gma-button secondary">
                            ← Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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

.gma-edit-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-edit-container {
    max-width: 800px;
    margin: 0 auto;
}

.gma-edit-title {
    font-size: 2.5em;
    color: var(--text-color);
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.gma-edit-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    transition: var(--transition);
    overflow: hidden;
    animation: slideIn 0.5s ease;
}

.gma-edit-card-header {
    padding: 20px;
    background: linear-gradient(135deg, var(--primary-color), #357abd);
    color: white;
}

.gma-edit-card-header h2 {
    margin: 0;
    font-size: 1.5em;
}

.gma-edit-card-body {
    padding: 30px;
}

.gma-form-group {
    margin-bottom: 20px;
}

.gma-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-color);
}

.gma-input, .gma-select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e1e1;
    border-radius: var(--border-radius);
    font-size: 1em;
    transition: var(--transition);
}

.gma-input:focus, .gma-select:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
}

.gma-button {
    padding: 12px 24px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-weight: 600;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
}

.gma-notice {
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    animation: slideIn 0.5s ease;
}

.gma-notice.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.gma-notice.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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
    .gma-edit-container {
        padding: 0 15px;
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
document.addEventListener('DOMContentLoaded', function() {
    // Animação para inputs
    const inputs = document.querySelectorAll('.gma-input, .gma-select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });

    // Validação do formulário
    const form = document.querySelector('.gma-edit-form');
    form.addEventListener('submit', function(e) {
        const requiredInputs = form.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = 'var(--danger-color)';
                input.style.animation = 'shake 0.5s ease';
            } else {
                input.style.borderColor = '';
                input.style.animation = '';
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Animação de shake para campos inválidos
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    `;
    document.head.appendChild(style);
});
</script>