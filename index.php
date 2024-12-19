<?php
/*
  Plugin Name: Paymentwoocommerce_v1
  
  Description: Extends WooCommerce with Paymentwoocommerce_v1 Payment Gateway.
  Version: 1.1
  Author: Paymentwoocommerce_v1

  Copyright: © 2017 Paymentwoocommerce_v1.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH'))
    exit;
add_action('plugins_loaded', 'woocommerce_cms_init', 0);

function woocommerce_cms_init() {
    if (!class_exists('WC_Payment_Gateway'))
        return;

    /**
     * Gateway class
     */
    class WC_cms extends WC_Payment_Gateway {

        public function __construct() {
            // Go wild in here
            $this->id = 'cms';
			//echo '<script> alert("'.$this->id.'"); </script>';
            $this->method_title = __('Paymentwoocommerce_v1', 'cms');
            //$this->icon = plugins_url('images/payicon.png', __FILE__);
            $this->has_fields = false;
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->merchant_id = $this->settings['merchant_id'];
            $this->working_key = $this->settings['working_key'];
            //$this->access_code = $this->settings['access_code'];

            //Gateway specific fields start
            $this->totype = $this->settings['totype'];
            $this->partenerid = $this->settings['partenerid'];
            $this->language = $this->settings['language'];
            
            $this->ipaddr = $this->settings['ipaddr'];
            $this->livemode = $this->settings['livemode'];
            $this->liveurl = $this->settings['liveurl'];
            $this->testurl = $this->settings['testurl'];
			$this->queryurl = $this->settings['queryurl'];
           // $this->testurl = $this->settings['testurl'];
            $this->paymenttype = "";
            $this->cardtype = "";
            $this->reservedField1 = "";
            $this->reservedField2 = "";
            $this->url = '';
            //Gateway specific fields end

          
            $this->notify_url = home_url('index.php/checkout/wc-api/WC_cms');
            $this->msg['message'] = "";
            $this->msg['class'] = "";

            //update for woocommerce >2.0
            add_action('woocommerce_api_wc_cms', array($this, 'check_cms_response'));
            add_action('valid-cms-request', array($this, 'successful_request'));
            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            }
            add_action('woocommerce_receipt_cms', array($this, 'receipt_page'));
            add_action('woocommerce_thankyou_cms', array($this, 'thankyou_page'));
        }

        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'Paymentwoocommerce_v1'),
                    'type' => 'checkbox',
                    'label' => __('Enable Paymentwoocommerce_v1 Module.', 'Paymentwoocommerce_v1'),
                    'default' => 'no'),
                'title' => array(
                    'title' => __('Title:', 'Paymentwoocommerce_v1'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'Paymentwoocommerce_v1'),
                    'default' => __('Paymentwoocommerce_v1', 'Paymentwoocommerce_v1')),
                'description' => array(
                    'title' => __('Description:', 'Paymentwoocommerce_v1'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'Paymentwoocommerce_v1'),
                    'default' => __('Pay securely by Credit or internet banking through Paymentwoocommerce_v1 Secure Servers.', 'Paymentwoocommerce_v1')),
                'merchant_id' => array(
                    'title' => __('Merchant ID', 'Paymentwoocommerce_v1'),
                    'type' => 'text',
                    'description' => __('This id(USER ID) available at "Generate Working Key" of "Settings and Options at Paymentwoocommerce_v1."')),
                'working_key' => array(
                    'title' => __('Working Key', 'Paymentwoocommerce_v1'),
                    'type' => 'text',
                    'description' => __('Given to Merchant by Paymentwoocommerce_v1', 'Paymentwoocommerce_v1'),
                ),
                'totype' => array(
                    'title' => __('Partner Name', 'Paymentwoocommerce_v1'),
                    'type' => 'text',
                    'description' => __('Processor Name', 'Paymentwoocommerce_v1'),
                ),
				
				'partenerid' => array(
                    'title' => __('Partner Id', 'Paymentwoocommerce_v1'),
                    'type' => 'text',
                    'description' => __('Enter Partner Id', 'Paymentwoocommerce_v1'),
                ),
				
				'language' => array(
					'title' 			=> __('Your Store Language', 'Paymentwoocommerce_v1'),
					'type' 			=> 'select',
					'options' 		=> array('bg'=>'Bulgarian', 'en'=>'English', 'ja'=>'Japanese', 'ro'=>'Romanian', 'sp'=>'Spanish'),
					'description' => __('Your Store Language', 'Paymentwoocommerce_v1')
				),
				
				
				'ipaddr' => array(
                    'title' => __('Ip Address', 'Paymentwoocommerce_v1'),
                    'type' => 'text',
                    'description' => __('Enter your ip address', 'Paymentwoocommerce_v1'),
                ),
				
                
				
				'livemode' => array(
					'title' 			=> __('Live Mode Activation', 'Paymentwoocommerce_v1'),
					'type' 			=> 'select',
					'options' 		=> array('N'=>'N','Y'=>'Y'),
					'description' => __('Live Mode Activation', 'Paymentwoocommerce_v1')
				),
				
                'liveurl' => array(
                    'title' => __('Live Mode URL', 'Payment'),
                    'type' => 'text',
                    'description' => __('Live Mode Transaction URL', 'Payment'),
                ),
                'testurl' => array(
                    'title' => __('Test Mode URL', 'Payment'),
                    'type' => 'text',
                    'description' => __('Test Mode Transaction URL', 'Payment'),
                ),
				
				'queryurl' => array(
                    'title' => __('Query URL', 'Payment'),
                    'type' => 'text',
                    'description' => __('Query URL', 'Payment'),
                )
            );
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         * */
        public function admin_options() {
            echo '<h3>' . __('Payment Gateway', 'Payment') . '</h3>';
            echo '<p>' . __('Paymentwoocommerce_v1 is  payment gateway for online shopping') . '</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        /**
         *  There are no payment fields for cms, but we want to show the description if set.
         * */
        function payment_fields() {
            if ($this->description)
                echo wpautop(wptexturize($this->description));
        }

        /**
         * Receipt Page
         * */
        function receipt_page($order) {

            echo '<p>' . __('Thank you for your order, please click the button below to pay with  Payment.', 'Payment') . '</p>';
            echo $this->generate_cms_form($order);
        }

        /*         * * Thankyou Page* */

        function thankyou_page($order) {
            if (!empty($this->instructions))
                echo wpautop(wptexturize($this->instructions));
        }

        /**
         * Process the payment and return the result
         * */
        function process_payment($order_id) 
        {
            $order = new WC_Order($order_id);            
            $order->update_status('pending',__('Awaiting offline payment', 'wc-gateway-offline'));
            $description=$order_id.'-'.$this->merchant_id;
            global $wpdb;
            $table_name = $wpdb->prefix . "cms_tbl";
            $sql = $wpdb->insert('' . $table_name . '', array('woocommerce_id' =>$order_id,'order_id' =>$description,'order_status' => 'authstarted' ));                                   
            $order->reduce_order_stock();
            WC()->cart->empty_cart();
            return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url($order));
        }

        /**
         * Check for valid cms server callback
         * */
        function check_cms_response() {
            global $woocommerce;
            $msg['class'] = 'error';
           

            if (isset($_REQUEST['status'])) {
                $trackingid = $_REQUEST['trackingid'];
                $order_id = $_REQUEST["desc"];
                $amount = $_REQUEST["amount"];
                $descriptor = $_REQUEST["descriptor"];
                $checksum = $_REQUEST["checksum"];
                if ($order_id != '') {
                    try {
                        
                        list($first, $last) = explode("-", $order_id);
                        $order = new WC_Order($first);
                        $order_status = $_REQUEST['status'];
                        $transauthorised = false;
                       
								
							
                            if ($order_status == "Y") {
                                $transauthorised = true;
                              
                                $msg['class'] = 'success';
                                
                                   // $order->update_status('processing', __('Payment successful'));
                                    $order->update_status('completed', __('Payment successful'));
                                    $order->add_order_note('Payment successful.');                                     
                                    global $wpdb;
                                    $wpdb->update('wp_cms_tbl', array('order_status' => 'capturesuccess','tracking_id'=>$trackingid), array('woocommerce_id' => $first));
                                    $woocommerce->cart->empty_cart();
									
									
                                
                            } else if ($order_status === "P") {
                              
                                $msg['class'] = 'success';
                                $order->update_status('pending', __('Payment pending'));
                                global $wpdb;
                                $wpdb->update('wp_cms_tbl', array('order_status' => 'authstarted','tracking_id'=>$trackingid), array('woocommerce_id' => $first));
                                $woocommerce->cart->empty_cart();
								
								
								
                            }
							else if ($order_status === "C") {
                              
                                $msg['class'] = 'success';
                                $order->update_status('cancelled', __('Payment cancelled'));
                                global $wpdb;
                                $wpdb->update('wp_cms_tbl', array('order_status' => 'cancelled','tracking_id'=>$trackingid), array('woocommerce_id' => $first));
                                $woocommerce->cart->empty_cart();
								
								
								
                            }
							else if ($order_status === "N") {
                                $msg['class'] = 'error';
                                
                                $order->update_status('failed', __('Payment Declined'));
                                global $wpdb;
                                $wpdb->update('wp_cms_tbl', array('order_status' => 'authfailed','tracking_id'=>$trackingid), array('woocommerce_id' => $first));
                                $woocommerce->cart->empty_cart();
								
                            } else {
                                $msg['class'] = 'error';
                               
                            }
                        
                    } catch (Exception $e) {

                        $msg['class'] = 'error';
                        
                    }
                }
            }

            if (function_exists('wc_add_notice')) {
                wc_add_notice($msg['message'], $msg['class']);
            } else {
                if ($msg['class'] == 'success') {
                    $woocommerce->add_message($msg['message']);
                } else {
                    $woocommerce->add_error($msg['message']);
                }
                $woocommerce->set_messages();
            }
            //$redirect_url = get_permalink(woocommerce_get_page_id('myaccount'));
            $redirect_url = $this->get_return_url($order);
            wp_redirect($redirect_url);
            exit;
        }

        /*
          //Removed For WooCommerce 2.0
          function showMessage($content){
          return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
          } */

        /**
         * Generate cms button link
         * */
        public function generate_cms_form($order_id) {
            global $woocommerce;
            $order = new WC_Order($order_id);
            $order_id = $order_id . '-' .$this->merchant_id;
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            //var_dump("merchant_id".get_locale());
            //Hardcoded datas cms update it...
            $ip = "127.0.0.1";
            $redirecturl = $this->notify_url;
            $country = $woocommerce->customer->get_country();
            $CURRENCY = get_woocommerce_currency();
            $checksum = MD5( $testchecksum = trim($this->merchant_id) . "|" . trim($this->totype) . "|" . trim(number_format((float)$order->order_total, 2, '.', '')) . "|" . trim($order_id) . "|" . trim($redirecturl) . "|" . trim($this->working_key));
            if ('Y' == $this->livemode) {
                $this->url =$this->liveurl;
            } else {
                $this->url =$this->testurl;
            }

			
			/********************************************/
			
					$country_code = array(
					"AF"=>"093", 
					"AX"=>"358", 
					"AL"=>"355",
					"DZ"=>"231",
					"AS"=>"684",
					"AD"=>"376",
					"AO"=>"244",
					"AI"=>"001",
					"AQ"=>"000",
					"AG"=>"001",
					"AR"=>"054",
					"AM"=>"374",
					"AW"=>"297",
					"AU"=>"061",
					"AT"=>"043",
					"AZ"=>"994",
					"BS"=>"001",
					"BH"=>"973",
					"BD"=>"880",
					"BB"=>"001",
					"BY"=>"375",
					"BE"=>"032",
					"BZ"=>"501",
					"BJ"=>"229",
					"BM"=>"001",
					"BT"=>"975",
					"BO"=>"591",
					"BA"=>"387",
					"BW"=>"267",
					"BV"=>"000",
					"BR"=>"055",
					"IO"=>"246",
					"VG"=>"001",
					"BN"=>"673",
					"BG"=>"359",
					"BF"=>"226",
					"BI"=>"257",
					"KH"=>"855",
					"CM"=>"237",
					"CA"=>"001",
					"CV"=>"238",
					"KY"=>"001",
					"CF"=>"236",
					"TD"=>"235",
					"CL"=>"056",
					"CN"=>"086",
					"CX"=>"061",
					"CC"=>"061",
					"CC"=>"061",
					"CO"=>"057",
					"KM"=>"269",
					"CK"=>"682",
					"CR"=>"506",
					"CI"=>"225",
					"HR"=>"385",
					"CU"=>"053",
					"CY"=>"357",
					"CZ"=>"420",
					"CD"=>"243",
					"DK"=>"045",
					"DJ"=>"253",
					"DM"=>"001",
					"DO"=>"001",
					"EC"=>"593",
					"EG"=>"020",
					"SV"=>"503",
					"GQ"=>"240",
					"ER"=>"291",
					"EE"=>"372",
					"ET"=>"251",
					"FK"=>"500",
					"FO"=>"298",
					"FJ"=>"679",
					"FI"=>"358",
					"FR"=>"033",
					"GF"=>"594",
					"PF"=>"689",
					"TF"=>"000",
					"GA"=>"241",
					"GM"=>"220",
					"GE"=>"995",
					"DE"=>"049",
					"GH"=>"233",
					"GI"=>"350",
					"GR"=>"030",
					"GL"=>"299",
					"GD"=>"001",
					"GP"=>"590",
					"GU"=>"001",
					"GT"=>"502",
					"GG"=>"000",
					"GN"=>"224",
					"GW"=>"245",
					"GY"=>"592",
					"HT"=>"509",
					"HM"=>"672",
					"HN"=>"504",
					"HK"=>"852",
					"HU"=>"036",
					"IS"=>"354",
					"IN"=>"091",
					"ID"=>"062",
					"IR"=>"098",
					"IQ"=>"964",
					"IE"=>"353",
					"IL"=>"972",
					"IT"=>"039",
					"JM"=>"001",
					"JP"=>"081",
					"JE"=>"044",
					"JO"=>"962",
					"KZ"=>"007",
					"KE"=>"254",
					"KI"=>"686",
					"KW"=>"965",
					"KG"=>"996",
					"LA"=>"856",
					"LV"=>"371",
					"LB"=>"961",
					"LS"=>"266",
					"LR"=>"231",
					"LY"=>"218",
					"LI"=>"423",
					"LT"=>"370",
					"LU"=>"352",
					"MO"=>"853",
					"MK"=>"389",
					"MG"=>"261",
					"MW"=>"265",
					"MY"=>"060",
					"MV"=>"960",
					"ML"=>"223",
					"MT"=>"356",
					"MH"=>"692",
					"MQ"=>"596",
					"MR"=>"222",
					"MU"=>"230",
					"YT"=>"269",
					"MX"=>"052",
					"FM"=>"691",
					"MD"=>"373",
					"MC"=>"377",
					"MN"=>"976",
					"ME"=>"382",
					"MS"=>"001",
					"MA"=>"212",
					"MZ"=>"258",
					"MM"=>"095",
					"NA"=>"264",
					"NR"=>"674",
					"NP"=>"977",
					"AN"=>"599",
					"NL"=>"031",
					"NC"=>"687",
					"NZ"=>"064",
					"NI"=>"505",
					"NE"=>"227",
					"NG"=>"234",
					"NU"=>"683",
					"NF"=>"672",
					"KP"=>"850",
					"MP"=>"001",
					"NO"=>"047",
					"OM"=>"968",
					"PK"=>"092",
					"PW"=>"680",
					"PS"=>"970",
					"PA"=>"507",
					"PG"=>"675",
					"PY"=>"595",
					"PE"=>"051",
					"PH"=>"063",
					"PN"=>"064",
					"PL"=>"048",
					"PT"=>"351",
					"PR"=>"001",
					"QA"=>"974",
					"CG"=>"242",
					"RE"=>"262",
					"RO"=>"040",
					"RU"=>"007",
					"RW"=>"250",
					"BL"=>"590",
					"SH"=>"290",
					"KN"=>"001",
					"LC"=>"001",
					"MF"=>"590",
					"PM"=>"508",
					"VC"=>"001",
					"WS"=>"685",
					"SM"=>"378",
					"ST"=>"239",
					"SA"=>"966",
					"SN"=>"221",
					"RS"=>"381",
					"SC"=>"248",
					"SL"=>"232",
					"SG"=>"065",
					"SK"=>"421",
					"SI"=>"386",
					"SB"=>"677",
					"SO"=>"252",
					"ZA"=>"027",
					"GS"=>"000",
					"KR"=>"082",
					"ES"=>"034",
					"LK"=>"094",
					"SD"=>"249",
					"SR"=>"597",
					"SJ"=>"047",
					"SZ"=>"268",
					"SE"=>"046",
					"CH"=>"041",
					"SY"=>"963",
					"TW"=>"886",
					"TJ"=>"992",
					"TZ"=>"255",
					"TH"=>"066",
					"TL"=>"670",
					"TG"=>"228",
					"TK"=>"690",
					"TO"=>"676",
					"TT"=>"001",
					"TN"=>"216",
					"TR"=>"090",
					"TM"=>"993",
					"TC"=>"001",
					"TV"=>"688",
					"UG"=>"256",
					"UA"=>"380",
					"AE"=>"971",
					"GB"=>"044",
					"US"=>"001",
					"VI"=>"001",
					"UY"=>"598",
					"UZ"=>"998",
					"VU"=>"678",
					"VA"=>"379",
					"VE"=>"058",
					"VN"=>"084",
					"WF"=>"681",
					"EH"=>"212",
					"YE"=>"967",
					"ZM"=>"260",
					"ZW"=>"263",
					);
			
			/*******************************************/
			
			
            $cms_args = array(
                'toid' => $this->merchant_id,
                'partenerid' => $this->partenerid,
                'pctype' => "1_1|1_2",
                'ipaddr' => $this->ipaddr,
                'paymenttype' => $this->paymenttype,
                'cardtype' => $this->cardtype,
                'reservedField1' => $this->reservedField1,
                'reservedField2' => $this->reservedField2,
                'totype' => $this->title,
                'amount' => $order->order_total,
                //'order_id' => "TestOrder",
                'description' => $order_id,
                'orderdescription' => $order_id,
                'redirecturl1' => $this->notify_url,
                'billing_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                'TMPL_street' => trim($order->billing_address_1, ','),
                'billing_country' => wc()->countries->countries [$order->billing_country],
                'TMPL_state' => $order->billing_state,
                'TMPL_city' => $order->billing_city,
                'TMPL_zip' => $order->billing_postcode,
                'TMPL_telno' => $order->billing_phone,
                'TMPL_emailaddr' => $order->billing_email,
                'delivery_name' => $order->shipping_first_name . ' ' . $order->shipping_last_name,
                'delivery_address' => $order->shipping_address_1,
                'delivery_country' => $order->shipping_country,
                'delivery_state' => $order->shipping_state,
                'delivery_tel' => '',
                'delivery_city' => $order->shipping_city,
                'delivery_zip' => $order->shipping_postcode,
                'language' => get_locale(),
                'ipaddr' => $ip,
                'TMPL_COUNTRY' => $woocommerce->customer->get_country(),
                'TMPL_CURRENCY' => $CURRENCY,
                'currency' => $CURRENCY
            );

		   	/*echo $testchecksum;
		
			echo "<br>";
			echo "<br>";
			var_dump($cms_args);
			die();*/
			//die();
            //break;
            foreach ($cms_args as $param => $value) {
                $paramsJoined[] = "$param=$value";
            }
            $merchant_data = implode('&', $paramsJoined);
            if(phpversion() <= '7.0.10'){
				$encrypted_data = encrypt1($merchant_data, $this->working_key);
			} else{
                $encrypted_data = encrypt2($merchant_data, $this->working_key);
            }
            $cms_args_array = array();
            $cms_args_array[] = "<input type='hidden' name='encRequest' value='{$encrypted_data}'/>";
            //cms elements
            $cms_args_array[] = "<input type='hidden' name='toid' value='{$this->merchant_id}'/>";
            $cms_args_array[] = "<input type='hidden' name='partenerid' value='{$this->partenerid}'/>";
            $cms_args_array[] = "<input type='hidden' name='lang' value='{$this->language}'/>";
            $cms_args_array[] = "<input type='hidden' name='ipaddr' value='{$this->ipaddr}'/>";
            $cms_args_array[] = "<input type='hidden' name='paymenttype' value='{$this->paymenttype}'/>";
            $cms_args_array[] = "<input type='hidden' name='cardtype' value='{$this->cardtype}'/>";
            $cms_args_array[] = "<input type='hidden' name='reservedField1' value='{$this->reservedField1}'/>";
            $cms_args_array[] = "<input type='hidden' name='reservedField2' value='{$this->reservedField2}'/>";
            $cms_args_array[] = "<input type='hidden' name='totype' value='{$this->totype}'/>";
            $cms_args_array[] = "<input type='hidden' name='pctype' value='1_1|1_2'/>";
            $cms_args_array[] = "<input type='hidden' name='amount' value='{$order->order_total}'/>";
            $cms_args_array[] = "<input type='hidden' name='orderdescription' value='{$order_id}'/>";
            $cms_args_array[] = "<input type='hidden' name='description' value='{$order_id}'/>";
            $cms_args_array[] = "<input type='hidden' name='redirecturl' value='{$redirecturl}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_street' value='{$order->billing_address_1}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_state' value='{$order->billing_state}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_city' value='{$order->billing_city}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_zip' value='{$order->billing_postcode}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_telno' value='{$order->billing_phone}'/>";
			
			$country_value = $country_code[$country];
			$cms_args_array[] = "<input type='hidden' name='telnocc' value='{$country_value }'/>";
			
            $cms_args_array[] = "<input type='hidden' name='TMPL_emailaddr' value='{$order->billing_email}'/>";
            $cms_args_array[] = "<input type='hidden' name='language' value='{get_locale()}'/>";
            $cms_args_array[] = "<input type='hidden' name='ipaddr' value='{$ip}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_COUNTRY' value='{$country}'/>";
            $cms_args_array[] = "<input type='hidden' name='TMPL_CURRENCY' value='{$CURRENCY}'/>";
            $cms_args_array[] = "<input type='hidden' name='currency' value='{$CURRENCY}'/>";
            $cms_args_array[] = "<input type='hidden' name='checksum' value='{$checksum}'/>";
            //$cms_args_array[] = "<input type='hidden' name='terminalid' value='2055'/>";

            wc_enqueue_js('
    $.blockUI({
        message: "' . esc_js(__('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'woocommerce')) . '",
        baseZ: 99999,
        overlayCSS:
        {
            background: "#fff",
            opacity: 0.6
        },
        css: {
            padding:        "20px",
            zindex:         "9999999",
            textAlign:      "center",
            color:          "#555",
            border:         "3px solid #aaa",
            backgroundColor:"#fff",
            cursor:         "wait",
            lineHeight:     "24px",
        }
    });
jQuery("#submit_cms_payment_form").click();
');

            $form = '<form action="' . esc_url($this->url) . '" method="post" id="cms_payment_form" target="_top">
' . implode('', $cms_args_array) . '
<!-- Button Fallback -->
<div class="payment_buttons">
<input type="submit" class="button alt" id="submit_cms_payment_form" value="' . __('Pay via Payment WooCommerce', 'woocommerce') . '" /> <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', 'woocommerce') . '</a>
</div>
<script type="text/javascript">
jQuery(".payment_buttons").hide();
</script>
</form>';
            return $form;
        }

        // get all pages
        function get_pages($title = false, $indent = true) {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title)
                $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                // show indented child pages?
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while ($has_parent) {
                        $prefix .= ' - ';
                        $next_page = get_page($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                // add to page list array array
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }

    }

    /**
     * Add the Gateway to WooCommerce
     * */
    function woocommerce_add_cms_gateway($methods) {
        $methods[] = 'WC_cms';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_cms_gateway');
}

/*
  cms functions
 */

function encrypt1($plainText, $key) {
    $secretKey = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
    $blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
    $plainPad = pkcs5_pad($plainText, $blockSize);
    if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) {
        $encryptedText = mcrypt_generic($openMode, $plainPad);
        mcrypt_generic_deinit($openMode);
    }
    return bin2hex($encryptedText);
}

function decrypt1($encryptedText, $key) {
    $secretKey = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $encryptedText = hextobin($encryptedText);
    $openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
    mcrypt_generic_init($openMode, $secretKey, $initVector);
    $decryptedText = mdecrypt_generic($openMode, $encryptedText);
    $decryptedText = rtrim($decryptedText, "\0");
    mcrypt_generic_deinit($openMode);
    return $decryptedText;
}

function encrypt2($plainText, $key) {
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = openssl_random_pseudo_bytes($ivlen);
$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
$ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
}

function decrypt2($encryptedText, $key) {
//decrypt later....
$c = base64_decode($ciphertext);
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = substr($c, 0, $ivlen);
$hmac = substr($c, $ivlen, $sha2len=32);
$ciphertext_raw = substr($c, $ivlen+$sha2len);
$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
}




//*********** Padding Function *********************

function pkcs5_pad($plainText, $blockSize) {
    $pad = $blockSize - (strlen($plainText) % $blockSize);
    return $plainText . str_repeat(chr($pad), $pad);
}

//********** Hexadecimal to Binary function for php 4.0 version ********

function hextobin($hexString) {
    $length = strlen($hexString);
    $binString = "";
    $count = 0;
    while ($count < $length) {
        $subString = substr($hexString, $count, 2);
        $packedString = pack("H*", $subString);
        if ($count == 0) {
            $binString = $packedString;
        } else {
            $binString .= $packedString;
        }

        $count += 2;
    }
    return $binString;
}

function cms_debug($what) {
    echo '<pre>';
    print_r($what);
    echo '</pre>';
}

/**
  ------------------------------------------------------- Insert Table Starts Here ----------------------------------------------
 * */
function create_cms_table() {
    global $wpdb;
    //print_r($wpdb);
    $table_name = $wpdb->prefix . "cms_tbl";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(11) NOT NULL AUTO_INCREMENT,
        woocommerce_id varchar(255) NOT NULL,
        order_id varchar(255) NOT NULL,
        order_status varchar(255) NOT NULL,
        tracking_id varchar(255) DEFAULT '' NOT NULL,
        time timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY id (id)
        ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_cms_table');
/**
  ------------------------------------------------------- Insert Table Ends Here ----------------------------------------------
 * */
/**
  -------------------------------------------------------- Reconcilation Button starts here ---------------------------------------------
 * */
add_action('admin_menu', 'my_plugin_settings');

function my_plugin_settings() {
    add_menu_page('cms', 'Reconciliation', 'administrator', 'insert-my-meta', 'my_plugin_settings_page', 'dashicons-filter', '54');
}

function my_plugin_settings_page() {
    echo '<div class="container">';
    echo '<br>';
   // echo '<div class="page-header">';
    //echo '<div class="well">';
    //echo '<h3 class="text-center" style="color:#0073aa;">Recon Page</h3>';
  
    //echo '</div>';
   // echo '</div>';
    echo '</div>';
    global $wpdb;
    /*  Paging starts here  */
    $pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;
    $limit = 15; // number of rows in page
    $offset = ( $pagenum - 1 ) * $limit;
    $total = $wpdb->get_var("SELECT COUNT(`id`) FROM wp_cms_tbl where order_status = 'authstarted' " );
    $num_of_pages = ceil($total / $limit);

    /*  Paging ends here  */
    $fivesdrafts = $wpdb->get_results("SELECT * FROM wp_cms_tbl WHERE order_status = 'authstarted' LIMIT $offset,$limit");
    if ($fivesdrafts) {
        
            ?>
            <?php
             echo "<p style='color:red;'>";
   if(isset($f_msg))
   {
       echo $f_msg;
   }
   echo "</p>";
   
   echo "<p style='color:green;'>";
   if(isset($s_msg))
   {
       echo $s_msg;
   }
   echo "</p>";
               echo "<br>";
            echo "<div class='container'>";
            echo "<form action='' method='post'>";
            echo "<div class='table-responsive'>";
            echo "<div class='panel panel-default'>";
            echo "<table class='table table-condensed table-hover table-bordered'>";
            echo "<tr  style='background-color:#f1f1f1;'>";
            echo "<th style='color:#0073aa;'> </th>";
            echo "<th style='color:#0073aa;' class='text-center'>Order Number</th>";
            echo "<th style='color:#0073aa;' class='text-center'>Order Description</th>";
            echo "<th style='color:#0073aa;' class='text-center'>Order Title</th>";
            echo "<th style='color:#0073aa;' class='text-center'>Tracking Id</th>";
            echo "<th style='color:#0073aa;' class='text-center'>Order Status</th>";
            echo "</tr>";
            foreach ($fivesdrafts as $post) {
            echo "<tr style='color:#0073aa;'>";
            echo "<td class='text-center'> <input id='checkbox' type='checkbox' name='id[]' value='" . $post->woocommerce_id . "' class='checkbox checkbox-primary'> </td>";
            echo "<td class='text-center'>$post->woocommerce_id</td>";
            echo "<td class='text-center'>$post->order_id</td>";
            echo "<td class='text-center'>$post->time</td>";
             if(!empty($post->tracking_id))
             {
            echo "<td class='text-center'>$post->tracking_id</td>";
             } else{
                 echo "<td class='text-center'>-</td>";
             }
             
            echo "<td class='text-center'>$post->order_status</td>";
            echo "</tr>";
                    }
            echo "</table>";
            echo "</div>"; /* rounded div ends */
            echo "</div>";
            echo "</div>";
            ?>
            <!-- Add the pagination functions here. -->
            <?php
        /* Paging starts here */
        $page_links = paginate_links(array(
            'base' => add_query_arg('pagenum', '%#%'),
            'format' => '',
            'prev_text' => __( '&laquo;', 'text-domain' ),
            'next_text' => __( '&raquo;', 'text-domain' ),
            'total' => $num_of_pages,
            'current' => $pagenum
            ));

        if ($page_links) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
        /* Paging ends here */
        echo "<div class='container'>";
        echo "<td><input type='submit' value='Recon' name='recon'.$post->woocommerce_id class='btn btn-primary' > </td>";
        echo "</form>";
        echo "</div>";
    } else {
        ?>
        <div class="alert alert-danger">
            <strong>Sorry!</strong> No Records Found.
        </div>
        <?php
    }
    /**
      --------------------------------- Update Query Starts Here ------------------------------------
     * */
    //if (isset($_POST['update'])) 
    if (isset($_POST["recon"]) == "Recon") {
        $pg = new WC_cms();
        $ids = $_POST["id"];
        foreach ($ids as $id) {
            global $wpdb;
            $wp_cms_tbl_var = $wpdb->get_row("SELECT order_id,tracking_id FROM wp_cms_tbl where woocommerce_id=".$id);
            if (!empty($wp_cms_tbl_var)) {
                $description = $id."-".$pg->merchant_id;
                $trackingid =null;
                $str = $pg->merchant_id . "|" . $pg->working_key. "|" . $id."-".$pg->merchant_id ;
			    $checksum = md5($str);
                $request = "toid=" . $pg->merchant_id . "&trackingid=" . $trackingid . "&description=" . $description . "&checksum=" . $checksum;
                $ch = curl_init();
                $url= $pg->queryurl."/".$id."-".$pg->merchant_id;
				
			$data = "authentication.memberId=$pg->merchant_id" .
			"&authentication.checksum=$checksum" .
			"&paymentType=IN" .
			"&idType=MID";

			$ch = curl_init();
		     curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array());
			$result = curl_exec($ch);
			
	      $json = json_decode($result);

          $update_cms_tbl = $wpdb->update('wp_cms_tbl', array('order_status' => trim($json ->status)), array('woocommerce_id' => $id));
                $woo_commerce_status = "";
                if ($json ->status == 'capturesuccess' || $json ->status == 'authsuccessful') {
                    $woo_commerce_status = "wc-processing";
                } else if ($json ->status == 'authstarted' || $json ->status == 'begun') {
                    $woo_commerce_status = "wc-pending";
                } else {
                    $woo_commerce_status = "wc-failed";
                }
                $wpdb->update('wp_posts', array('post_status' => $woo_commerce_status), array('ID' => $id));
				
				 if ($update_cms_tbl) {
                    echo "<p style='color:green;'>Updated Successfully!!!</p>";
                } else {
                    echo "<p style='color:red;'>Error: Record(s) Not Updated!!!</p>";
                }
            }
        }
    }
	
	
	
	
	
    /**
      --------------------------------- Update Query Ends Here ------------------------------------
     * */
// echo "<pre>";
// print_r($_POST["id"]);

    //echo "<pre>";
   // print_r($id_cms);

    //echo "<pre>";
   // print_r($update_cms);

// echo "<br>";
// echo "<br>";
// echo "<pre>";
// print_r($wpdb->query);
}



/** Custom Code **/

add_filter('woocommerce_thankyou_order_received_text', 'woo_change_order_received_text', 10, 2 );
function woo_change_order_received_text( $str, $order ) {
	
	
	
	$json = json_decode($order, true);
	
   // $new_str = $str . ' We have emailed the purchase receipt to you. success'. "This is json---- ".$order;
	
	 if($json['status'] == 'completed')
	 {
		// print_r($order);
     $new_str ='<div style="padding: 5px; background-color: #4CAF50; color: white;"><strong> Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon. </strong> </div>';
	 }
	 /* else if($json['status'] == 'failed')
	{
		$new_str = $str . 'Thank you for shopping with us. However, the transaction has been Failed.';
	 } 
	 else if($json['status'] == 'pending')
	 {
		 $new_str = $str . 'Thank you for shopping with us. However, the transaction is in pending process.';
	 } */
	 else{
		 $new_str = '<div style="padding: 5px; background-color: #ff9800; color: white;"><strong>Thank you for shopping with us. However, the transaction has been cancelled. </strong></div>';
	 } 
	
	
    return $new_str;
}



/**
  -------------------------------------------------------- My meta data ends here ---------------------------------------------
 * */
/**
  ---------------------------------- bootstrap starts here --------------------------------------------
 * */
wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
wp_enqueue_script('prefix_bootstrap');


$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if (is_admin() && ( $actual_link == get_site_url() . '/wp-admin/admin.php?page=insert-my-meta' )) {
	// CSS
	wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
	wp_enqueue_style('prefix_bootstrap');
}
/**
  ------------------------------------- bootstrap ends here ------------------------------------------------
 * */
?>
