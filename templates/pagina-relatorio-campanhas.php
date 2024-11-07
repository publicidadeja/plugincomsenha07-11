<?php if (!defined('ABSPATH')) exit;

if (!function_exists('gma_obter_dados_relatorio_campanhas')) {
    require_once GMA_PLUGIN_DIR . 'includes/estatisticas.php';
}

$periodo = isset($_GET['periodo']) ? sanitize_text_field($_GET['periodo']) : 'mes';
$dados_relatorio = gma_obter_dados_relatorio_campanhas($periodo);
?>

<div class="gma-report-wrap">
    <div class="gma-report-header">
        <h1 class="gma-report-title">📊 Relatório de Campanhas</h1>
        
        <form method="get" class="gma-filter-form">
            <input type="hidden" name="page" value="gma-relatorio-campanhas">
            <div class="gma-form-group">
                <label for="periodo">Período de Análise:</label>
                <select name="periodo" id="periodo" class="gma-select">
                    <option value="mes" <?php selected($periodo, 'mes'); ?>>Mensal</option>
                    <option value="semana" <?php selected($periodo, 'semana'); ?>>Semanal</option>
                    <option value="dia" <?php selected($periodo, 'dia'); ?>>Diário</option>
                </select>
                <button type="submit" class="gma-button primary">
                    <i class="dashicons dashicons-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="gma-report-content">
        <?php if ($dados_relatorio) : ?>
            <?php foreach ($dados_relatorio as $campanha) : ?>
                <div class="gma-campaign-card">
                    <div class="gma-campaign-header">
                        <h2><?php echo esc_html($campanha->nome); ?></h2>
                        <div class="gma-campaign-stats">
                            <div class="gma-stat-item">
                                <i class="dashicons dashicons-visibility"></i>
                                <span class="stat-value"><?php echo number_format($campanha->visualizacoes ?? 0); ?></span>
                                <span class="stat-label">Visualizações</span>
                            </div>
                            <div class="gma-stat-item">
                                <i class="dashicons dashicons-pointer"></i>
                                <span class="stat-value"><?php echo number_format($campanha->cliques ?? 0); ?></span>
                                <span class="stat-label">Cliques</span>
                            </div>
                            <div class="gma-stat-item">
                                <i class="dashicons dashicons-download"></i>
                                <span class="stat-value"><?php echo number_format($campanha->downloads ?? 0); ?></span>
                                <span class="stat-label">Downloads</span>
                            </div>
                            <div class="gma-stat-item">
                                <i class="dashicons dashicons-media-document"></i>
                                <span class="stat-value"><?php echo number_format($campanha->numero_materiais ?? 0); ?></span>
                                <span class="stat-label">Materiais</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="gma-charts-grid">
                        <div class="gma-chart-container">
                            <h3>Visualizações</h3>
                            <canvas id="grafico-visualizacoes-<?php echo esc_attr($campanha->id); ?>"></canvas>
                        </div>
                        <div class="gma-chart-container">
                            <h3>Cliques</h3>
                            <canvas id="grafico-cliques-<?php echo esc_attr($campanha->id); ?>"></canvas>
                        </div>
                        <div class="gma-chart-container">
                            <h3>Downloads</h3>
                            <canvas id="grafico-downloads-<?php echo esc_attr($campanha->id); ?>"></canvas>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="gma-empty-state">
                <i class="dashicons dashicons-chart-bar"></i>
                <p>Nenhum dado encontrado para o período selecionado.</p>
                <p>Tente selecionar um período diferente ou aguarde mais dados serem coletados.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
:root {
    --primary-color: #4a90e2;
    --secondary-color: #2ecc71;
    --text-color: #2c3e50;
    --background-color: #f5f6fa;
    --card-background: #ffffff;
    --border-radius: 10px;
    --transition: all 0.3s ease;
}

.gma-report-wrap {
    padding: 20px;
    background: var(--background-color);
    min-height: 100vh;
}

.gma-report-header {
    margin-bottom: 30px;
    text-align: center;
}

.gma-report-title {
    font-size: 2.5em;
    color: var(--text-color);
    margin-bottom: 20px;
}

.gma-filter-form {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 30px;
}

.gma-form-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.gma-select {
    padding: 8px 15px;
    border: 2px solid #eee;
    border-radius: var(--border-radius);
    font-size: 1em;
    min-width: 150px;
}

.gma-button {
    padding: 8px 20px;
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

.gma-campaign-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px;
    animation: slideIn 0.5s ease;
}

.gma-campaign-header {
    margin-bottom: 20px;
}

.gma-campaign-header h2 {
    margin: 0 0 20px 0;
    color: var(--text-color);
}

.gma-campaign-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.gma-stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.gma-stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.gma-stat-item .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.stat-value {
    display: block;
    font-size: 1.5em;
    font-weight: bold;
    color: var(--text-color);
}

.stat-label {
    display: block;
    color: #666;
    font-size: 0.9em;
}

.gma-charts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Força 3 colunas */
    gap: 15px;
    margin-top: 20px;
}

.gma-chart-container {
    background: #f8f9fa;
    padding: 10px;
    border-radius: var(--border-radius);
    height: 200px; /* Altura fixa menor */
    width: 100%;
}

.gma-chart-container h3 {
    font-size: 0.9em;
    margin: 0 0 10px 0;
    text-align: center;
    color: var(--text-color);
}

.gma-empty-state {
    text-align: center;
    padding: 50px 20px;
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.gma-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 20px;
    color: #666;
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
    .gma-filter-form {
        flex-direction: column;
    }
    
    .gma-form-group {
        flex-direction: column;
        width: 100%;
    }
    
    .gma-select {
        width: 100%;
    }
    
    .gma-button {
        width: 100%;
        justify-content: center;
    }
}
  @media (max-width: 992px) {
    .gma-charts-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 colunas em tablets */
    }
}

@media (max-width: 768px) {
    .gma-charts-grid {
        grid-template-columns: 1fr; /* 1 coluna em celulares */
    }
    
    .gma-chart-container {
        height: 180px; /* Altura ainda menor em mobile */
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($) {
    var dadosRelatorio = <?php echo json_encode($dados_relatorio); ?>;
    
    dadosRelatorio.forEach(function(campanha) {
        // Configuração comum para todos os gráficos
        const chartConfig = {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5, // Controla a proporção altura/largura
            plugins: {
                legend: {
                    display: false // Remove a legenda
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    padding: 8,
                    cornerRadius: 4
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        maxTicksLimit: 5, // Limita o número de valores no eixo Y
                        callback: function(value) {
                            if (value >= 1000) {
                                return (value/1000).toFixed(1) + 'k';
                            }
                            return value;
                        }
                    },
                    grid: {
                        display: false // Remove as linhas de grade
                    }
                },
                x: {
                    grid: {
                        display: false // Remove as linhas de grade
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.4, // Suaviza as linhas
                    borderWidth: 2
                },
                point: {
                    radius: 3, // Pontos menores
                    hoverRadius: 5
                }
            }
        };

        // Gráfico de visualizações
        new Chart(
            document.getElementById('grafico-visualizacoes-' + campanha.id).getContext('2d'),
            {
                type: 'line',
                data: {
                    labels: ['<?php echo esc_js($periodo); ?>'],
                    datasets: [{
                        label: 'Visualizações',
                        data: [campanha.visualizacoes],
                        borderColor: '#4a90e2',
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        fill: true
                    }]
                },
                options: chartConfig
            }
        );

        // Gráfico de cliques
        new Chart(
            document.getElementById('grafico-cliques-' + campanha.id).getContext('2d'),
            {
                type: 'line',
                data: {
                    labels: ['<?php echo esc_js($periodo); ?>'],
                    datasets: [{
                        label: 'Cliques',
                        data: [campanha.cliques],
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        fill: true
                    }]
                },
                options: chartConfig
            }
        );

        // Gráfico de downloads
        new Chart(
            document.getElementById('grafico-downloads-' + campanha.id).getContext('2d'),
            {
                type: 'line',
                data: {
                    labels: ['<?php echo esc_js($periodo); ?>'],
                    datasets: [{
                        label: 'Downloads',
                        data: [campanha.downloads],
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        fill: true
                    }]
                },
                options: chartConfig
            }
        );
    });
});
</script>