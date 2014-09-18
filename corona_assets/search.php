<?php

// This is a helper class to make paginating 
// records easy.
class Search {
	
	public $persistant = FALSE; //search string from previous page load
	
	function __construct() {
		if(isset($_POST['search_string']) && isset($_GET['search'])){
			$this->persistant = $_POST['search_string'];
			$_SESSION['search_string'] = $this->persistant;
		}elseif(isset($_GET['search'])){
			$this->persistant = $_SESSION['search_string'];
		}else{
			if(isset($_SESSION['search_string'])){
				unset($_SESSION['search_string']);
			}
		}
	}
	
	public static function sql($user_input){
		global $db;
		$date_eval = TRUE;
		
		$user_input_date = strtotime($user_input);//just a preliminary check
		if($user_input_date==FALSE) {$date_eval = FALSE; echo "strtotime Fail";}

		$search = '#( / | \. | \h | - )#';//apparently whitespace in the code breaks it, so this line is copied below, but below all the whitespace is remeoved.
		$search = '#(/|\.|\h|-)#';
		if(preg_match($search,$user_input)==FALSE) {$date_eval = FALSE; echo "match Fail";}

		if($date_eval){//The user supplied a correct date!
			//Now we should provide a small range for the date to give
			//a three day window
			$range_a = $user_input_date - (60*60*24);
			$range_b = $user_input_date + (60*60*24);

			$sql = "
				SELECT * FROM calls
				WHERE
					tsCalled BETWEEN $range_a AND $range_b
			";
			//echo 'Found Date: '.$sql.' <br>THE RANGE:'.date('m-d-Y',$range_a).'----'.date('m-d-Y',$range_b).'<br><br>';
			return $sql;
		}else{//is not a valid date... must be a control number
			$user_input = $db->escape_value($user_input);
			$sql = "
				SELECT * FROM calls
				WHERE
					UMR_VendorControlNum LIKE '$user_input' 
				OR
					UMR_CustomerControlNum LIKE '$user_input'
			";
			//echo 'Found Number: '.$sql.'<br><br>';
			return $sql;
		}
	}

}
$search = new Search();

?>