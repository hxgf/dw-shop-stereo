<?

$GLOBALS['pagetitle'] = 'Account - Teal Deer';

class shop_account extends glbl {

	// page title
	public function pagetitle(){
		return $GLOBALS['pagetitle'];
	}


	public $c_account = true;

	public function order_status(){
		$empty = true;
		$thanks = false;

		if ($GLOBALS['urlstring']['r'] == 'thanks'){
			$thanks = true;
		}
		
		$user_id = $GLOBALS['user_id'];
		
		// for debugging
/*
		if ($GLOBALS['user_id'] == '1'){
			$user_id = '254';
		}
*/
		
		$a = db_get("shop_orders","user_id='".$user_id."' AND status != 'incomplete' ORDER BY date_paid DESC");
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
		


		$_a = db_get("shop_orders","user_id='".$user_id."' AND status = 'incomplete'");

		if ($_a){
			$empty = false;
			$order_incomplete = true;			
		}

		$o = array(
			'empty' => $empty,
			'thanks' => $thanks,
			'order_detail' => $order_detail,
			'order_incomplete' => $order_incomplete
		);

		return $o;
	}


	public function user_data(){

		$_ud = db_get("users_extra","user_id='".$GLOBALS['user_id']."'");
		$user = $_ud['r'][0];
		$a = db_get("users","id='".$GLOBALS['user_id']."'");

		$o = array(
			'user' => $user,
			'email' => $a['r'][0]['email']
		);

		return $o;

	}

}