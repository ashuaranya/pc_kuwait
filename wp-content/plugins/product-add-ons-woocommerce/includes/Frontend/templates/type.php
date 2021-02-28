<?php
$format = $type->type;
$type_template = __DIR__ . '/type-' . $format . '.php';
if ($type->status === 'disable') {
    return ;
} ?>
	<div class="zaddon-type-container <?= $type->accordion === 'close' && $format !== 'select' ? 'zaddon_closed' : ''?>" data-id="<?= $type->getID() ?>">
    <h3><?= $type->title ?>  <?=  $format !== 'select' ? '<button class="zaddon-open" />' : ''?></h3>
	<?php
    if (!$type->hide_description && $type->description) {
        $classes[] = $type->display_description_on_expansion ? 'zaddon_hide_on_toggle' : '';
        if ($type->accordion === 'close') {
						$classes[] = 'zaddon_hide';
				}
        echo '<p class="' . esc_attr( join( ' ', (array) $classes ) ) . '">' . $type->description . '</p>';
    }
    $name = 'zaddon[' . $group->getID() . '][' . $type->getID() . ']';
    $default_template = \ZAddons\Frontend\Product::DEFAULT_TEMPLATE_PATH;
    $file = '/' . $format . '.php';
    $template_path = apply_filters( 'za_template_path', $default_template );
    $include_default = apply_filters( 'zaddon_option_include_default_template', true, $type );
    if (!$include_default) {
        do_action('zaddon_option_template', $name, $type);
    }
    if ($include_default && file_exists($template_path . $file)) {
        include $template_path . $file;
    } elseif ($include_default && file_exists($default_template . $file)) {
        include $default_template . $file;
    }?>
    <input type="hidden" name="<?= $name ?>[type]" value="<?= $format ?>">
    <?php do_action('zaddon_after_option_template', $name, $type); ?>
</div>

