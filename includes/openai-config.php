<?php
if (!defined('ABSPATH')) exit;

// Remova ou comente a função existente gma_add_openai_settings_page()

// Adicione esta nova função
function gma_register_openai_settings() {
    add_settings_section(
        'gma_openai_settings',
        'Configurações OpenAI',
        null,
        'gma-settings'
    );

    add_settings_field(
        'gma_openai_api_key',
        'Chave API OpenAI',
        'gma_openai_api_key_callback',
        'gma-settings',
        'gma_openai_settings'
    );

    register_setting('gma_settings', 'gma_openai_api_key');
}
add_action('admin_init', 'gma_register_openai_settings');

// Adicione a função de callback
function gma_openai_api_key_callback() {
    $api_key = get_option('gma_openai_api_key', '');
    ?>
    <input type="text" 
           id="gma_openai_api_key" 
           name="gma_openai_api_key" 
           value="<?php echo esc_attr($api_key); ?>" 
           class="regular-text">
    <p class="description">Insira sua chave API da OpenAI</p>
    <?php
}
// Renderizar página de configurações
function gma_render_openai_settings() {
    if (isset($_POST['gma_openai_api_key'])) {
        update_option('gma_openai_api_key', sanitize_text_field($_POST['gma_openai_api_key']));
        echo '<div class="notice notice-success"><p>Chave API salva com sucesso!</p></div>';
    }
    
    $api_key = get_option('gma_openai_api_key', '');
    ?>
    <div class="wrap">
        <h2>Configurações OpenAI</h2>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="gma_openai_api_key">Chave API OpenAI</label></th>
                    <td>
                        <input type="text" 
                               id="gma_openai_api_key" 
                               name="gma_openai_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" 
                               class="regular-text">
                        <p class="description">Insira sua chave API da OpenAI</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Salvar Configurações'); ?>
        </form>
    </div>
    <?php
}

