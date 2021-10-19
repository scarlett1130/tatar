<?php

####################################################
##   s@smartservs.com     &&   BASEL WAEL    ##
##   admin@smartservs.com    &&   Wael Seif  ##
##   jokar@smartservs.com    &&   mohamed joker   ##
##   skype : SmartServs &&   www.smartservs.com           ##
####################################################
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );

require('./ftd-core/smartservs-conf-ftd/s1.php');
 
$packages = $AppConfig['plus']['packages'];




function getRate() {
  $req_url = 'https://api.exchangerate-api.com/v4/latest/USD';
            $response_json = file_get_contents($req_url);
		
            // Continuing if we got a result
            if(false !== $response_json) {

                // Try/catch for json_decode operation
                try {

                // Decoding
                $response_object = json_decode($response_json);
	
                 
                return $response_object->rates->SAR;
                 
                }
                catch(Exception $e) {
                  
                  return 0;
                }
            }
}

function vc_variable( $parms=array() ,  $variable = false) {
            if ( isset( $parms[$variable] ) ) {
                return ($parms[$variable]);
            }
        }

 
        
        function vc_cookie( $variable = false) {
            if ( isset( $_COOKIE[$variable] ) ) {
                return ($_COOKIE[$variable]);
            }
        }

function vc_object( $object=false , $key='null' ) {
        
            if( is_object( $object ) ) {
                if( property_exists( $object , $key) ) {
                    return $object->{ $key };
                }
            }else if( is_array( $object )) {
                if( isset( $object[$key]) ) {
                    return $object[$key];
                }
            }
        }
        
        

$Rate = getRate();

#$USD_price = round(($base_price * $response_object->rates->SAR), 4);

foreach($packages as $key => $orderData) {
  	if((int) vc_variable($orderData , 'goldplus') > 0 ) {
            $orderData['gold'] = vc_variable($orderData , 'goldplus');
        }
        
        if((int) vc_variable($orderData , 'costplus') > 0 ) {
            $orderData['cost'] = vc_variable($orderData , 'costplus');
        }
  
 
  
      if(strtolower(vc_variable($orderData , 'currency')) == 'usd' ) {
			 
        $orderData['cost'] = round(($orderData['cost'] * $Rate), 4);
        
         
        
      }
  
  $packages[$key] = $orderData;
}





 class httpClient
{
   
   public $db;
   
   
   public function getKey( )
            {
                return md5( $this->getdomain( ) );
            }
   
   
   
   public function getInstance( )
            {
                 
     			
                $key = $this->getkey( );
     
                $list = vc_cookie($key);
                if(!$list) return;
                $list = base64_decode($list);
                $list = unserialize($list);
                $data = json_decode(json_encode($list));
                if(!is_object($data)) return;
                $username = $data->uname;
                $jsn = json_decode('{}');
      
                $userData = $this->getPlayerData($username);
                  
                foreach ($userData as $key => $value) {
                    $jsn->{$key} = $value;
                }
                
                
                if(!vc_object($jsn , 'playerId')) {
                  return;
                }
                
   
               
                return $jsn;
            }
            
            public function is_logged( )
            {
                if($this->getInstance()) {
                    return true;
                }
            }
   
   
   public function getPlayerData( $userID=0)
            {
     
                $db = $this->connectSQL();
                
                $sQl = $db->query("SELECT * FROM p_players WHERE id='{$userID}' OR name='{$userID}' LIMIT 1 ");
                $result = [];
                while ($row = $sQl->fetch_assoc()) {
                    $row['prevPlayerId'] =0;
                    $row['playerId'] = $row['id'];
                    $row['isAgent'] = 0;
                    $row['isSpy'] = '';
                    $row['gameStatus'] = 0;
                    $row['actions'] = '';
                    $result = $row;
                }
        		
      
                return $result;
            }
   
   
   
   public function connectSQL()
            {
                global $AppConfig;
        
                $host = $AppConfig['db']['host'];
                $dbUser = $AppConfig['db']['user'];
                $dbPassword = $AppConfig['db']['password'];
                $database = $AppConfig['db']['database'];
        
                $this->db = new mysqli(
                    $host,
                    $dbUser,
                    $dbPassword,
                    $database
                );
        
                return $this->db;
            }
        
            public function disconnectSQL( )
            {
                $this->db->close();
            }
   
   
    public function execute($ch,  $closeAfterDone = true)
    {
        $curlResponse = curl_exec($ch);
        if ($curlResponse === false) {
            $curlErrNo = curl_errno($ch);
            $curlError = curl_error($ch);
            if ($closeAfterDone) {
                curl_close($ch);
            }
            print_r(sprintf('Curl error (code %d): %s', $curlErrNo, $curlError));
            throw new \RuntimeException(sprintf('Curl error (code %d): %s', $curlErrNo, $curlError));
        }
        if ($closeAfterDone) {
            curl_close($ch);
        }
        return $curlResponse;
    }

    public function login()
    {
        global $AppConfig;
      $payLink = $AppConfig['paylink'];
        $postData = [
            'persistToken' => false,
            'apiId' => $AppConfig['paylink']['apiId'],
            'secretKey' => $AppConfig['paylink']['secretKey']
        ];
        $json = $this->postRequest($postData, 'https://restapi.paylink.sa/api/auth');
        return $json->id_token;
    }

    public function getRequest($url, $token = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); // the endpoint in paylink to generate the token.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($token) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                'Authorization: Bearer ' . $token
            ]);
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
            ]);
        }
        return json_decode($this->execute($curl));
    }
  
  public function getDomain( )
            {
                $surl = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $surl = preg_replace( "/^(www\\.)/", "", $surl );
                $arr = explode( "/", $surl );
                $count = sizeof( $arr ) - 1;
                if ( 0 < $count )
                {
                    $surl = "";
                    $i = 0;
                    while ( $i < $count )
                    {
                        $surl .= $arr[$i]."/";
                        ++$i;
                    }
                }
                return strtolower( rtrim($surl , '/') ).'/';
            }
  
  
   public function getUrl( )
            {
                return $_SERVER['REQUEST_SCHEME'].'://' .  $this->getdomain( ) ;
            }

    /**
     * @param $postData
     * @param $url
     * @return mixed
     */
    public function postRequest($postData, $url)
    {
        $postString = json_encode($postData);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $url); // the endpoint in paylink to generate the token.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postString);
        return json_decode($this->execute($curl));
    }
   
    public function system_log_attemps( $csrf_attack ) {
                $this->has_csrf_session();
                if( empty( $_SESSION[$csrf_attack] )) {
                    $_SESSION[$csrf_attack] = 1;
                } else {
                    $_SESSION[$csrf_attack] += 1;
                }
                return $_SESSION[$csrf_attack];
            }
        
            public function csrf_destroy( $csrf_token=false ) {
                $this->has_csrf_session();
                if( !empty( $_SESSION['csrf_token'] )) {
                    $_SESSION['csrf_token'] = false;
                }
            }
        
            public function verify_csrf_token( $csrf_token='null' ) {
                $this->has_csrf_session();
                return ( (string) $_SESSION['csrf_token'] ==  (string) $csrf_token );
            }
        
            public function has_csrf_session() {
                if(!isset($_SESSION) ) {
                    session_start();
                }
            }
        
            public function generate_csrf() {
                $this->has_csrf_session();
                if (empty($_SESSION['csrf_token'])) {
                    if (function_exists('mcrypt_create_iv')) {
                        $_SESSION['csrf_token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
                    } else {
                        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
                    }
                }
                return  $_SESSION['csrf_token'];
            }
        
        
            public function generate_token() {
                $this->has_csrf_session();
                $_SESSION['payment_token'] = generate_random(64);   
                return  $_SESSION['payment_token'];
            }
        
        
        
        
            public function generate_hash($len=32)
            {
                if (function_exists('mcrypt_create_iv')) {
                    return bin2hex(mcrypt_create_iv($len, MCRYPT_DEV_URANDOM));
                } else {
                    return bin2hex(openssl_random_pseudo_bytes($len));
                }
            }
        
            public function generate_random($len=32)
            {
                return (
                    base64_encode(
                        md5(
                            sha1(
                                md5(
                                    sha1(
                                        $this->generate_hash($len)
                                    )
                                )
                            )
                        )
                    )
                );
            }
        
            public function pwd_hash($pwd=false)
            {
                return (
                    md5(
                        sha1(
                            md5(
                                sha1(
                                    $pwd
                                )
                            )
                        )
                    )
                );
            }
   
   
   public function generateToken()
            {
                $this->csrf_destroy();
                return $this->generate_csrf();
            }
            
            public function getToken()
            {
                return $this->generate_csrf();
            }
            
            public function clearToken()
            {
                $this->csrf_destroy();
            }
   
}

class GPage extends PopupPage

{



    public $requestPaymentProvider = FALSE;

    public $providerType = "";

    public $package = NULL;

    public $payment = NULL;

    public $secureId = NULL;

    public $Domain = NULL;

        public $gold = NULL;



    public function GPage()

    {

        parent::popuppage();
$this->contentCssClass = 'payment';

        $this->viewFile = "payment.phtml";

    }



    public function load()

    {
	global $packages;
        parent::load();
		
        $this->Domain = webhelper::getbaseurl();

        if ( isset( $_GET['p'], $_GET['pg'] ) )

        {

            $this->providerType = trim( $_GET['p'] );

            $this->packageIndex = trim( $_GET['pg'] );

            if ( isset( $this->appConfig['plus']['payments'][$this->providerType], $this->appConfig['plus']['packages'][$this->packageIndex] ) )

            {

                if( $this->providerType == 'paylink' || $this->providerType == 'apple_pay') {

                    
	

$HttpClient = new HttpClient();
 
$token = $HttpClient->login();

  $userData = $HttpClient->getInstance();

                    ?>
<script defer    src="https://paylink.sa/assets/js/paylink.js"></script>
<style>
        html, body
        {
            font-size: inherit !important;
            zoom: normal !important;
            
        }

        .messagebox , #container {
            display: none  !important;
        }

        body.manual
        {
            zoom: normal !important;
        }
    </style>
<script type="text/javascript">
  
  window.addEventListener('load' , function(){
    
    window.packages = <?= json_encode($packages); ?>;
    
    

    function successCallback() {
        console.log('success');
    }
    
    function __PayNow(type , index) {
     
      	var package = packages[index];
       console.log(package);
      if(type == 'apple') {
        	applePayNow(index  , package);
      } else {
        payNow(index , package);
      }
      
       
    }

    function payNow(id , package ) {
        // 3) Send the the generated token value to client side.
        const token = '<?= $token ?>';
      
      if(package['cost'] < 5 ){
         alert('الحد الادنى للشحن 5 ريال سعودي');
            setTimeout(() => {
                window.close();
            }, 1000);
        return;
      }

        // 4) In the client side create the order details for the buyer.
        let order = new Order({
            callBackUrl: '<?= $HttpClient->getUrl() ?>vc-payment.php?route=end&token=<?= $HttpClient->generateToken() ?>&id='+id, // callback page URL (for example http://localhost:6655 processPayment.php) in your site to be called after payment is processed. (mandatory)
            clientName: '<?= vc_object($userData , 'name') ?>', // the name of the buyer. (mandatory)
            clientMobile: '0', // the mobile of the buyer. (mandatory)
            amount: package['cost'], // the total amount of the order (including VAT or discount). (mandatory). NOTE: This amount is used regardless of total amount of products listed below.
            orderNumber: package['gold']+'-'+id+'<?= str_shuffle('0987654321').time() ?>', // the order number in your system. (mandatory)
            clientEmail: '<?= vc_object($userData , 'email') ?>', // the email of the buyer (optional)
            products: [ // list of products (optional)
                {title: package['name']+' '+package['gold']+' ذهب ' , price: package['cost'], qty: 1},
            ],
        });

        // 5) Create PaylinkPayments javascript object from paylink.js SDK library.
        // let payment = new PaylinkPayments({mode: 'test', defaultLang: 'ar', backgroundColor: '#EEE'});
        let payment = new PaylinkPayments({mode: 'production', defaultLang: 'ar', backgroundColor: '#EEE'});

        // 6) Call openPayment function to open the payment popup screen. It takes the generated "token" and the "order" of the buyer.
        payment.openPayment(token, order, successCallback);
        // 7) NOTE: After the payment is processed (either paid or declined), you must from the server side call
        // the endpoint https://restapi.paylink.sa/api/getInvoice/{transactionNo} for production or
        // the endpoint https://restpilot.paylink.sa/api/getInvoice/{transactionNo} for testing
        // to check the invoice status as appear in the processPayment.php example file.
    }

    function applePayNow(id , package) {
        // 3) Create PaylinkPayments javascript object from paylink.js SDK library.
        let payment = new PaylinkPayments({mode: 'production', defaultLang: 'ar', backgroundColor: '#EEE'});

        // 4) Check if the current browser support apple pay.
        if (payment.isApplePayAllowed()) {
            // 5) Send the the generated token value to client side.
            const token = '<?= $token ?>';

            // 6) In the client side create the order details for the buyer.
            let order = new Order({
            callBackUrl: '<?= $HttpClient->getUrl() ?>vc-payment.php?route=end&token=<?= $HttpClient->generateToken() ?>&id='+id, // callback page URL (for example http://localhost:6655 processPayment.php) in your site to be called after payment is processed. (mandatory)
            clientName: '<?= vc_object($userData , 'name') ?>', // the name of the buyer. (mandatory)
            clientMobile: '0', // the mobile of the buyer. (mandatory)
            amount: package['cost'], // the total amount of the order (including VAT or discount). (mandatory). NOTE: This amount is used regardless of total amount of products listed below.
            orderNumber: package['gold']+'-'+id+'<?= str_shuffle('0987654321').time() ?>',
            clientEmail: '<?= vc_object($userData , 'email') ?>', // the email of the buyer (optional)
            products: [ // list of products (optional)
                {title: package['name']+' '+package['gold']+' ذهب ' , price: package['cost'], qty: 1},
            ],
        });


            // 7) Call openPayment function to open the payment popup screen. It takes the generated "token" and the "order" of the buyer.
            payment.openApplePay(token, order, successCallback);

            // 8) NOTE: After the payment is processed (either paid or declined), you must from the server side call
            // the endpoint https://restapi.paylink.sa/api/getInvoice/{transactionNo} for production or
            // the endpoint https://restpilot.paylink.sa/api/getInvoice/{transactionNo} for testing
            // to check the invoice status as appear in the processPayment.php example file.
        } else {
            alert('This browser does not support ApplePay. Please use Safari on any Apple Device.');
            setTimeout(() => {
                window.close();
            }, 1000);
        }
    }

    setTimeout(() => {
      


        
        <?php if( $this->providerType == 'paylink'): ?>
            __PayNow('pay' , <?= (int) $this->packageIndex ?>);
            <?php else: ?>
                __PayNow('apple' , <?= (int) $this->packageIndex ?>);
                <?php endif; ?>
        
            }, 2000);
});

</script>
                
                <?php

                } else {

                    
                    $this->title = sprintf( payment_loading." %s ...", $this->appConfig['plus']['payments'][$this->providerType]['name'] );

                    $this->package = $this->appConfig['plus']['packages'][$this->packageIndex];
                    
                    $this->payment = $this->appConfig['plus']['payments'][$this->providerType];
                    $this->pg_name = $this->appConfig['plus']['payments'][$this->providerType]['pg_name'];
                    $this->pg_serviceid = $this->appConfig['plus']['payments'][$this->providerType]['pg_serviceid'];
                    $this->pg_return_url = $this->appConfig['plus']['payments'][$this->providerType]['pg_return_url'];
                    $this->pg_cancel_url = $this->appConfig['plus']['payments'][$this->providerType]['pg_cancel_url'];
                    
                    $this->requestPaymentProvider = isset( $_GET['c'] );
                    
                    if ( $this->requestPaymentProvider )
                    
                    {
                        
                        $this->layoutViewFile = NULL;
                        
                        $this->secureId = base64_encode( $this->player->playerId );
                        
                    }
                }

            }

            else

            {

                echo "<script type=\"text/javascript\">self.close();</script>";

            }

        }

    }



}



$p = new GPage();

$p->run();

if(@$_GET['p'] == 'close') {
    echo "<script type=\"text/javascript\">window.close();</script>";
}

?>
