<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
require_once GMA_PLUGIN_DIR . 'includes/campanhas.php';
require_once GMA_PLUGIN_DIR . 'includes/materiais.php';

function gma_obter_contagem_materiais_por_tipo_campanha($tipo_campanha) {
    global $wpdb;
    $tabela_materiais = $wpdb->prefix . 'gma_materiais';
    $tabela_campanhas = $wpdb->prefix . 'gma_campanhas';
    $sql = "SELECT COUNT(*) FROM $tabela_materiais m JOIN $tabela_campanhas c ON m.campanha_id = c.id WHERE c.tipo_campanha = %s";
    return $wpdb->get_var($wpdb->prepare($sql, $tipo_campanha));
}
?>

<div class="wrap gma-dashboard-container">
    <h1 class="gma-title">Gerenciador de Marketing Avançado</h1>

    <div class="gma-dashboard">
        <div class="gma-row">
            <div class="gma-card animate-card">
                <div class="gma-card-icon">📈</div>
                <h2>Campanhas de Marketing</h2>
                <div class="gma-card-body">
                    <p class="gma-card-value counter"><?php echo count(gma_listar_campanhas_por_tipo('marketing')); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=gma-campanhas&tipo=marketing'); ?>" class="gma-button">Ver Todas</a>
                </div>
            </div>

            <div class="gma-card animate-card">
                <div class="gma-card-icon">✅</div>
                <h2>Campanhas de Aprovação</h2>
                <div class="gma-card-body">
                    <p class="gma-card-value counter"><?php echo count(gma_listar_campanhas_por_tipo('aprovacao')); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=gma-campanhas&tipo=aprovacao'); ?>" class="gma-button">Ver Todas</a>
                </div>
            </div>
        </div>

        <div class="gma-row">
            <div class="gma-card animate-card">
                <div class="gma-card-icon">🎨</div>
                <h2>Materiais de Marketing</h2>
                <div class="gma-card-body">
                    <p class="gma-card-value counter"><?php echo gma_obter_contagem_materiais_por_tipo_campanha('marketing'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=gma-materiais'); ?>" class="gma-button">Ver Todos</a>
                </div>
            </div>

            <div class="gma-card animate-card">
                <div class="gma-card-icon">📋</div>
                <h2>Materiais de Aprovação</h2>
                <div class="gma-card-body">
                    <p class="gma-card-value counter"><?php echo gma_obter_contagem_materiais_por_tipo_campanha('aprovacao'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=gma-materiais'); ?>" class="gma-button">Ver Todos</a>
                </div>
            </div>
        </div>

        <div class="gma-row">
            <div class="gma-card gma-card-full animate-card">
                <div class="gma-card-icon">⚡</div>
                <h2>Ações Rápidas</h2>
                <div class="gma-card-body gma-actions">
                    <a href="<?php echo admin_url('admin.php?page=gma-campanhas'); ?>" class="gma-button gma-button-primary">Nova Campanha</a>
                    <a href="<?php echo admin_url('admin.php?page=gma-novo-material'); ?>" class="gma-button gma-button-secondary">Novo Material</a>
                </div>
            </div>
        </div>

        <div class="gma-row">
            <div class="gma-card gma-card-full animate-card">
                <div class="gma-card-icon">📊</div>
                <h2>Resumo do Relatório</h2>
                <div class="gma-card-body">
                    <?php
                    $dados_grafico = array(
                        'Campanhas de Marketing' => count(gma_listar_campanhas_por_tipo('marketing')),
                        'Campanhas de Aprovação' => count(gma_listar_campanhas_por_tipo('aprovacao')),
                        'Materiais de Marketing' => gma_obter_contagem_materiais_por_tipo_campanha('marketing'),
                        'Materiais de Aprovação' => gma_obter_contagem_materiais_por_tipo_campanha('aprovacao')
                    );
                    ?>
                    <canvas id="gma-grafico-pizza" class="gma-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.gma-dashboard-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    font-family: 'Segoe UI', Roboto, sans-serif;
}

.gma-title {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
    font-size: 2.5em;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.gma-dashboard {
    max-width: 1200px;
    margin: 0 auto;
}

.gma-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.gma-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    flex: 1;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.gma-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.gma-card-icon {
    font-size: 2em;
    margin-bottom: 15px;
}

.gma-card h2 {
    color: #2c3e50;
    font-size: 1.5em;
    margin-bottom: 20px;
}

.gma-card-value {
    font-size: 3em;
    font-weight: bold;
    color: #3498db;
    margin: 20px 0;
    text-align: center;
}

.gma-button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    text-align: center;
    margin: 5px;
}

.gma-button-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
}

.gma-button-secondary {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
}

.gma-button:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.gma-chart {
    width: 100% !important;
    height: 400px !important;
    margin-top: 20px;
}

.animate-card {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media screen and (max-width: 768px) {
    .gma-row {
        flex-direction: column;
    }
    
    .gma-card {
        margin-bottom: 20px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animação para os números
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.innerText);
        let count = 0;
        const speed = 2000 / target;
        
        const updateCount = () => {
            if(count < target) {
                count++;
                counter.innerText = count;
                setTimeout(updateCount, speed);
            }
        };
        
        updateCount();
    });

    // Configuração do gráfico
    const ctx = document.getElementById('gma-grafico-pizza').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($dados_grafico)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($dados_grafico)); ?>,
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f1c40f'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });

    // Efeito de animação para os cards
    const cards = document.querySelectorAll('.animate-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>