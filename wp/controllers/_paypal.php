<? 


function doDirectPayment($creditcardtype,$acct,$expdate,$cvv2,$email,$firstname,$lastname,$street,$city,$state,$countrycode,$zip,$amt,$invnum){

	$nvp = 
		'METHOD=DoDirectPayment&' .
		'PAYMENTACTION=' . urlencode('Sale') .'&' .
		'IPADDRESS=' . urlencode(client_ip()) .'&' .

		'CREDITCARDTYPE=' .urlencode($creditcardtype) .'&' .
		'ACCT=' . urlencode($acct) .'&' .		
		'EXPDATE=' . urlencode($expdate) .'&' .

		'EMAIL=' . urlencode($email) .'&' .
		'FIRSTNAME=' . urlencode($firstname) .'&' .
		'LASTNAME=' . urlencode($lastname) .'&' .

		'STREET=' . urlencode($street) .'&' .
		'CITY=' . urlencode($city) .'&' .
		'STATE=' . urlencode($state) .'&' .
		'COUNTRYCODE=' . urlencode($countrycode) .'&' .
		'ZIP=' . urlencode($zip) .'&' .

		'AMT=' . urlencode($amt) .'&' .
		'INVNUM=' . urlencode($invnum);


		// make request
		return nvpRequest($nvp);
}


function authDirectPayment($creditcardtype,$acct,$expdate,$cvv2,$email,$firstname,$lastname,$street,$city,$state,$countrycode,$zip,$amt,$invnum){
/*

fixit make this work
	
	$nvp = 
		'METHOD=DoDirectPayment&' .
		'PAYMENTACTION=' . urlencode('Authorization') .'&' .
		'IPADDRESS=' . urlencode(client_ip()) .'&' .

		'CREDITCARDTYPE=' .urlencode($creditcardtype) .'&' .
		'ACCT=' . urlencode($acct) .'&' .		
		'EXPDATE=' . urlencode($expdate) .'&' .

		'EMAIL=' . urlencode('email@gmail.com') .'&' .
		'FIRSTNAME=' . urlencode($firstname) .'&' .
		'LASTNAME=' . urlencode($lastname) .'&' .

		'STREET=' . urlencode($email) .'&' .
		'CITY=' . urlencode($city) .'&' .
		'STATE=' . urlencode($state) .'&' .
		'COUNTRYCODE=' . urlencode($countrycode) .'&' .
		'ZIP=' . urlencode($zip) .'&' .

		'AMT=' . urlencode($amt) .'&' .
		'INVNUM=' . urlencode($invnum.'AUTH');
				
	
	$authid = nvpRequest($nvp);
	
	if ($authid == false){
		return false;
	}else{
		nvpRequest('METHOD=DoVoid&'.'AUTHORIZATIONID=' . urlencode($authid));	
		return true;
	}
*/

	return true;
}


function hi(){
	return "hi";
}


function nvpRequest($nvp){

		$payload = 
			'USER=' . urlencode($GLOBALS['cfg']['paypal']['api_username']) .'&' .
			'PWD=' . urlencode($GLOBALS['cfg']['paypal']['api_password']) .'&'.
			'SIGNATURE=' . $GLOBALS['cfg']['paypal']['api_signature'] . '&'.
			'VERSION='.urlencode(56.0).'&' . $nvp;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $GLOBALS['cfg']['paypal']['server']);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		
		$ret = curl_exec($ch);
			curl_close($ch);

			
	return writeResponse($ret);


}





function writeResponse($res){
	
			// if there's ever a need to duplicate unserialize() functionality, this is it. still no clue why unserialize() doesn't work here. suck a fuck.
	
			$dd = explode('&',$res);
			
			foreach ($dd as $d){
	
				$dv = explode('=',$d);
	
				$k = $dv[0];
				$v = urldecode($dv[1]);
				
				$data[$k] = $v;
			}
		
		$fh = fopen("paypal.log", 'a') or die("can't open file");
			$log = $res."\n\n\n\n";
		fwrite($fh, $log);
		fclose($fh);

	if ($data['ACK'] == 'Failure'){
/*
		$GLOBALS['ret']['message'] = $data['L_ERRORCODE0'] . ': ' . $data['L_SHORTMESSAGE0'] . ' ('.$data['L_LONGMESSAGE0'].')';			
		echo json_encode($GLOBALS['ret']);
		die;
*/
		$ret = str_replace('This transaction cannot be processed. ','',$data['L_LONGMESSAGE0']);
		if ($data['L_LONGMESSAGE1']){
			$ret .=  str_replace('This transaction cannot be processed. ',' ',$data['L_LONGMESSAGE1']);
		}
		return $ret;
	}else{
		return 'true';
/*
		if ($data['TRANSACTIONID']){
			return $data['TRANSACTIONID'];
		}else{
			return true;
		}
*/
	}

}


