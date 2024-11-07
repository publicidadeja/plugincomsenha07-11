<?php
if (!defined('ABSPATH')) exit;
?>

<div class="gma-activation-wrap">
    <div class="gma-activation-container">
        <h1 class="gma-activation-title">🔑 Ativação do Plugin</h1>

        <?php
        // Exibir mensagens de erro/sucesso
        if (isset($_GET['message'])) {
            $message_type = isset($_GET['type']) ? $_GET['type'] : 'error';
            $message_class = $message_type === 'success' ? 'gma-notice-success' : 'gma-notice-error';
            echo '<div class="gma-notice ' . $message_class . '">';
            echo '<p>' . esc_html($_GET['message']) . '</p>';
            echo '</div>';
        }
        ?>

        <div class="gma-activation-card">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="gma-activation-form">
                <?php wp_nonce_field('gma_ativar_licenca', 'gma_licenca_nonce'); ?>
                <input type="hidden" name="action" value="gma_ativar_licenca">

                <div class="gma-form-group">
                    <label for="codigo_licenca">
                        <i class="dashicons dashicons-key"></i> Código de Licença
                    </label>
                    <input type="text" 
                           name="codigo_licenca" 
                           id="codigo_licenca" 
                           class="gma-input"
                           placeholder="XXXX-XXXX-XXXX-XXXX"
                           required>
                </div>

                <div class="gma-form-actions">
                    <button type="submit" class="gma-button primary">
                        <i class="dashicons dashicons-yes"></i> Ativar Licença
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #4a90e2;
    --success-color: #2ecc71;
    --error-color: #e74c3c;
    --text-color: #2c3e50;
    --background-color: #f5f6fa;
    --card-background: #ffffff;
    --border-radius: 10px;
    --transition: all 0.3s ease;
}

.gma-activation-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-activation-container {
    max-width: 600px;
    margin: 0 auto;
}

.gma-activation-title {
    font-size: 2.5em;
    color: var(--text-color);
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
}

.gma-activation-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 30px;
    animation: slideIn 0.5s ease;
}

.gma-form-group {
    margin-bottom: 20px;
}

.gma-form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-color);
}

.gma-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e1e1;
    border-radius: var(--border-radius);
    font-size: 1.2em;
    transition: var(--transition);
    text-align: center;
    letter-spacing: 2px;
}

.gma-input:focus {
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    text-decoration: none;
    width: 100%;
    justify-content: center;
}

.gma-button.primary {
    background: var(--primary-color);
    color: white;
}

.gma-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.gma-notice {
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    animation: slideIn 0.5s ease;
}

.gma-notice-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.gma-notice-error {
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
    .gma-activation-container {
        padding: 0 15px;
    }
    
    .gma-activation-title {
        font-size: 2em;
    }
}
</style>