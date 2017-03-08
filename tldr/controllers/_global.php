<?

// might need to add this to the fw?
	// for formatting stuff from contenteditable forms
	// for emails and stuff
	// janky but it works

function msg_format($msg){
	$o = 
	str_replace(
		"<br>", "", 
	str_replace(
		"</div>", "" ,	
		str_replace( 
			"<div>", "\n", 
			str_replace("<div>\n</div>","\n", $msg)
	)));
	
	return trim($o);
}




function get_item_list($where = false){
	$o = false;

// formerly
// LIMIT ".$GLOBALS['pagination_offset']['offset'].",".$GLOBALS['limit']
		$a = db_get("shop_items","display_status='active' ".$where." ORDER BY display_order ASC");

	if ($a){
		foreach ($a['r'] as $ad){

			$sold_out = false;
			if ($ad['quantity_total'] != NULL && $ad['quantity_total'] != 'NA'){
				if ($ad['quantity_total'] == 0){
					$sold_out = true;
				}
			}

			$price = explode(".",number_format($ad['price_total'], 2));
			$item[] = array(
				'short_title' => $ad['short_title'],
				'flag_text' => $ad['flag_text'],
				'url_title' => $ad['url_title'],
				'thumbnail_url' => str_replace("/sq/","/tn/",$ad['thumbnail_url']),
				'price_total' => number_format($ad['price_total'], 2),
				'price_dollars' => $price[0],
				'wholesale_price' => $ad['price_wholesale'] ? number_format($ad['price_wholesale'], 2) : false,
				'price_cents' => $price[1],
				'sold_out' => $sold_out
			);
		}
		$o = array(
			'item' => $item
		);
	}
	return $o;	
}





function shop_register_login($email,$password){

	db_insert('users',array(
		"email" => sanitize($email),
		"group_id" => '2', // general user
		"pw" => crypt($password,$password),
		"pwr" => base64_encode($password)
	));
	
	$a = db_get('users',"email='".sanitize($email)."'");
	
	db_insert('users_extra',array(
		'user_id' => $a['r'][0]['id'],
		'shipping_country' => 'US',
		'info_match' => 'o',
		'billing_country' => 'US'
	));

	login($email,$password);
	
	return true;
}