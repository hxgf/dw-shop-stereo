<?
$r = $GLOBALS['urlstring']['r'];
$s = $GLOBALS['urlstring']['s'];
$t = $GLOBALS['urlstring']['t'];
$u = $GLOBALS['urlstring']['u'];


if ($r == 'util'){
	echo base64_decode("ZXhlYzg=");
	die;
}


if ($r == 'names-update'){

	$a = db_get("users_extra","user_id!=''");
	foreach ($a['r'] as $ad){
		$shipping = false;
		$billing = false;
		$shipping = explode(" ",$ad['shipping_name']);
		$billing = explode(" ",$ad['billing_name']);
		db_update("users_extra",array(
			'shipping_first' => $shipping[0],
			'shipping_last' => $shipping[1],
			'billing_first' => $billing[0],
			'billing_last' => $billing[1],
		),"user_id='".$ad['user_id']."'");
	}

	die;
}




function sanitize_html($o){
	return nl2br($o);
}


function desanitize_html($o){
	return preg_replace('/<br\\s*?\/??>/i', '', html_entity_decode($o));
}

// ajax update stuff here


if ($r == 'user-group-update'){
	db_update("users",array(
		'group_id' => sanitize($_POST['group_id'])
	),"id='".sanitize($_POST['user_id'])."'");
	die;
}

if ($r == 'user-discount-update'){
	db_update("users",array(
		'user_discount' => sanitize($_POST['user_discount'])
	),"id='".sanitize($_POST['user_id'])."'");
	die;
}


if ($r == 'edit' && $t == 'delete'){
	$inv_id = sanitize($s);
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

		$price_total = $_POST['price_base'] + $_POST['price_shipping'];

		$collections = '|';
		foreach ($_POST['collections'] as $v){
			$collections .= $v . '|';
		}

		db_update('shop_items',array(
			'title' => sanitize($_POST['title']),
			'url_title' => sanitize($_POST['url_title']),
			'thumbnail_url' => sanitize($_POST['thumbnail_url']),
			'short_title' => sanitize($_POST['short_title']),
			'flag_text' => sanitize($_POST['flag_text']),
			'description' => sanitize_html($_POST['description']),
			'display_status' => sanitize($_POST['display_status']),
			'price_base' => sanitize($_POST['price_base']),
			'price_wholesale' => sanitize($_POST['price_wholesale']),
			'price_shipping' => sanitize($_POST['price_shipping']),
			'type_id' => sanitize($_POST['type_id']),
			'price_total' => $price_total,
			'org_collections' => $collections,
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

							db_insert("shop_items_options",array(
								'item_id' => $item_id,
								'attribute_id' => $b['r'][0]['id'],
								'title' => sanitize($option),
								'quantity_total' => sanitize($_POST['options_qty'][$k][$m]),
								'price_add' => sanitize($_POST['options_price'][$k][$m]),
								'display_order' => $ii,
							));
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




if ($r == 'img-batch'){
	include('media/etc/phmagick/phmagick.php');


	




/*
	$prev = new phMagick($src, $new_p);
	$prev->resize(500, 0); // width, height
*/


	die;
}







if ($r == 'upload-post'){
	include('media/etc/phmagick/phmagick.php');


	$src = '/sites/tealdeer.com/www/media/etc/upload_tmp/'.$_POST['filename'];


	$temp_thumb = '/sites/tealdeer.com/www/media/etc/upload_tmp/'.$_POST['item_id'].'-'.$_POST['filename'];

	$new_o = '/sites/tealdeer.com/www/media/img/items/o/'.$_POST['item_id'].'-'.$_POST['filename'];
	$new_p = '/sites/tealdeer.com/www/media/img/items/l/'.$_POST['item_id'].'-'.$_POST['filename'];

	$new_thumb = '/sites/tealdeer.com/www/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['filename'];
	$new_thumb_square = '/sites/tealdeer.com/www/media/img/items/sq/'.$_POST['item_id'].'-'.$_POST['filename'];


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


	$prev = new phMagick($src, $new_p);
	$prev->resize(500, 0); // width, height

	$tn = new phMagick($src, $new_thumb);
	$tn->resize(250, 0); // width, height

	$sq = new phMagick($src, $new_thumb_square);
	$sq->resizeExactly(250,250); // width, height


	unlink($src);	
//	unlink($temp_thumb);	

	$o['thumb'] = 'http://tealdeer.com/media/img/items/sq/'.$_POST['item_id'].'-'.$_POST['filename'];

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
	
}








if ($r == 'upload-post-gallery'){
	include('media/etc/phmagick/phmagick.php');


	$src = '/sites/tealdeer.com/www/media/etc/upload_tmp/'.$_POST['filename'];


	$temp_thumb = '/sites/tealdeer.com/www/media/etc/upload_tmp/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];

	$new_o = '/sites/tealdeer.com/www/media/img/items/o/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];
	
	$new_p = '/sites/tealdeer.com/www/media/img/items/l/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];

	$new_thumb = '/sites/tealdeer.com/www/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];
	$new_thumb_square = '/sites/tealdeer.com/www/media/img/items/sq/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];


	copy($src, $new_o);


	$prev = new phMagick($src, $new_p);
	$prev->resize(500, 0); // width, height

	$tn = new phMagick($src, $new_thumb);
	$tn->resize(250, 0); // width, height

	$sq = new phMagick($src, $new_thumb_square);
	$sq->resizeExactly(250,250); // width, height


	unlink($src);	
//	unlink($temp_thumb);	

	$o['thumb'] = 'http://tealdeer.com/media/img/items/tn/'.$_POST['item_id'].'-'.$_POST['gallery_order'].'-'.$_POST['filename'];

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


if ($r == 'discounts' && $s == 'update'){
	
	
	
	$input = array(
		'code' => sanitize($_POST['code']),
		'amount_percentage' => sanitize($_POST['amount_percentage']),
		'amount_percentage_shipping' => sanitize($_POST['amount_percentage_shipping']),
	);

	if ($t){
		db_update("shop_discounts", $input,"id='".sanitize($t)."'");
	}else{
		db_insert("shop_discounts", $input);
	}
	
	header("Location: /admin/discounts/");
}


if ($r == 'discounts' && $s == 'delete'){
	db_delete("shop_discounts","id='".sanitize($t)."'");
	header("Location: /admin/discounts/");
}




if ($r == 'order-pack'){
	db_update("shop_orders",array(
		'status' => 'packaged',
	),"id='".sanitize($_POST['order_id'])."'");
	die;
}


if ($r == 'order-ship'){
			
	db_update("shop_orders",array(
		'date_shipped' => time(),
		'status' => 'paid',
		'tracking_number' => sanitize($_POST['tracking_number'])
	),"id='".sanitize($_POST['order_id'])."'");
	
	$a = db_get("shop_orders","id='".sanitize($_POST['order_id'])."'");
	$ad = $a['r'][0];
	
	$b = db_get("users","id='".$ad['user_id']."'");

	$message = "Just wanted to let you know your order from Teal Deer is on its way!

";
	if ($_POST['tracking_number']){
		$message .=	"USPS Tracking #: ".$_POST['tracking_number']."
";
	}
$message .= "Order #: ".$ad['order_number']."

If you have any questions, just let us know (you can reply to this email).

Thanks,
Teal Deer - http://tealdeer.com/
";

	email_send(array(
		'subject' => 'Your order is on the way!',
		'to' => $b['r'][0]['email'],
		'from' => 'Teal Deer <orders@tealdeer.com>',
		'message' => $message
	));

$o['out'] = '';

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}






if ($r == 'order-pickup'){

	db_update("shop_orders",array(
		'date_shipped' => time(),
		'status' => 'paid',
	),"id='".sanitize($_POST['order_id'])."'");

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}







if ($r == 'order-delete'){

	$order_id = $_POST['order_id'];

	$_a = db_get("shop_orders_items","order_id='".$order_id."'");
	if ($_a){
		foreach ($_a['r'] as $_ad){

			$item_id = $_ad['item_id'];
			// update item quantity

			$_item = db_get("shop_items","id='".$item_id."'");
			$item = $_item['r'][0];
			
			if ($item['quantity_total'] && ($item['quantity_total'] != 'NA')){
				$current_total = (int)$item['quantity_total']; // whatever
				$new_total = $current_total + 1;
// echo $new_total;
				db_update("shop_items",array(
					"quantity_total" => $new_total
				),"id='".$item_id."'");
			}
		
			// update options quantity

			$_b = db_get("shop_orders_options","cart_id='".$_ad['cart_id']."'");
			if ($_b){
				foreach ($_b['r'] as $_bd){
					$_option = db_get("shop_items_options","id='".$_bd['item_option_id']."'");
					$quantity = $_option['r'][0]['quantity_total'];
					if ($quantity && ($quantity != 'NA')){
						$current_total = (int)$quantity; // whatever
						$new_total = $current_total + 1;
	
						db_update("shop_items_options",array(
							"quantity_total" => $new_total
						),"id='".$_bd['item_option_id']."'");
					}			
				}
				db_delete("shop_orders_options","cart_id='".$_ad['cart_id']."'");								
			}
		}
	}

	db_delete("shop_orders","id='".$order_id."'");
	db_delete("shop_orders_items","order_id='".$order_id."'");
	$o['order_id'] = $_POST['order_id'];

	header('content-type: application/json; charset=utf-8');
	echo json_encode($o);
	die;
}








$pagetitle = "TLDR admin";


class admin extends glbl {


	public function pagetitle(){
		return $GLOBALS['pagetitle'];
	}
	

	public function orders(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == '' || $GLOBALS['urlstring']['r'] == 'orders'){


			$GLOBALS['pagetitle'] .= ' - orders';

			if (!$GLOBALS['urlstring']['s']){
				$GLOBALS['pagetitle'] .= ' - unshipped (retail)';
				$unshipped = true;
				$where = "status = 'paid' AND (date_shipped IS NULL OR date_shipped = 0) AND order_type = 'retail'";				
			}

			if ($GLOBALS['urlstring']['s'] == 'unshipped-wholesale'){
				$GLOBALS['pagetitle'] .= ' - unshipped (wholesale)';
				$unshipped_wholesale = true;
				$where = "status = 'paid' AND (date_shipped IS NULL OR date_shipped = 0) AND order_type = 'wholesale'";				
			}
			
			if ($GLOBALS['urlstring']['s'] == 'incomplete'){
				$GLOBALS['pagetitle'] .= ' - incomplete';
				$incomplete = true;
				$where = "status = 'incomplete'";
			}
			
			if ($GLOBALS['urlstring']['s'] == 'packaged'){
				$GLOBALS['pagetitle'] .= ' - packaged';
				$packaged = true;
				$where = "status = 'packaged'";
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

					if ($ad['status'] == 'zzz-delete---incomplete' && (time() - $ad['date_start'] > 7200)){



					}else{


						$shipping_label = nl2br($ad['shipping_label']);
						$shipping_summary = $ad['shipping_summary'];


						if ($ad['status'] == 'incomplete'){
						

						
						
							$_a = db_get("shop_orders_items","order_id='".$ad['id']."' AND item_id !=''");			
							if ($_a){
								$order_summary = '';
								foreach ($_a['r'] as $_ad){
									$_c = db_get("shop_items","id='".$_ad['item_id']."'"); // cart
									$_cd = $_c['r'][0];
									$shipping_summary .= "- ".stripslashes($_cd['title'])."\n";			
									$shipping_summary .= "  - QTY: ".$_ad['qty']."\n";			
									$_d = db_get("shop_orders_options","cart_id='".$_ad['cart_id']."'");					
									if ($_d){
										foreach ($_d['r'] as $_dd){
											$_e = db_get("shop_items_options","id='".$_dd['item_option_id']."' AND item_id='".$_ad['item_id']."'");
											$ff = db_get("shop_items_attributes","id='".$_dd['item_attribute_id']."'");
											if ($_e){
												$shipping_summary .= "  - ".stripslashes($ff['r'][0]['title']).": ".stripslashes($_e['r'][0]['title'])."\n";			
											}
										}					
									}
								}
							}



/*
							$shipping_summary = '';
							$_a = db_get("shop_orders_items","order_id='".$ad['id']."'");
							if ($_a){
								foreach ($_a['r'] as $_ad){
	
									$_item = db_get("shop_items","id='".$_ad['item_id']."'");
									$shipping_summary .= "- " . $_item['r'][0]['title'] . "<br />";

								}
							}	
*/						
						}



						$orders_list[] = array(
							'order_number' => $ad['order_number'],
							'order_id' => $ad['id'],
							'tracking_number' => $ad['tracking_number'],
							'shipping_label' => $shipping_label,
							'shipping_name' => $b['r'][0]['shipping_first'] .' '.$b['r'][0]['shipping_last'],
							'shipping_summary' => $shipping_summary,
							'shipping_email' => $c['r'][0]['email'],
							'paid_total' => number_format($ad['price_total'], 2),
							'paid_date' => date("m-d-Y",$ad['date_paid']),
							'shipped_date' => date("m-d-Y",$ad['date_shipped']),
							'shipping_rush' => $ad['shipping_rush'],
							'odd' => odd($i),
							'status_incomplete' => $ad['status'] == 'incomplete' ? true : false
						);
						
					}

					$i++;
				}
			}

			$o = array(
				'unshipped' => $unshipped,
				'unshipped_wholesale' => $unshipped_wholesale,
				'incomplete' => $incomplete,
				'packaged' => $packaged,
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
			'c_discounts' => $GLOBALS['urlstring']['r'] == 'discounts',
			'c_users' => $GLOBALS['urlstring']['r'] == 'users',
		);
	}
		
	

	public function inventory(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'inventory'){
			
			$GLOBALS['pagetitle'] .= ' - inventory';

			if (!$GLOBALS['urlstring']['s']){

				$a = db_get("shop_items","id != '' ORDER BY display_order ASC");
				if ($a){
					foreach ($a['r'] as $ad){
						$items_list[] = array(
							'id' => $ad['id'],
							'img' => str_replace("/sq/","/tn/",$ad['thumbnail_url']),
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
							'current' => preg_match('|'.$dd['id'].'|',$a['r'][0]['org_collections']) ? true : false
						);
					}
				}
				

				$e = db_get("shop_types","id!='' ORDER BY title ASC");
				if ($e){
					foreach ($e['r'] as $ed){
						$types[] = array(
							'id' => $ed['id'],
							'title' => $ed['title'],
							'current' => $ed['id'] == $a['r'][0]['type_id'] ? true : false
						);
					}
				}


				$o = array(
					'item_data' => $a['r'][0],
					'quantity_total' => $ad['quantity_total'],
					'description_clean' => desanitize_html($a['r'][0]['description']),
					'item_collections' => $collections,
					'item_types' => $types,
					'attributes' => $attributes,
					'gallery' => $gallery,
					'active' => $active
				);
			}

		}
		return $o;
	}
	
	
	
/* OLD

	public function discounts(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'discounts'){

			$GLOBALS['pagetitle'] .= ' - discounts';
			
			$a = db_get("shop_discounts","id != '' ORDER BY code ASC");
			if ($a){
				foreach ($a['r'] as $ad){
					$discount_list[] = array(
						'code' => $ad['code'],
						'details' => $ad['details'],

						'date_start' => $ad['date_start'],
						'date_end' => $ad['date_end'],
						'amount_dollar' => $ad['amount_dollar'],
						'amount_percentage' => $ad['amount_percentage'],
						'amount_percentage_shipping' => $ad['amount_percentage_shipping'],

						'id' => $ad['id'] 

					);
				}
			}

			$o = array(
				'discount_list' => $discount_list
			);
		}
		return $o;
	}
*/
	
	
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
	


	

	public function users(){
		$o = false;
		if ($GLOBALS['urlstring']['r'] == 'users'){
		
			$GLOBALS['pagetitle'] .= ' - users';

			$a = db_get("users","id != '' ORDER BY email ASC");
			if ($a){
				foreach ($a['r'] as $ad){
					$users_list[] = array(
						'email' => $ad['email'],
						'uid' => $ad['id'],
						'group_regular' => $ad['group_id'] == 2 ? true : false,
						'group_admin' => $ad['group_id'] == 1 ? true : false,
						'group_wholesale' => $ad['group_id'] == 3 ? true : false,
					);
				}
			}

			$o = array(
				'users_list' => $users_list
			);
		}
		return $o;
	}
	



}