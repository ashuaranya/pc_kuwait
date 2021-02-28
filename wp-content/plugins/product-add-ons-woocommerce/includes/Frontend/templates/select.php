<div class="zaddon_select zaddon_option">
	<select
        name="<?= $name ?>[value]"
        data-type="<?= $type->type ?>"
        <?php do_action('zaddon_input_property', $type); ?>
    >
		<option value="">
            <?php echo \ZAddons\get_customize_addon_option('zac_select_drop_down_label', __('Choose an option', 'product-add-ons-woocommerce')) ?>
		</option>
		<?php foreach ($type->values as $value) {
            if ($value->hide) {
                continue;
            }?>
			<option
				value="<?= $value->getID() ?>"
				data-price="<?= $value->price ?>"
				title="<?= !$value->hide_description ? $value->description : '' ?>"
				<?php do_action('zaddon_add_select_option_property', $value); ?>
				<?= $value->checked ? "selected" : "" ?>
			>
				<?= $value->title ?>
				<?= $value->price ? '(' . apply_filters('zaddon_option_format_price', wc_price($value->price), $type, $value) . ')' : "" ?>
			</option>
		<?php } ?>
	</select>
</div>
