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
	public function __construct(  ) {
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
		__('UPS Settings', 'shipping-label-gen'),  // Admin page title
		__('UPS Settings', 'shipping-label-gen'),  // Admin menu label
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
				'title'=>'Account & API Info',
				'page'=>'ups_account_details',
				'fields'=> array(
					array(
						'id'=> 'ups_access_key',
						'title'=>__('Access Key/API Key', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_access_userid',
						'title'=>__('UPS Username/UserID', 'shipping-label-gen'),
						'callback'=> [$this,'text_callback']
					),
					array(
						'id'=> 'ups_access_userpass',
						'title'=>__('UPS User Password', 'shipping-label-gen'),
						'callback'=> [$this,'text_callback']
					),
				)
			),
			'setting_2_id' => array(
				'title'=>'Shipper Info',
				'page'=>'ups_shipper_info',
				'fields'=> array(
					array(
						'id'=> 'ups_shipper_number',
						'title'=>__('UPS Shipper Number / Account Number', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_name',
						'title'=>__('Shipper Name', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_attention_name',
						'title'=>__('Shipper Attention Name/ Business Name', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_address_line',
						'title'=>__('Shipper Address Line', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_postal_code',
						'title'=>__('Shipper Postal Code', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_city',
						'title'=>__('Shipper City', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_state_province_code',
						'title'=>__('Shipper State Province Code', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_country_code',
						'title'=>__('Shipper Country Code', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_shipper_email_address',
						'title'=>__('Shipper Email Address', 'shipping-label-gen'),
						'callback'=> [$this, 'text_callback']
					),
					array(
						'id'=> 'ups_phone_number',
						'title'=>__('Shipper Phone Number', 'shipping-label-gen'),
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
						$field['title'] //id 
					) 
				);
			}
			register_setting($values['page'], $values['page']);
		} // end of foreach
	} // end of ups_setting_options()

	/**
	 * Callback for text type input field rendering
	 * @param mixed $args
	 * @return void
	 */
	public function text_callback($args) { 
		$options = get_option($args[0]);
		echo '<input type="text" class="regular-text" id="' . esc_attr($args[1]) . '" name="'. esc_html($args[0]) .'[' . esc_html($args[1]) . ']" value="' . esc_html($options['' . $args[1] . '']) . '"></input>';
	}

	/**
	 * Display registered settings section and fields
	 * @return void
	 */
	public function ups_setting_general_index() {
		?>
		<div class="wrap">  
			<div id="icon-themes" class="icon32"></div>  
			<h2><?php _e('UPS Settings', 'shipping-label-gen')?></h2>  
			<?php
				settings_errors(); 
				$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'first';  
			?>
			<h2 class="nav-tab-wrapper">  
				<a href="?page=ups-setting-general-options&tab=first" class="nav-tab <?php echo esc_attr($active_tab) == 'first' ? 'nav-tab-active' : ''; ?>"><?php _e('Account & API Info', 'shipping-label-gen')?></a>  
				<a href="?page=ups-setting-general-options&tab=second" class="nav-tab <?php echo esc_attr($active_tab) == 'second' ? 'nav-tab-active' : ''; ?>"><?php _e('Shipper Info', 'shipping-label-gen')?></a>
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
