<?

$r = $GLOBALS['urlstring']['r'];
$s = $GLOBALS['urlstring']['s'];
$t = $GLOBALS['urlstring']['t'];
$u = $GLOBALS['urlstring']['u'];
















if ($r == 'asd'){
// echo client_ip();
echo base64_decode("c25vd2Rlbg==");
	die;
}


if ($r == 'wholesale-login'){

	$a = db_get("downloads_pw","id='1'");

	if ($_POST['pw'] == $a['r'][0]['pw']){
		cookie_set("wholesale_pw",$_POST['pw']);
		$o['success'] = true;
	}else{
		$o['error'] = true;
	}


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
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











if ($r == 'cart-validate'){

	$f = array();
	parse_str($_POST['form'],$f);

	foreach ($f as $k => $v){
		// check for required fields (all fields are required)
		if (($f['info_match'] && substr($k, 0, 9) == 'shipping_') || $k == 'token' || $k == 'credit'){
		}else{
			if (!$v){
				$o['error'][] = $k;
			}			
		}
	}


	// and make sure this card is good
/* fixit
	if ($validate_cc){
		validateCC();
	}
*/


	if (!$o['error']){
		$o['success'] = true;		
	}

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}











if ($r == 'cart-checkout'){

	require_once("_paypal.php");


	// data setup
	$f = array();
	parse_str($_POST['form'],$f);

	$_account = db_get("users","id='".sanitize($user_id)."'");
	$account = $_account['r'][0];
	$_user = db_get("users_extra","user_id='".sanitize($user_id)."'");
	$user = $_user['r'][0];


	// transaction id
	$transaction_id = time('Ymd').'-'.sanitize($_POST['order_id']);
	


	$shipping_rush = ''; // does this matter? can't we just put it in the db?

	if ($_POST['shipping_rush'] == 'true'){
		$shipping_rush= 'o';
	}

	// info match and save user data
	$shipping = 'billing';
	if (!$f['info_match']){
		$shipping = 'shipping';
	}

	$billing_name = sanitize($f['billing_name']);
	$billing_address = sanitize($f['billing_address']);
	$billing_city = sanitize($f['billing_city']);
	$billing_state = sanitize($f['billing_state']);
	$billing_zip = sanitize($f['billing_zip']);
	$billing_country = sanitize($f['billing_country']);
	$shipping_name = sanitize($f[$shipping.'_name']);
	$shipping_address = sanitize($f[$shipping.'_address']);
	$shipping_city = sanitize($f[$shipping.'_city']);
	$shipping_state = sanitize($f[$shipping.'_state']);
	$shipping_zip = sanitize($f[$shipping.'_zip']);
	$shipping_country = sanitize($f[$shipping.'_country']);


	db_update("users_extra",array(
		'billing_name' => $billing_name,
		'billing_address' => $billing_address,
		'billing_city' => $billing_city,
		'billing_state' => strtoupper($billing_state),
		'billing_zip' => $billing_zip,
		'billing_country' => $billing_country,
		'shipping_name' => $shipping_name,
		'shipping_address' => $shipping_address,
		'shipping_city' => $shipping_city,
		'shipping_state' => strtoupper($shipping_state),
		'shipping_zip' => $shipping_zip,
		'shipping_country' => $shipping_country,
		'info_match' => sanitize($f['info_match']),
		'credit_balance' => $user['credit_balance'] - $f['credit'] // potentially a security thing?
	),"user_id='".sanitize($user_id)."'");

			
	$billing_label = $billing_name."\n".
		$billing_address."\n".
		$billing_city.", ".$billing_state." (".$billing_country.")\n".
		$billing_zip;

	$shipping_label = $shipping_name."\n".
		$shipping_address."\n".
		$shipping_city.", ".$shipping_state." (".$shipping_country.")\n".
		$shipping_zip;


	$order_summary = get_order_summary($_POST['order_id']);

	// charge the card

if ($user_id != 1){

	set_time_limit(120);
	
	$nm = explode(" ", $billing_name);


	$ddp = doDirectPayment(
		$f['cc_t'],
		$f['cc_n'],
		str_replace("/","",$f['cc_e']),
		$f['cc_cvv'],
		$account['email'],
		$nm[0],
		$nm[1],
		$billing_address,
		$billing_city,
		$billing_state,
		$billing_country,
		$billing_zip,
		$_POST['total'],
		$transaction_id
	);

	if ($ddp != 'true'){
		$o['error'] = $ddp;
	}
	
}





	// and we should be good to go
	if (!$o['error']){
		
	db_update("shop_orders",array(
		'order_number' => $transaction_id,
		'price_discount_code' => sanitize($_POST['discount_code']),
		'price_total' => sanitize($_POST['total']),
		'shipping_label' => $shipping_label,
		'billing_label' => $billing_label,
		'shipping_summary' => $order_summary,
		'client_ip' => client_ip(),
		'status' => 'paid',
		'shipping_rush' => $shipping_rush,
		'date_paid' => time()
	),"id='".sanitize($_POST['order_id'])."'");	

	


	$receipt = "
Bill to:
".$billing_label."

Ship to:
".$shipping_label."

".$order_summary."

TOTAL $" . $_POST['total'] . "

ORDER #".$transaction_id."

We'll let you know when we ship your package.

WPC.CO - http://warpaintstore.com/";


		email_send(array(
			'to' => $account['email'],
			'from' => "warpaint clothing co <warpaintclothing@gmail.com>",
			'subject' => "Receipt for order #".$transaction_id,
			'message' => msg_format( $receipt )
		));


		$o['success'] = true;
	}


	
	


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
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
		$cart_item = db_get("shop_orders_items","order_id='".$order_id."' AND item_id='".sanitize($item_id)."'");

		if ($attributes){

			foreach ($attributes as $key => $value){

				// add the attributes to the item in the cart

				db_insert("shop_orders_options",array(
					'item_id' => sanitize($item_id),
					'item_attribute_id' => sanitize(str_replace("attr-","",$key)),
					'item_option_id' => sanitize($value),
					'cart_id' => $cart_item['r'][0]['cart_id']
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
	if (!$f['info_match']){
		$shipping = 'shipping';
	}

	$billing_name = sanitize($f['billing_name']);
	$billing_address = sanitize($f['billing_address']);
	$billing_city = sanitize($f['billing_city']);
	$billing_state = sanitize($f['billing_state']);
	$billing_zip = sanitize($f['billing_zip']);
	$billing_country = sanitize($f['billing_country']);
	$shipping_name = sanitize($f[$shipping.'_name']);
	$shipping_address = sanitize($f[$shipping.'_address']);
	$shipping_city = sanitize($f[$shipping.'_city']);
	$shipping_state = sanitize($f[$shipping.'_state']);
	$shipping_zip = sanitize($f[$shipping.'_zip']);
	$shipping_country = sanitize($f[$shipping.'_country']);

	db_update("users_extra",array(
		'billing_name' => $billing_name,
		'billing_address' => $billing_address,
		'billing_city' => $billing_city,
		'billing_state' => $billing_state,
		'billing_zip' => $billing_zip,
		'billing_country' => $billing_country,
		'shipping_name' => $shipping_name,
		'shipping_address' => $shipping_address,
		'shipping_city' => $shipping_city,
		'shipping_state' => $shipping_state,
		'shipping_zip' => $shipping_zip,
		'shipping_country' => $shipping_country,
		'info_match' => sanitize($f['info_match'])
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






if ($r == 'contact-process'){

	email_send(array(
		'to' => $GLOBALS['cfg']['contact']['admin_email'],
		'from' => $_POST['email'],
		'subject' => $_POST['subject'],
		'message' => msg_format( $_POST['message'] )
	));

	
	$o['success'] = true;

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}




class ajax extends glbl {


}