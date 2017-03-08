<?
$GLOBALS['wholesale'] = $GLOBALS['group_id'] == 3 ? true : false;









	$where = false;
	$GLOBALS['pagetitle'] = 'All Products - Teal Deer';

	if ($GLOBALS['urlstring']['r'] != 'p'){
		$a = db_get("shop_types","url_title = '".$GLOBALS['urlstring']['r']."'");
		if ($a){
			$where = "AND type_id='".$a['r'][0]['id']."'";
			$GLOBALS['pagetitle'] = $a['r'][0]['title'] .' - Teal Deer';
		}		
	}



	$GLOBALS['limit'] = 15;
	$GLOBALS['pagination_offset'] = pg_offset($GLOBALS['urlstring']['s'],$GLOBALS['limit']);	
	$GLOBALS['b'] = db_get("shop_items","display_status='active' $where ORDER BY display_order ASC", true);
	
	$item_list = get_item_list($where);







class shop_list extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'];
	}
	
	public $c_shop = true;	

	// list of items
	public function item_list(){
		return $GLOBALS['item_list'];
	}

	public function paginate(){
		$pg = $GLOBALS['urlstring']['r'] ? $GLOBALS['urlstring']['r'] : 'p';
		$o = pg( '/shop/'.$pg.'/', $GLOBALS['pagination_offset']['current'], $GLOBALS['b']['total'], $GLOBALS['limit']);
		return $o;
	}
	

}