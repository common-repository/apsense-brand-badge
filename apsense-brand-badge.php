<?php
/*
Plugin Name: APSense Brand Badge
Plugin URI: http://www.apsense.com/bmc/start
Description: Add APSense Brand Badge on your blog site by one-click.
Version: 3.5
Author: APSense.com
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: brand_badge_apsense
Domain Path: /languages
*/

$apsense_plugin_url = plugins_url() . '/apsense-brand-badge';
load_plugin_textdomain( 'apsense-brand-badge', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if ( is_admin() ) {
	add_filter('plugin_action_links', 'apsense_brandbadge_plugin_action_links', 10, 2);
	add_action('admin_menu', 'apsense_brandbadge_admin_menu');
}
else
{
	add_action( 'wp_head', 'apsense_brandbadge_code', 1 );
}

if ( function_exists('register_activation_hook') )
	register_activation_hook( __FILE__, 'apsense_brandbadge_plugin_activation' );

if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook( __FILE__, 'apsense_brandbadge_plugin_uninstall' );

function apsense_brandbadge_plugin_uninstall() {
	delete_option( 'apsense_brand_tag' );
	delete_option( 'apsense_brand_type' );	
	delete_option( 'apsense_brand_uid' );	
	
}

function apsense_brandbadge_plugin_activation() {

}

function apsense_brandbadge_admin_menu() {
	add_options_page('', '', 'manage_options', __FILE__, 'apsense_brandbadge_admin_settings_show', '', 6);
}


function apsense_brandbadge_admin_settings_show() {
	global $apsense_plugin_url;
	
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' , 'brand_badge_apsense') );
	}
	
	/* save settings */
	if ( @$_POST[ 'brand_type' ] != '' ) {	
		apsense_brandbadge_admin_settings_save();
	}
		
	echo '<div class="wrap" style="padding-bottom:50px;"><div class="icon32" id="icon-users"></div>';
	echo '<h2>'. __('APSense Brand Badge', 'brand_badge_apsense').'</h2>';
	echo '<form name="apsense_settings_form" method="post" action="">'; 	
	$checked = 'checked="checked"';

	?>
	
	<input type="hidden" name="code" value="" />	
	<br/>
	<table border="0">
	<tr>
		<td style="width:150px;" valign="top"><?php _e('Brand Tag', 'share_button_apsense'); ?></td>
		<?php
			$brand_tag = trim(get_option( 'apsense_brand_tag', '' ));			
		?>
		<td>
		<input type="text" name="brand_tag" value="<?php echo $brand_tag; ?>" /> <br>
		Notice: URL of your brand page. http://www.apsense.com/brand/<b>XXXXXX</b>.<br>XXXXX is your brand tag. Enter the tag name only.<br><br></td>
	</tr>
	<tr>
		<td style="width:150px;" valign="top"><?php _e('Brand Badge Type', 'brand_badge_apsense'); ?>
		</td>
		<?php
			$apsense_brand_type = get_option( 'apsense_brand_type', 1);
			$checked = ' checked="checked" ';
			$apsense__checked = '';
			$apsense_ribbon_checked = '';
			$apsense_corner_checked = '';
			switch ( $apsense_brand_type ) {
				case 1: 	$apsense_ribbon_checked = $checked; break;
				case 2: 	$apsense_corner_checked = $checked; break;
				default: $apsense_ribbon_checked = $checked;
			}			
		?>		
		<td height="100"><input type="radio" name="brand_type" value="1" <?php echo $apsense_ribbon_checked; ?>  /> Ribbon Style<br/>
		<img src="http://www.apsense.com/public/bmc_corner1.png" /><br/><br/>
		<input type="radio" name="brand_type" value="2" <?php echo $apsense_corner_checked; ?> /> Corner Style<br/>
		<img src="http://www.apsense.com/public/bmc_corner2.png" /><br/><br/>	
		</td>	
	</tr>
	<tr>
		<td style="width:100px;">
		&nbsp;
		</td>
		<td>
		<a href="http://www.apsense.com/bmc/start" target="blank">Create a Brand Page for your website.</a>
		</td>
	</tr>
	<tr>
		<td style="width:100px;">
		&nbsp;
		</td>
		<td>
		<br/><br/><input class="button-primary" name="submit" type="submit"  value="<?php _e('Save Settings', 'share_button_apsense'); ?>" />
		</td>
	</tr>
	</table>
	
	</form>
</div>
	
<?php	
	
}

function apsense_brandbadge_admin_settings_save() {

	global $apsense_plugin_url;	

	if ( @$_POST[ 'brand_type' ] != '' )
		$post = true;
	else
		$post = false;	
			
	/* save badge style */
	if ( $post ) {
		$brand_type = @$_POST[ 'brand_type' ];
		update_option( 'apsense_brand_type', $brand_type );		
	} else {
		$brand_type = get_option ( 'apsense_brand_type', 1);
	}				
	
	/* save brand tag */
	if ( $post ) {
		$brand_tag = @trim($_POST[ 'brand_tag' ]);
		update_option( 'apsense_brand_tag', $brand_tag );		
	} else {
		$brand_type = get_option ( 'apsense_brand_tag', '');
	}	

}

function apsense_brandbadge_code() {

	global $apsense_plugin_url, $wp_version;
	
	/* Do not show brand badge in feeds */
	if ( is_feed() ) {
		return '';
	}

	$brand_type = get_option( 'apsense_brand_type', 2);
	$brand_tag = get_option( 'apsense_brand_tag', '');

	if ($brand_tag == '')
		return $content;

	/* default code */
	$code1 = "\n<script type='text/javascript'>var brandtag = '$brand_tag';</script>\n<script type='text/javascript' src='http://www.apsense.com/bmc-badge.js'></script>\n";
	$code2 = "\n<script type='text/javascript'>var brandtag = '$brand_tag';</script>\n<script type='text/javascript' src='http://www.apsense.com/bmc-badge2.js'></script>\n";	
	
	if ($brand_type == 2)
		$new_content = $code2;
	else
		$new_content = $code1;  
		
	echo($new_content);
}  

function apsense_brandbadge_plugin_action_links( $links, $file ) {
    static $this_plugin;
    if ( !$this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }
 
    // check to make sure we are on the correct plugin
    if ( $file == $this_plugin ) {
         $settings_link = '<a href="options-general.php?page=apsense-brand-badge/apsense-brand-badge.php">' . __('Settings', 'brand_badge_apsense') . '</a>';
        array_unshift( $links, $settings_link );
    }
 
    return $links;
}

?>