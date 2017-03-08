<?


$GLOBALS['pagetitle'] = $cfg['vr']['site_title'];




function get_order_summary($id, $array = false){
	$order_summary = false;
	// 	order summary, update status in db
	$b = db_get("shop_orders_items","order_id='".sanitize($id)."' AND item_id !=''");			
	if ($b){
		$order_summary = '';
		foreach ($b['r'] as $bd){
			$c = db_get("shop_items","id='".$bd['item_id']."'"); // cart
			$cd = $c['r'][0];


			$order_summary .= "- ".stripslashes($cd['title'])." (";		
			$order_summary .= stripslashes($cd['subtitle']).")\n";			

			$options = false;
			$d = db_get("shop_orders_options","cart_id='".$bd['cart_id']."'");					
			if ($d){
				foreach ($d['r'] as $dd){
					$e = db_get("shop_items_options","id='".$dd['item_option_id']."' AND item_id='".$bd['item_id']."'");
					$ff = db_get("shop_items_attributes","id='".$dd['item_attribute_id']."'");
					if ($e){


						$order_summary .= "  - ".stripslashes($ff['r'][0]['title']).": ".stripslashes($e['r'][0]['title'])."\n";							$options[] = array(
							'option' => $ff['r'][0]['title'],
							'attribute' => $e['r'][0]['title']
						);
					}
				}					
			}
			$order_summary .= "\n";
			$orders_array[] = array(
				'id' => $cd['id'],
				'title' => $cd['title'],
				'url_title' => $cd['url_title'],
				'subtitle' => $cd['subtitle'],
				'thumbnail_url' => $cd['thumbnail_url'],
				'options' => $options
			);
		}
	}

	$o = $order_summary;
	if ($array){
		$o = $orders_array;
	}

	return $o;	
}




/*

function get_order_summary($id, $array = false){
	$order_summary = false;
	// 	order summary, update status in db

	$b = db_get("shop_orders_items","order_id='".sanitize($id)."' AND item_id !=''");			
	if ($b){
		$order_summary = '';
		foreach ($b['r'] as $bd){
			$c = db_get("shop_items","id='".$bd['item_id']."'"); // cart
			$cd = $c['r'][0];
			$order_summary .= "- ".stripslashes($cd['title'])." (";		
			$order_summary .= stripslashes($cd['subtitle']).")\n";			
			$d = db_get("shop_orders_options","cart_id='".$bd['cart_id']."'");					
			if ($d){
				foreach ($d['r'] as $dd){
					$e = db_get("shop_items_options","id='".$dd['item_option_id']."' AND item_id='".$bd['item_id']."'");
					$ff = db_get("shop_items_attributes","id='".$dd['item_attribute_id']."'");
					if ($e){

						$order_summary .= "  - ".stripslashes($ff['r'][0]['title']).": ".stripslashes($e['r'][0]['title'])."\n";
						$options[] = array(
							'option' => $ff['r'][0]['title'],
							'attribute' => $e['r'][0]['title']
						);

					}
				}					
			}

			$order_summary .= "\n";
			$orders_array[] = array(
				'title' => $cd['title'],
				'subtitle' => $cd['subtitle'],
				'thumbnail_url' => $cd['thumbnail_url'],
				'options' => $options
			);
		}
	}




	$o = $order_summary;
	if ($array){
		$o = $orders_array;
	}

	return $o;
}
*/




// fixit make this into a portable component
function notes_sanitize($note){

	// fixit do whatever note parsing

/*
- remove unnecessary div tags (but keep the p tags)
- strip blank image-rows, .takeout
*/

	$notes = str_replace('<b>','<strong>',str_replace('</b>','</strong>',urldecode($note)));
	$notes = str_replace('<i>','<em>',str_replace('</i>','</em>',$notes));
	$notes = str_replace('<div><br></div>','<div class="br"><br></div>',$notes);
	$html = str_replace('>',']',str_replace('<','[',$notes));
	return sanitize($html);
}


function desanitize_edit($text){
// htmlspecialchars_decode(str_replace('\n',"\n",str_replace('\r',"\r",$input)), ENT_QUOTES)
	
	$html = str_replace(']','>',str_replace('[','<',$text));
	
	$notes = str_replace('<strong>','<b>',str_replace('</strong>','</b>',$html));
	$notes = str_replace('<em>','<i>',str_replace('</em>','</i>',$notes));

	return htmlspecialchars_decode($notes, ENT_QUOTES);
}


function desanitize_render($text){

	return desanitize_edit($text);
}









function std_date($timestamp){
	// return $timestamp as Jan 30, 2011
	return date("M j, Y",$timestamp);
}


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




function get_item_list($q){
	$o = false;

	// /shop/ /guys/
	$where = "collection_id = '1'";

	if ($q == 'basketball'){
		$where = "collection_id = '1'";
	}
	if ($q == 'guys'){
		$where = "collection_id = '2' AND (org_sex IS NULL OR org_sex = 'm')";
	}
	if ($q == 'ladies'){
		$where = "collection_id = '2' AND org_sex = 'w'";
	}

	$a = db_get("shop_items","display_status='active' AND ".$where." ORDER BY display_order ASC"); // fixit pagination and other logic
	if ($a){
		foreach ($a['r'] as $ad){
			$price = explode(".",number_format($ad['price_total'], 2));
			$item[] = array(
				'id' => $ad['id'],
				'title' => $ad['title'],
				'subtitle' => $ad['subtitle'],
				'url_title' => $ad['url_title'],
				'thumbnail_url' => $ad['thumbnail_url'],
				'price_total' => $ad['price_total'],
				'price_dollars' => $price[0],
				'price_cents' => $price[1]
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
		'billing_country' => 'US',
		'system_info' => $_SERVER['HTTP_USER_AGENT']
	));

	login($email,$password);
	
	return true;
}