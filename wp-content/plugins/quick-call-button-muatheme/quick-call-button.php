<?php 
/* 
Plugin Name: 	Quick Call Button Pro
Plugin URI: 	https://muatheme.com
Description: 	add quick call button, call now button for any wordpress website on ipad and mobile phone.
Tags: 			muatheme, quick call button, call now button, ipad, mobile, responsive, buttons, phone, call, contact
Author URI: 	https://muatheme.com
Author: 		MuaTheme.com
Version: 		1.0.3
License: 		GPL2
Text Domain:    lv-web
*/

define('LV_QUICK_CALL_BUTTON_VERSION', '1.0.3');
define('LV_QUICK_CALL_BUTTON_DIR', plugin_dir_path(__FILE__));
define('LV_QUICK_CALL_BUTTON_URI', plugins_url('/', __FILE__));

class LV_Quick_Call_Button
{
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'lv-web',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
	public $menu_id;
	
	/**
	 * Plugin initialization
	 *
	 * @since 1.0.1
	 */
	public function __construct() {
		
		// localization
		load_plugin_textdomain( 'lv-web' );

		// admin
		add_action( 'admin_menu', array( $this, 'lv_add_admin_menu' ));
		add_action( 'admin_enqueue_scripts', array( $this, 'lv_admin_scripts' ));
		
		// create needed initialization
		add_action('admin_init', array( $this, 'lv_register_options_settings') );
		
		// create custom footer
		add_action('wp_footer', array( $this, 'lv_add_buttons'), 10);
		
		// grab the options, use for entire object
		$this->lv_options = $this->lv_options();
	}

	/**
	 * Add Menu Page
	 *
	 * @since 1.0.1
	 */
	public function lv_add_admin_menu() {
    	add_options_page('Settings Page for Quick Call Button', 'Quick Call Button', 'publish_posts', 'lv_quick_call_button', array($this,'lv_options_page'),''); 
	}
	
	/**
	 * Add Resources
	 *
	 * @since 1.0.1
	 */
	function lv_admin_scripts() {

		if (get_current_screen()->base == 'settings_page_lv_quick_call_button') {
		    wp_enqueue_script('media-upload'); //Provides all the functions needed to upload, validate and give format to files.
wp_enqueue_script('thickbox'); //Responsible for managing the modal window.
wp_enqueue_style('thickbox'); //Provides the styles needed for this window.
	        wp_register_script( 'lv_js', plugins_url('assets/js/quick-call-button-admin.js', __FILE__), array('jquery'), '1.0.3', true );
	        wp_enqueue_script( 'lv_js' );
			
		    wp_enqueue_style('wp-color-picker');
		    wp_enqueue_script('iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
		    wp_enqueue_script('wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false,1);
		      
	    }
	}

	/**
	 * Whitelist Options
	 *
	 * @since 1.0.1
	 */
	function lv_register_options_settings() { 
	    register_setting( 'lv_custom_options-group', 'lv_options' );
	}  
	    
	/**
	 * Options Page
	 *
	 * @since 1.0.1
	 */
	function lv_options_page() {
		global $_wp_admin_css_colors, $wp_version;
		
		// access control
	    if ( !(isset($_GET['page']) && $_GET['page'] == 'lv_quick_call_button' )) 
	    	return;
		?>
	
		<div class='wrap'>
			<h2><?php _e('Quick Button','lv-web') ?></h2>
			<form method="post" action="options.php" class="form-table">
				<?php
				wp_nonce_field('lv_options');
				settings_fields('lv_custom_options-group');
				?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="lv_options" />
				<h2 class='title'><?php _e('Settings','lv-web') ?></h2>
				<p><?php _e('Change the appearance of the quick call button display on the screen.The default is 1920px.','lv-web') ?></p>
				<table border=0 cellpadding=2 cellspacing="2">
				<tr>
				    <th><?php _e('Display On Screen','lv-web') ?></th>
				        <td>
					        <label><span class="dashicons dashicons-smartphone"></span><span class="dashicons dashicons-minus"></span><span class="dashicons dashicons-tablet"></span><span class="dashicons dashicons-minus"></span><span class="dashicons dashicons-laptop"></span><span class="dashicons dashicons-minus"></span><span class="dashicons dashicons-desktop"></span> <span class="dashicons dashicons-arrow-down-alt"></span></label><br />
							<input name="lv_options[screen_size]" placeholder="1920" value='<?php echo $this->lv_options['screen_size']; ?>' /><label>px</label>
				        </td>
				        <td></td>
				        
				</tr>
				</table>
				
				<p><?php _e('Move the quick call button by changing the digits in the settings box below. Default Top is 50% & Left is 3% .','lv-web') ?></p>
				<table border=0 cellpadding=2 cellspacing="2">
				<tr>
				    <th><?php _e('Top','lv-web') ?></th>
				        <td>
					        <input name="lv_options[move_top]" placeholder="90" value='<?php echo $this->lv_options['move_top']; ?>' /><label>%</label>
				        </td>
				        <td></td>
				</tr>
				<tr>
				    <th><?php _e('Left','lv-web') ?></th>
				        <td>
					        <input name="lv_options[move_left]" placeholder="3" value='<?php echo $this->lv_options['move_left']; ?>' /><label>%</label>
				        </td>
				        <td></td>
				</tr>
				</table>
				
				<p><?php _e('Adding a phone number for a quick call button will appear on your website.','lv-web') ?></p>
				<table border=0 cellpadding=2 cellspacing="2">
				<tr>
					<th><?php _e('Link','lv-web') ?></th>
					<td>
						<input name="lv_options[phone_number]" placeholder="+08495558888" value='<?php echo $this->lv_sanitize_phone($this->lv_options['phone_number']); ?>' /><br />
						
					</td>
					<td><strong>Định dạng link đặc biệt:</strong>
						<ul>
						    <li>- Link Email: <b>mailto:webmaster@muatheme.com</b></li>
						    <li>- Link số điện thoại: <b>tel:0888090898</b></li>
						    <li>- Link Facebook Messenger: <b>http://www.messenger.com/t/muathemewp</b></li>
						    <li>- Link Zalo: <b>http://zalo.me/0888090898</b></li>
						</ul></td>
				</tr>
				</table>
				
				<p><?php _e('Adding a text for a quick call button will appear on your website.','lv-web') ?></p>
				<table border=0 cellpadding=2 cellspacing="2">
				<tr>
				    <th><?php _e('Text','lv-web') ?></th>
				        <td>
				            <input type="text" id="quick-call-button-text" name="lv_options[call_text]" value="<?php echo $this->lv_options['call_text'] ?>" placeholder="Call Now" /><br />
					        <input type="text" class="colourme" name="lv_options[call_text_color]" value="<?php echo $this->lv_options['call_text_color']; ?>"><br/>
					        <input type="checkbox" id="quick-call-button-text-1" name="lv_options[call_text_tablet]" value="1024" <?php checked('1024', $this->lv_options['call_text_tablet']) ?>"  />
							<label for="quick-call-button-text-1"><span class="dashicons dashicons-tablet"></span><?php _e(' Disabled Text On Tablet','lv-web') ?></label><br />
							<input type="checkbox" id="quick-call-button-text-2" name="lv_options[call_text_desktop]" value="1024" <?php checked('1024', $this->lv_options['call_text_desktop']) ?>"  />
							<label for="quick-call-button-text-2"><span class="dashicons dashicons-laptop"></span><?php _e(' Disabled Text On Desktop','lv-web') ?></label>
							
				        </td>
				</tr>  
				<tr>
				    <th><?php _e('Bar Background','lv-web') ?></th>
					    <td>
						    <input type="text" class="colourme" name="lv_options[bg_color]" value="<?php echo $this->lv_options['bg_color']; ?>">
					    </td>
				</tr>
				      
				<tr>
					<th><?php _e('Button Color','lv-web') ?></th>
					    <td>
						    <input type="text" class="colourme" name="lv_options[call_color]" value="<?php echo $this->lv_options['call_color']; ?>">
					    </td>
				</tr>
				
				<tr>
					<th><?php _e('Select Images Button','lv-web') ?></th>
						<td>
						
							<select type="text" class="widefat" name="lv_options[call_image]" value="<?php echo $this->lv_options['call_image']; ?>">
								<option value="quick-alo-cart-img-circle" <?php selected ( $this->lv_options['call_image'], 'quick-alo-cart-img-circle' ) ?>>Giỏ hàng</option>
								<option value="quick-alo-phone-img-circle" <?php selected ( $this->lv_options['call_image'], 'quick-alo-phone-img-circle' ) ?>>Điện thoại</option>
								<option value="quick-alo-email-img-circle" <?php selected ( $this->lv_options['call_image'], 'quick-alo-email-img-circle' ) ?>>Email</option>
			<option value="quick-alo-facebook-img-circle" <?php selected ( $this->lv_options['call_image'], 'quick-alo-facebook-img-circle' ) ?>>Facebook</option>
				<option value="quick-alo-messenger-img-circle" <?php selected ( $this->lv_options['call_image'], 'quick-alo-messenger-img-circle' ) ?>>Facebook Messenger</option>
				<option value="quick-alo-zalo-img-circle" <?php selected ( $this->lv_options['call_image'], 'quick-alo-zalo-img-circle' ) ?>>Zalo</option>
					</select>
						</td>
				</tr>
				</table>
				<p>Custom Button Icon</p>
				<table border=0 cellpadding=2 cellspacing="2">
				<tr>
					<th><?php _e('Custom Image','lv-web') ?></th>
					    <td>
						      <input type="checkbox" id="lv_checkbox_custom_image" name="lv_options[checkbox_custom_image]" value="1" <?php checked('1', $this->lv_options['checkbox_custom_image']) ?>"  />
					    </td>
				</tr>
				<tr id="lv_row_upload_custom_icon" <?php echo ($this->lv_options['checkbox_custom_image']=="1"?"":"style='display:none'"); ?>>
				    <th><?php _e('Upload Image','lv-web') ?></th>
					    <td>
			<input type="text" id="lv_upload_image" name="lv_options[upload_image_custom]" value="<?php echo $this->lv_options['upload_image_custom'] ?>" size="50" />
	<input id="lv_upload_image_logo_button" type="button" class="button" value="Upload Image" /> 
					    </td>
				</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes','lv-web') ?>" />
				
				</p>
			</form>
		</div>

	  	<?php
	}
	
	// Adding Custom Quick Call Buttons.
	function lv_add_buttons() {
		
		$return =  "\n\n
			<!-- Start Quick Buttons By https://muatheme.com -->";

		// Setup valuable settings.
		if ($this->lv_mandatory_have_info()) {
			
			// adding the enque here will setup the style.
			wp_register_style( 'lv_css', plugins_url('/assets/css/quick-call-button.css', __FILE__) , false, '1.0.3' );
			wp_enqueue_style( 'lv_css');
			//wp_register_script( 'lv_js', plugins_url('/assets/js/drag-quick-call-button.js', __FILE__), array('jquery'), '1.0.3', true );
	        //wp_enqueue_script( 'lv_js' );
			// code button
			//Check Custom Image checked:
			$isCustomImg = $this->lv_options['checkbox_custom_image'];
			$imgIcon = $this->lv_options['call_image'];
			$imgIconUrl = plugins_url('/images/quick-call-button-phone.png', __FILE__);
			
			if($isCustomImg == 1)
			{
			    
			    $imgIcon = 'quick-alo-custom-img-circle';
			    $imgIconUrl = $this->lv_options['upload_image_custom'];
			}
			
		
			$return .=  "
			<div class='quick-call-button'></div>
			<div class='call-now-button'>";	
			
			if ( !empty($this->lv_options['phone_number']) ) { 
				$return .= "
				<div><p class='call-text'><a href='".$this->lv_sanitize_phone($this->lv_options['phone_number'])."' rel='nofollow' title='{$this->lv_options['call_text']}' > {$this->lv_options['call_text']} </a></p>
					<a href='".$this->lv_sanitize_phone($this->lv_options['phone_number'])."' rel='nofollow' title='{$this->lv_options['call_text']}' >
					<div class='quick-alo-ph-circle'></div>
                    <div class='quick-alo-ph-circle-fill'></div>
                    <div class='quick-alo-ph-btn-icon {$imgIcon}'></div>
					</a>
				</div>"; 
			}
			$return .= "
			</div>
		
			<style> 
				@media screen and (max-width: {$this->lv_options['screen_size']}px) { 
				.call-now-button { display: flex !important; background: {$this->lv_options['bg_color']}; }  
				.quick-call-button { display: block !important; } 
				}
                @media screen and (min-width: {$this->lv_options['call_text_desktop']}px) { 
				.call-now-button .call-text { display: none !important; } 
				} 
				@media screen and (max-width: {$this->lv_options['call_text_tablet']}px) { 
				.call-now-button .call-text { display: none !important; } 
				} 
				.call-now-button { top: {$this->lv_options['move_top']}%; }
				.call-now-button { left: {$this->lv_options['move_left']}%; }
				.call-now-button { background: {$this->lv_options['bg_color']}; }
				.quick-alo-ph-btn-icon { background-color: {$this->lv_options['call_color']} !important; }
				.call-now-button .call-text { color: {$this->lv_options['call_text_color']}; }
				.call-now-button .call-text > a:link, .call-now-button .call-text > a:visited { color: {$this->lv_options['call_text_color']}; }
				";
				if($isCustomImg == 1)
			    {
				    $return .= "    .quick-alo-ph-btn-icon.quick-alo-custom-img-circle
    {
       background:url(". $imgIconUrl .") no-repeat center center;
        background-size: 40px 40px;
    }";
				
			    }
			$return .= "
			</style>";
		} 
		$return .= "
			<!-- /End Quick Buttons By https://muatheme.com -->\n\n";
			
		echo apply_filters('lv_output',$return);
	}
	
	// Checking and setting the default options.
	function lv_options() { 
	   
		$defaults = array(
			'screen_size' 	 => '2880',
			'move_left'      => '3',
			'move_top'       => '90',
			'bg_color' 		 => '#1a1919',
			'call_text_tablet' => '',
			'call_text_desktop' => '',
			'call_text'   	 => __('Call Now','lv-web'),
			'call_color' 	 => '#0c3',
			'call_text_color'=> '#fff',
			'phone_number' 	 => '',
			'checkbox_custom_image' => ''
		);

		// Get user options
		$lv_options = get_option('lv_options');		
		
		// if the user hasn't made settings yet, default
		if (is_array($lv_options)) {
			// Lets make sure we have a value for each as some might be new.
			foreach ($defaults as $k => $v)
				if (!isset($lv_options[$k]) || empty($lv_options[$k]))
					$lv_options[$k] = $v;
		} 
		// Must be first, lets use defaults
		else {
			$lv_options = $defaults;
		}
		
		return $lv_options;
	}
	
	/**
	 * Mandatory phone number information is required.
	 *
	 * @since 1.0.1
	 */
	function lv_mandatory_have_info() {
		return ((isset($this->lv_options['phone_number']) && !empty($this->lv_options['phone_number']))) ? true : false;
	}

	/**
	 * helper, clean phone
	 *
	 * @since 1.0.1
	 */
	function lv_sanitize_phone($number) {
		return  $number;
	}
	
}
new LV_Quick_Call_Button();