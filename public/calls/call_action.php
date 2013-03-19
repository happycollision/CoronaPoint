<?php 
	require_once('../initialize.php');
		
	if(empty($_POST)) {
		$session->message('Error CA004: Nothing was sent to the database. Please try again. If the problem persists, contact the application developer with the error number.','error');
		redirect_to('index.php');
	}
	
	
	//ddprint($_POST);
	if(isset($_POST['submit_new_call'])): //is a new call
	
		//ddprint($_POST);
		$required_fields = array(
            'printer_id'=>'Printer Affected',
            'generalProblem'=>'General Description of Problem',
            'tsCalled_day'=>'Day',
            'tsCalled_month'=>'Month',
            'tsCalled_year'=>'Year',
            'tsCalled_time'=>'Time',
            'tsCalled_ampm'=>'AM/PM Selector',
            'tech_id'=>'Technician Assigned',
            'operatorName'=>'Operator Name'
            );
        $call = new Call;
    
        required_fields_spinner($call, $required_fields);
		
		//ddprint($_POST);
		
		//put remaining $_POST atts into string for date
		$d = $_POST['tsCalled_day'];
		$m = $_POST['tsCalled_month'];
		$y = $_POST['tsCalled_year'];
		$t = $_POST['tsCalled_time'];
		$a = $_POST['tsCalled_ampm'];
		
		$call->tsCalled = strtotime("$m/$d/$y $t$a");
		if($call->tsCalled == false){$form_errors[] = new FormError(array(
			'field_id' => 'tsCalled',
			'error_message' => 'The date or time you gave doesn\'t seem to exist',
			'field_name' => 'Time Called'
			));
		}
		$call->tsCreated = time();
		$call->UMR_Closed = 0;
		$call->UMR_IsDowntime = 1;
		
		//ddprint($call);
		//ddprint($form_errors);
		if(count($form_errors)==0){ // no user mistakes detcted. hooray.
			if($call->save()){
				$session->message('Call created successfully:','success');
				redirect_to('index.php');
			}else{
				$_SESSION['POST'] = $_POST;
				$session->message('Error CA046: There was a problem creating the call. Please try again. If the problem persists, contact the application developer', 'error');
				redirect_to('create_call.php');
			}
		}else{ //the user screwed up!
			//ddprint( $_SESSION);
			$_SESSION['POST'] = $_POST;
			$_SESSION['form_errors'] = $form_errors;
			redirect_to('create_call.php');
		}
	
	else: //is an update		
		$calls = array();
		//first we need to break up all the individual calls
		foreach($_POST as $k => $v){
			//let's find the position of the final '_'
			$pos = strrpos($k, '_');
			//now let's get the id
			$call_id = substr($k, $pos+1);
			//then the field that possibly needs to be changed
			$form_field = substr($k, 0, $pos);
			
			//Finally, let's put all that info into a new array
			$calls[$call_id][$form_field] = $v;
		}
		//ddprint($calls);
		
		//let's go ahead and save any unique codes now, and save
		$error_codes_container = array();
		foreach($calls as $call){
			//to call the database once:
			if(isset($call['UMR_ErrorCodes'])){
				$error_codes_container[] = $call['UMR_ErrorCodes'];
			}
		}
		$all_error_codes = implode("\n",$error_codes_container);
		ErrorCode::unique_codes($all_error_codes);
		
		//save the relationship changes as well
		ErrorCode::save_relationships($calls);
		
		$save_all = false;
		if(isset($calls['all'])){
			//we will save all calls at the end
			$save_all = true;
			unset($calls['all']);
		}
		
		foreach($calls as $id => $call){
			
            foreach($call as $k => $v){
				//match the tsField format in the key and save the actual db field part of the string
				if(preg_match("/^(.*ts.*)_/", $k,$matches)){
					//get the final time indicator (month, day, etc) from end of key
					$pos = strrpos($k, '_');
					$type = substr($k,$pos+1);
					//add array part with information like: $call[db_tsField][time_indcator] ex. $call[tsCalled][year]
					$call[$matches[1]][$type] = $v;
					//add array part with list of all tsFields for future use
					$call['tsFields'][$matches[1]] = $matches[1];
				}
			}
			
			//timestamp the tsFields
			foreach($call['tsFields'] as $tsField){
				$timestamp = '';
				if($call[$tsField]['year']>1){
					//the following units are first set up in functions.php
					$y = $call[$tsField]['year'];
					$m = $call[$tsField]['month'];
					$d = $call[$tsField]['day'];
					$t = $call[$tsField]['time'];
					$a = $call[$tsField]['ampm'];
					
					$timestamp = strtotime("$m/$d/$y $t$a");
					if($timestamp==false){
						$form_errors[] = new FormError(array(
							'error_message' => 'One of your dates was not entered properly. Please double check.',
							'object_id' => $id
						));
					}
				}
				$call[$tsField] = $timestamp;
			}
			//bait…
			$temp_calls[$id] = $call;
		}
		
		//... and switch.
		$calls = $temp_calls;
		
		//gotta check that the user didn't change the site, but then forget to specify the printer        
        //also force a 'UMR_IsDowntime' key to be present, since blank checkboxes don't send.
        //also include a timestamp for deletion, if required
        foreach($calls as $id=>$array){
        	if(array_key_exists('printer_id',$array)){ 
        		if($array['printer_id']==null){
        			$session->message('Please be sure to specify a printer. Any other changes may have been lost, so please double check.','warning');
        			redirect_to('edit_call.php?id='.$id.'&site='.$array['site_id']);
        		}
            }
            if(!array_key_exists('UMR_IsDowntime',$array)){
                $array['UMR_IsDowntime'] = 0;
            }
            if(array_key_exists('deleteCall',$array)){
            	$array['tsDeleted'] = time();
            }
            if(array_key_exists('UMR_DeferredTime',$array)){
				$def_time_array = explode(':',$array['UMR_DeferredTime']);
				$def_time_array[0] = $def_time_array[0]*60*60; //hours to seconds
				if(isset($def_time_array[1])){
					$def_time_array[1] = $def_time_array[1]*60; //minutes to seconds
				}else{
					$def_time_array[1] = 0;
				}
				$seconds = $def_time_array[0] + $def_time_array[1];
				$array['UMR_DeferredTime'] = $seconds;
            }
            $calls[$id] = $array;
        }

        
		//get the calls by id so they can be updated
		foreach($calls as $id => $array){
			$ids[] = $id;
		}

		$string_ids = implode(',', $ids);
		$old_calls = Call::find_by_ids($string_ids);
		
		//echo $string_ids;
		//ddprint($old_calls);
		//ddprint($calls);
		
		//merge the two sets of data
		//look for equal ids first
		foreach($old_calls as $old){
			foreach($calls[$old->id] as $k => $v){
				if($old->has_attribute($k)) $old->$k = $v;
			}
		}
		//echo '<hr>'; ddprint($old_calls);
				
		$new_calls = $old_calls;
		
		if(count($form_errors) > 0){
			$id_list = null;
			foreach($form_errors as $error){
				if(!empty($error->object_id)){
					$id_list[] = $error->object_id;
				}
			}
			if(is_array($id_list)) $id_list = implode(',',$id_list);
			$_SESSION['form_errors'] = $form_errors;
			redirect_to("edit_call.php?id={$id_list}");
		}
		
		if($save_all==false){ //just save one.
			//ddprint($calls);
			foreach($calls as $id => $call){
				if(array_key_exists('submit_single', $call)){
					foreach($new_calls as $new_call){
						if($new_call->id==$id){
							if($new_call->save()){
								$msg = "The call with the id number {$new_call->id} was successfully updated.";
								$type = 'success';
							}else{
								$msg = "Error CA141: The call with the id number {$new_call->id} was NOT updated.";
								$type = 'error';
							}
							$session->message($msg, $type);
							redirect_to('index.php');
						}
					}
				}
			}
		}else{ //save all
			foreach($new_calls as $new_call){
				if($new_call->save()){
					$msg = "The call with the id number {$new_call->id} was successfully updated.";
					$type = 'success';
				}else{
					$msg = "Error CA170: The call with the id number {$new_call->id} was NOT updated.";
					$type = 'error';
				}
				$session->message($msg, $type);
			}
		}
			

		redirect_to('index.php');
	endif;

?>

