

<?php
if (!defined('ABSPATH')) exit;
// Handle category deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (
        isset($_POST['gma_nonce']) && 
        wp_verify_nonce($_POST['gma_nonce'], 'delete_category') && 
        isset($_POST['category_id'])
    ) {
        $category_id = intval($_POST['category_id']);
        
        if (gma_excluir_categoria($category_id)) {
            echo '<div class="gma-notice success">
                    <i class="dashicons dashicons-yes-alt"></i> 
                    Categoria excluída com sucesso!
                  </div>';
        } else {
            echo '<div class="gma-notice error">
                    <i class="dashicons dashicons-warning"></i> 
                    Erro ao excluir categoria.
                  </div>';
        }
    }
}

// Handle category edit
if (isset($_POST['action']) && $_POST['action'] === 'edit_category') {
    if (
        isset($_POST['gma_nonce']) && 
        wp_verify_nonce($_POST['gma_nonce'], 'edit_category') && 
        isset($_POST['category_id']) && 
        isset($_POST['new_name'])
    ) {
        $category_id = intval($_POST['category_id']);
        $new_name = sanitize_text_field($_POST['new_name']);
        
        if (gma_editar_categoria($category_id, $new_name)) {
            echo '<div class="gma-notice success">
                    <i class="dashicons dashicons-yes-alt"></i> 
                    Categoria atualizada com sucesso!
                  </div>';
        } else {
            echo '<div class="gma-notice error">
                    <i class="dashicons dashicons-warning"></i> 
                    Erro ao atualizar categoria.
                  </div>';
        }
    }
}
?><?php if (!defined('ABSPATH')) exit; ?>
<!-- Modal de Edição -->
<div id="edit-form" class="gma-modal" style="display:none;">
    <div class="gma-modal-content">
        <div class="gma-modal-header">
            <h2><i class="dashicons dashicons-edit"></i> Editar Categoria</h2>
        </div>
        <div class="gma-modal-body">
<form method="post" class="gma-form">
    <?php wp_nonce_field('edit_category', 'gma_nonce'); ?>
    <input type="hidden" name="action" value="edit_category">
    <input type="hidden" name="category_id" id="edit-category-id">
    <div class="gma-form-group">
        <label for="new-name">Novo Nome:</label>
        <input type="text" 
               name="new_name" 
               id="edit-category-name" 
               required>
    </div>
    <div class="gma-form-actions">
        <button type="submit" class="gma-button primary">
            <i class="dashicons dashicons-saved"></i> Salvar
        </button>
        <button type="button" 
                class="gma-button" 
                onclick="closeEditForm()">
            <i class="dashicons dashicons-no"></i> Cancelar
        </button>
    </div>
</form>
        </div>
    </div>
</div>
<div class="gma-create-wrap">
    <div class="gma-create-header">
        <h1 class="gma-create-title">📝 Nova Categoria</h1>
    </div>

    <?php
    if (isset($_POST['criar_categoria'])) {
        $nome = sanitize_text_field($_POST['nome_categoria']);
        $categoria_id = gma_criar_categoria($nome);

        if ($categoria_id) {
            echo '<div class="gma-notice success">
                    <i class="dashicons dashicons-yes-alt"></i> 
                    Categoria criada com sucesso!
                  </div>';
        } else {
            echo '<div class="gma-notice error">
                    <i class="dashicons dashicons-warning"></i> 
                    Erro ao criar categoria. Verifique se já não existe uma categoria com este nome.
                  </div>';
        }
    }
    ?>

    <div class="gma-content-grid">
        <!-- Formulário de Criação -->
        <div class="gma-card">
            <div class="gma-card-header">
                <h2><i class="dashicons dashicons-plus-alt"></i> Criar Nova Categoria</h2>
            </div>
            <div class="gma-card-content">
                <form method="post" class="gma-form" id="create-category-form">
                    <div class="gma-form-group">
                        <label for="nome_categoria">Nome da Categoria</label>
                        <input type="text" 
                               name="nome_categoria" 
                               id="nome_categoria" 
                               required 
                               placeholder="Digite o nome da categoria">
                    </div>
                    <button type="submit" 
                            name="criar_categoria" 
                            class="gma-button primary">
                        <i class="dashicons dashicons-saved"></i> 
                        Criar Categoria
                    </button>
                </form>
            </div>
        </div>

        <!-- Listagem de Categorias -->
        <div class="gma-card">
            <div class="gma-card-header">
                <h2><i class="dashicons dashicons-category"></i> Categorias Existentes</h2>
            </div>
            <div class="gma-card-content">
                <?php
                $categories = gma_listar_categorias();
                if (empty($categories)) {
                    echo '<div class="gma-empty-state">
                            <i class="dashicons dashicons-format-status"></i>
                            <p>Nenhuma categoria cadastrada.</p>
                            <p>Crie sua primeira categoria usando o formulário ao lado.</p>
                          </div>';
                } else {
                ?>
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
                                            <button class="gma-button edit" 
                                                    onclick="openEditForm(<?php echo $category->id; ?>, '<?php echo esc_js($category->nome); ?>')">
                                                <i class="dashicons dashicons-edit"></i>
                                            </button>
                                            <form method="post" style="display:inline;">
                                                <?php wp_nonce_field('delete_category', 'gma_nonce'); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
                                                <button type="submit" 
                                                        class="gma-button delete" 
                                                        onclick="return confirmDelete()">
                                                    <i class="dashicons dashicons-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
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

.gma-create-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-create-header {
    margin-bottom: 30px;
    text-align: center;
}

.gma-create-title {
    font-size: 2.5em;
    color: var(--text-color);
    margin: 0;
}

.gma-content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.gma-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    animation: slideIn 0.5s ease;
}

.gma-card-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.gma-card-header h2 {
    margin: 0;
    font-size: 1.2em;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.gma-card-content {
    padding: 20px;
}

.gma-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.gma-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.gma-form-group label {
    font-weight: 600;
    color: var(--text-color);
}

.gma-form-group input {
    padding: 12px;
    border: 2px solid #eee;
    border-radius: var(--border-radius);
    font-size: 1em;
    transition: var(--transition);
}

.gma-form-group input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(74,144,226,0.2);
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

.gma-table-responsive {
    overflow-x: auto;
}

.gma-table {
    width: 100%;
    border-collapse: collapse;
}

.gma-table th,
.gma-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.gma-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.gma-notice {
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.5s ease;
}

.gma-notice.success {
    background: #d4edda;
    color: #155724;
}

.gma-notice.error {
    background: #f8d7da;
    color: #721c24;
}

.gma-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.gma-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 20px;
}
  
  .gma-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.gma-modal-content {
    background: var(--card-background);
    border-radius: var(--border-radius);
    width: 100%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.3s ease;
}

.gma-modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.gma-modal-header h2 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.gma-modal-body {
    padding: 20px;
}

.gma-form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
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
    .gma-content-grid {
        grid-template-columns: 1fr;
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
    window.openEditForm = function(categoryId, categoryName) {
        $('#edit-form').fadeIn(200);
        $('#edit-category-id').val(categoryId);
        $('#edit-category-name').val(categoryName);
    }

    window.closeEditForm = function() {
        $('#edit-form').fadeOut(200);
    }

    // Fechar modal ao clicar fora
    $(document).on('click', '.gma-modal', function(e) {
        if ($(e.target).hasClass('gma-modal')) {
            closeEditForm();
        }
    });

    // Fechar modal com tecla ESC
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            closeEditForm();
        }
    });

    function confirmDelete() {
        return confirm('🗑️ Tem certeza que deseja excluir esta categoria?');
    }

    $('#create-category-form').on('submit', function(e) {
        var categoryName = $('#nome_categoria').val().trim();
        if (!categoryName) {
            e.preventDefault();
            alert('Por favor, insira um nome para a categoria.');
            return false;
        }
    });

    window.confirmDelete = confirmDelete;
});
</script>