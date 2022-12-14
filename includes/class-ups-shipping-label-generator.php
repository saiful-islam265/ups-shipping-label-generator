<?php

//Load the autoloader.
require_once plugin_dir_path( __FILE__ ) . '../vendor/autoload.php';

/**
 * Generte shipping label "class"
 */
final class UPS_Shipping_Label_Generator {
	public $shipment = '';
	public $package = '';
	public $shipper = '';
	public $shipperAddress = '';
	public $address = '';
	public $shipTo = '';
	public $shipFrom = '';
	public $soldTo = '';
	public $service = '';
	public $unit = '';
	public $rateInformation = '';
	public $dimensions = '';
	public $api = '';
	public $shipping_label_dir = '';

	/**
	* Initializing plugin
	*/
	public function __construct(){
		add_action('woocommerce_thankyou', [$this, 'create_shipping_label'], 10, 1);
		add_action('plugins_loaded', [$this, 'create_shipping_label_dir']);add_filter('manage_edit-shop_order_columns', [$this, 'add_bol_column']);
		add_action('manage_shop_order_posts_custom_column', [$this, 'populate_bol_column_data'], 10, 2);
		$this->load_dependencies();
		new UPS_Shipping_Label_Generator_Admin();
	}

	/**
	 * Include the following files that make up the plugin:
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-ups-shipping-label-generator-admin.php';
	}

	/**
	 * Added shipping label column in woocommerce order list page
	 * 
	 * @param mixed $columns returns columns as we instruct
	 * 
	 * @return mixed
	 */
	public function add_bol_column( $columns ){
		$columns['ups_shipping_label'] = __("UPS Shipping Label", '');
		return $columns;
	}

	/**
	 * Displaying shipping label pdf in woocommerce order list page
	 * 
	 * @param mixed $column  column name for order list page
	 * @param mixed $post_id postid for exact order
	 * 
	 * @return void
	 */
	public function populate_bol_column_data($column, $post_id){
		$upload_dir = wp_upload_dir();
		$confirmation_data_array = get_post_meta($post_id, 'confirmation_data_array',  true);
		
		if ('ups_shipping_label' == $column) {
			if (!empty($confirmation_data_array) && is_array($confirmation_data_array)) {
				/* $pdf = $this->shipping_label_dir . '/'. "$post_id.gif";
				$base64_string = $confirmation_data_array['PackageResults']->LabelImage->GraphicImage;
				$ifp = fopen($pdf, 'wb');
				fwrite($ifp, base64_decode($base64_string));
				fclose($ifp); */
				$pritable_link = $upload_dir['baseurl'] . '/shipping_label/'. "$post_id.gif";
				printf('<a href="%s" target="_blank">%s</a>', $pritable_link, __('View', ''));
			}
		}
	}
	/**
	 * Init function for single tone approach
	 *
	 * @return void
	 */
	public static function init()
	{
		static $instance = false;
		if (!$instance) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Creates shipping label directory in wp upoloads directory
	 * 
	 * @return void
	 */
	public function create_shipping_label_dir()
	{
		$upload_dir   = wp_upload_dir();
		$shipping_dir = $upload_dir["basedir"] . "/shipping_label";
		if (!file_exists($shipping_dir)) {
			wp_mkdir_p($shipping_dir);
		}
		$this->shipping_label_dir = $shipping_dir;
	}

	/**
	 * Creates shipping label in pdf through ups api and saves in wp directory
	 * 
	 * @param $order_id order_id 
	 * 
	 * @return void
	 */
	public function create_shipping_label($order_id){
		$shipper_info = get_option('ups_shipper_info');
		$api_info = get_option('ups_account_details');
		$access_key = $api_info['Access Key/API Key'];
		$ups_user_id = $api_info['UPS Username/UserID'];
		$ups_user_pass = $api_info['UPS User Password'];
		$account_number = $shipper_info['UPS Shipper Number / Account Number'];
		$shipper_name = $shipper_info['Shipper Name'];
		$attention_name = $shipper_info['Shipper Attention Name/ Business Name'];
		$address_line = $shipper_info['Shipper Address Line'];
		$postal_code = $shipper_info['Shipper Postal Code'];
		$shipper_city = $shipper_info['Shipper City'];
		$state_code = $shipper_info['Shipper State Province Code'];
		$country_code = $shipper_info['Shipper Country Code'];
		$shipper_email = $shipper_info['Shipper Email Address'];
		$shipper_phone = $shipper_info['Shipper Phone Number'];

		$order = wc_get_order($order_id);
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();
			$product = wc_get_product($product_id);
			$woocommerce_dimension_unit = get_option('woocommerce_dimension_unit');
			$woocommerce_weight_unit = get_option('woocommerce_weight_unit');
			$weight = $product->get_weight();
			$length = $product->get_length();
			$width = $product->get_width();
			$height = $product->get_height();
			break;
		}
		// 4. Echo image only if $cat_in_order == true   
		$this->shipment = new Ups\Entity\Shipment;
		// Set shipper
		$this->shipper = $this->shipment->getShipper();
		$this->shipper->setShipperNumber('0A160X');
		$this->shipper->setName('HypeMill/Red Industries');
		$this->shipper->setAttentionName('HypeMill');
		$this->shipperAddress = $this->shipper->getAddress();
		$this->shipperAddress->setAddressLine1('1236 Industrial Ave #107');
		$this->shipperAddress->setPostalCode('28054');
		$this->shipperAddress->setCity('GASTONIA');
		$this->shipperAddress->setStateProvinceCode('NC'); // required in US
		$this->shipperAddress->setCountryCode('US');
		$this->shipper->setAddress($this->shipperAddress);
		$this->shipper->setEmailAddress('hello@hoodsly.com');
		$this->shipper->setPhoneNumber('8778470405');
		$this->shipment->setShipper($this->shipper);

		// shipping
		$address_state = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_shipping_country();
		$this->address = new \Ups\Entity\Address();
		$this->address->setAddressLine1($order->get_shipping_address_1());
		$this->address->setAddressLine2($order->get_shipping_address_2());
		$this->address->setPostalCode($order->get_shipping_postcode());
		$this->address->setCity($order->get_shipping_city());
		$this->address->setStateProvinceCode($address_state);  // Required in US
		$this->address->setCountryCode($order->get_shipping_country());
		$this->shipTo = new \Ups\Entity\ShipTo();
		$this->shipTo->setAddress($this->address);
		$this->shipTo->setCompanyName($order->get_shipping_first_name().' '.$order->get_shipping_last_name());
		$this->shipTo->setAttentionName(sprintf('%s %s', $order->get_shipping_first_name(), $order->get_shipping_last_name()));
		$this->shipTo->setEmailAddress($order->get_billing_email());
		$this->shipTo->setPhoneNumber($order->get_billing_phone());
		$this->shipment->setShipTo($this->shipTo);

		// From address
		$this->address = new \Ups\Entity\Address();
		$this->address->setAddressLine1('1236 Industrial Ave #107');
		$this->address->setPostalCode('28054');
		$this->address->setCity('GASTONIA');
		$this->address->setStateProvinceCode('NC');
		$this->address->setCountryCode('US');
		$this->shipFrom = new \Ups\Entity\ShipFrom();
		$this->shipFrom->setAddress($this->address);
		$this->shipFrom->setName('HypeMill');
		$this->shipFrom->setAttentionName($this->shipFrom->getName());
		$this->shipFrom->setCompanyName('HypeMill/Red Industries');
		$this->shipFrom->setEmailAddress('hello@hoodsly.com');
		$this->shipFrom->setPhoneNumber('8778470405');
		$this->shipment->setShipFrom($this->shipFrom);

		// Sold to
		$this->address = new \Ups\Entity\Address();
		$this->address->setAddressLine1($order->get_shipping_address_1());
		$this->address->setAddressLine2($order->get_shipping_address_2());
		$this->address->setPostalCode($order->get_shipping_postcode());
		$this->address->setCity($order->get_shipping_city());
		$this->address->setCountryCode($order->get_shipping_country());
		$this->address->setStateProvinceCode($address_state);
		$this->soldTo = new \Ups\Entity\SoldTo;
		$this->soldTo->setAddress($this->address);
		$this->soldTo->setAttentionName(sprintf('%s %s', $order->get_shipping_first_name(), $order->get_shipping_last_name()));
		$this->soldTo->setCompanyName($this->soldTo->getAttentionName());
		$this->soldTo->setEmailAddress($order->get_billing_email());
		$this->soldTo->setPhoneNumber($order->get_billing_phone());
		$this->shipment->setSoldTo($this->soldTo);

		// Set service
		$this->service = new \Ups\Entity\Service;
		$this->service->setCode(\Ups\Entity\Service::S_GROUND);
		$this->service->setDescription($this->service->getName());
		$this->shipment->setService($this->service);

		/* // Mark as a return (if return)
		if ($return) {
			$returnService = new \Ups\Entity\ReturnService;
			$returnService->setCode(\Ups\Entity\ReturnService::PRINT_RETURN_LABEL_PRL);
			$shipment->setReturnService($returnService);
		} */

		// Set description
		$this->shipment->setDescription('String');

		// Add Package
		$this->package = new \Ups\Entity\Package();
		$this->package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
		$this->package->getPackageWeight()->setWeight($weight);
		$this->unit = new \Ups\Entity\UnitOfMeasurement;
		if ($woocommerce_weight_unit == 'kg') {
			$this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_KGS);
		} elseif ($woocommerce_weight_unit == 'lbs') {
			$this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_LBS);
		}
		$this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_LBS);
		$this->package->getPackageWeight()->setUnitOfMeasurement($this->unit);

		// Set Package Service Options
		$packageServiceOptions = new \Ups\Entity\PackageServiceOptions();
		$packageServiceOptions->setShipperReleaseIndicator(true);
		$this->package->setPackageServiceOptions($packageServiceOptions);

		// Set dimensions
		$this->dimensions = new \Ups\Entity\Dimensions();
		$this->dimensions->setHeight($height);
		$this->dimensions->setWidth($width);
		$this->dimensions->setLength($length);
		$this->unit = new \Ups\Entity\UnitOfMeasurement;
		if ($woocommerce_dimension_unit == 'cm') {
			$this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_CM);
		} elseif ($woocommerce_dimension_unit == 'in') {
			$this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);
		}
		$this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);
		$this->dimensions->setUnitOfMeasurement($this->unit);
		$this->package->setDimensions($this->dimensions);

		// Add descriptions because it is a package
		$this->package->setDescription('XX');

		// Add this package
		$this->shipment->addPackage($this->package);

		// Set Reference Number
		//$referenceNumber = new \Ups\Entity\ReferenceNumber;
		/* if ($return) {
			$referenceNumber->setCode(\Ups\Entity\ReferenceNumber::CODE_RETURN_AUTHORIZATION_NUMBER);
			$referenceNumber->setValue($return_id);
		} else {
			$referenceNumber->setCode(\Ups\Entity\ReferenceNumber::CODE_INVOICE_NUMBER);
			$referenceNumber->setValue('2574');
		} */
		//$shipment->setReferenceNumber($referenceNumber);;
		// Set payment information
		$this->shipment->setPaymentInformation(new \Ups\Entity\PaymentInformation('prepaid', (object)array('AccountNumber' => $this->shipper->getShipperNumber())));

		// Ask for negotiated rates (optional)
		$this->rateInformation = new \Ups\Entity\RateInformation;
		$this->rateInformation->setNegotiatedRatesIndicator(1);
		$this->shipment->setRateInformation($this->rateInformation);
		// Get shipment info
		try {
			$this->api = new Ups\Shipping($access_key, $ups_user_id, $ups_user_pass);
			$confirm = $this->api->confirm(\Ups\Shipping::REQ_VALIDATE, $this->shipment);
			update_post_meta($order_id, 'created_shipments_details_array', (array) $confirm);
			update_post_meta($order_id, 'ShipmentIdentificationNumber', $confirm->ShipmentIdentificationNumber);
			if ($confirm) {
				$accept = $this->api->accept($confirm->ShipmentDigest);
				update_post_meta($order_id, 'confirmation_data_array', (array) $accept);
				$order->update_status('wc-completed', '', true);
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
		$label_file = $this->shipping_label_dir . '/'. "$order_id.pdf";
		$base64_string = $accept->PackageResults->LabelImage->GraphicImage;
		/* $ifp = fopen($label_file, 'wb');
		fwrite($ifp, base64_decode($base64_string));
		fclose($ifp); */
		$imagick = new Imagick();
		$imagick->readImageBlob($base64_string);
		$imagick->rotateImage(new ImagickPixel(), 90);
		$imagick->setFormat("pdf");
		$imagick->writeImage($label_file);
		
		/* $degrees = 270;
		$source = imagecreatefromgif($label_file);
		$rotate = imagerotate($source, $degrees, 0);
		$img_resized = imagescale($rotate, 595, 816);
		imagegif($img_resized, $label_file); */
	}
}
