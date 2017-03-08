<?


class shop_detail extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'];
	}

	public function current_shop(){
		return true;
	}

	// list of items
	public function item_detail(){
		$o = false;
		
		$a = db_get("shop_items","id='".sanitize($GLOBALS['urlstring']['r'])."'");
		if ($a){
			$ad = $a['r'][0];
			$price = explode(".",number_format($ad['price_base'], 2));

			$attributes = false;
			$b = db_get("shop_items_attributes","item_id='".$ad['id']."' ORDER BY display_order ASC");
			if ($b){
				foreach ($b['r'] as $bd){
					$options = false;
					$c = db_get("shop_items_options","attribute_id='".$bd['id']."' ORDER BY display_order ASC");
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
				foreach ($g['r'] as $gd){
					$gallery[] = array(
						'url_small' => $gd['url_small'],
						'url_large' => $gd['url_large']
					);
				}
			}

			$GLOBALS['pagetitle'] = $GLOBALS['pagetitle'] .' - '.$ad['title'];

			$o = array(
				'title' => $ad['title'],
				'id' => $ad['id'],
				'subtitle' => $ad['subtitle'],
				'url_title' => $ad['url_title'],
				'preview_image' => str_replace("/tn/","/o/",$ad['thumbnail_url']),
				'gallery' => $gallery,
				'price_dollars' => $price[0],
				'price_cents' => $price[1],
				'price_base' => $ad['price_base'],
				'price_shipping' => $price_shipping,
				'description' => html_entity_decode($ad['description']),
				'attributes' => $attributes,
				'sold_out' => $sold_out
			);
		}

		return $o;
	}


}