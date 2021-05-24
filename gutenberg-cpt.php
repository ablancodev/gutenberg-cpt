<?php
/**
 * gutenberg-cpt.php
 *
 * Copyright (c) 2011,2021 Antonio Blanco http://www.ablancodev.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco
 * @package gutenberg-cpt
 * @since gutenberg-cpt 1.0.0
 *
 * Plugin Name: Gutenberg Custom Post Types
 * Plugin URI: http://www.eggemplo.com
 * Description: Add Gutenberg support to your custom post types.
 * Version: 1.0.0
 * Author: eggemplo
 * Author URI: http://www.ablancodev.com
 * Text Domain: gutenberg-cpt
 * Domain Path: /languages
 * License: GPLv3
 */
define ( 'GUTENBERGCPT_FILE', __FILE__ );
define ( 'GUTENBERGCPT_PLUGIN_URL', plugin_dir_url ( GUTENBERGCPT_FILE ) );

if (! defined ( 'GUTENBERGCPT_CORE_DIR' )) {
	define ( 'GUTENBERGCPT_CORE_DIR', GUTENBERGCPT_PLUGIN_URL . '/gutenberg-cpt' );
}

class GutenbergCPT_Plugin {

	public static $notices = array ();


	public static function init() {
		add_action ( 'init', array (
				__CLASS__,
				'wp_init' 
		) );
		add_action ( 'admin_notices', array (
				__CLASS__,
				'admin_notices' 
		) );

	}
	public static function wp_init() {
		load_plugin_textdomain ( 'gutenberg-cpt', null, 'gutenberg-cpt/languages' );

		add_action ( 'admin_menu', array (
				__CLASS__,
				'admin_menu' 
		), 40 );

        add_filter('use_block_editor_for_post_type', array( __CLASS__, 'activar_gutenberg_cpt' ), 10, 2);

	}

	public static function admin_notices() {
		if (! empty ( self::$notices )) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_menu_page (
				__ ( 'Gutenberg CPT', 'gutenberg-cpt' ),
				__ ( 'Gutenberg CPT', 'gutenberg-cpt' ),
				'manage_options', 'gutenberg-cpt',
				array (
					__CLASS__,
					'gutenberg_cpt_menu_settings' 
				),
				GUTENBERGCPT_PLUGIN_URL . '/images/settings.png' );
	}

	public static function gutenberg_cpt_menu_settings() {
		// if submit
		if ( isset( $_POST ["gutenbergcpt_settings"] ) && wp_verify_nonce ( $_POST["gutenbergcpt_settings"], "gutenbergcpt_settings" ) ) {
			// saving
            $cpts = get_post_types( 
                array(
                    'public' => true,
                    '_builtin' => false
                ),
                'objects'
            );
            if ( $cpts ) {
                foreach ( $cpts as $cpt ) {
                    update_option( "gutenbergcpt_" . $cpt->name, isset($_POST["gutenbergcpt_" . $cpt->name]) ? sanitize_text_field($_POST["gutenbergcpt_" . $cpt->name]) : 0);
                }
            }
            echo "Saved.";
		}
		?>
		<h2><?php echo __( 'Gutenberg Custom Post Types', 'gutenberg-cpt' ); ?></h2>

		<form method="post" action="" enctype="multipart/form-data" >
			<div class="">
                <?php
                    $cpts = get_post_types( 
                        array(
                            'public' => true,
                            '_builtin' => false
                        ),
                        'objects'
                    );
                    if ( $cpts ) {
                        foreach ( $cpts as $cpt ) {
                            $checked = get_option("gutenbergcpt_" . $cpt->name, '0') ? ' checked="checked" ' : '';
                            ?>
                            <p>
                                <input type="checkbox" name="gutenbergcpt_<?php echo $cpt->name;?>" <?php echo $checked;?> value="1" /> <?php echo $cpt->labels->singular_name;?>
                            </p>
                            <?php
                        }
                    }
   
                    wp_nonce_field ( 'gutenbergcpt_settings', 'gutenbergcpt_settings' )?>
					<input type="submit"
					value="<?php echo __( "Save", 'gutenberg-cpt' );?>"
					class="button button-primary button-large" />
			</div>
		</form>
		<?php 
	}

    public static function activar_gutenberg_cpt ($can_edit, $post_type) {
        error_log("entramos");
        if ( get_option("gutenbergcpt_" . $post_type, '0') ) {
            error_log("si -->" . $post_type);
            $can_edit = true;
        }
        return $can_edit;
    }
}
GutenbergCPT_Plugin::init();