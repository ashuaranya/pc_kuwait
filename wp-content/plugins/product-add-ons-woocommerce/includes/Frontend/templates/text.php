<?php foreach ($type->values as $value) {
    if ($value->hide) {
        continue;
    }?>
	<div class="zaddon_text zaddon_option">
		<label for="zaddon_<?= $value->getID() ?>">
			<?php do_action('zaddon_before_option_title', $value, $name); ?>
			<?= $value->title ?> <?= $value->price ? '(' . apply_filters('zaddon_option_format_price', wc_price($value->price), $type, $value) . ')' : "" ?>
		</label>
		<br>
		<input
			type="text"
			<?= isset($value->required) && $value->required ? "required" : "" ?>
			id="zaddon_<?= $value->getID() ?>"
			name="<?= $name ?>[value][<?= $value->getID() ?>]"
			data-price="<?= $value->price ?>"
			data-type="<?= $type->type ?>"
            <?php do_action('zaddon_input_property', $type); ?>
        />
			<?= !$value->hide_description ? '<p class="zaddon-option-description">' . $value->description . '</p>': "" ?>
			<?php do_action('zaddon_after_option_input', $value, $name); ?>
	</div>
<?php } ?>
