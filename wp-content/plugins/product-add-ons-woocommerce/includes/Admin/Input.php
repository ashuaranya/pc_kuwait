<?php

namespace ZAddons\Admin;

class Input {
	public static function renderCheckbox( $args ) {
		$name  = $args['name'];
		$value = get_option( $name );
		?>
        <input type="checkbox" id="<?php echo $name ?>"
               name="<?php echo $name ?>" <?php if ( $value ) : echo 'checked'; endif; ?>/>
		<?php if ( isset( $args['label'] ) ) : ?>
            <label for="<?php echo $name; ?>"><?php echo $args['label']; ?></label>
		<?php endif;
	}

	public static function renderText( $args ) {
		$value = $args['value'] ? $args['value'] : get_option( $args['name'] );
		$style = $args['style'] ? 'style="' . $args['style'] . '"' : '';
		?>
        <input type="text" name="<?php echo $args['name'] ?>" value="<?php echo $value; ?>" <?php echo $style; ?>/>
		<?php if ( isset( $args['description'] ) ) : ?>
            <label><?php echo $args['description']; ?></label>
		<?php endif;
	}

	public static function renderSelect( $args ) {
		$value = get_option( $args['name'] );
		?>
        <select name="<?php echo $args['name']; ?>">
			<?php
			foreach ( $args['options'] as $option ) { ?>
                <option value="<?php echo $option['value'] ?>" <?php selected( $value, $option['value'] ); ?>>
					<?php echo $option['label']; ?>
                </option>
			<?php } ?>
        </select>
		<?php
	}
}
