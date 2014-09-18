<?php
// If it's going to need the database, then it's
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class ErrorCode extends DatabaseObject {
	
	protected static $table_name="error_codes";
	protected static $db_fields = array('id','name','code','notes');
	
	//ids
	public $id;
	public $name;
	public $code;
	public $notes;


	/*
		This one takes an object array of calls and fills each call with corresponding error objects.
		It should be called when calls are populated in calls.php (class definition).
	*/
	public static function populate_errors($call_object_array){
        global $db, $error_codes;
        $error_ids = array();
        $calls_with_errors = array();
        $errors_x_call = array();
        //lets get just the ids for the calls we need
        foreach($call_object_array as $call){$calls_ids[]=$call->id;}
        $id_string = implode(',',$calls_ids);
        
        $sql="SELECT error_code_id, call_id FROM calls_x_error_codes WHERE call_id IN($id_string)";
        
        $result = $db->query($sql);
        if($result){// do the rest of this function
			while($row = $db->fetch_array($result)){
				$error_ids[] = $row['error_code_id'];
				$calls_with_errors[] = $row['call_id'];
				$errors_x_call[$row['call_id']][] = $row['error_code_id'];
			}
        
			//this is dense... basically recreating the necessary parts of the databse here to distribute as objects in the call objects themselves.
			$error_ids = array_unique($error_ids);
			$error_ids = implode(',',$error_ids);
			if($error_ids!=null){
				$error_codes = static::find_by_ids($error_ids);
			
				$calls_with_errors = array_unique($calls_with_errors);
				foreach($calls_with_errors as $id){
					foreach($errors_x_call[$id] as $the_error_id){
						$call_object_array[$id]->UMR_ErrorCodes[]=$error_codes[$the_error_id];
					}
				}
			}
		}
		return $call_object_array;
	}
	
	public static function unique_codes($error_codes, $save = true){
		global $db, $session;
		$save = true;
		$error_codes = strtoupper($error_codes);
		$error_codes = explode("\n",$error_codes);
		foreach($error_codes as $error_code){
			$error_code = trim($error_code);
			$error_code = $db->escape_value($error_code);
			if($error_code===''||$error_code===null){}else{$temp_codes[] = $error_code;}
		}
		if(isset($temp_codes)){
			$error_codes = $temp_codes;
		}else{
			$error_codes = array();
		}

		$error_code_string = implode('\',\'',$error_codes);
		
		$sql = "SELECT code FROM error_codes WHERE code IN('$error_code_string')";
		$result = $db->query($sql);
		$codes_in_db = array();
		if($result){
			while($row = $db->fetch_array($result)){
				$codes_in_db[] = $row['code'];
			}
		}
		foreach($codes_in_db as $code_in_db){
			$key = array_search($code_in_db,$error_codes);
			unset($error_codes[$key]);
		}

		if(count($error_codes)>0){
			if($save){
				foreach($error_codes as $error_code){
					$code_object = new ErrorCode;
					$code_object->code = $error_code;
					if($code_object->save()){
						//success.  Shouldn't really say anything to the user, because they are already saving a call.
						//echo $db->last_query.'<br><br>';
					}else{
						$session->message("Error EC075: There was a problem saving the error code called {$error_code}.  This likely did not affect the other call information you were working on. But the error code was not saved into the call.  Please try again.  If the problem persists, contact the application developer with the code at the beginning of this message.",'error');
						//log_action('MySQL Error', 'MySQL errored out accompanied by application error EC075.');
					}
				}
			}
		}else{
			return false;
		}
		return true;
	}
	
	public static function save_relationships($call_post_array){
		global $db, $session;
		//NOTE: $call_post_array expects an array of calls with ids as their keys. NOT call objects.  This function is to be used inside call_action.php
		
		//separate calls into array, futher separate errors into call array
		foreach($call_post_array as $id => $call){	
			$error_codes_x_call[$id] = '';
			$call_id_set[] = $id;
			if(!isset($all_error_codes)) $all_error_codes = array();
			if(isset($call['UMR_ErrorCodes'])){
				$exploded_codes = explode("\n",strtoupper($call['UMR_ErrorCodes']));
				foreach($exploded_codes as $key => $ex_code){
					if($ex_code==='') unset($exploded_codes[$key]);
				}
				$error_codes_x_call[$id] = array_map('trim',$exploded_codes);
				$these_error_codes = explode("\n",$call['UMR_ErrorCodes']);
				$these_error_codes = array_map('trim',$these_error_codes);
				$all_error_codes = array_merge($all_error_codes, $these_error_codes);
			}else{
				$error_codes_x_call[$id] = array(); //so we can delete all errors if necessary
			}
		}
		$call_id_list = implode(',',$call_id_set);
		foreach($all_error_codes as $an_error_code){
			$an_error_code = $db->escape_value($an_error_code);
			if($an_error_code===''){}else{$temp_all_codes[] = $an_error_code;}
		}
		if(isset($temp_all_codes))$all_error_codes = $temp_all_codes;
		$all_error_codes = implode('\',\'',array_unique($all_error_codes));
		
		//get the ids for each error code that we will be dealing with (they have already been set by _unique)
		$sql = "SELECT id, code FROM error_codes WHERE code IN('{$all_error_codes}')";
		$result = $db->query($sql);
		$error_codes_with_id = array();
		if($result){
			while($row = $db->fetch_array($result)){
				$error_codes_with_id[$row['id']] = $row['code'];
			}
		}
		//replace error codes submitted with their respective ids
		foreach($error_codes_x_call as $call_id => $error_codes_array){
			foreach($error_codes_array as $error_code){
				$temp_error_codes_x_call[$call_id][] = array_search($error_code,$error_codes_with_id);
			}
		}

		if(isset($temp_error_codes_x_call))$error_codes_x_call = $temp_error_codes_x_call;
		
		//check the current relationships for each call.  Use a custom SQL call for that so we don't do more work than necessary.
		$sql = "SELECT error_code_id, call_id
				FROM calls_x_error_codes
				WHERE call_id IN({$call_id_list})";
		$error_codes_x_call_db = array();
		
		$result = $db->query($sql);
		if($result){
			while($row = $db->fetch_array($result)){
				$error_codes_x_call_db[$row['call_id']][] = $row['error_code_id'];
			}
		}
		
		

		//whiddle down the arrays so that each is unique.  One represents items to be added; the other represents items to be deleted.
		foreach($error_codes_x_call as $call_id => $new_data){
			if(array_key_exists($call_id,$error_codes_x_call_db)){
				foreach($new_data as $new_key => $new_code_id){
					$old_key = array_search($new_code_id,$error_codes_x_call_db[$call_id]);
					if(!($old_key===false)){
						unset($error_codes_x_call[$call_id][$new_key]);
						unset($error_codes_x_call_db[$call_id][$old_key]);
					}
				}
			}
		}
		
		
		//create new relationships where they exist
		foreach($error_codes_x_call as $call_id => $new_code_id_array){
			if(count($new_code_id_array)>0){
				$sql = "INSERT INTO calls_x_error_codes (call_id, error_code_id)
						VALUES ";
				foreach($new_code_id_array as $new_code_id){		
					$sql_array[] = "('{$call_id}','{$new_code_id}')";
				}
				if(isset($sql_array)){
					$sql .= implode(', ',$sql_array);
					if($db->query($sql)){
						//success!
					}else{
						//damn.
						$session->message("Error EC138: Any additional error codes were not saved to the call with description \"".Call::find_by_id($call_id)->generalProblem."\".  Any other work may not have been affected.",'error');
					}
				}
			}
		}
		//delete old relationships where they do not exist
		foreach($error_codes_x_call_db as $call_id => $old_codes_array){
			foreach($old_codes_array as $old_code_id){
				$old_code_id_string[] = $old_code_id;
			}
			if(isset($old_code_id_string)){//then we need to delete
				$old_code_id_string = implode(',',$old_code_id_string);
				//ddprint($old_code_id_string);
				$sql = "DELETE FROM calls_x_error_codes
						WHERE call_id='{$call_id}'
						AND error_code_id IN({$old_code_id_string})";
				if($db->query($sql)){
					//success
				}else{
					//damn.
					$session->messages("Error EC151: The error code $old_code was not removed from the call with database id $call_id.  Any other work may not have been affected.",'error');
				}
			}
		}
	}
}