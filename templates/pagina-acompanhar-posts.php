<form method="get" action="">
    <input type="hidden" name="page" value="gma-acompanhar-posts">
    <label for="gma_status_filtro">Filtrar por Status:</label>
    <select name="gma_status_filtro" id="gma_status_filtro">
        <option value="">Todos</option>
        <option value="pendente" <?php selected( 'pendente',  $_GET['gma_status_filtro'] ?? '' ); ?>>Pendente</option>
        <option value="aprovado" <?php selected( 'aprovado', $_GET['gma_status_filtro'] ?? '' ); ?>>Aprovado</option>
        <option value="reprovado" <?php selected( 'reprovado', $_GET['gma_status_filtro'] ?? '' ); ?>>Reprovado</option>
        <option value="edicao" <?php selected( 'edicao', $_GET['gma_status_filtro'] ?? '' ); ?>>Edição</option>
    </select>
    <input type="submit" class="button" value="Filtrar">
</form>

<div class="wrap">
    <h1>Acompanhar Posts</h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
    <th>ID do Material</th>
    <th>Campanha</th>
    <th>Nome do Material</th> 
    <th>Status</th>
    <th>Ações</th> 
</tr>
        </thead>
        <tbody>
    <?php
$args = array(
    'post_type' => 'gma_material', // <-- Ajustado para o tipo de post 'gma_material' (ou o tipo que você usa para materiais)
    'posts_per_page' => -1, // Exibir todos os materiais
    'meta_key' => 'status_aprovacao', // Para ordenar pelo status
    'orderby' => 'meta_value',
    'order' => 'ASC'
);

$materiais = new WP_Query($args);

if ($materiais->have_posts()) :
    while ($materiais->have_posts()) : $materiais->the_post();
        $campanha_id = get_post_meta(get_the_ID(), 'campanha_id', true);
        $campanha = gma_obter_campanha($campanha_id); 
    ?>
        <tr>
            <td><?php echo get_the_ID(); ?></td> 
            <td><?php echo esc_html($campanha->nome); ?></td> 
            <td><?php echo get_the_title(); ?></td>
            <td><?php echo get_post_meta(get_the_ID(), 'status_aprovacao', true); ?></td>
            <td>
                <a href="<?php echo admin_url('admin-post.php?action=gma_aprovar_material&material_id=' . get_the_ID()); ?>" class="button">Aprovar</a>
                <a href="<?php echo admin_url('admin-post.php?action=gma_reprovar_material&material_id=' . get_the_ID()); ?>" class="button">Reprovar</a>
                <a href="<?php echo admin_url('admin-post.php?action=gma_solicitar_edicao_material&material_id=' . get_the_ID()); ?>" class="button">Solicitar Edição</a>
            </td>
        </tr>
    <?php
    endwhile;
    wp_reset_postdata();
else :
    ?>
    <tr>
        <td colspan="5">Nenhum material encontrado.</td>
    </tr>
    <?php
endif;
?>
</tbody>
    </table>
</div>