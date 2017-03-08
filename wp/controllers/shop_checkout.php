<?


class shop_checkout extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'] .' - cart';
	}

	public function current_shop(){
		return true;
	}


	public function cart(){
		$o = false;
		if ($GLOBALS['r'] == ''){

			$_a = db_get("shop_orders","user_id='".$GLOBALS['user_id']."' AND status = 'incomplete'");
			$_order = $_a['r'][0];

			$a = db_get("shop_orders_items","order_id='".$_order['id']."' ORDER BY cart_id ASC");

			$_ud = db_get("users_extra","user_id='".$GLOBALS['user_id']."'");
			$user = $_ud['r'][0];

			$total = 0;
/* 			$shipping_total = 3; // default for tldr SUCK MY DICK */
			$empty = true;
			if ($a){
				foreach ($a['r'] as $ad){

					$_b = db_get("shop_items","id='".$ad['item_id']."'");

					$attributes = false;
					$item_total = $_b['r'][0]['price_base'];
/* 					$shipping_total = $shipping_total + $_b['r'][0]['price_shipping']; */

					$b = db_get("shop_orders_options","cart_id='".$ad['cart_id']."'");
					if ($b){
						foreach ($b['r'] as $bd){
							$c = db_get("shop_items_attributes","id='".$bd['item_attribute_id']."'");
							$d = db_get("shop_items_options","id='".$bd['item_option_id']."'");
							$attributes[] = array(
								'attribute' => $c['r'][0]['title'],
								'option' =>  $d['r'][0]['title']
							);
							$item_total = $item_total + $d['r'][0]['price_add'];
						}
					}


					$total = $total + $item_total;

					$items[] = array(
						'title' => $_b['r'][0]['title'],
						'url_title' => $_b['r'][0]['url_title'],
						'thumbnail_url' => $_b['r'][0]['thumbnail_url'],
						'cart_id' => $ad['cart_id'],
						'item_total' => number_format($item_total, 2),
						'attributes' => $attributes,
					);
				}
				$empty = false;
			}




			$discount_code = false;
			$discount_details = false;

			if ($_order['price_discount_code']){
				$_dc = db_get("shop_discounts","code='".$_order['price_discount_code']."'");
				if ($_dc){
					$dc = $_dc['r'][0];
					$discount_code = $_order['price_discount_code'];
					$discount_details = $dc['details'];
					
					if ($dc['amount_dollar']){
						$discount_amount = (int)$dc['amount_dollar'];
					}
					
					if ($dc['amount_percentage']){
						$_da = (int)$dc['amount_percentage'];
						$discount_amount = $total * ( $_da / 100 );
					}
					
					$discount = number_format($discount_amount, 2);
					$total = $total - $discount;

				}
			}




/* 			$shipping = number_format($shipping_total, 2); */
/* 			$total = $total + $shipping; */

/*
			if ( $user['billing_state'] == 'OK' || $user['shipping_state'] == 'OK' ){
				$tax = number_format($total * 0.08375, 2);
				$total = $total + $tax;
			}
*/




			if ($user['credit_balance']){
				if ($user['credit_balance'] > $total){
					$credit = $total;
					$total = 0;
				}else{
					$credit = $user['credit_balance'];
					$total = $total - $credit;					
				}
			}			


			if ($total < 0){
				$total = 0;
			}

			$o = array(
				'items' => $items,
				'discount' => $discount,
				'credit' => $credit,
/* 				'shipping' => $shipping, */
				'tax' => $tax,
				'total' => number_format($total, 2),
				'discount_code' => $discount_code,
				'discount_details' => $discount_details,
				'empty' => $empty,
				'user' => $user,
				'order_id' => $_order['id']
			);
		}
		return $o;
	}

}