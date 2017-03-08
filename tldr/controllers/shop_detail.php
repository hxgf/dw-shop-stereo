<?
$GLOBALS['wholesale'] = $GLOBALS['group_id'] == 3 ? true : false;




$a = db_get("shop_items","url_title='".sanitize($GLOBALS['urlstring']['r'])."'");

$GLOBALS['type_id'] = $a['r'][0]['type_id'];


class shop_detail extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'];
	}
	
	public $c_shop = true;

	// list of items
	public function item_detail(){
		$o = false;


		
		if ($GLOBALS['a']){
			$ad = $GLOBALS['a']['r'][0];
			$price = explode(".",number_format($ad['price_base'], 2));

			$attributes = false;
			$b = db_get("shop_items_attributes","item_id='".$ad['id']."' ORDER BY display_order ASC");
			if ($b){
				foreach ($b['r'] as $bd){
					$options = false;
					$c = db_get("shop_items_options","attribute_id='".$bd['id']."' ORDER BY title ASC");
					if ($c){
						foreach ($c['r'] as $cd){

							$sold_out = false;
							if ($cd['quantity_total'] != NULL && $cd['quantity_total'] != 'NA'){
								if ($cd['quantity_total'] == 0){
									$sold_out = true;
								}
							}

							$options[] = array(
								'title' => $cd['title'], // fixit logic
								'value' => $cd['id'],
								'price' => $cd['price_add'],
								'default' => $cd['display_default'],
								'sold_out' => $sold_out
							);
						}
					}
					$attributes[] = array(
						'label' => $bd['label'],
						'id' => $bd['id'],
						'description' => $bd['description'],
						'options' => $options
					);
				}
			}

			$price_shipping = false;
			if ($ad['price_shipping'] && $ad['price_shipping'] != 0){
				$price_shipping = number_format($ad['price_shipping'], 2);
			}

			// fixit might need a bit more advanced logic
			$sold_out = false;
			if ($ad['quantity_total'] != NULL && $ad['quantity_total'] != 'NA'){
				if ($ad['quantity_total'] == 0){
					$sold_out = true;
				}
			}






			$g = db_get("shop_items_images","item_id = '".$ad['id']."'");
			if ($g){

				$gallery[] = array(
					'url_small' => str_replace("/sq/","/tn/",$ad['thumbnail_url']),
					'url_preview' => str_replace("/sq/","/l/",str_replace("/tn/","/l/",$ad['thumbnail_url'])),
					'url_large' => str_replace("/sq/","/o/",str_replace("/tn/","/o/",$ad['thumbnail_url']))
				);

				foreach ($g['r'] as $gd){

/* 						'url_small' => str_replace("/tn/","/sq/",$gd['url_small']), */
					$gallery[] = array(
						'url_small' => str_replace("/sq/","/tn/",$gd['url_small']),
						'url_preview' => str_replace("/o/","/l/",$gd['url_large']),
						'url_large' => $gd['url_large']
					);
				}
			}

			$GLOBALS['pagetitle'] = $ad['title'] . ' - Teal Deer';


			$button_visible = true;
			if ($sold_out){
				$button_visible = false;
			}
			if ($GLOBALS['wholesale'] && !$ad['price_wholesale']){
				$button_visible = false;
			}

			$o = array(
				'title' => $ad['title'],
				'id' => $ad['id'],
				'subtitle' => $ad['subtitle'],
				'flag_text' => $ad['flag_text'],
				'url_title' => $ad['url_title'],
				'original_image' => str_replace("/sq/","/o/",str_replace("/tn/","/o/",$ad['thumbnail_url'])),
				'preview_image' => str_replace("/sq/","/l/",str_replace("/tn/","/l/",$ad['thumbnail_url'])),
				'gallery' => $gallery,

				'price_total' => number_format($ad['price_total'], 2),
				'wholesale_price' => $ad['price_wholesale'] ? number_format($ad['price_wholesale'], 2) : false,

				'price_dollars' => $price[0],
				'price_cents' => $price[1],
				'price_base' => $GLOBALS['wholesale'] ? $ad['price_wholesale'] : $ad['price_base'],
				'price_shipping' => $price_shipping,
				'description' => html_entity_decode($ad['description']),
				'attributes' => $attributes,
				'sold_out' => $sold_out,
				'button_visible' => $button_visible
			);
		}

		return $o;
	}


}