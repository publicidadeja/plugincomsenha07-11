<?php
get_header();

$campanha_id = get_query_var('campanha_id');
gma_atualizar_visualizacao_campanha($campanha_id);
$campanha = gma_obter_campanha($campanha_id);
$estatisticas = gma_obter_estatisticas($campanha_id);

if ($campanha) :
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Estilos gerais */
    body {
        font-family: 'Roboto', sans-serif;
        line-height: 1.6;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .gma-campanha-wrapper {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .gma-campanha-hero {
        background-size: cover;
        background-position: center;
        color: white;
        padding: 40px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .gma-campanha-hero-content {
        text-align: center;
    }

    .gma-campanha-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .gma-campanha-dates {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 10px;
    }

    .gma-date-item {
        margin-right: 20px;
    }

    .gma-date-item i {
        margin-right: 5px;
    }

    .gma-download-button {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
    }

    .gma-download-button i {
        margin-right: 5px;
    }

    .gma-download-button:hover {
        background-color: #0062cc;
    }

    /* Ícones */
    i.fas, i.fab {
        margin-right: 8px;
    }

    /* Seções do conteúdo */
    .gma-campanha-content {
        display: flex;
        gap: 20px;
    }

    .gma-campanha-sidebar {
        width: 300px;
    }

    .gma-campanha-main {
        flex-grow: 1;
    }

    .gma-campanha-section {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .gma-campanha-section h2 {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .gma-campanha-section h2 i {
        color: var(--primary-color);
        margin-right: 10px;
    }

    .gma-content-expandable {
        /* Estilos para o conteúdo expansível */
    }

    .gma-campanha-stats {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .gma-stats-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .gma-stats-list li {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }

    .gma-stats-list li i {
        margin-right: 8px;
        color: var(--primary-color);
    }

    .gma-campanha-copy {
        /* Estilos para a seção de Copy */
    }

    .gma-copy-button {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
    }

    .gma-copy-button i {
        margin-right: 5px;
    }

    .gma-copy-button:hover {
        background-color: #0062cc;
        color: white;
        text-decoration: none;
    }

    .gma-campanha-materiais {
        /* Estilos para a seção de Materiais */
    }

    .gma-materiais-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .gma-material-card {
        background-color: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .gma-material-image {
        background-size: cover;
        background-position: center;
        height: 200px;
        border-radius: 8px;
        position: relative;
        cursor: pointer;
    }

    .gma-material-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        padding: 10px;
        border-radius: 0 0 8px 8px;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: background-color 0.3s ease;
    }

    .gma-material-overlay:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .gma-material-copy {
        margin-top: 10px;
        /* Estilos para o texto da copy do material */
    }

    /* Botões */
    .gma-button {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: none;
        cursor: pointer;
    }

    .gma-button-primary {
        background-color: #007bff;
        color: white;
    }

    .gma-button-primary:hover {
        background-color: #0062cc;
    }

    .gma-button-secondary {
        background-color: #ffc107;
        color: white;
    }

    .gma-button-secondary:hover {
        background-color: #e0a800;
    }

    /* Lightbox */
    .gma-lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8); /* Fundo escurecido */
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000; /* Certifique-se de que o lightbox fique por cima de outros elementos */
    }

    .gma-lightbox-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 90%; /* Ajuste a largura conforme necessário */
        text-align: center;
        margin: 20px;
        position: relative; /* Para posicionar o botão de fechar */
    }

    .gma-lightbox-image {
        max-width: 100%;
        max-height: 80vh; /* Ajuste a altura máxima conforme necessário */
        display: block;
        margin: 0 auto;
    }

    .gma-lightbox-close {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        background-color: #fff;
        color: #333;
        padding: 10px 15px;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .gma-lightbox-close:hover {
        background-color: #ddd;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .gma-campanha-content {
            flex-direction: column;
        }

        .gma-campanha-sidebar {
            width: 100%;
            margin-bottom: 20px;
        }

        .gma-button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .gma-copy-button {
            flex: 1 1 100px; /* Ajusta a largura dos botões */
            margin-bottom: 5px; /* Adiciona espaçamento entre os botões */
        }
    }

    .gma-button-group {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .gma-copy-button {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
    }

    .gma-copy-button i {
        margin-right: 5px;
    }

    .gma-copy-button:hover {
        background-color: #0062cc;
        color: white;
        text-decoration: none;
    }

    /* Estilos para evitar o scroll lateral */
    .gma-campanha-section p {
        word-break: break-word; /* Permite que as palavras quebrem dentro da linha */
    }

    .gma-campanha-section pre {
        white-space: pre-wrap; /* Preserva espaços em branco e quebra de linha */
    }
</style>

<div class="gma-campanha-wrapper">
    <div class="gma-campanha-hero" style="background-image: url('<?php echo esc_url($campanha->imagem_url); ?>');">
        <div class="gma-campanha-hero-content">
            <h1 class="gma-campanha-title"><?php echo esc_html($campanha->nome); ?></h1>
            <div class="gma-campanha-dates">
                <span class="gma-date-item"><i class="fas fa-calendar-alt"></i> Criada em: <?php echo esc_html(date('d/m/Y', strtotime($campanha->data_criacao))); ?></span>
            </div>
        </div>

        <?php if (!empty($campanha->imagem_url)) : ?>
            <a href="<?php echo esc_url($campanha->imagem_url); ?>" class="gma-download-button" onclick="window.open(this.href); return false;">
                <i class="fas fa-download"></i> Baixar Imagem
            </a>
        <?php endif; ?>
    </div>

    <div class="gma-campanha-content">
        <div class="gma-campanha-sidebar">
            <div class="gma-sidebar-item gma-campanha-stats">
                <h3><i class="fas fa-chart-bar"></i> Estatísticas da Campanha</h3>
                <ul class="gma-stats-list">
                    <li><i class="fas fa-eye"></i> Visualizações: <?php echo esc_html($estatisticas->visualizacoes); ?></li>
                    <li><i class="fas fa-mouse-pointer"></i> Cliques: <?php echo esc_html($estatisticas->cliques); ?></li>
                    <li><i class="fas fa-chart-line"></i> Conversões: <?php echo esc_html($estatisticas->conversoes); ?></li>
                </ul>
            </div>

            <?php if (!empty($campanha->link_canva)) : ?>
                <div class="gma-sidebar-item">
                    <a href="<?php echo esc_url($campanha->link_canva); ?>" target="_blank" class="gma-button gma-button-primary" data-campanha-id="<?php echo esc_attr($campanha_id); ?>">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="gma-campanha-main">
            <?php if (!empty($campanha->descricao)) : ?>
                <div class="gma-campanha-section gma-campanha-descricao">
                    <h2><i class="fas fa-info-circle"></i> Sobre a Campanha</h2>
                    <div class="gma-content-expandable">
                        <?php echo wp_kses_post($campanha->descricao); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($campanha->copy)) : ?>
                <div class="gma-campanha-section gma-campanha-copy">
                    <h2><i class="fas fa-copy"></i> Copy da Campanha</h2>
                    <div class="gma-content-expandable">
                        <p id="copy-text"><?php echo wp_kses_post($campanha->copy); ?></p>
                        <button class="gma-copy-button" onclick="copiarTexto('copy-text')">
                            <i class="fas fa-clipboard"></i> Copiar Texto
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $materiais = gma_obter_materiais_campanha($campanha_id);

            if ($materiais) :
            ?>
                <div class="gma-campanha-section gma-campanha-materiais">
                    <h2><i class="fas fa-images"></i> Materiais da Campanha</h2>
                    <div class="gma-materiais-grid">
                        <?php foreach ($materiais as $material) : ?>
                            <div class="gma-material-card">
                                <?php if (!empty($material->imagem_url)) : ?>
                                    <div class="gma-material-image" style="background-image: url('<?php echo esc_url($material->imagem_url); ?>')" data-src="<?php echo esc_url($material->imagem_url); ?>">
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($material->copy)) : ?>
                                    <div class="gma-material-copy">
                                        <p id="copy-text-<?php echo esc_attr($material->id); ?>"><?php echo wp_kses_post($material->copy); ?></p>
                                        <div class="gma-button-group">
                                            <button class="gma-copy-button" onclick="copiarTexto('copy-text-<?php echo esc_attr($material->id); ?>')">
                                                <i class="fas fa-clipboard"></i> Copiar Texto
                                            </button>
                                            <?php if (!empty($material->link_canva)) : ?>
                                                <a href="<?php echo esc_url($material->link_canva); ?>" target="_blank" class="gma-copy-button">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($material->imagem_url)) : ?>
                                                <a href="<?php echo esc_url($material->imagem_url); ?>" download class="gma-copy-button">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="gma-lightbox">
    <div class="gma-lightbox-content">
        <img class="gma-lightbox-image" src="" id="lightbox-image" alt="Imagem da Campanha">
        <span class="gma-lightbox-close" onclick="fecharLightbox()">×</span>
    </div>
</div>

<script>
function copiarTexto(id) {
    const text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text)
        .then(() => {
            alert('Texto copiado para a área de transferência!');
        })
        .catch(err => {
            console.error('Falha ao copiar texto: ', err);
        });
}

function abrirLightbox(imagemUrl) {
    document.getElementById('lightbox-image').src = imagemUrl;
    document.querySelector('.gma-lightbox').style.display = 'flex';
}

function fecharLightbox() {
    document.querySelector('.gma-lightbox').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const materialImages = document.querySelectorAll('.gma-material-image');
    materialImages.forEach(image => {
        image.addEventListener('click', () => {
            abrirLightbox(image.dataset.src);
        });
    });
});
</script>

<?php
endif;
get_footer();
?>