<?php if (!defined('ABSPATH')) exit; ?>

<div class="gma-list-wrap">
    <div class="gma-list-header">
        <h1 class="gma-list-title">📑 Categorias de Campanhas</h1>
        <a href="<?php echo admin_url('admin.php?page=gma-criar-categoria'); ?>" class="gma-button primary">
            <i class="dashicons dashicons-plus-alt"></i> Nova Categoria
        </a>
    </div>

    <?php settings_errors('gma_messages'); ?>

    <div class="gma-list-content">
        <?php
        $categories = gma_listar_categorias();

        if (empty($categories)) {
            echo '<div class="gma-empty-state">
                    <i class="dashicons dashicons-category"></i>
                    <p>Nenhuma categoria encontrada.</p>
                    <p>Comece criando uma nova categoria usando o botão acima.</p>
                  </div>';
        } else {
        ?>
            <div class="gma-card">
                <div class="gma-table-responsive">
                    <table class="gma-table">
                        <thead>
                            <tr>
                                <th>Nome da Categoria</th>
                                <th class="actions">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category) : ?>
                                <tr>
                                    <td><?php echo esc_html($category->nome); ?></td>
                                    <td class="actions">
                                        <button class="gma-button edit" onclick="openEditForm(<?php echo $category->id; ?>, '<?php echo esc_js($category->nome); ?>')">
                                            <i class="dashicons dashicons-edit"></i>
                                        </button>
                                        <form method="post" style="display:inline;">
                                            <?php wp_nonce_field('delete_category', 'gma_nonce'); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
                                            <button type="submit" class="gma-button delete" onclick="return confirmDelete()">
                                                <i class="dashicons dashicons-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Modal de Edição -->
    <div id="edit-modal" class="gma-modal">
        <div class="gma-modal-content">
            <div class="gma-modal-header">
                <h2>✏️ Editar Categoria</h2>
                <button type="button" class="gma-modal-close" onclick="closeEditForm()">&times;</button>
            </div>
            <form method="post" class="gma-modal-form">
                <?php wp_nonce_field('edit_category', 'gma_nonce'); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" id="edit-category-id">
                
                <div class="gma-form-group">
                    <label for="new-name">Nome da Categoria</label>
                    <input type="text" name="new_name" id="edit-category-name" required>
                </div>

                <div class="gma-modal-actions">
                    <button type="submit" class="gma-button primary">
                        <i class="dashicons dashicons-saved"></i> Salvar
                    </button>
                    <button type="button" class="gma-button secondary" onclick="closeEditForm()">
                        <i class="dashicons dashicons-no"></i> Cancelar
                    </button>
                </div>
            </form>
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

.gma-list-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.gma-list-title {
    font-size: 2.5em;
    color: var(--text-color);
    margin: 0;
}

.gma-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 20px;
    animation: slideIn 0.5s ease;
}

.gma-table-responsive {
    overflow-x: auto;
}

.gma-table {
    width: 100%;
    border-collapse: collapse;
}

.gma-table th,
.gma-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.gma-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.gma-table tr:hover {
    background: #f8f9fa;
}

.gma-button {
    padding: 10px 20px;
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

.gma-button.edit {
    background: var(--primary-color);
    color: white;
    padding: 8px;
}

.gma-button.delete {
    background: var(--danger-color);
    color: white;
    padding: 8px;
}

.gma-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.gma-empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #666;
}

.gma-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 20px;
}

.gma-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.gma-modal-content {
    position: relative;
    background: white;
    width: 90%;
    max-width: 500px;
    margin: 50px auto;
    border-radius: var(--border-radius);
    animation: modalSlideIn 0.3s ease;
}

.gma-modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.gma-modal-header h2 {
    margin: 0;
    font-size: 1.5em;
}

.gma-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.gma-modal-form {
    padding: 20px;
}

.gma-form-group {
    margin-bottom: 20px;
}

.gma-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.gma-form-group input {
    width: 100%;
    padding: 10px;
    border: 2px solid #eee;
    border-radius: var(--border-radius);
}

.gma-modal-actions {
    display: flex;
    gap: 10px;
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

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .gma-list-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .actions {
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
    function openEditForm(categoryId, categoryName) {
        $('#edit-modal').fadeIn(300);
        $('#edit-category-id').val(categoryId);
        $('#edit-category-name').val(categoryName);
    }

    function closeEditForm() {
        $('#edit-modal').fadeOut(300);
    }

    function confirmDelete() {
        return confirm('🗑️ Tem certeza que deseja excluir esta categoria?');
    }

    // Fechar modal ao clicar fora
    $(window).click(function(e) {
        if ($(e.target).is('#edit-modal')) {
            closeEditForm();
        }
    });

    // Prevenir fechamento ao clicar dentro do modal
    $('.gma-modal-content').click(function(e) {
        e.stopPropagation();
    });

    window.openEditForm = openEditForm;
    window.closeEditForm = closeEditForm;
    window.confirmDelete = confirmDelete;
});
</script>