<?php
/**
 * Plugin Name: UPS Shipping Label Generator
 * Plugin URI:  https://wppool.dev
 * Description: This plugin will create shipping label for ups courier right after any order created.
 * Version:     1.0
 * Author:      Saiful Islam
 * Author URI:  https://wppool.dev
 * Text Domain: hoodsly-hub
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';


final class UPSShippingLabel
{
    const VERSION = '1.0.0';
    const ACCESSKEY = '9DA11D2B5E981955';
    const USERID = 'HollieJFox';
    const PASSWORD = 'HypeMill2020';
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
    private $shipping_label_dir = '';
    public function __construct()
    {
        add_action('woocommerce_thankyou', [$this, 'create_shipping_label'], 10, 1);
		//$this->create_shipping_label('5823');
        //add_action('init', [$this, 'test_data']);
        add_action('plugins_loaded', [$this, 'create_shipping_label_dir']);add_filter('manage_edit-shop_order_columns', [$this, 'add_bol_column']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'populate_bol_column_data'], 10, 2);
    }

    function test_data(){
        //$this->shipment = new Ups\Entity\Shipment;
        $order = new WC_Order('13957');
        $items = $order->get_items(); 
        $cat_in_order = false;
        
        foreach ( $items as $item ) {      
            $product_id = $item->get_product_id();
            $product = wc_get_product( $product_id );
            if ( has_term( 'Samples', 'product_cat', $product_id ) ) {
                $woocommerce_dimension_unit = get_option('woocommerce_dimension_unit');
                $woocommerce_weight_unit = get_option('woocommerce_weight_unit');
                $weight = $product->get_weight();
                $length = $product->get_length();
                $width = $product->get_width();
                $height = $product->get_height();
                $cat_in_order = true;
                break;
            }
        }
        
        // 4. Echo image only if $cat_in_order == true   
        /* if ( $cat_in_order ) {
            //write_log($woocommerce_dimension_unit);
            $order->update_status( 'wc-completed', '', true );
        } */
        //
    }
    
    // wc order BOL column
    public function add_bol_column( $columns ) {
        $columns['ups_shipping_label'] = __("UPS Shipping Label", '');
        return $columns;
    }

    public function populate_bol_column_data($column, $post_id){
        $upload_dir = wp_upload_dir();
        $confirmation_data_array = get_post_meta( $post_id, 'confirmation_data_array',  true);
        $created_shipments_details_array 	= get_post_meta( '13941', 'created_shipments_details_array', true );
        
        if( 'ups_shipping_label' == $column ){
            if(!empty($confirmation_data_array) && is_array($confirmation_data_array)){
                $pdf = $this->shipping_label_dir . '/'. "$post_id.gif";
                $base64_string = $confirmation_data_array['PackageResults']->LabelImage->GraphicImage;
                $ifp = fopen($pdf, 'wb');
                fwrite($ifp, base64_decode($base64_string));
                fclose($ifp);
                $pritable_link = $upload_dir['baseurl'] . '/shipping_label/'. "$post_id.gif";
                printf('<a href="%s" target="_blank">%s</a>',$pritable_link, __('View', ''));
            }/*  else{
                //write_log("kich8");
                printf('<a href="#" class="gen_ship_lab" data-bol="">%s</a>', __('Generate', ''));
            } */
        }
    }
    /**
     * init function for single tone approach
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

    public function create_shipping_label_dir(){
        $upload_dir   = wp_upload_dir();
        $shipping_dir = $upload_dir["basedir"] . "/shipping_label";
        if ( ! file_exists( $shipping_dir ) ) {
            wp_mkdir_p( $shipping_dir );
        }
        $this->shipping_label_dir = $shipping_dir;
    }

    public function create_shipping_label($order_id)
    {
        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $cat_in_order = false;
        foreach ( $items as $item ) {
            $product_id = $item->get_product_id();
            $product = wc_get_product( $product_id );
            if ( has_term( 'Samples', 'product_cat', $product_id ) ) {
                $woocommerce_dimension_unit = get_option('woocommerce_dimension_unit');
                $woocommerce_weight_unit = get_option('woocommerce_weight_unit');
                $weight = $product->get_weight();
                $length = $product->get_length();
                $width = $product->get_width();
                $height = $product->get_height();
                $cat_in_order = true;
                break;
            }
        }
        
        // 4. Echo image only if $cat_in_order == true   
        if ($cat_in_order) {
            $this->shipment = new Ups\Entity\Shipment;
            // $line_items = array();
            // foreach ($order->get_items() as  $item_key => $item_values) {
            //     $item_data = $item_values->get_data();
            //     $new_arr = [];
            //     $new_arr['name'] = $item_data['name'];
            //     $new_arr['id'] = $item_data['id'];
            //     $new_arr['product_id'] = $item_data['product_id'];
            //     $new_arr['variation_id'] = $item_data['variation_id'];
            //     $new_arr['quantity'] = $item_data['quantity'];
            //     $line_items[] = $new_arr;
            // }
            // foreach ($order->get_items('shipping') as $item_id => $item) {
            //     /* $order_item_name             = $item->get_name();
            //     $order_item_type             = $item->get_type();
            //     $shipping_method_id          = $item->get_method_id(); // The method ID
            //     $shipping_method_instance_id = $item->get_instance_id(); // The instance ID
            //     $shipping_method_total_tax   = $item->get_total_tax();
            //     $shipping_method_taxes       = $item->get_taxes(); */

            //     $shipping_method_total       = $item->get_total();
            //     $shipping_method_id          = $item->get_method_id(); // The method ID
            //     $shipping_method_title       = $item->get_method_title();
            // }
            // $data = $order->get_data();
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
            if($woocommerce_weight_unit == 'kg'){
                $this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_KGS);
            }elseif($woocommerce_weight_unit == 'lbs'){
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
            if($woocommerce_dimension_unit == 'cm'){
                $this->unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_CM);
            }elseif ($woocommerce_dimension_unit == 'in') {
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
            $this->shipment->setPaymentInformation(new \Ups\Entity\PaymentInformation('prepaid', (object)array('AccountNumber' => '0A160X')));

            // Ask for negotiated rates (optional)
            $this->rateInformation = new \Ups\Entity\RateInformation;
            $this->rateInformation->setNegotiatedRatesIndicator(1);
            $this->shipment->setRateInformation($this->rateInformation);
            // Get shipment info
            try {
                $this->api = new Ups\Shipping(self::ACCESSKEY, self::USERID, self::PASSWORD);
                $confirm = $this->api->confirm(\Ups\Shipping::REQ_VALIDATE, $this->shipment);
                update_post_meta($order_id, 'created_shipments_details_array', (array) $confirm);
                update_post_meta($order_id, 'ShipmentIdentificationNumber', $confirm->ShipmentIdentificationNumber);

                if ($confirm) {
                    $accept = $this->api->accept($confirm->ShipmentDigest);
                    update_post_meta($order_id, 'confirmation_data_array', (array) $accept);
                    $order->update_status( 'wc-completed', '', true );
                }
            } catch (\Exception $e) {
                var_dump($e);
            }
            /* $label_file = $order_id .".gif";
            $base64_string = $accept->PackageResults->LabelImage->GraphicImage;
            $ifp = fopen($label_file, 'wb');
            fwrite($ifp, base64_decode($base64_string));
            fclose($ifp); */
        }
    }
}

/**
 * initialise the main function
 *
 * @return void
 */
function ups_shipping_label()
{
    return UPSShippingLabel::init();
}

// let's start the plugin
ups_shipping_label();
