<?


class shop_list extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'] .' - shop';
	}

	public function current_shop(){
		$o = false;
		if ($GLOBALS['urlstring']['q'] == 'guys'){
			$o = true;			
		}
		return $o;
	}
	

	public function current_basketball(){
		$o = false;
		if ($GLOBALS['urlstring']['q'] == 'shop' || $GLOBALS['urlstring']['q'] == 'basketball'){
			$o = true;			
		}
		return $o;
	}
	

	public function current_ladies(){
		$o = false;
		if ($GLOBALS['urlstring']['q'] == 'ladies'){
			$o = true;			
		}
		return $o;
	}

	// list of items
	public function item_list(){
		return get_item_list($GLOBALS['urlstring']['q']);
	}

}