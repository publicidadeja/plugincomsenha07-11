<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="gma-wrap">
    <h1 class="gma-main-title">Gerenciador de Campanhas</h1>

    <?php
    // Mensagens de sucesso/erro
    if (isset($_GET['success']) && $_GET['success'] === 'true') {
        echo '<div class="gma-notice success"><p>✓ Campanha excluída com sucesso!</p></div>';
    }
    
    // Obter campanhas com filtro de categoria
    $categoria_filtro = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
    $campanhas = $categoria_filtro !== null ? 
        array_filter(gma_listar_campanhas(), function($c) use ($categoria_filtro) {
            return $c->categoria_id == $categoria_filtro;
        }) : 
        gma_listar_campanhas();
    ?>

    <div class="gma-container">
        <!-- Form de Nova Campanha -->
        <div class="gma-card nova-campanha">
            <div class="gma-card-header">
                <h2>📝 Nova Campanha</h2>
            </div>
            <div class="gma-card-body">
                <form method="post" class="gma-form">
                    <div class="gma-form-group">
                        <input type="text" name="nome_campanha" class="gma-input" placeholder="Nome da Campanha" required>
                    </div>
                    <div class="gma-form-group">
                        <input type="text" name="cliente_campanha" class="gma-input" placeholder="Nome do Cliente/Projeto" required>
                    </div>
                    <div class="gma-form-group">
                        <select name="categoria_id" class="gma-select" required>
                            <option value="">Selecione uma categoria (Crie em "Categorias"</option>
                            <?php foreach (gma_listar_categorias() as $categoria) : ?>
                                <option value="<?php echo esc_attr($categoria->id); ?>">
                                    <?php echo esc_html($categoria->nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="gma-form-group">
                        <select id="tipo_campanha" name="tipo_campanha" class="gma-select" required>
                            <option value="">Selecione o tipo de campanha</option>
                            <option value="marketing">Campanha de Marketing</option>
                            <option value="aprovacao">Aprovação de Conteúdo</option>
                        </select>
                    </div>
                    <button type="submit" name="criar_campanha" class="gma-button primary">
                        <span class="icon">✓</span> Criar Campanha
                    </button>
                </form>
            </div>
        </div>

        <!-- Filtro de Campanhas -->
        <div class="gma-card filtro-campanhas">
            <div class="gma-card-header">
                <h2>🔍 Filtrar Campanhas</h2>
            </div>
            <div class="gma-card-body">
                <form method="get" class="gma-form-inline">
                    <input type="hidden" name="page" value="gma-campanhas">
                    <select name="categoria" class="gma-select">
                        <option value="">Todas as categorias</option>
                        <?php foreach (gma_listar_categorias() as $categoria) : ?>
                            <option value="<?php echo esc_attr($categoria->id); ?>" 
                                <?php selected(isset($_GET['categoria']) && $_GET['categoria'] == $categoria->id); ?>>
                                <?php echo esc_html($categoria->nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="gma-button secondary">
                        <span class="icon">🔍</span> Filtrar
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de Campanhas -->
        <div class="gma-card campanhas-existentes">
            <div class="gma-card-header">
                <h2>📋 Campanhas Existentes</h2>
            </div>
            <div class="gma-card-body">
                <div class="gma-table-responsive">
                    <table class="gma-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Cliente/Projeto</th>
                                <th>Categoria</th>
                                <th>Data de Criação</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campanhas as $campanha) : 
                                // Gerar URL de exclusão com nonce
                                $delete_url = wp_nonce_url(
                                    admin_url('admin-post.php?action=gma_excluir_campanha&campanha_id=' . $campanha->id),
                                    'gma_excluir_campanha'
                                );
                                
                                // Gerar link da campanha
                                $link_campanha = home_url('/campanha/' . $campanha->id);
                            ?>
                                <tr class="gma-table-row">
                                    <td><?php echo esc_html($campanha->nome); ?></td>
                                    <td><?php echo esc_html($campanha->cliente); ?></td>
                                    <td>
                                        <?php
                                        if ($campanha->categoria_id) {
                                            $categoria = gma_obter_categoria($campanha->categoria_id);
                                            echo esc_html($categoria->nome);
                                        } else {
                                            echo 'Sem categoria';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($campanha->data_criacao); ?></td>
                                    <td>
                                        <span class="gma-badge <?php echo esc_attr($campanha->tipo_campanha); ?>">
                                            <?php echo esc_html($campanha->tipo_campanha); ?>
                                        </span>
                                    </td>
                                    <td class="gma-actions">
                                        <div class="gma-button-group">
                                            <a href="<?php echo admin_url('admin.php?page=gma-novo-material&campanha_id=' . $campanha->id); ?>" 
                                               class="gma-button icon-button" 
                                               title="Adicionar Material">
                                                ➕
                                            </a>
                                            <a href="<?php echo esc_url($link_campanha); ?>" 
                                               class="gma-button icon-button" 
                                               title="Ver Campanha" 
                                               target="_blank">
                                                👁️
                                            </a>
                                            <a href="<?php echo admin_url('admin.php?page=gma-editar-campanha&campanha_id=' . $campanha->id); ?>" 
                                               class="gma-button icon-button" 
                                               title="Editar">
                                                ✏️
                                            </a>
                                            <a href="<?php echo esc_url($delete_url); ?>" 
                                               class="gma-button icon-button delete" 
                                               title="Excluir">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

.gma-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-main-title {
    font-size: 2.5em;
    color: var(--text-color);
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.gma-container {
    max-width: 1200px;
    margin: 0 auto;
}

.gma-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    transition: var(--transition);
    overflow: hidden;
}

.gma-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.gma-card-header {
    padding: 20px;
    background: linear-gradient(135deg, var(--primary-color), #357abd);
    color: white;
}

.gma-card-header h2 {
    margin: 0;
    font-size: 1.5em;
}

.gma-card-body {
    padding: 20px;
}

.gma-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
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
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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

.gma-button.icon-button {
    padding: 8px;
    border-radius: 50%;
    width: 35px;
    height: 35px;
}

.gma-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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

.gma-table-row:hover {
    background: #f8f9fa;
}

.gma-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
}

.gma-badge.marketing {
    background: #e3f2fd;
    color: #1976d2;
}

.gma-badge.aprovacao {
    background: #e8f5e9;
    color: #388e3c;
}

.gma-button-group {
    display: flex;
    gap: 8px;
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
    .gma-table-responsive {
        overflow-x: auto;
    }
    
    .gma-button-group {
        flex-wrap: wrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animação para cards
    const cards = document.querySelectorAll('.gma-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Feedback visual nos botões
    const buttons = document.querySelectorAll('.gma-button');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.classList.contains('delete')) {
                if (!confirm('Tem certeza que deseja excluir esta campanha?')) {
                    e.preventDefault();
                }
            }
            
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
        });
    });

    // Highlight na linha da tabela
    const tableRows = document.querySelectorAll('.gma-table-row');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'background-color 0.3s ease';
        });
    });
});
</script>