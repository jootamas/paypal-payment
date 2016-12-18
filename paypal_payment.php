<?php
class PayPalPayment {
 /**
  * Use PayPal sandbox
  * @var bool true|false
  */
 private $sandbox = true;
 /**
  * Your PayPal email address
  * @var string
  */
 private $email = '';
 /**
  * Currency code, USD, EUR, etc.
  * List of currencies: https://developer.paypal.com/docs/classic/api/currency_codes
  * @var string
  */
 private $currency = '';
 /**
  * After successful payment PayPal will be return to this URL
  * @var string
  */
 private $return_url = '';
 /**
  * After canceled payment PayPal will be return to this URL
  * @var string
  */
 private $cancel_url = '';
 /**
  * After successful payment PayPal will be call this URL with payment datas in POST (e.g. https://yoursite.com/verify.php)
  * @var string
  */
 private $notify_url = '';
 /**
  * URL of PayPal payment button (e.g. https://yoursite.com/images/paypal-button.png)
  * @var string
  */
 private $button_url = '';
 /**
  * Overwrite default parameters
  * @param string Name of variable
  * @param string New value
  * @return void
  */
 public function set($key, $value){
  $this->$key = $value;
 }
 /**
  * Get PayPal payment button
  * @param array
  * - item_name : string Title of purchase
  * - amount : int|float Amount of purchase
  * - shipping : int|float Shipping price
  * - quantity : int Quantity of purchased products
  * - custom : string Optional custom data, e.g. order ID
  * @return string HTML
  */
 public function getButton($buttonParams){
  $button = '<form action="https://www.'.($this->sandbox ? 'sandbox.' : '').'paypal.com/cgi-bin/webscr" method="post">'."\n";
  $button .= '<input type="hidden" name="business" value="'.$this->email.'" />'."\n";
  $button .= '<input type="hidden" name="currency_code" value="'.strtoupper($this->currency).'" />'."\n";
  $button .= '<input type="hidden" name="cmd" value="_xclick" />'."\n";
  $button .= '<input type="hidden" name="return" value="'.$this->return_url.'" />'."\n";
  $button .= '<input type="hidden" name="cancel_return" value="'.$this->cancel_url.'" />'."\n";
  $button .= '<input type="hidden" name="custom" value="'.$buttonParams['custom'].'" />'."\n";
  $button .= '<input type="hidden" name="notify_url" value="'.$this->notify_url.'" />'."\n";
  $button .= '<input type="hidden" name="item_name" value="'.$buttonParams['item_name'].'" />'."\n";
  $button .= '<input type="hidden" name="rm" value="2" />'."\n";
  $button .= '<input type="hidden" name="amount" value="'.$buttonParams['amount'].'" />'."\n";
  $button .= '<input type="hidden" name="shipping" value="'.$buttonParams['shipping'].'" />'."\n";
  $button .= '<input type="hidden" name="quantity" value="'.$buttonParams['quantity'].'" />'."\n";
  $button .= '<input type="hidden" name="no_note" value="0" />'."\n";
  if(isset($this->button_url)){
	  $button .= '<input type="image" src="'.$this->button_url.'" />'."\n";
  } else {
	  $button .= '<input type="submit" value="Pay with PayPal" />'."\n";
  }
  $button .= '</form>'."\n";
  return $button;
 }
 /**
  * Validate payment
  * @return bool true|false
  */
 public function verifyPayment(){
  /**
   * Set request
   * Via https://github.com/paypal/ipn-code-samples/blob/master/php/PaypalIPN.php
   */
  $raw_post_data = file_get_contents('php://input');
  $raw_post_array = explode('&', $raw_post_data);
  $myPost = [];
  foreach($raw_post_array as $keyval){
   $keyval = explode('=', $keyval);
   if(count($keyval) == 2){
    // PayPal: "Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it."
    if($keyval[0] === 'payment_date'){
     if(substr_count($keyval[1], '+') === 1){
      $keyval[1] = str_replace('+', '%2B', $keyval[1]);
     }
    }
    $myPost[$keyval[0]] = urldecode($keyval[1]);
   }
  }
  $request = 'cmd=_notify-validate';
  $get_magic_quotes_exists = false;
  if(function_exists('get_magic_quotes_gpc')){
   $get_magic_quotes_exists = true;
  }
  foreach($myPost as $key => $value){
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){
    $value = urlencode(stripslashes($value));
   } else {
    $value = urlencode($value);
   }
   $request .= '&'.$key.'='.$value;
  }
  /**
   * Send request
   */
  $paypal_url = 'https://ipnpb.'.(PAYPAL_BUTTON_SANDBOX ? 'sandbox.' : '').'paypal.com/cgi-bin/webscr';
  $ch = curl_init($paypal_url);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
  curl_setopt($ch, CURLOPT_SSLVERSION, 6);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);
  $result = trim(curl_exec($ch));
  /**
   * Get result
   * "PayPal sends a single word back - either VERIFIED (if the message matches the original) or INVALID (if the message does not match the original)."
   * https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNIntro
   */
  if($result == 'VERIFIED'){
  	return true;
  } else {
  	return false;
  }
 }
}
?>
