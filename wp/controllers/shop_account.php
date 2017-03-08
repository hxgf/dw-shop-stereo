<?


class shop_account extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'] .' - account';
	}



	public function order_status(){
		$empty = true;
		$thanks = false;

		if ($GLOBALS['urlstring']['r'] == 'thanks'){
			$thanks = true;
		}
		
		$a = db_get("shop_orders","user_id='".$GLOBALS['user_id']."' AND status != 'incomplete' ORDER BY date_paid DESC");
		if ($a){
			$empty = false;
			
			foreach ($a['r'] as $ad){

				$status = 'awaiting shipment';
				if ($ad['date_shipped']){
					$status = 'shipped on ' . date("M j, Y", $ad['date_shipped']);
					$tracking = $ad['tracking_number'];
				}

				$order_detail[] = array(
					'order_number' => $ad['order_number'],
					'price_total' => number_format($ad['price_total'], 2),
					'status' => $status,
					'tracking_number' => $tracking,
					'shipping_summary' => $ad['shipping_summary']
				);
			}

		}
		
		$o = array(
			'empty' => $empty,
			'thanks' => $thanks,
			'order_detail' => $order_detail
		);

		return $o;
	}


	public function user_data(){

		$_ud = db_get("users_extra","user_id='".$GLOBALS['user_id']."'");
		$user = $_ud['r'][0];
		$a = db_get("users","id='".$GLOBALS['user_id']."'");

		$o = array(
			'user' => $user,
			'email' => $a['r'][0]['email'],
		);

		return $o;

	}

}