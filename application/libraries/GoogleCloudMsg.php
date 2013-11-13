<?php
class wr_gcm{
	const API_KEY = 'AIzaSyBxVOv5VE63ENm7YinR9q9sH72KMoQSS54';
	const GCM_URL = 'https://android.googleapis.com/gcm/send';

	public function send($message,$registrationIDs){

		// Replace with real client registration IDs
		//$registrationIDs = array( "reg id1","reg id2");

		// Message to be sent
		// Set POST variables

		$fields = array(
				'registration_ids'=>$registrationIDs,
				'data'=>$message
				);
		$headers = array(
				'Authorization: key=' . self::API_KEY,
				'Content-Type: application/json'
				);
		
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST
		// data
		curl_setopt( $ch, CURLOPT_URL, self::GCM_URL );
		curl_setopt( $ch, CURLOPT_POST,
				true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		//curl_setopt( $ch, CURLOPT_POSTFIELDS,
		//json_encode( $fields ) ); 
		curl_setopt($ch,
				CURLOPT_SSL_VERIFYPEER, false);
		//     curl_setopt($ch, CURLOPT_POST, true);
		//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields ));

		// Execute post
		$result = curl_exec($ch);

		// Close connection
		curl_close($ch);
		log_message('debug','gcm test'.$result);
		return $result;
	}
}
?>
