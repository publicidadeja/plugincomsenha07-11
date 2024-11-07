<?php
// Enfileira os scripts e estilos necessários
wp_enqueue_script('jquery');
wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);
wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js', array(), null, true);
wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
wp_enqueue_script('gma-script', plugin_dir_url(__FILE__) . '../assets/js/gma-script.js', array('jquery', 'swiper', 'gsap', 'sweetalert2'), '1.0.0', true);

wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css');
wp_enqueue_style('sweetalert2-css', 'https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css');

// Passa dados para o JavaScript
wp_localize_script('gma-script', 'gmaAjax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('gma_ajax_nonce')
));

get_header();

$campanha_id = get_query_var('campanha_id'); 
gma_atualizar_visualizacao_campanha($campanha_id); 
$campanha = gma_obter_campanha($campanha_id);
$materiais = gma_listar_materiais($campanha_id);

if ($campanha) :
?>

<style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2ecc71;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --text-color: #34495e;
        --background-color: #ecf0f1;
    }

    body {
        font-family: 'Roboto', sans-serif;
        background-color: var(--background-color);
        color: var(--text-color);
    }

    .gma-container {
        max-width: 900px; /* Ajuste a largura máxima conforme necessário */
        margin: 0 auto;
        padding: 20px;
    }

    .gma-title {
        font-size: 2rem; /* Aumente o tamanho da fonte do título */
        font-weight: 700;
        color: var(--primary-color);
        text-align: center;
        margin-bottom: 30px;
        text-transform: uppercase;
    }

    .swiper-container {
        width: 100%;
        padding-top: 50px;
        padding-bottom: 50px;
    }

    .swiper-slide {
        background-position: center;
        background-size: cover;
        width: 100%;
        height: auto;
        min-height: 300px;
    }

    .gma-material {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .gma-material-image-container {
        position: relative;
        height: 250px; /* Ajuste a altura da imagem conforme necessário */
        overflow: hidden;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .gma-material-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gma-material-zoom {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 50%;
        padding: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .gma-material-zoom:hover {
        background-color: rgba(255, 255, 255, 0.9);
    }

    .gma-material-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .gma-copy {
        font-size: 1rem; /* Ajuste o tamanho da fonte do copy */
        line-height: 1.5;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .gma-status {
        font-weight: 500;
        text-transform: uppercase;
        margin-bottom: 15px;
    }

    .gma-material.status-aprovado .gma-status { color: var(--secondary-color); }
    .gma-material.status-reprovado .gma-status { color: var(--danger-color); }
    .gma-material.status-pendente .gma-status { color: var(--warning-color); }

    .gma-acoes {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 20px;
    }

    .gma-acoes button {
        padding: 10px 15px; /* Aumente o padding dos botões */
        border: none;
        border-radius: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.1s ease;
        flex: 1;
    }

    .gma-aprovar { background-color: var(--secondary-color); color: white; }
    .gma-reprovar { background-color: var(--danger-color); color: white; }
    .gma-editar { background-color: var(--primary-color); color: white; }

    .lightbox {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden; /* Impede scroll na imagem */
        background-color: rgba(0,0,0,0.9);
    }

    .lightbox-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .close-lightbox {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
        cursor: pointer;
    }

    .close-lightbox:hover,
    .close-lightbox:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    .gma-edicao {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin-top: 15px;
        display: none;
    }

    .gma-edicao h3 {
        margin-bottom: 10px;
    }

    .gma-edicao label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .gma-edicao textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        resize: vertical;
        margin-bottom: 15px;
    }

    .gma-edicao button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-right: 10px;
    }

    .gma-edicao .gma-salvar-edicao {
        background-color: var(--secondary-color);
        color: white;
    }

    .gma-edicao .gma-cancelar-edicao {
        background-color: var(--danger-color);
        color: white;
    }

    @media (max-width: 768px) {
        .swiper-container {
            slidesPerView: 1; 
        }

        .gma-container {
            max-width: 100%;
        }

        .gma-material-card {
            width: 100%;
        }

        .gma-acoes {
            flex-direction: column;
        }

        .gma-aprovar, .gma-reprovar, .gma-editar {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .gma-campanha-content {
            flex-direction: column;
            gap: 1rem;
        }

        .gma-campanha-sidebar {
            margin-right: 0;
            margin-bottom: 2rem;
        }

        .gma-campanha-hero {
            height: 40vh;
        }

        .gma-campanha-title {
            font-size: 1.8rem;
        }

        .gma-campanha-dates {
            flex-direction: column;
            gap: 0.5rem;
        }

        .gma-copy {
            font-size: 0.8rem;
        }

        .gma-material-image-container {
            height: 250px;
        }

        .gma-material-zoom {
            top: 5px;
            right: 5px;
        }
    }
</style>

<div class="gma-container">
    <h1 class="gma-title"><?php echo esc_html($campanha->nome); ?></h1>

    <?php if ($materiais) : ?>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ($materiais as $material) : ?>
                    <div class="swiper-slide">
                        <div class="gma-material status-<?php echo esc_attr($material->status_aprovacao ?? 'pendente'); ?>" data-material-id="<?php echo esc_attr($material->id); ?>">
                            <div class="gma-material-image-container">
                                <img class="gma-material-image lightbox-trigger" src="<?php echo esc_url($material->imagem_url); ?>" alt="Material">
                                <span class="gma-material-zoom" title="Ampliar imagem">🔍</span>
                            </div>
                            <div class="gma-material-content">
                                <p class="gma-copy"><?php echo wp_kses_post($material->copy ?? ''); ?></p>
                                <p class="gma-status">Status: <?php echo esc_html(ucfirst($material->status_aprovacao ?? 'Pendente')); ?></p>
                                <div class="gma-acoes">
                                    <button class="gma-aprovar" <?php echo $material->status_aprovacao === 'aprovado' ? 'disabled' : ''; ?>>Aprovar</button>
                                    <button class="gma-reprovar" <?php echo $material->status_aprovacao === 'reprovado' ? 'disabled' : ''; ?>>Reprovar</button>
                                    <button class="gma-editar" data-material-id="<?php echo esc_attr($material->id); ?>">Editar</button>
                                </div>
                                <div class="gma-edicao" style="display: none;">
                                    <h3>Editar Material</h3>
                                    <label for="alteracao-arte-<?php echo esc_attr($material->id); ?>">Qual alteração na arte?</label>
                                    <textarea id="alteracao-arte-<?php echo esc_attr($material->id); ?>" class="gma-alteracao-arte" rows="4"><?php echo esc_textarea($material->feedback ?? ''); ?></textarea>
                                    <label for="copy-edit-<?php echo esc_attr($material->id); ?>">Editar Copy:</label>
                                    <textarea id="copy-edit-<?php echo esc_attr($material->id); ?>" class="gma-copy-edit" rows="4"><?php echo esc_textarea($material->copy ?? ''); ?></textarea>
                                    <button class="gma-salvar-edicao" data-material-id="<?php echo esc_attr($material->id); ?>">Salvar Edição</button>
                                    <button class="gma-cancelar-edicao">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    <?php else : ?>
        <p>Nenhum material encontrado para esta campanha.</p>
    <?php endif; ?>
</div>

<div id="imageLightbox" class="lightbox">
    <span class="close-lightbox">×</span>
    <img class="lightbox-content" id="lightboxImage">
</div>

<?php
endif;

add_action('wp_footer', 'gma_initialize_swiper', 100);
function gma_initialize_swiper() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var swiper = new Swiper('.swiper-container', {
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        // Lightbox
        $('.lightbox-trigger').click(function() {
            $('#lightboxImage').attr('src', $(this).attr('src'));
            $('#imageLightbox').show();
        });

        $('.close-lightbox').click(function() {
            $('#imageLightbox').hide();
        });

        // Edição de Material
        $('.gma-editar').click(function() {
            var materialId = $(this).data('material-id');
            $('.gma-edicao[data-material-id="' + materialId + '"]').show();
        });

        $('.gma-cancelar-edicao').click(function() {
            $(this).closest('.gma-edicao').hide();
        });

        // Swiper Slide Change
        swiper.on('slideChange', function() {
            $('.gma-edicao').hide(); // Fecha a caixa de edição ao mudar de slide
        });
    });
    </script>
    <?php
}

get_footer();
?>