## Simple PayPal payment button and payment verification

### Usage
1. add paypal_payment.php to your system
2. fill it email address, currency code, etc.

#### Generate payment form

```php
$payment = Array();
$payment['item_name'] = 'Sample order';
$payment['amount'] = 99;
$payment['shipping'] = 9;
$payment['quantity'] = 1;
$payment['custom'] = 'ORDER001'; // order ID

$PayPalPayment = new PayPalPayment();

/* Optional: overwrite default parameters */
$PayPalPayment->set('email', 'other_email@mysite.com');

echo $PayPalPayment->displayButton($payment);
```

#### Verify payment

Use it on return page, notify page and cancel page

```php
$PayPalPayment = new PayPalPayment();
if($PayPalPayment->verifyPayment()){
	/* verified, do something */
} else {
	/* invalid, do something */
}
```

### Notes

On the return/notify/cancel page the $_POST array will contains payment datas. If you set "custom" as order ID, you can identify the order with it, and you can modify the order in the database (e.g. payment_status : success/canceled).

#### Input variables list
https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables
https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/formbasics

#### IPN validation example
https://developer.paypal.com/docs/classic/ipn/gs_IPN

-----

*All suggestions will be welcome. :)*
