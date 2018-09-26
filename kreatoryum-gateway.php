<?php
/*
* Plugin Name: WooCommerce Kreatoryum KuveytTurk Gateway
* Plugin URI: http://kreatoryum.com.tr
* Description: Kuveyt Turk Free POS 3D Secure Modülü.
* Author: Can Berk Öcalır
* Author URI: http://canocalir.com
* Version: 1.0.1
* License: GPL version 3 or later - http://www.gnu.org/licenses/quick-guide-gplv3.html
*
*/

function add_krea_kvyt_commerce_gateway( $methods ) {
  $methods[] = 'wc_krea_kvyt';
  return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'add_krea_kvyt_commerce_gateway' );

add_action( 'plugins_loaded', 'woocommerce_kuveyt', 0 );

function woocommerce_kuveyt() {

  if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    return;
  };

  //Gateway DIR
  define('KREAPLGDIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
  /*
  * Krea Gateway Class
  */
  class WC_krea_kvyt extends WC_Payment_Gateway {

    function __construct() {

      // Register plugin information
      $this->id			    = 'krea_kvyt';
      $this->has_fields = true;
      $this->supports   = array(
        'products',
        'subscriptions',
        'subscription_cancellation',
        'subscription_suspension',
        'subscription_reactivation',
        'subscription_date_changes',
      );

      // Create plugin fields and settings
      $this->init_form_fields();
      $this->init_settings();

      // Get setting values
      foreach ( $this->settings as $key => $val ) $this->$key = $val;

      // Load plugin checkout ikon
      $this->icon = KREAPLGDIR . 'images/cards.png';

      // Add hooks
      add_action('woocommerce_before_my_account',array( $this, 'add_payment_method_options' ) );
      add_action('woocommerce_receipt_krea', array( $this, 'receipt_page' ) );
      add_action('woocommerce_update_options_payment_gateways',array( $this, 'process_admin_options' ) );
      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      add_action('wp_enqueue_scripts',array( $this, 'add_krea_scripts' ) );
      add_action('wc_krea_control', array( $this, 'wc_krea_control' ) );
      add_action('woocommerce_api_wc_krea_kvyt', array( $this, 'krea_return_control' ) );


    }
    function wc_krea_control($post)
    {
      global $woocommerce;
      $onemli = explode('SIPARIS',$post['MerchantOrderId']);
      $order = new WC_Order($onemli[1]);



      if(($post['ProvisionNumber']) and ($post['ResponseCode']=='00'))
      {


        $order->add_order_note( __( 'Banka Sipariş Numarası ' , 'woocommerce' ) . $oidd);
        $order->payment_complete();


        wp_redirect($this->get_return_url( $order ));

      }
      else
      {

        $hata = $post['ResponseMessage'];
        $order->add_order_note( __( 'Hata Kodu: '.$hata, 'woocommerce' ) . '');
        wp_redirect(add_query_arg('order', $order->id, add_query_arg('hata_mesaji',$hata, get_permalink(woocommerce_get_page_id('checkout' )))));
      }

    }

    function krea_return_control(){

      global $woocommerce;

      @ob_clean();

      if ( ! empty( $_POST )) {

        header( 'HTTP/1.1 200 OK' );

        do_action( "wc_krea_control", $_POST );

      }
      else
      {
        wp_die( "Bu link direk kullanılamaz" );
      }
    }

    /**
    * Initialize Gateway Settings Form Fields.
    */
    function init_form_fields() {
      //Api Kullanıcı Adı  Mağaza ID   API Şifre  Terminal ID

      $this->form_fields = array(
        'enabled'     => array(
          'title'       => __( 'Aktif/Pasif', 'woothemes' ),
          'label'       => __( 'Kreatoryum Kuveyt Türk Sanal Pos', 'woothemes' ),
          'type'        => 'checkbox',
          'description' => '',
          'default'     => 'no'
        ),
        'title'       => array(
          'title'       => __( 'Title', 'woothemes' ),
          'type'        => 'text',
          'description' => __( 'Ödeme sayfasında gözüken başlık', 'woothemes' ),
          'default'     => __( 'Kredi Kartıyla Ödeme (EST)', 'woothemes' )
        ),
        'description' => array(
          'title'       => __( 'Açıklama', 'woothemes' ),
          'type'        => 'textarea',
          'description' => __( 'Ödeme sayfasında gözüken açıklama', 'woothemes' ),
          'default'     => '3D Secure ile Güvenle Ödeme Yapın'
        ),

        'magaza_id'    => array(
          'title'       => __( 'Mağaza ID ', 'woothemes' ),
          'type'        => 'text',
          'description' => __( 'Bankadan Aldığınız Mağaza ID', 'woothemes' ),
          'default'     => ''
        ),
        'api_kullanici'    => array(
          'title'       => __( 'api_kullanici ID ', 'woothemes' ),
          'type'        => 'text',

          'default'     => ''
        ),
        'api_sifre'    => array(
          'title'       => __( 'api_sifre ID ', 'woothemes' ),
          'type'        => 'text',

          'default'     => ''
        ),

        'musteri_id'    => array(
          'title'       => __( 'Müşteri ID ', 'woothemes' ),
          'type'        => 'text',
          'description' => __( 'Bankadan Aldığınız Müşteri ID', 'woothemes' ),
          'default'     => ''
        ),

        'redirect_page_id' => array(

          'title' 		=> __('Dönüş Sayfası'),

          'type' 			=> 'select',

          'options' 		=> $this->get_pages('Select Page'),

          'description' 	=> __('Ödeme/KASA Sayfası Seçili Olmalı', 'kdc'),

          'desc_tip' 		=> true

        ),
      );
    }


    /**
    * UI - Admin Panel Options
    */
    function admin_options() {


      ?>
      <h3><?php _e( 'Krea E-commerce Kuveyt Türk','woothemes' ); ?></h3>
      <p><?php _e( 'Kuveyt Türk Sanal Pos Ayar Sayfası', 'woothemes' ); ?></p>
      <table class="form-table">
        <?php $this->generate_settings_html(); ?>
      </table>
    <?php }
    /**
    * UI - Payment page fields
    */
    function payment_fields() {
      global $woocommerce;
      $APIAS = $woocommerce->cart;

      // Description of payment method from settings
      if ( $this->description ) { ?>
        <p><?php echo $this->description; ?></p>
      <?php }




      if($_GET['hata_mesaji'])
      {


        echo "
        <script type='text/javascript'>alert('Ödeme alınamadı HATA MESAJI:".htmlentities($_GET['hata_mesaji'])."');</script>

        ".'<div class="error"><p>'.__('HATA KODU: '.htmlentities($_GET['hata_mesaji']).'
        <br />
        Kartınızdan ücret çekimi gerçekleşmemiştir. Lütfen işleminizi tekrarlayınız.
        ', 'woothemes').'</p></div>';
      }
      ?>



      <!-- Show input boxes for new data -->
      <div id="krea-new-info">

        <!-- Credit card number -->
        <p class="form-row form-row-first">
          <label for="ccisim"><?php echo __( 'Kart Üzerindeki İsim-Soyisim', 'woocommerce' ) ?> <span class="required">*</span></label>
          <input type="text" class="input-text" id="ccisim" value="" name="ccisim" maxlength="22" />
        </p>


        <p class="form-row form-row-first">
          <label for="ccnum"><?php echo __( 'Kredi Kartı Numarası', 'woocommerce' ) ?> <span class="required">*</span></label>
          <input type="text" class="input-text" id="ccnum" value="" name="ccnum" maxlength="16" />
        </p>

        <div class="clear"></div>
        <!-- Credit card expiration -->
        <p class="form-row form-row-first">
          <label for="cc-expire-month"><?php echo __( 'Son Kullanma Tarihi', 'woocommerce') ?> <span class="required">*</span></label>
          <div class="clear"></div>
          <select name="expmonth" id="expmonth" style="float:left;width:50px;" class="woocommerce-select woocommerce-cc-month">
            <option value=""><?php _e( 'Ay', 'woocommerce' ) ?></option><?php
            $months = array();
            for ( $i = 1; $i <= 12; $i ++ ) {
              $timestamp = mktime( 0, 0, 0, $i, 1 );
              $months[ date( 'n', $timestamp ) ] = date( 'n', $timestamp );
            }
            foreach ( $months as $num => $name ) {
              printf( '<option value="%u">%s</option>', $num, $name );
            } ?>
          </select>
          <select name="expyear" id="expyear"  style="float:left; margin-left:5px;  width:80px;" class="woocommerce-select woocommerce-cc-year">
            <option value=""><?php _e( 'Yıl', 'woocommerce' ) ?></option><?php
            $years = array();
            for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i ++ ) {
              printf( '<option value="20%u">20%u</option>', $i, $i );
            } ?>
          </select>

          <div style="clear:both;"></div>
          <div class="clear"></div>

          <label style="position:relative; top:5px;" for="cvv"><?php _e( 'CCV', 'woocommerce' ) ?> <span class="required">*</span></label>
          <input oninput="validate_cvv(this.value)" type="text" class="input-text" id="cvv" name="cvv" maxlength="4" style="width:45px; position:relative; top:5px;" />
          <div class="clear"></div>
          <span class="help" style="position:relative; top:10px;"><?php _e( 'Kredi Kartının Arkasındaki 3 haneli kod.', 'woocommerce' ) ?></span>


        </p>
        <p>
          <input type="hidden" name="taksit" value="1" />

        </p>

        <div class="clear"></div>
        <?php

      }

      /**
      * Process the payment and return the result.
      */
      function oran_ekleme($fiyat,$oran){
        return  ($fiyat +(($fiyat * $oran) / 100));
      }

      function get_pages($title = false, $indent = true) {

        $wp_pages = get_pages('sort_column=menu_order');

        $page_list = array();

        if ($title) $page_list[] = $title;

        foreach ($wp_pages as $page) {

          $prefix = '';

          // show indented child pages?

          if ($indent) {

            $has_parent = $page->post_parent;

            while($has_parent) {

              $prefix .=  ' - ';

              $next_page = get_page($has_parent);

              $has_parent = $next_page->post_parent;

            }

          }

          // add to page list array array

          $page_list[$page->ID] = $prefix . $page->post_title;

        }

        return $page_list;

      }

      function process_payment( $order_id ) {

        global $woocommerce;

        $order = new WC_Order( $order_id );

        // Convert CC expiration date from (M)M-YYYY to MMYY
        $expmonth = $this->get_post( 'expmonth' );
        if ( $expmonth < 10 ) $expmonth = '0' . $expmonth;
        if ( $this->get_post( 'expyear' ) != null ) $expyear = substr( $this->get_post( 'expyear' ), -2 );
        $redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url() . "/":get_permalink($this->redirect_page_id);
        $redirect_url = add_query_arg( 'wc-api', 'wc_krea_kvyt', $redirect_url );
        $Name				= $this->get_post('ccisim');
        $CardNumber			= $this->get_post('ccnum');
        $CardExpireDateMonth= $expmonth;
        $CardExpireDateYear	= $expyear;
        $CardCVV2			= $this->get_post('cvv');
        $APIVersion = "1.0.1";
        $Type 		= "Sale";
        $amount 	= str_replace('.', '', number_format($order->order_total, 2, '',''));
        $CurrencyCode = "0949"; //TL
        $MerchantOrderId = date('his').$this->magaza_id.'SIPARIS'.$order->id;
        $CustomerId = $this->musteri_id;//Müsteri Numarasi
        $MerchantId = $this->magaza_id; //Magaza Kodu
        $OkUrl 		=  str_replace('http://','https://',$redirect_url);
        $FailUrl 	= str_replace('http://','https://',$redirect_url);
        $UserName	= $this->api_kullanici; // kullanici api_kullanici / api_sifre
        $Password	= $this->api_sifre;
        $HashedPassword = base64_encode(sha1($Password,"ISO-8859-9")); //md5($Password);
        $HashData 	= base64_encode(sha1($MerchantId.$MerchantOrderId.$amount.$OkUrl.$FailUrl.$UserName.$HashedPassword , "ISO-8859-9"));
        $TransactionSecurity=3;
        $kartno 	= str_replace('-','',str_replace(' ','',$this->get_post( 'ccnum' )));
        switch($kartno[0])
        {
          case '4':
          $kart_tipi = 'Visa';
          break;
          case '5':
          $kart_tipi = 'MasterCard';
          break;
        }

        $sendxml =  array(
          'APIVersion'=>'1.0.0',
          'OkUrl'=>$OkUrl,
          'FailUrl'=>$FailUrl,
          'HashData'=>$HashData,
          'MerchantId'=>$MerchantId,
          'CustomerId'=>$CustomerId,
          'UserName'=>$UserName,
          'CardNumber'=>$CardNumber,
          'CardExpireDateYear'=>$CardExpireDateYear,
          'CardExpireDateMonth'=>$CardExpireDateMonth,
          'CardCVV2'=>$CardCVV2,
          'CardHolderName'=>$Name,
          'CardType'=>$kart_tipi,
          'BatchID'=>'0',
          'TransactionType'=>$Type,
          'InstallmentCount'=>'0',
          'Amount'=>$amount,
          'DisplayAmount'=>$amount,
          'CurrencyCode'=>$CurrencyCode,
          'MerchantOrderId'=>$MerchantOrderId,
          'TransactionSecurity'=>$TransactionSecurity);
          $_SESSION['sendxml'] = $sendxml;

          return array(
            'result' 	=> 'success',
            'redirect'	=>  KREAPLGDIR.'gateway-kuveyt.php'
          );
        }

        function validate_fields() {


          global $woocommerce;




          $cardNumber          = $this->get_post( 'ccnum' );
          $cardCSC             = $this->get_post( 'cvv' );
          $cardExpirationMonth = $this->get_post( 'expmonth' );
          $cardExpirationYear  = $this->get_post( 'expyear' );
          // Check card number
          if ( empty( $cardNumber ) || ! ctype_digit( $cardNumber ) ) {
            $woocommerce->add_error( __( 'GİRİLEN KREDİ KARTI NUMARASI YANLIŞ', 'woocommerce' ) );
            return false;
          }

          // Check security code
          if ( ! ctype_digit( $cardCSC ) ) {
            $woocommerce->add_error( __( 'GİRİLEN CCV NO HATALIDIR.', 'woocommerce' ) );
            return false;
          }

          // Check expiration data
          $currentYear = date( 'Y' );

          if ( ! ctype_digit( $cardExpirationMonth ) || ! ctype_digit( $cardExpirationYear ) ||
          $cardExpirationMonth > 12 ||
          $cardExpirationMonth < 1 ||
          $cardExpirationYear < $currentYear ||
          $cardExpirationYear > $currentYear + 20
        ) {
          $woocommerce->add_error( __( 'KARTIN SON KUL. TARİHİ HATALIDIR', 'woocommerce' ) );
          return false;
        }

        // Strip spaces and dashes
        $cardNumber = str_replace( array( ' ', '-' ), '', $cardNumber );

        return true;

      }

      /**
      * Send the payment data to the gateway server and return the response.
      */



      function receipt_page( $order ) {
        echo '<p>' . __( 'Siparişini için teşekkür eder yine bekleriz.', 'woocommerce' ) . '</p>';
      }

      /**
      * Include jQuery and our scripts
      */
      function add_krea_scripts() {


        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'edit_billing_details', KREAPLGDIR . 'js/edit_billing_details.js', array( 'jquery' ), 1.0 );
        wp_enqueue_script( 'check_cvv', KREAPLGDIR . 'js/check_cvv.js', array( 'jquery' ), 1.0 );

      }

      /**
      * Get the current user's login name
      */
      private function get_user_login() {
        global $user_login;
        get_currentuserinfo();
        return $user_login;
      }

      /**
      * Get post data if set
      */
      private function get_post( $name ) {
        if ( isset( $_POST[ $name ] ) ) {
          return $_POST[ $name ];
        }
        return null;
      }

      /**
      * Check whether an order is a subscription
      */
      private function is_subscription( $order ) {
        return class_exists( 'WC_Subscriptions_Order' ) && WC_Subscriptions_Order::order_contains_subscription( $order );
      }

      /**
      * Generate a string of 36 alphanumeric characters to associate with each saved billing method.
      */
      function random_key() {

        $valid_chars = array( 'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9' );
        $key = '';
        for( $i = 0; $i < 36; $i ++ ) {
          $key .= $valid_chars[ mt_rand( 0, 61 ) ];
        }
        return $key;

      }

    }


  }
