<?php

/**
 * Register shortcode to show by default.
 * [tfdefault name=""]
 *
 * @link       https://liontude.com/postmenu/
 * @since      1.0.0
 *
 * @package    Postmenu
 * @subpackage Postmenu/public/shortcodes
 */
function shortcode_postmenu_default( $atts ) {
	ob_start();
	extract( shortcode_atts( [ 'name' => '' ], $atts ) );
	?>

    <div><?php echo $name; ?></div>

	<?php return ob_get_clean();
}
