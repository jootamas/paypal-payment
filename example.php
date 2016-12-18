<?php
	include('paypal_payment.php');
	/**
	 *
	 */
	if($_POST){
		$PayPalPayment = new PayPalPayment();
		if($PayPalPayment->verifyPayment()){
			/* verified, do something */
			echo 'VERIFIED';
			echo '<br />';
		} else {
			/* invalid, do something */
			echo 'INVALID';
			echo '<br />';
		}
	}
	/**
	 *
	 */
	$payment = Array();
	$payment['item_name'] = 'Sample order';
	$payment['amount'] = 99;
	$payment['shipping'] = 9;
	$payment['quantity'] = 1;
	$payment['custom'] = 'ORDER001'; // order ID
	$PayPalPayment = new PayPalPayment();
	echo $PayPalPayment->getButton($payment);
?>
