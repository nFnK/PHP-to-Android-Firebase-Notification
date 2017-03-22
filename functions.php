<?php 	
		
	function data_check() {
		
		$result_string = "";
		$result_string_old = "";	
		global $tokens;
		global $message;		
		
		//New Post Database
		$conn = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
			$sql = "SELECT sm_message FROM special_messages";
			$result = $conn->query($sql);		
			$row = $result->fetch_array(MYSQL_NUM);
			$result_string = $row[0];
		mysqli_close($conn);
		
		//OPENS NEW DB AND DROPS POST
			$connect = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
			$sql = "DELETE FROM special_messages";
			$connect->query($sql);
			$connect->close();
		
		//Old Post Database
		$conn_old = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
			$sql_old = "SELECT sm_messageOld FROM special_messages_old";
			$result_old = $conn_old->query($sql_old);		
			$row_old = $result_old->fetch_array(MYSQL_NUM);
			$result_string_old = $row_old[0];
		mysqli_close($conn_old);
		
		//print_r($result_string);
		
		if (strcmp($result_string, $result_string_old) == 0) {
			print_r("Doing Nothing");
			$result_string = "";
			$result_string_old = "";	
			exit();
		} 
		
		if(strcmp($result_string_old, $result_string) !== 0) {
			//OPENS OLD DB AND DROPS POST
			$connect = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
			$sql = "DELETE FROM special_messages_old";
			$connect->query($sql);
			$connect->close();	
			
			//COPIES NEW POST INTO OLD DB				
			$con = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");			
			$q="INSERT INTO special_messages_old (sm_messageOld) VALUES ( '$result_string') "
			." ON DUPLICATE KEY UPDATE sm_messageOld= '$result_string';";              
			mysqli_query($con,$q) or die(mysqli_error($con));	
			mysqli_close($con);
			
			//OPENS DATABASE AND READS TOKENS INTO ARRAY	
			$conn = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase");
			$sql = "Select Token From users";
			$result = mysqli_query($conn,$sql);
			$tokens = array();
				if(mysqli_num_rows($result) > 0 ){
					while ($row = mysqli_fetch_assoc($result)) {
						$tokens[] = $row["Token"];
					}
				}
				mysqli_close($conn);
	
			//Old Post Database
			$conn_old = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
			$sql_old = "SELECT sm_messageOld FROM special_messages_old";
			$result_old = $conn_old->query($sql_old);		
			$row_old = $result_old->fetch_array(MYSQL_NUM);
			$result_string_old = $row_old[0];
			mysqli_close($conn_old);
			$message = array("message" => $result_string_old);	
							
			//CALLING THE PUSH_NOTIFICATION FUNCTION
			include "push_notification.php";
			send_notification($tokens, $message);
						
			//OPENS NEW DB AND DROPS POST
			$connect = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
			$sql = "DELETE FROM special_messages";
			$connect->query($sql);
			$connect->close();
			print_r("Success updating the old db!");
			print_r($result_string);	
			$result_string = "";
			$result_string_old = "";	
			exit();			
		} else {
		
			print_r("DATABASE NOT UPDATED");
			$result_string = "";
			$result_string_old = "";	
			exit();
			
			}
			
	}	
	
	function get_data($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$fbdata = curl_exec($ch);
		curl_close($ch);
		return $fbdata;
	}
	
	function gen_data() {
	
		$data = get_data("https://graph.facebook.com/v2.7/101578056559737/feed?access_token=1746486195602888|KGwQ-a1vj5lzIF8ExtV9mZejiFg");
		$result = json_decode($data); 
		$latest_post =  $result->data[0];		
		$latest_post_text = $latest_post->message;
		//$latest_post_link = $latest_post->actions[0]->link;		
		message_db($latest_post_text);
		return $latest_post_text;
	}
	
	function message_db($message) {
		$conn = mysqli_connect("localhost","firebaseUser","Gallardo24","hc_firebase") or die("Error connecting");
		$q="INSERT INTO special_messages (sm_message) VALUES ( '$message') "
              	." ON DUPLICATE KEY UPDATE sm_message= '$message';";              
	      	mysqli_query($conn,$q) or die(mysqli_error($conn));	
	      	mysqli_close($conn);  
	}
	
	gen_data();
	data_check();
?>