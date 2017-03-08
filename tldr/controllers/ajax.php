<?

$GLOBALS['wholesale'] = $GLOBALS['group_id'] == 3 ? true : false;


$r = $GLOBALS['urlstring']['r'];
$s = $GLOBALS['urlstring']['s'];
$t = $GLOBALS['urlstring']['t'];
$u = $GLOBALS['urlstring']['u'];



function password_generate($pw){
	$o = crypt($pw,$pw) .' |||| '. base64_encode($pw);
	return $o;	
}


if ($r == 'password-reset'){
	
	echo password_generate('oklahoma');
	die;
	

	
}



if ($r == 'asd'){

/*
	$a = db_get("shop_items", "title IS NOT NULL");
	foreach ($a['r'] as $ad){
		db_update("shop_items", array(
			'short_title' => $ad['title']
		), "id='".$ad['id']."'");
		echo $ad['title'];
		echo "<br />";
	}
*/

//echo client_ip();
/*

	$a = db_get("shop_items_images","id!=''");
	foreach ($a['r'] as $ad){
		db_update("shop_items_images",array(
			'url_small' => str_replace("tldr.cassett.es","tealdeer.com",$ad['url_small']),
			'url_large' => str_replace("tldr.cassett.es","tealdeer.com",$ad['url_large']),
		),"id='".$ad['id']."'");
	}
*/
// echo $_SERVER['HTTP_USER_AGENT'];
	die;
}





if ($r == 'shipping-rush'){

	$status = '';

	if ($_POST['status'] == 'true'){
		$status = 'o';
	}
	
	db_update("shop_orders",array(
		'shipping_rush' => $status
	),"order_id='".sanitize($_POST['order_id'])."'");


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}









if ($r == 'cart-qty'){


	db_update("shop_orders_items",array(
		'qty' => $_POST['qty']
	), "cart_id='".$_POST['cart_id']."'");


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}








if ($r == 'cart-validate'){

	$f = array();
	parse_str($_POST['form'],$f);

	foreach ($f as $k => $v){
		// check for required fields (all fields are required)
		if ((substr($k, 0, 9) == 'shipping_') || $k == 'token'){
		}else{
			if (!$v){
				$o['error'][] = $k;
			}			
		}
	}

	if (!$o['error']){
		$o['success'] = true;		
	}

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}











if ($r == 'cart-checkout'){



	// data setup
	$f = array();
	parse_str($_POST['form'],$f);

	$_account = db_get("users","id='".sanitize($user_id)."'");
	$account = $_account['r'][0];


	// transaction id
	$transaction_id = time('Ymd').'-'.sanitize($_POST['order_id']);
	


	$shipping_rush = ''; // does this matter? can't we just put it in the db?

	if ($_POST['shipping_rush'] == 'true'){
		$shipping_rush= 'o';
	}

	// info match and save user data
	$shipping = 'billing';
/*
	if (!$f['info_match']){
		$shipping = 'shipping';
	}
*/

	$billing_first = sanitize($f['billing_first']);
	$billing_last = sanitize($f['billing_last']);
	$billing_address = sanitize($f['billing_address']);
	$billing_city = sanitize($f['billing_city']);
	$billing_state = sanitize($f['billing_state']);
	$billing_zip = sanitize($f['billing_zip']);
	$billing_country = sanitize($f['billing_country']);
	$billing_phone = sanitize($f['billing_phone']);
	$shipping_first = sanitize($f[$shipping.'_first']);
	$shipping_last = sanitize($f[$shipping.'_last']);
	$shipping_address = sanitize($f[$shipping.'_address']);
	$shipping_city = sanitize($f[$shipping.'_city']);
	$shipping_state = sanitize($f[$shipping.'_state']);
	$shipping_zip = sanitize($f[$shipping.'_zip']);
	$shipping_country = sanitize($f[$shipping.'_country']);
	$shipping_phone = sanitize($f[$shipping.'_phone']);

	db_update("users_extra",array(
		'billing_first' => $billing_first,
		'billing_last' => $billing_last,
		'billing_address' => $billing_address,
		'billing_city' => $billing_city,
		'billing_state' => strtoupper($billing_state),
		'billing_zip' => $billing_zip,
		'billing_country' => $billing_country,
		'billing_phone' => $billing_phone,
		'shipping_first' => $shipping_first,
		'shipping_last' => $shipping_last,
		'shipping_address' => $shipping_address,
		'shipping_city' => $shipping_city,
		'shipping_state' => strtoupper($shipping_state),
		'shipping_zip' => $shipping_zip,
		'shipping_country' => $shipping_country,
		'shipping_phone' => $shipping_phone,
//		'info_match' => sanitize($f['info_match'])
	),"user_id='".sanitize($user_id)."'");

	$billing_name = $billing_first ." ". $billing_last;
	$shipping_name = $shipping_first ." ". $shipping_last;
	
/*
	$billing_label = $billing_name."\n".
		$billing_phone."\n".
		$billing_address."\n".
		$billing_city.", ".$billing_state." (".$billing_country.")\n".
		$billing_zip;
*/

	$shipping_label = $shipping_name."\n".
		$shipping_phone."\n".
		$shipping_address."\n".
		$shipping_city.", ".$shipping_state." (".$shipping_country.")\n".
		$shipping_zip;



	// 	order summary, update status in db
	$b = db_get("shop_orders_items","order_id='".sanitize($_POST['order_id'])."' AND item_id !=''");			
	if ($b){
		$order_summary = '';
		foreach ($b['r'] as $bd){
			$c = db_get("shop_items","id='".$bd['item_id']."'"); // cart
			$cd = $c['r'][0];
			$order_summary .= "- ".stripslashes($cd['title'])."\n";			
			$order_summary .= "  - QTY: ".$bd['qty']."\n";			
			$d = db_get("shop_orders_options","cart_id='".$bd['cart_id']."'");					
			if ($d){
				foreach ($d['r'] as $dd){
					$e = db_get("shop_items_options","id='".$dd['item_option_id']."' AND item_id='".$bd['item_id']."'");
					$ff = db_get("shop_items_attributes","id='".$dd['item_attribute_id']."'");
					if ($e){
						$order_summary .= "  - ".stripslashes($ff['r'][0]['title']).": ".stripslashes($e['r'][0]['title'])."\n";			
					}
				}					
			}
		}
	}




	// charge the card
	require_once("media/etc/stripe/lib/Stripe.php");
	
	// live: XsAmQoIW1zvRrGVPKhcXKa5JXWJGKJse
	// test: YQjM3sd3A0BXC9JJLS2CdKUtLcnf78U8
		// client live: pk_1oFOnezZ5lpI9MTci2vgdvOxHFSGH
		// client test: pk_3f5Uf31LwYRwLm032IXRfnXgYme44
	Stripe::setApiKey("XsAmQoIW1zvRrGVPKhcXKa5JXWJGKJse");

	try {
			Stripe_Charge::create(array(
			  "amount" => $_POST['total'] * 100,
			  "currency" => "usd",
			  "card" => $f['token'],
			  "description" => "Order from Tealdeer.com"));
	} catch (Exception $e) {    
	    $o['error'] = $e->getMessage();
	}



	// and we should be good to go
	if (!$o['error']){
		
	db_update("shop_orders",array(
		'order_number' => $transaction_id,
		'price_discount_code' => sanitize($_POST['discount_code']),
		'price_total' => sanitize($_POST['total']),
		'shipping_label' => $shipping_label,
// 		'billing_label' => $billing_label,
		'shipping_summary' => $order_summary,
		'client_ip' => client_ip(),
		'status' => 'paid',
		'shipping_rush' => $shipping_rush,
		'date_paid' => time(),
		'client_user_agent' => $_SERVER['HTTP_USER_AGENT']
	),"id='".sanitize($_POST['order_id'])."'");	
	
	// reset the user one-time discount codes
	db_update("users",array(
		'user_discount' => ''
	),"id='".sanitize($user_id)."'");


	// success email


	$receipt = "Hey ". $billing_first .",

Here's a receipt for your order from Teal Deer:

".$order_summary."

TOTAL $" . $_POST['total'] . "

ORDER #".$transaction_id."


Ship to:
".$shipping_label."


Let us know if you have any questions (you can reply to this email). Thanks!

Teal Deer - http://tealdeer.com/";


		email_send(array(
			'to' => $account['email'],
			'from' => "Teal Deer <orders@tealdeer.com>",
			'subject' => "Your Teal Deer Receipt",
			'message' => msg_format( $receipt )
		));

		email_send(array(
			'to' => 'haleyluna@gmail.com',
			'from' => "Teal Deer <orders@tealdeer.com>",
			'subject' => "blue monday",
			'message' => msg_format( "hey there's a new order. get it? http://tealdeer.com/admin/" )
		));

		$o['success'] = true;
	}


	
	


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}















if ($r == 'cart-checkout-summary'){

	// 	order summary, update status in db
	$b = db_get("shop_orders_items","order_id='". 275 ."' AND item_id !=''");			
	if ($b){
		$order_summary = '';
		foreach ($b['r'] as $bd){
			$c = db_get("shop_items","id='".$bd['item_id']."'"); // cart
			$cd = $c['r'][0];
			$order_summary .= "- ".stripslashes($cd['title'])."\n";			
			$d = db_get("shop_orders_options","cart_id='".$bd['cart_id']."'");					
			if ($d){
				foreach ($d['r'] as $dd){
					$e = db_get("shop_items_options","id='".$dd['item_option_id']."' AND item_id='".$bd['item_id']."'");
					$ff = db_get("shop_items_attributes","id='".$dd['item_attribute_id']."'");
					if ($e){
						$order_summary .= "  - ".stripslashes($ff['r'][0]['title']).": ".stripslashes($e['r'][0]['title'])."\n";			
					}
				}					
			}
		}
	}


	echo $order_summary;
	die;

}








if ($r == 'cart-add'){

	if (!$user_id){
		$o['login'] = true;
	}else{

		
		// get the order id
		$_order = db_get("shop_orders","user_id='".sanitize($user_id)."' AND status = 'incomplete'");
		if (!$_order){
			db_insert("shop_orders",array(
				'user_id' => sanitize($user_id),
				'status' => 'incomplete',
				'order_type' => $GLOBALS['wholesale'] ? 'wholesale' : 'retail',
				'date_start' => time()
			));
			$_order = db_get("shop_orders","user_id='".sanitize($user_id)."' AND status = 'incomplete'");
		}
		
		$order_id = $_order['r'][0]['id'];
		$item_id = $_POST['item_id'];



		// place the item in the cart
		db_insert("shop_orders_items",array(
			'order_id' => $order_id,
			'item_id' => sanitize($item_id),
			'qty' => 1
		));


		// update the inventory (if you need to)
		$_item = db_get("shop_items","id='".sanitize($item_id)."'");
		$item = $_item['r'][0];

		if ($item['quantity_total'] && ($item['quantity_total'] != 'NA')){
			$current_total = (int)$item['quantity_total']; // whatever
			$new_total = $current_total - 1;			
			if ($new_total < 0){
				$new_total = 0;
			}

			db_update("shop_items",array(
				"quantity_total" => $new_total
			),"id='".sanitize($item_id)."'");
		}

		// process the attributes (if you need to)
		$attributes = array();
		$out .= parse_str($_POST['attributes'], $attributes);
		
		// get this item's cart id
		$cart_item = db_get("shop_orders_items","order_id='".$order_id."' AND item_id='".sanitize($item_id)."' ORDER BY cart_id DESC LIMIT 1");

		if ($attributes){

			foreach ($attributes as $key => $value){

				// add the attributes to the item in the cart

				db_insert("shop_orders_options",array(
					'item_id' => sanitize($item_id),
					'item_attribute_id' => sanitize(str_replace("attr-","",$key)),
					'item_option_id' => sanitize($value),
					'cart_id' => $cart_item['r'][0]['cart_id'] // 
				));

				// adjust the inventory quantity (if you need to)
				$_option = db_get("shop_items_options","id='".sanitize($value)."'");
				$quantity = $_option['r'][0]['quantity_total'];
				if ($quantity && ($quantity != 'NA')){
					$current_total = (int)$quantity; // whatever
					$new_total = $current_total - 1;			
					if ($new_total < 0){
						$new_total = 0;
					}		
					db_update("shop_items_options",array(
						"quantity_total" => $new_total
					),"id='".sanitize($value)."'");
				}
			}
		}

		// and we should be all good now
		// return success
		$o['success'] = true;
		
	}


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}








if ($r == 'cart-remove'){


	$cart_id = sanitize($_POST['cart_id']);
	
	$a = db_get("shop_orders_items","cart_id='".$cart_id."'");
	
	$item_id = $a['r'][0]['item_id'];
	$order_id = $a['r'][0]['order_id'];

	// update item quantity
	$_item = db_get("shop_items","id='".$item_id."'");
	$item = $_item['r'][0];
	
	if ($item['quantity_total'] && ($item['quantity_total'] != 'NA')){
		$current_total = (int)$item['quantity_total']; // whatever
		$new_total = $current_total + 1;
		db_update("shop_items",array(
			"quantity_total" => $new_total
		),"id='".$item_id."'");
	}


	// update options quantity
	$b = db_get("shop_orders_options","cart_id='".$cart_id."'");
	if ($b){
		foreach ($b['r'] as $bd){
			$_option = db_get("shop_items_options","id='".$bd['item_option_id']."'");
			$quantity = $_option['r'][0]['quantity_total'];
			if ($quantity && ($quantity != 'NA')){
				$current_total = (int)$quantity; // whatever
				$new_total = $current_total + 1;

				db_update("shop_items_options",array(
					"quantity_total" => $new_total
				),"id='".$bd['item_option_id']."'");
			}			
		}
	}

	// and get rid of this shit
	db_delete("shop_orders_items","cart_id='".$cart_id."'");
	db_delete("shop_orders_options","cart_id='".$cart_id."'");

	$o['success'] = true;


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}




if ($r == 'discount-update'){
	
	$code = strtolower(sanitize($_POST['discount_code']));
	
	$a = db_get("shop_discounts","code='".$code."'");
	if ($a){
		db_update("shop_orders",array(
			'price_discount_code' => $code
		),"id='".sanitize($_POST['order_id'])."'");
	}else{
		db_update("shop_orders",array(
			'price_discount_code' => NULL
		),"id='".sanitize($_POST['order_id'])."'");
	}
	
	$o['success'] = true;

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}









if ($r == 'login-remote'){

	// let's try a login
	$login = login($_POST['email'],$_POST['password']);
	if ($login['error'] == 'email'){
		// bad email...let's make a new account!
		shop_register_login($_POST['email'],$_POST['password']);

		// and call it a day
		$o['success'] = true;
	}
	
	// bad password, show an error
	if ($login['error'] == 'password'){
		$o['password'] = true;
	}
	
	// success! let's gtfo
	if ($login['success']){
		$o['success'] = true;
	}

	
	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}









if ($r == 'login-head'){

	// let's try a login
	$login = login($_POST['email'],$_POST['password']);
	if ($login['error'] == 'email'){

		$o['email'] = true;
	}
	
	if ($login['error'] == 'password'){
		$o['password'] = true;
	}
	
	if ($login['success']){
		$o['success'] = true;
	}

	
	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}










if ($r == 'account-update'){

	$f = array();
	parse_str($_POST['form'],$f);

	$shipping = 'billing';
/*
	if (!$f['info_match']){
		$shipping = 'shipping';
	}
*/

	$billing_first = sanitize($f['billing_first']);
	$billing_last = sanitize($f['billing_last']);
	$billing_address = sanitize($f['billing_address']);
	$billing_city = sanitize($f['billing_city']);
	$billing_state = sanitize($f['billing_state']);
	$billing_zip = sanitize($f['billing_zip']);
	$billing_country = sanitize($f['billing_country']);
	$billing_phone = sanitize($f['billing_phone']);
	$shipping_first = sanitize($f[$shipping.'_first']);
	$shipping_last = sanitize($f[$shipping.'_last']);
	$shipping_address = sanitize($f[$shipping.'_address']);
	$shipping_city = sanitize($f[$shipping.'_city']);
	$shipping_state = sanitize($f[$shipping.'_state']);
	$shipping_zip = sanitize($f[$shipping.'_zip']);
	$shipping_country = sanitize($f[$shipping.'_country']);
	$shipping_phone = sanitize($f[$shipping.'_phone']);

	db_update("users_extra",array(
		'billing_first' => $billing_first,
		'billing_last' => $billing_last,
		'billing_address' => $billing_address,
		'billing_city' => $billing_city,
		'billing_state' => $billing_state,
		'billing_zip' => $billing_zip,
		'billing_country' => $billing_country,
		'billing_phone' => $billing_phone,
		'shipping_first' => $shipping_first,
		'shipping_last' => $shipping_last,
		'shipping_address' => $shipping_address,
		'shipping_city' => $shipping_city,
		'shipping_state' => $shipping_state,
		'shipping_zip' => $shipping_zip,
		'shipping_country' => $shipping_country,
		'shipping_phone' => $shipping_phone,
//		'info_match' => sanitize($f['info_match'])
	),"user_id='".sanitize($user_id)."'");


	$users_input['email'] = sanitize($f['email']);
	
	if ($f['pw']){
		$users_input['pw'] = crypt($f['pw'],$f['pw']);
		$users_input['pwr'] = base64_encode($f['pw']);
		setcookie($GLOBALS['cfg']['vr']['cookieprefix']."[pwe]", "", time()-3600, "/", $GLOBALS['cfg']['vr']['cookiedomain']);
		setcookie($GLOBALS['cfg']['vr']['cookieprefix']."[pwe]", crypt($f['pw'],$f['pw']), time() + 31556926, "/", $GLOBALS['cfg']['vr']['cookiedomain']);
	}

	db_update("users",$users_input,"id='".sanitize($user_id)."'");

	
	$o['success'] = true;

//	$o['error'] = 'validation error';

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}



if ($r == 'whatever'){
	echo $GLOBALS['cfg']['contact']['admin_email'];
	die;
}


if ($r == 'contact-process'){

	email_send(array(
		'to' => 'orders@tealdeer.com',
		'from' => $_POST['email'],
		'subject' => '[tealdeer.com] ' . $_POST['subject'],
		'message' => msg_format( $_POST['message'] )
	));

	
	$o['success'] = true;

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}




class ajax extends glbl {


}