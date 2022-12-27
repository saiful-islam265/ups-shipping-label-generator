<?php
/**
 * Summary of UPS_Shipping_Label_Generator_Admin
 */
class Shipping_Label_Generator_Admin_UPS {
	/**
	 * Initialize the class
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action("admin_init", [$this,"ups_setting_options"]);
		add_action("admin_menu", [$this,"ups_setting_admin"]);
	}

	/**
	 * Registered custom menu to settings section
	 * @return void
	 */
	public function ups_setting_admin() {
		/* Base Menu */
		add_menu_page(
		__('UPS Settings', 'shipping-label-generator-with-ups'),  // Admin page title
		__('UPS Settings', 'shipping-label-generator-with-ups'),  // Admin menu label
		'manage_options',
		'ups-setting-general-options', // Admin slug
		[$this, 'ups_setting_general_index']); // Display Page
	}

	/**
	 * Register settings section and fields
	 * @return void
	 */
	public function ups_setting_options() { 
		$settings = array(
			'setting_1_id' => array(
				'title'=>__('Account & API Info'),
				'page'=>'ups_account_details',
				'fields'=> array(
					array(
						'id'=> 'ups_access_key',
						'title'=>__('Access Key/API Key', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_access_userid',
						'title'=>__('UPS Username/UserID', 'shipping-label-generator-with-ups'),
						'callback'=> [$this,'text_callback']
					),
					array(
						'id'=> 'ups_access_userpass',
						'title'=>__('UPS User Password', 'shipping-label-generator-with-ups'),
						'callback'=> [$this,'text_callback']
					)
				)
			),
			'setting_2_id' => array(
				'title'=>__('Shipper Info'),
				'page'=>'ups_shipper_info',
				'fields'=> array(
					array(
						'id'=> 'ups_shipper_number',
						'title'=>__('UPS Shipper Number / Account Number', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_name',
						'title'=>__('Shipper Name', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_attention_name',
						'title'=>__('Shipper Attention Name/ Business Name', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_address_line',
						'title'=>__('Shipper Address Line', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_postal_code',
						'title'=>__('Shipper Postal Code', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_city',
						'title'=>__('Shipper City', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_state_province_code',
						'title'=>__('Shipper State Province Code', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_country_code',
						'title'=>__('Shipper Country Code', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_email_address',
						'title'=>__('Shipper Email Address', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_phone_number',
						'title'=>__('Shipper Phone Number', 'shipping-label-generator-with-ups'),
						'callback'=> [$this, 'text_callback']
					),
				)
			)
		);
		foreach( $settings as $id => $values){
			add_settings_section(
				$id, // ID used to identify this section and with which to register options
				$values['title'],
				'', // Callback used to render the description of the section
				$values['page'] // Page on which to add this section of options
			);
			
			foreach ($values['fields'] as $field) {
				// code...
				add_settings_field(  
					$field['id'],         // ID used to identify the field throughout the theme             
					$field['title'],                    // The label to the left of the option interface element
					$field['callback'],   
					$values['page'],         // The page on which this option will be added            
					$id,          // ID of the section
					array(
						$values['page'], //option name
						$field['title'], //id
						$field['id'], //id
					) 
				);
			}
			register_setting(
				$values['page'],
				$values['page'],
			);
		} // end of foreach
	} // end of ups_setting_options()

	/**
	 * sanitize settings
	 * @param mixed $option
	 * @return string
	 */
	public function sanitize_register_field( $option ){
		//sanitize
		$option = sanitize_text_field($option);
		
		return $option;
	}

	/**
	 * Callback for text type input field rendering
	 * @param mixed $args
	 * @return void
	 */
	public function text_callback($args) {
		$options = get_option($args[0]);
		$options = !empty($options) ? $options : array();
		echo '<input type="text" class="regular-text" id="' . esc_attr($args[2]) . '" name="'. esc_html($args[0]) .'[' . esc_html($args[2]) . ']" value="' . esc_html($options[$args[2]]) . '">';
	}

	/**
	 * Callback for radio type input field rendering
	 * @param mixed $args
	 * @return void
	 */
	public function radio_callback($args) {
		$options = get_option($args[0]);
		$mode_option = [
			'sandbox' => 'Sandbox API',
			'production' => 'Production API',
		];
		foreach($mode_option as $key => $value){
			echo '<label for="' . esc_attr($value) . '" style="padding-right: 20px !important"><input type="radio" class="regular-text" id="' . esc_attr($value) . '" name="'. esc_html($args[0]) .'[' . esc_html($args[2]) . ']" value="'.$key.'" '.checked($options[$args[2]], $key, false).'>'.$value.'</label>';
		}
	}

	/**
	 * Callback for checkbox type input field rendering
	 * @param mixed $args
	 * @return void
	 */
	public function checkbox_callback($args) {
		$options = get_option($args[0]);
		$select_items = [
			'TestOne' => 'Test One',
			'TestTwo' => 'Test Two',
			'TestThree' => 'Test Three',
		];
		echo '<fieldset>';
		foreach ($select_items as $key => $value) {
			$checked = '';
			if(in_array($key, $options[$args[2]])){
				$checked = 'checked';
			}
			echo '<label for="'.$key.'" style="padding-right: 20px !important"><input type="checkbox" class="regular-text" id="' . esc_attr($key) . '" name="'. esc_html($args[0]) .'[' . esc_html($args[2]) . '][]" value="'.$key.'" '.$checked.'>'.$value.'</label>';
		}
		echo '</fieldset>';
	}

	/**
	 * Callback for select type input field rendering
	 * @param mixed $args
	 * @return void
	 */
	public function select_callback($args) {
		$options = get_option($args[0]);
		$option_items = [
			'testOne' => 'Test option One',
			'testTwo' => 'Test option Two',
			'testThree' => 'Test option Three',
			'testFour' => 'Test option Four',
			'testFive' => 'Test option Five'
		];
		echo '<select type="text" class="regular-text" id="' . esc_attr($args[2]) . '" name="'. esc_html($args[0]) .'[' . esc_html($args[2]) . ']">';
		foreach ($option_items as $key => $value) {
			echo '<option value="'.$key.'" ' . selected($options[$args[2]], $key, false) . '>'.$value.'</option>';
		}
		echo '</select>';
	}

	/**
	 * Display registered settings section and fields
	 * @return void
	 */
	public function ups_setting_general_index() {
		?>
		<div class="wrap">  
			<div id="icon-themes" class="icon32"></div>  
			<h2><?php _e('UPS Settings', 'shipping-label-generator-with-ups')?></h2>  
			<?php
				settings_errors(); 
				$active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field($_GET[ 'tab' ]) : 'first';  
			?>
			<h2 class="nav-tab-wrapper">  
				<a href="?page=ups-setting-general-options&tab=first" class="nav-tab <?php echo esc_attr($active_tab) == 'first' ? 'nav-tab-active' : ''; ?>"><?php _e('Account & API Info', 'shipping-label-generator-with-ups') ?></a>  
				<a href="?page=ups-setting-general-options&tab=second" class="nav-tab <?php echo esc_attr($active_tab) == 'second' ? 'nav-tab-active' : ''; ?>"><?php _e('Shipper Info', 'shipping-label-generator-with-ups')?></a>
			</h2>  

			<form method="post" action="options.php">  
				<?php
				if( $active_tab == 'first' ) {
					settings_fields( 'ups_account_details' );
					do_settings_sections( 'ups_account_details' ); 
				} else if( $active_tab == 'second' ) {
					settings_fields( 'ups_shipper_info' );
					do_settings_sections( 'ups_shipper_info' );
				} 
				?>             
				<?php submit_button(); ?>
			</form> 
		</div> 
		<?php
	}
}
// EOF
