<?
$r = $GLOBALS['urlstring']['r'];
$s = $GLOBALS['urlstring']['s'];
$t = $GLOBALS['urlstring']['t'];
$u = $GLOBALS['urlstring']['u'];



function sanitize_html($o){
	return nl2br($o);
}


function desanitize_html($o){
	return preg_replace('/<br\\s*?\/??>/i', '', html_entity_decode($o));
}

function sanitize_money($o){
	return str_replace("$", "", $o);
}






function tweet_send($twitter_account,$message){
// fixit test this
/* fixit wp credentials
	if ($twitter_account == 'jeffclickhomes'){
		$ckey = 'seHHGIR5nF5zvm5gBd3XA';
		$csecret = 'RRKlY78ccAFmpd9f7wdK783laNl0Kj2JMEHpL4nwU';
	  $usrtoken = '15191414-ceinvJAXxUOJXohWo2bLgIO8UNUiE3kzwrFfqGB4Q';
	  $usrsecret = 'AasnfTcJ865DQnyyeY2WFjrw2Qe3Bl0YHzdHcmQWg';	
	}
*/

/*
	echo "let's don't & say we did: ";
	echo $message;
	echo "<hr />";
*/


// fixit later enable
/*
	$tmhOAuth = new tmhOAuth(array(
	  'consumer_key'    => $ckey,
	  'consumer_secret' => $csecret,
	  'user_token'      => $usrtoken,
	  'user_secret'     => $usrsecret,
	));
*/
	
	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
	  'status' => $msg
	));

}






function blog_social($ad){
	include('media/etc/twitter_flickr_api.php');

	$blog_url = "http://warpaintstore.com/blog/".$ad['url_title']."/";

	tweet_send('warpaintrags','NEW BLOG: '.$ad['title'].' - '.$blog_url);
}






// ajax update stuff here




if ($r == 'order-summary'){

$a = get_order_summary(354, true);
print_r($a);



/*
	$a = db_get("shop_orders","id!=''");
	if ($a){
		foreach ($a['r'] as $ad){
			db_update("shop_orders",array(
				'shipping_summary' => get_order_summary($ad['id'])
			),"id='".$ad['id']."'");
			echo get_order_summary($ad['id']);
		}
	}
*/

die;
}




if ($r == 'edit' && $t == 'delete'){
	$inv_id = sanitize($s);

/*
	db_update('shop_items',array(
		'display_status' => 'deleted'
	),"id='".$inv_id."'");
*/

	db_delete('shop_items',"id='".$inv_id."'");
	db_delete("shop_items_attributes","item_id='".$inv_id."'");
	db_delete("shop_items_options","item_id='".$inv_id."'");
	db_delete("shop_items_images","item_id='".$inv_id."'");
	// we may need to delete the images themselves...i doubt it will be an issue		
	header("Location: /admin/inventory/");
}


if ($r == 'item-urltitle'){

	$o['urltitle'] = url_title($_POST['title']);

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}

/*

if ($r == 'item-urltitle-fix'){

	$a = db_get("shop_items","title != ''");
	foreach ($a['r'] as $ad){
		db_update("shop_items",array('url_title' => url_title($ad['title'])),"id='".$ad['id']."'"); 
	}

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}
*/








if ($r == 'stockists' && $s == 'update'){
	
	$input = array(
		'title' => sanitize($_POST['title']),
		'address_1' => sanitize($_POST['address_1']),
		'address_2' => sanitize($_POST['address_2']),
		'url' => sanitize($_POST['url']),
		'thumbnail_url' => sanitize($_POST['thumbnail_url']),
	);

	if ($t){
		db_update("stockist", $input,"id='".sanitize($t)."'");
	}else{
		db_insert("stockist", $input);
	}
	
	header("Location: /admin/stockists/");
}




if ($r == 'blog' && $s == 'update'){

	$date_added = strtotime(str_replace('-','/',$_POST['date_added']));

	$input = array(
		'title' => sanitize($_POST['title']),
		'uid' => $GLOBALS['user_id'],
		'date_added' => $date_added,
		'url_title' => url_title(sanitize($_POST['title'])),
		'img_link_url' => sanitize($_POST['img_link_url']),
		'img_url' => str_replace("/tn/","/o/",sanitize($_POST['img_url'])),
		'content' => notes_sanitize($_POST['content']),
	);

	if ($t){
		db_update("blog", $input,"id='".sanitize($t)."'");
	}else{
		db_insert("blog", $input);
	}

	if ($date_added <= time() ){
		$a = db_get("blog","id != '' ORDER BY id DESC LIMIT 1");
		$b = db_get("blog_social_log","blog_id='".$a['r'][0]['id']."'");
		if (!$b){
			blog_social($a['r'][0]);
			db_insert("blog_social_log",array(
				'blog_id' => $a['r'][0]['id'],
				'date_posted' => time()
			));
		}
	}

// fixit if it's going on the site, send it to facebook and twitter

	
	header("Location: /admin/blog/");
}




if ($r == 'discounts' && $s == 'update'){
	
	$input = array(
		'code' => sanitize($_POST['code']),
		'amount_percentage' => sanitize($_POST['amount_percentage']),
	);

	if ($t){
		db_update("shop_discounts", $input,"id='".sanitize($t)."'");
	}else{
		db_insert("shop_discounts", $input);
	}
	
	header("Location: /admin/discounts/");
}





if ($r == 'wholesale' && $s == 'pw-change'){
		db_update("downloads_pw", array(
		'pw' => sanitize($_POST['pw']),
	),"id='1'");
	header("Location: /admin/wholesale/");
};



if ($r == 'wholesale' && $s == 'update'){


	if(move_uploaded_file($_FILES['file_upload']['tmp_name'], 'media/downloads/' . basename($_FILES['file_upload']['name']))) {
	  $file_url = "/media/downloads/" . basename($_FILES['file_upload']['name']);
	}

	
	$input = array(
		'title' => sanitize($_POST['title']),
		'description' => sanitize($_POST['description']),
		'file_url' => $file_url, // fixit upload processing
		'date_added' => time()
	);

	if ($t){
		db_update("downloads", $input,"id='".sanitize($t)."'");
	}else{
		db_insert("downloads", $input);
	}
	
	header("Location: /admin/wholesale/");
}



if ($r == 'stockists' && $s == 'delete'){
	db_delete("stockist","id='".sanitize($t)."'");
	header("Location: /admin/stockists/");
}
if ($r == 'blog' && $s == 'delete'){
	db_delete("blog","id='".sanitize($t)."'");
	header("Location: /admin/blog/");
}
if ($r == 'discounts' && $s == 'delete'){
	db_delete("shop_discounts","id='".sanitize($t)."'");
	header("Location: /admin/discounts/");
}
if ($r == 'wholesale' && $s == 'delete'){
	db_delete("downloads","id='".sanitize($t)."'");
	header("Location: /admin/wholesale/");
}
















if ($r == 'inventory-order-update'){

	$i = 1;
	foreach ($_POST['item_list'] as $pl){
		db_update('shop_items', array(
			'display_order' => $i
		),"id='".$pl."'");	
		$i++;
	}

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;	
}




if ($r == 'gallery-delete'){

	// get from db where display_order seq and item id = 

		// delete the image
	
	// delete from db where display_order seq
	
	
	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}






if ($r == 'item-add'){


	db_insert("shop_items",array(
		'title' => sanitize($_POST['title']),
		'url_title' => url_title(sanitize($_POST['title']))
	));
	
	$a = db_get("shop_items","id !='' ORDER BY id DESC LIMIT 1");


	$o['item_id'] = $a['r'][0]['id'];


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;	
}







if ($r == 'credit-update'){

	db_update("users_extra",array(
		'credit_balance' => sanitize(sanitize_money($_POST['credit_balance'])),
	),"user_id='".sanitize($_POST['user_id'])."'");

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;	
}



if ($r == 'titles-fix'){
	$a = db_get("shop_items","id!=''");
	foreach ($a['r'] as $ad){
		db_update("shop_items",array(
			'title' => ucwords(strtolower($ad['title']))
		),"id='".$ad['id']."'");
		echo ucwords(strtolower($ad['title']));
		echo "<br />";
	}
	die;
}





if ($r == 'edit' && $t == 'update'){
	
	

	
/* fixit enable
	
		if ($_POST['qty'] == 0){ // stupid as fuck
			$qty = 0;
		}else{
			$qty = sanitize($_POST['qty']);
		}
*/

// fixit vars, db

	if ($_POST['title']){




/* not now, but
	display_order
	quantity_total
	type_id
	org_tags
	org_sets
*/

		$item_id = sanitize($s);

		$price_total = sanitize_money($_POST['price_base']) + sanitize_money($_POST['price_shipping']);

		db_update('shop_items',array(
			'title' => sanitize($_POST['title']),
			'url_title' => sanitize($_POST['url_title']),
			'thumbnail_url' => sanitize($_POST['thumbnail_url']),
			'subtitle' => sanitize($_POST['subtitle']),
			'collection_id' => sanitize($_POST['collection_id']),
			'org_sex' => sanitize($_POST['org_sex']),
			'description' => sanitize_html($_POST['description']),
			'display_status' => sanitize($_POST['display_status']),
			'price_base' => sanitize(sanitize_money($_POST['price_base'])),
			'price_shipping' => sanitize(sanitize_money($_POST['price_shipping'])),
			'price_total' => $price_total,
			'quantity_total' => sanitize($_POST['quantity_total'])
		),"id='".$item_id."'");

		// take care of the attributes
		db_delete("shop_items_attributes","item_id='".$item_id."'");
		db_delete("shop_items_options","item_id='".$item_id."'");


		if ($_POST['attribute']){

			$i = 1;

			foreach ($_POST['attribute'] as $k => $attribute){
		
				if ($attribute != ''){		

					db_insert("shop_items_attributes",array(
						'item_id' => $item_id,
						'title' => sanitize($attribute),
						'label' => sanitize($attribute),
						'display_order' => sanitize($i),
/* 						'required' => $_POST['required'][$k] */
					));
					
					$i++;

					$b = db_get("shop_items_attributes","id!='' ORDER BY id DESC LIMIT 1");
		
					$ii = 1;
					foreach ($_POST['options'][$k] as $m => $option){
						if ($option != ''){
		
/* fixit enable
						if ($_POST['options_qty'][$k][$m]){
								$qty = sanitize($_POST['options_qty'][$k][$m]);
							}
*/
							$input_o = array(
								'item_id' => $item_id,
								'attribute_id' => $b['r'][0]['id'],
								'title' => sanitize($option),
								'price_add' => sanitize_money($_POST['options_price'][$k][$m]),
								'quantity_total' => sanitize($_POST['options_qty'][$k][$m]),
								'display_order' => $ii,
							);
							db_insert("shop_items_options",$input_o);
							$ii++;
						}
					}
				}	
			}
		}
		
		// loop of gallery extras
		db_delete("shop_items_images","item_id='".$item_id."'");
		if ($_POST['gallery']){
			$iii = 1;
			foreach ($_POST['gallery'] as $g){
				if ($g){
					db_insert('shop_items_images',array(
						'url_small' => $g,
						'url_large' => str_replace('/tn/','/o/',$g),
						'display_order' => $iii,
						'item_id' => $item_id,
					));
				}
				$iii++;
			}	
		}

	}	
	
	
		
	
	
	
header("Location: /admin/inventory/");
	
	
}




if ($r == 'upload-post'){
	include('media/etc/phmagick/phmagick.php');


	$src = $cfg['vr']['serverpath'] . '/media/etc/upload_tmp/'.$_POST['filename'];


	$temp_thumb = $cfg['vr']['serverpath'] . '/media/etc/upload_tmp/'.$_POST['item_id'].'-'.$_POST['filename'];

	$new_o = $cfg['vr']['serverpath'] . '/media/img/items/o/'.$_POST['item_id'].'-'.$_POST['filename'];

	$new_thumb = $cfg['vr']['serverpath'] . '/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['filename'];


	copy($src, $new_o);

/*
	$is = getimagesize($new_thumb);
	if ($is[0] > $is[1]){ // if width > height
		$sq = new phMagick($src, $temp_thumb);
		$sq->resize(0,250); // width, height
	}
	else{
		$sq = new phMagick($src, $temp_thumb);
		$sq->resize(250,0);
	}

	$pm = new phMagick($temp_thumb, $new_thumb);		
	$pm->crop(250,250,0,0,'NorthWest');
*/

	$sq = new phMagick($src, $new_thumb);
	$sq->resizeExactly(250,250); // width, height


	unlink($src);	
//	unlink($temp_thumb);	

	$o['thumb'] = 'http://warpaint.webfactional.com/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['filename'];

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
	
}








if ($r == 'upload-post-gallery'){
	include('media/etc/phmagick/phmagick.php');


	$src = $cfg['vr']['serverpath'] . '/media/etc/upload_tmp/'.$_POST['filename'];


	$temp_thumb = $cfg['vr']['serverpath'] . '/media/etc/upload_tmp/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];

	$new_o = $cfg['vr']['serverpath'] . '/media/img/items/o/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];

	$new_thumb = $cfg['vr']['serverpath'] . '/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];


	copy($src, $new_o);


	$sq = new phMagick($src, $new_thumb);
	$sq->resizeExactly(250,250); // width, height


	unlink($src);	
//	unlink($temp_thumb);	

	$o['thumb'] = 'http://warpaint.webfactional.com/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
	
}





















if ($r == 'item-add'){


	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}



if ($r == 'discounts' && $s == 'add'){

	db_insert('shop_discount',array(
		'code' => sanitize($_POST['code']),
		'percentage' => sanitize($_POST['percentage']),
	));

}



if ($r == 'order-ship'){
			
	db_update("shop_orders",array(
		'date_shipped' => time(),
		'tracking_number' => sanitize($_POST['tracking_number'])
	),"id='".sanitize($_POST['order_id'])."'");
	
	$a = db_get("shop_orders","id='".sanitize($_POST['order_id'])."'");
	$ad = $a['r'][0];
	
	$b = db_get("users","id='".$ad['user_id']."'");

	$message = "Shipping Details:
".$ad['shipping_label']."

USPS Tracking #: ".$_POST['tracking_number']."
Order #: ".$ad['order_number']."

WPC.CO - http://warpaintstore.com/";

	email_send(array(
		'subject' => 'Your order has been shipped.',
		'to' => $b['r'][0]['email'],
		'from' => 'warpaint clothing co <warpaintclothing@gmail.com>',
		'message' => $message
	));

$o['out'] = '';

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}










$pagetitle = "WP admin";




class admin extends glbl {


	public function pagetitle(){
		return $GLOBALS['pagetitle'];
	}
	

	public function orders(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == '' || $GLOBALS['urlstring']['r'] == 'orders'){
			
			$GLOBALS['pagetitle'] .= ' - orders';

			if (!$GLOBALS['urlstring']['s']){
				$GLOBALS['pagetitle'] .= ' - unshipped';
				$unshipped = true;
				$where = "status = 'paid' AND (date_shipped IS NULL OR date_shipped = 0)";				
			}
			
			if ($GLOBALS['urlstring']['s'] == 'incomplete'){
				$GLOBALS['pagetitle'] .= ' - incomplete';
				$incomplete = true;
				$where = "status = 'incomplete'";
			}
						
			if ($GLOBALS['urlstring']['s'] == 'shipped'){
				$GLOBALS['pagetitle'] .= ' - shipped';
				$shipped = true;
				$where = "date_shipped != 0 ";
			}

			$a = db_get("shop_orders","id != '' AND " . $where . " ORDER BY date_start DESC"); // fixit
			$i = 1;
			if ($a){
				foreach ($a['r'] as $ad){
					$b = db_get("users_extra","user_id='".$ad['user_id']."'");
					$c = db_get("users","id='".$ad['user_id']."'");
					$orders_list[] = array(
						'order_number' => $ad['order_number'],
						'order_id' => $ad['id'],
						'tracking_number' => $ad['tracking_number'],
						'shipping_label' => nl2br($ad['shipping_label']),
						'shipping_name' => $b['r'][0]['shipping_name'],
						'shipping_summary' => get_order_summary($ad['id'], true),
						'shipping_email' => $c['r'][0]['email'],
						'paid_total' => number_format($ad['price_total'], 2),
						'paid_date' => date("m-d-Y",$ad['date_paid']),
						'shipped_date' => date("m-d-Y",$ad['date_shipped']),
						'shipping_rush' => $ad['shipping_rush'],
						'date_started' => date("m-d-Y",$ad['date_start']),
						'order_incomplete' => $incomplete,
						'odd' => odd($i)
					);
					$i++;
				}
			}

			$o = array(
				'unshipped' => $unshipped,
				'incomplete' => $incomplete,
				'shipped' => $shipped,
				'orders_list' => $orders_list,
			);
		}
		return $o;
	}
	

	public function nav(){
		return array(
			'c_inventory' => $GLOBALS['urlstring']['r'] == 'inventory' || $GLOBALS['urlstring']['r'] == 'edit' ? true : false,
			'c_orders' => $GLOBALS['urlstring']['r'] == '' || $GLOBALS['urlstring']['r'] == 'orders' ? true : false,
			'c_discounts' => $GLOBALS['urlstring']['r'] == 'discounts' ? true : false,
			'c_blog' => $GLOBALS['urlstring']['r'] == 'blog' ? true : false,
			'c_stockists' => $GLOBALS['urlstring']['r'] == 'stockists' ? true : false,
			'c_wholesale' => $GLOBALS['urlstring']['r'] == 'wholesale' ? true : false,
			'c_users' => $GLOBALS['urlstring']['r'] == 'users' ? true : false,
		);
	}
	
	

	public function inventory(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'inventory'){

			$GLOBALS['pagetitle'] .= ' - inventory';
			
			if (!$GLOBALS['urlstring']['s']){

				$GLOBALS['pagetitle'] .= ' - all items';

				$a = db_get("shop_items","display_status != 'deleted' ORDER BY display_order ASC");
				if ($a){
					foreach ($a['r'] as $ad){
						$items_list[] = array(
							'id' => $ad['id'],
							'img' => $ad['thumbnail_url'],
							'title' => stripslashes($ad['title']),
						);
					}
				}
				$all = array(
					'items_list' => $items_list
				);
			}

						
			if ($GLOBALS['urlstring']['s'] == 'types'){
				$types = true;
				//fixit what?
			}

						
			if ($GLOBALS['urlstring']['s'] == 'bulk-edit'){
				$bulk_edit = true;
				//fixit what?
			}


			$o = array(
				'all' => $all,
				'types' => $types,
				'bulk_edit' => $bulk_edit,
			);
		}
		return $o;
	}
	
	
	

	public function edit(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'edit'){
			
			$a = db_get("shop_items","id = '".sanitize($GLOBALS['urlstring']['s'])."'");

			if ($a){
				$ad = $a['r'][0];
			
			$GLOBALS['pagetitle'] .= ' - inventory - ' . $a['r'][0]['title'];
			
				$active = false;
				if ($a['r'][0]['display_status'] == 'active'){
					$active = true;
				}
				
				$b = db_get("shop_items_attributes","item_id='".sanitize($GLOBALS['urlstring']['s'])."' ORDER BY display_order ASC");
				if ($b){
					foreach ($b['r'] as $bd){
						$options = false;
						$c = db_get("shop_items_options","attribute_id='".$bd['id']."' ORDER BY display_order ASC");
						if ($c){
							foreach ($c['r'] as $cd){
								$options[] = array(
									'title' => $cd['title'],
									'price' => $cd['price_add'],
									'qty' => $cd['quantity_total'],
									'option_id' => $cd['id']
								);
							}
						}
						$attributes[] = array(
							'options' => $options,
							'attribute_id' => $bd['id'],
							'attribute_title' => $bd['title']
						);						
					}
				}

				$g = db_get("shop_items_images","item_id = '".sanitize($GLOBALS['urlstring']['s'])."'");
				if ($g){
					$i = 1;
					foreach ($g['r'] as $gd){
						$gallery_images[$i] = $gd['url_small'];
						$i++;
					}
				}

				$ii = 1;
				while ($ii <= 12){
					$gallery[] = array(
						'url' => $gallery_images[$ii],
						'seq' => $ii
					);
					$ii++;
				}

				$d = db_get("shop_collections","id!='' ORDER BY title ASC");
				if ($d){
					foreach ($d['r'] as $dd){
						$collections[] = array(
							'id' => $dd['id'],
							'title' => $dd['title'],
							'current' => $dd['id'] == $a['r'][0]['collection_id'] ? true : false
						);
					}
				}

				$o = array(
					'item_data' => $a['r'][0],
					'quantity_total' => $ad['quantity_total'],
					'description_clean' => desanitize_html($a['r'][0]['description']),
					'item_collections' => $collections,
					'attributes' => $attributes,
					'gallery' => $gallery,
					'sex_m' => $ad['org_sex'] == 'm' ? true : false,
					'sex_w' => $ad['org_sex'] == 'w' ? true : false,
					'active' => $active
				);
			}

		}
		return $o;
	}
	
	
	

	public function users(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'users'){

			$GLOBALS['pagetitle'] .= ' - users';

			$a = db_get("users","id != '' ORDER BY email ASC");
			if ($a){
				foreach ($a['r'] as $ad){
					$b = db_get("users_extra","user_id = '".$ad['id']."' LIMIT 1");

					$users_list[] = array(
						'email' => $ad['email'],
						'id' => $ad['id'],
						'shipping_name' => $b['r'][0]['shipping_name'],
						'credit_balance' => $b['r'][0]['credit_balance']
					);
				}
			}

			$o = array(
				'users_list' => $users_list
			);
		}
		return $o;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	

	public function stockists(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'stockists'){

			$GLOBALS['pagetitle'] .= ' - stockists';


			if ($GLOBALS['s'] == 'edit'){
				$a = db_get("stockist","id = '".sanitize($GLOBALS['t'])."'");
				$ad = $a['r'][0];
				if ($a){
					$new = true;
				}
				$edit = array(
					'id' => $ad['id'],
					'new' => $new,
					'title' => $ad['title'],
					'address_1' => $ad['address_1'],
					'address_2' => $ad['address_2'],
					'url' => $ad['url'],
					'thumbnail_url' => $ad['thumbnail_url'],
				);
			}else{
				$a = db_get("stockist","id != '' ORDER BY title ASC");
				if ($a){
					foreach ($a['r'] as $ad){
						$stockist_list[] = array(
							'title' => $ad['title'],
							'id' => $ad['id']
						);
					}
				}				
			}


			$o = array(
				'stockist_list' => $stockist_list,
				'edit' => $edit,
			);


		}
		return $o;
	}
	
	
	
	
	

	public function blog(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'blog'){

			$GLOBALS['pagetitle'] .= ' - blog';


			if ($GLOBALS['s'] == 'edit'){
				$a = db_get("blog","id = '".sanitize($GLOBALS['t'])."'");
				$ad = $a['r'][0];
				if ($a){
					$new = true;
				}
				
				$date = date('m-d-y',time());
				if ($ad['date_added']){
					$date = date('m-d-y',$ad['date_added']);
				}
				
				$edit = array(
					'id' => $ad['id'],
					'new' => $new,
					'title' => $ad['title'],
					'date' => $date,
					'url_title' => $ad['url_title'],
					'img_link_url' => $ad['img_link_url'],
					'img_url' => $ad['img_url'],
					'content' => desanitize_edit($ad['content']), // fixit desanitize
				);
			}else{
				$a = db_get("blog","id != '' ORDER BY id DESC");
				if ($a){
					foreach ($a['r'] as $ad){
						$blog_list[] = array(
							'title' => $ad['title'],
							'id' => $ad['id']
						);
					}
				}				
			}


			$o = array(
				'blog_list' => $blog_list,
				'edit' => $edit,
			);

		}
		return $o;
	}
	
	
	


	public function discounts(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'discounts'){

			$GLOBALS['pagetitle'] .= ' - discounts';


			if ($GLOBALS['s'] == 'edit'){
				$a = db_get("shop_discounts","id = '".sanitize($GLOBALS['t'])."'");
				$ad = $a['r'][0];
				if ($a){
					$new = true;
				}
				$edit = array(
					'id' => $ad['id'],
					'new' => $new,
					'code' => $ad['code'],
					'details' => $ad['details'],
					'date_start' => $ad['date_start'],
					'date_end' => $ad['date_end'],
					'amount_dollar' => $ad['amount_dollar'],
					'amount_percentage' => $ad['amount_percentage'],
					'amount_percentage_shipping' => $ad['amount_percentage_shipping'],
				);
			}else{
				$a = db_get("shop_discounts","id != '' ORDER BY code ASC");
				if ($a){
					foreach ($a['r'] as $ad){
						$discount_list[] = array(
							'title' => $ad['code'],
							'id' => $ad['id']
						);
					}
				}				
			}

			$o = array(
				'discount_list' => $discount_list,
				'edit' => $edit,
			);
		}
		return $o;
	}	
	
	
	
	

	public function wholesale(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'wholesale'){

			$GLOBALS['pagetitle'] .= ' - wholesale';

			if ($GLOBALS['s'] == 'edit'){
				$a = db_get("downloads","id = '".sanitize($GLOBALS['t'])."'");
				$ad = $a['r'][0];
				if ($a){
					$new = true;
				}
				$edit = array(
					'id' => $ad['id'],
					'new' => $new,
					'title' => $ad['title'],
					'description' => $ad['description'],
					'file_url' => $ad['file_url'],
				);
			}elseif($GLOBALS['s'] == 'pw'){
				$a = db_get("downloads_pw","id = '1'");
				$pwd = array(
					'pw' => $a['r'][0]['pw']
				);			
			}else{
				$a = db_get("downloads","id != '' ORDER BY title ASC");
				if ($a){
					foreach ($a['r'] as $ad){
						$downloads_list[] = array(
							'title' => $ad['title'],
							'id' => $ad['id']
						);
					}
				}				
			}


			$o = array(
				'downloads_list' => $downloads_list,
				'edit' => $edit,
				'pwd' => $pwd,
			);
		}
		return $o;
	}
	



}