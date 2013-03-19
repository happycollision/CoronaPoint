<?php
function site_type($type_id){
    switch ($type_id):
        case 2:
            return 'National Print Site';
            break;
        case 1:
            return 'Campus Print Site';
            break;
    endswitch;
}

function in_url($search_string){
    if(preg_match('#('.$search_string.')#i',$_SERVER['REQUEST_URI'])) return true;
    return false;
}

function format_date_string($format, $date_in){
	if(!is_numeric($date_in)){ $date_in_temp = strtotime($date_in);
	echo "\$date_in = $date_in and \$date_in_temp is $date_in_temp<br>";}
	if(isset($date_in_temp)){ if($date_in_temp!=false) $date_in = $date_in_temp;}
	return date($format,$date_in);
}

function strip_zeros_from_date( $marked_string="" ) {
  // first remove the marked zeros
  $no_zeros = str_replace('*0', '', $marked_string);
  // then remove any remaining marks
  $cleaned_string = str_replace('*', '', $no_zeros);
  return $cleaned_string;
}

function redirect_to( $location = NULL ) {
  if ($location != NULL) {
    header("Location: {$location}");
    exit;
  }
}

## backpost
/*
    This function performs three functions.  If first param is empty, it will pull data from $_SESSION['POST']
    which may have been set right before a page redirect with form data.  It will then capture that data in a
    new static variable ($old_post_info) for future calls. Returns $old_post_info.
    
    If param 1 has a value, it tests to see if $_SESSION['POST'] once had that array key and returns a boolean.
    
    If params 1 and 2 are set, it either echos the value once held in $_SESSION['POST'][$array_key], or it
    returns the value, based on whether $echo evaluates true or false.
*/
function backpost($array_key="",$echo=""){
    static $old_post_info;
    if($echo==="") unset($echo);
    if(empty($array_key)){
        if(isset($_SESSION['POST'])) {
            $old_post_info = $_SESSION['POST'];
            unset($_SESSION['POST']);
            //ddprint($site_info);
        }
        return $old_post_info;
    }
    if(isset($old_post_info[$array_key])){
        if(isset($echo) && $echo == true){
            echo $old_post_info[$array_key];
            return;
        }elseif(isset($echo) && $echo == false){
            return $old_post_info[$array_key];
        }
        return true;
    }
    return false;
}

function messages($msg="",$type="") { //will store or display any un-displayed messages
	global $session;
	$messages_output = "\n";
	
	if (!empty($msg)) {
		$session->non_session_message($msg, $type);
	} else {
		$messages = $session->get_messages();
		foreach($messages as $message){
			$messages_output .= "<p class=\"message {$message['type']}\">{$message['message']}</p>\n";
		}
		echo $messages_output;
	}
}

## Required Fields Spinner
/*
    Will take the values inside $_POST and distribute them to the object 
    that is passed in.  Will also check empty responses from the form
    used and will redirect with messaging if the field was required.
*/
function required_fields_spinner($object, $required_fields=''){
    global $session, $form_errors;
    foreach($_POST as $attribute => $value){
        if(empty($value) && is_array($required_fields)) {
            //check to see if the field was required
            $user_error = array_key_exists($attribute,$required_fields) ? true : false;
            if($user_error){
            	$vars = array(
            		'error_message' => "The {$required_fields[$attribute]} field is required.",
            		'object_id' => $object->id,
            		'field_name' => $required_fields[$attribute],
            		'field_id' => $attribute
            	);
            	            	
                $this_error = new FormError($vars);
                $form_errors[] = $this_error; // array($form_field, $message_to_user)
            }
        }
        if($object->has_attribute($attribute)){
            $object->$attribute = $value;
        }
    }
}
##  Fields Spinner
/*
    Similar to the function above, but can be used for multi object saves.
    It also will not thro up any errors in $form_errors. Will merely match
    data in an array (which must be passed in for this function) to 
    corresponding properties of an passed object.
*/
function fields_spinner($object, $attribute_values_array){
    foreach($attribute_values_array as $attribute => $value){
        if($object->has_attribute($attribute)){
            $object->$attribute = $value;
        }
    }
}

function __autoload($class_name) {
	$class_name = preg_replace( '/(.)([A-Z])/', '$1_$2', $class_name);
	$class_name = strtolower($class_name);
	$path = LIB_PATH.DS."{$class_name}.php";
	if(file_exists($path)) {
		require_once($path);
	} else {
		die("The file {$class_name}.php could not be found.");
	}
}

function template_part($template="") { //temp workaround for variable scope is defining the template path and including directly, not in this function.
	include(SITE_ROOT.DS.'public'.DS.'templates'.DS.$template.'.php');
}

function log_action($action, $message="") {
    global $session;
    $logfiledir = LIB_PATH.DS.'logs'.DS.date('Y').DS.date('m');
    is_dir($logfiledir) ? NULL : mkdir($logfiledir,0755,true);
    $logfile = $logfiledir . DS . 'log.txt';
    $new = file_exists($logfile) ? false : true;
    if($handle = fopen($logfile, 'a')) { // 'a' means append
        $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
        $content = "{$timestamp} | {$action}: {$message}\n";
        fwrite($handle, $content);
        fclose($handle);
        if($new) { chmod($logfile, 0755); }
    } else {
        $session->message('Could not open log file for writing.  This may not have affected performance, but please alert the application developer.','error');
    }
}

function datetime_to_text($datetime="") {
  $unixdatetime = strtotime($datetime);
  return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
}

// Time format is UNIX timestamp or
// PHP strtotime compatible strings
function dateDiff($time1, $time2, $precision = 6, $max_interval = 'year') {
	// If not numeric then convert texts to unix timestamps
	if (!is_numeric($time1)) $time1_temp = strtotime($time1);
	if (!is_numeric($time2)) $time2_temp = strtotime($time2);
	
	//if a timestamp is converted to a timestamp, it will fail.  This ensures that all mis-read timestamps will remain intact.
	if(isset($time1_temp)){ if($time1_temp!=false) $time1 = $time1_temp;}
	if(isset($time2_temp)){ if($time2_temp!=false) $time2 = $time2_temp;}
	
	// If time1 is bigger than time2
	// Then swap time1 and time2
	if ($time1 > $time2) {
		$ttime = $time1;
		$time1 = $time2;
		$time2 = $ttime;
	}
	
	// Set up intervals and diffs arrays
	$intervals = array('year','month','day','hour','minute','second');
	
	//fix possible user input error
	$max_interval = rtrim(trim(strtolower($max_interval)),'s');
	
	//downsize array to the maximum interval
	if(in_array($max_interval,$intervals)){
		while(isset($max_interval)&&$max_interval!=reset($intervals)) {
			array_shift($intervals);
		}
	}
	$diffs = array();
	
	// Loop thru all intervals
	foreach ($intervals as $interval) {
		// Set default diff to 0
		$diffs[$interval] = 0;
		// Create temp time from time1 and interval
		$ttime = strtotime("+1 " . $interval, $time1);
		// Loop until temp time is smaller than time2
		while ($time2 >= $ttime) {
			$time1 = $ttime;
			$diffs[$interval]++;
			// Create new temp time from time1 and interval
			$ttime = strtotime("+1 " . $interval, $time1);
		}
	}
	
	$count = 0;
	$times = array();
	// Loop thru all diffs
	foreach ($diffs as $interval => $value) {
		// Break if we have needed precission
		if ($count >= $precision) {
			break;
		}
		// Add value and interval 
		// if value is bigger than 0
		if ($value > 0) {
			// Add s if value is not 1
			if ($value != 1) {
				$interval .= "s";
			}
			// Add value and interval to times array
			$times[] = $value . " " . $interval;
			$count++;
		}
	}
	
	// Return string with times
	$output = implode(", ", $times);
	return !empty($output) ? $output : '0 seconds';
}

function monthly_open_time($date1=NULL, $date2=NULL){
	//if null, stamp now
	$date1 = $date1==NULL? time() : $date1;
	$date2 = $date2==NULL? time() : $date2;
	
	//switch variables: two lines of extra code... sue me.
	$time1 = $date1;
	$time2 = $date2;
	
	// If not numeric then convert texts to unix timestamps
	if (!is_numeric($time1)) $time1_temp = strtotime($time1);
	if (!is_numeric($time2)) $time2_temp = strtotime($time2);
	
	//if a timestamp is converted to a timestamp, it will fail.  This ensures that all mis-read timestamps will remain intact.
	if(isset($time1_temp)){ if($time1_temp!=false) $time1 = $time1_temp;}
	if(isset($time2_temp)){ if($time2_temp!=false) $time2 = $time2_temp;}
	
	// If time1 is bigger than time2
	// Then swap time1 and time2
	if ($time1 > $time2) {
		$ttime = $time1;
		$time1 = $time2;
		$time2 = $ttime;
	}
	
	//find number of months beyond first month
	$info1 = getdate($time1);
	$info2 = getdate($time2);
	$extra_months = 0;
	while($info1['year']+1<$info2['year']){
		$extra_months = $extra_months+12;
		$info1['year']++;
	}
	if($info1['mon']>$info2['mon']){
		$extra_months = $extra_months + (12-$info1['mon']);
		$info1['mon'] = 0;
	}
	while($info1['mon']<$info2['mon']){
		$extra_months++;
		$info1['mon']++;
	}
	$info1 = getdate($time1);
	
	$i=0;
	while($extra_months>=$i){
		//find the timestamp for the first and last second of each month 
		$first_second = strtotime("+$i months",strtotime($info1['month'].' 01, '.$info1['year']));
		$last_second = strtotime("+1 month",$first_second);
		
		if($i==0){//this is the first month
			$first_second = $time1;
		}
		if($i==$extra_months){//this is the last month
			$last_second = $time2;
		}
		
		echo date('F',$first_second).': '.dateDiff($first_second,$last_second,2,'hours').'<br>';
		$i++;
	}
	//echo 'Total: '.dateDiff($time1,$time2,6,'hours');
}

Class Readable_Dates{
	//all the timestampped fields from any class with $easy variable
	public $tsCalled;
	public $tsDispatchClosed;
	public $UMR_tsCENotified;
	public $UMR_tsCEArrived;
	public $UMR_tsClosed;
	public $tsEscalated;
	public $tsDeferredEnd;
	public $tsDeferredStart;
}

function readable_dates($object_array, $tsFields){ //creates dates in readable format for form fields
        //if there are no calls, allowing this function to run will break.
        if(count($object_array) < 1) return $object_array;
        
        if(!function_exists('ez_date_parse')){function ez_date_parse($object, $tsFields){
			// Create sub class
			$object->easy = new Readable_Dates;
			
			// List all timestampped variables from class that need to be turned into easily read values
			$tsArray = $tsFields;
			
			// Loop through each variable
			foreach($tsArray as $field){
				if($object->$field > 1){
					$object->easy->$field = array();
					$object->easy->{$field}['MM']            = date('m',$object->$field);
					//$object->easy->{$field}['M']             = date('n',$object->$field);
					$object->easy->{$field}['dd']            = date('d',$object->$field);
					//$object->easy->{$field}['d']             = date('j',$object->$field);
					//$object->easy->{$field}['dth']           = date('jS',$object->$field);
					//$object->easy->{$field}['Day']           = date('l',$object->$field);
					//$object->easy->{$field}['Day_short']     = date('D',$object->$field);
					$object->easy->{$field}['yyyy']          = date('Y',$object->$field);
					//$object->easy->{$field}['hh']            = date('h',$object->$field);
					//$object->easy->{$field}['24']            = date('H',$object->$field);
					//$object->easy->{$field}['mm']            = date('i',$object->$field);
					$object->easy->{$field}['ampm']          = date('a',$object->$field);
					//$object->easy->{$field}['Month']         = date('F',$object->$field);
					//$object->easy->{$field}['Mon']           = date('M',$object->$field);
					//$object->easy->{$field}['days_in_month'] = date('t',$object->$field);
					//$object->easy->{$field}['day_of_year']   = date('z',$object->$field);
					$object->easy->{$field}['mm/dd/yyyy']    = date('m/d/Y',$object->$field);
					$object->easy->{$field}['m/d/yyyy']      = date('n/j/Y',$object->$field);
					$object->easy->{$field}['h:mm am']       = date('g:ia',$object->$field);
					$object->easy->{$field}['h:mm']          = date('g:i',$object->$field);
					$object->easy->{$field}['24:mm']         = date('G:i',$object->$field);
					$object->easy->{$field}['date_time']     = date('n/j/Y g:ia',$object->$field);
					$object->easy->{$field}['text_day_date'] = date('l F jS, Y',$object->$field);
					$object->easy->{$field}['text_date']     = date('F jS, Y',$object->$field);
				}else{
					$object->easy->$field = null;
				}
			}
			return $object;
		}}
		
		if(is_object(current($object_array))){ //if an object array (for use in preparing a loop)
			foreach($object_array as $object){
				$object = ez_date_parse($object, $tsFields);
			}
		}elseif(is_object($object_array)){ //if actually just an object (for use inside a loop)
			$object = ez_date_parse($object, $tsFields);
		}
		
		return $object_array;
}


function easy_date_time($tsField,$object_or_array='',$nice_name='',$fill=FALSE){ //creates form html within given $object (ex: within $call loop)... Object must have $easy public var.
	
	if(is_object($object_or_array) && $fill==false): // means it is being updated
        $object = $object_or_array;
        ?>
		<div class="ez_date">
		Month:<select id="<?php echo $tsField;?>_month_<?php echo $object->id; ?>" name="<?php echo $tsField;?>_month_<?php echo $object->id; ?>">
			<option value=""></option>
			<option value="01" <?php if($object->easy->{$tsField}['MM']=='01'){echo 'selected';}?> >January</option>
			<option value="02" <?php if($object->easy->{$tsField}['MM']=='02'){echo 'selected';}?> >February</option>
			<option value="03" <?php if($object->easy->{$tsField}['MM']=='03'){echo 'selected';}?> >March</option>
			<option value="04" <?php if($object->easy->{$tsField}['MM']=='04'){echo 'selected';}?> >April</option>
			<option value="05" <?php if($object->easy->{$tsField}['MM']=='05'){echo 'selected';}?> >May</option>
			<option value="06" <?php if($object->easy->{$tsField}['MM']=='06'){echo 'selected';}?> >June</option>
			<option value="07" <?php if($object->easy->{$tsField}['MM']=='07'){echo 'selected';}?> >July</option>
			<option value="08" <?php if($object->easy->{$tsField}['MM']=='08'){echo 'selected';}?> >August</option>
			<option value="09" <?php if($object->easy->{$tsField}['MM']=='09'){echo 'selected';}?> >September</option>
			<option value="10" <?php if($object->easy->{$tsField}['MM']=='10'){echo 'selected';}?> >October</option>
			<option value="11" <?php if($object->easy->{$tsField}['MM']=='11'){echo 'selected';}?> >November</option>
			<option value="12" <?php if($object->easy->{$tsField}['MM']=='12'){echo 'selected';}?> >December</option>
		</select>
		Day:<input type="text" id="<?php echo $tsField;?>_day_<?php echo $object->id; ?>" name="<?php echo $tsField;?>_day_<?php echo $object->id; ?>" size="2" maxlength="2" value="<?php echo $object->easy->{$tsField}['dd'];?>" />
		Year:<input type="text" id="<?php echo $tsField;?>_year_<?php echo $object->id; ?>" name="<?php echo $tsField;?>_year_<?php echo $object->id; ?>" size="4" maxlength="4" value="<?php echo $object->easy->{$tsField}['yyyy'];?>" />
		
		<br />
		
		Time:<input type="text" id="<?php echo $tsField;?>_time_<?php echo $object->id; ?>" name="<?php echo $tsField;?>_time_<?php echo $object->id; ?>" size="5" maxlength="5" value="<?php echo $object->easy->{$tsField}['h:mm'];?>" />
		<input type="radio" id="<?php echo $tsField;?>_am_<?php echo $object->id; ?>" name="<?php echo $tsField;?>_ampm_<?php echo $object->id; ?>" size="5" maxlength="5" value="am" <?php if($object->easy->{$tsField}['ampm']=='am'){echo 'checked';}?> /><label class="note" for="<?php echo $tsField;?>_am_<?php echo $object->id; ?>">am</label>
		<input type="radio" id="<?php echo $tsField;?>_pm_<?php echo $object->id; ?>" name="<?php echo $tsField;?>_ampm_<?php echo $object->id; ?>" size="5" maxlength="5" value="pm" <?php if($object->easy->{$tsField}['ampm']=='pm'){echo 'checked';}?> /><label class="note" for="<?php echo $tsField;?>_pm_<?php echo $object->id; ?>">pm</label>
		</div><!--ez_date-->
        <?php
	
    
    elseif(is_array($object_or_array)): // means it was backposted while being created
        $array = $object_or_array;
        
        ?>
		<fieldset>
		<legend><?php echo $nice_name;?></legend>
			<label for="<?php echo $tsField;?>_month">Month:</label>
			<select id="<?php echo $tsField;?>_month" name="<?php echo $tsField;?>_month">
				<option value=""></option>
				<option value="01" <?php if($array["{$tsField}_month"]=='01'){echo 'selected';}?> >January</option>
				<option value="02" <?php if($array["{$tsField}_month"]=='02'){echo 'selected';}?> >February</option>
				<option value="03" <?php if($array["{$tsField}_month"]=='03'){echo 'selected';}?> >March</option>
				<option value="04" <?php if($array["{$tsField}_month"]=='04'){echo 'selected';}?> >April</option>
				<option value="05" <?php if($array["{$tsField}_month"]=='05'){echo 'selected';}?> >May</option>
				<option value="06" <?php if($array["{$tsField}_month"]=='06'){echo 'selected';}?> >June</option>
				<option value="07" <?php if($array["{$tsField}_month"]=='07'){echo 'selected';}?> >July</option>
				<option value="08" <?php if($array["{$tsField}_month"]=='08'){echo 'selected';}?> >August</option>
				<option value="09" <?php if($array["{$tsField}_month"]=='09'){echo 'selected';}?> >September</option>
				<option value="10" <?php if($array["{$tsField}_month"]=='10'){echo 'selected';}?> >October</option>
				<option value="11" <?php if($array["{$tsField}_month"]=='11'){echo 'selected';}?> >November</option>
				<option value="12" <?php if($array["{$tsField}_month"]=='12'){echo 'selected';}?> >December</option>
			</select>
			
			<label for="<?php echo $tsField;?>_day">Day:</label>
			<input type="text" id="<?php echo $tsField;?>_day" name="<?php echo $tsField;?>_day" size="2" maxlength="2" value="<?php echo $array["{$tsField}_day"];?>" />
			
			<label for="<?php echo $tsField;?>_year">Year:</label>
			<input type="text" id="<?php echo $tsField;?>_year" name="<?php echo $tsField;?>_year" size="4" maxlength="4" value="<?php echo $array["{$tsField}_year"];?>" />
			
			<br />
			
			<label for="<?php echo $tsField;?>_time">Time: (hh:mm)</label>
			<input type="text" id="<?php echo $tsField;?>_time" name="<?php echo $tsField;?>_time" size="5" maxlength="5" value="<?php echo $array["{$tsField}_time"];?>" />
			
			<input type="radio" id="<?php echo $tsField;?>_am" name="<?php echo $tsField;?>_ampm" value="am" <?php if($array["{$tsField}_ampm"]=='am'){echo 'checked';}?> /><label for="<?php echo $tsField;?>_am">am</label>
			
			<input type="radio" id="<?php echo $tsField;?>_pm" name="<?php echo $tsField;?>_ampm" value="pm" <?php if($array["{$tsField}_ampm"]=='pm'){echo 'checked';}?> /><label for="<?php echo $tsField;?>_pm">pm</label>	
		</fieldset>
        <?php
        
    
    else: // means it is brand new, or needs the current date filled
    	//$object_or_array will be a single call object, so the id needs setting, but in a way that allows its abscence as well:
    	$the_id ='';
    	if(isset($object_or_array)) $the_id = '_'.$object_or_array->id;
        
        ?>
		<fieldset>
		<legend><?php echo $nice_name;?></legend>
			<label for="<?php echo $tsField;?>_month<?php echo $the_id;?>">Month:</label>
			<select id="<?php echo $tsField;?>_month<?php echo $the_id;?>" name="<?php echo $tsField;?>_month<?php echo $the_id;?>">
				<option value=""></option>
				<option value="01" <?php if(date('m')=='01'){echo 'selected';}?> >January</option>
				<option value="02" <?php if(date('m')=='02'){echo 'selected';}?> >February</option>
				<option value="03" <?php if(date('m')=='03'){echo 'selected';}?> >March</option>
				<option value="04" <?php if(date('m')=='04'){echo 'selected';}?> >April</option>
				<option value="05" <?php if(date('m')=='05'){echo 'selected';}?> >May</option>
				<option value="06" <?php if(date('m')=='06'){echo 'selected';}?> >June</option>
				<option value="07" <?php if(date('m')=='07'){echo 'selected';}?> >July</option>
				<option value="08" <?php if(date('m')=='08'){echo 'selected';}?> >August</option>
				<option value="09" <?php if(date('m')=='09'){echo 'selected';}?> >September</option>
				<option value="10" <?php if(date('m')=='10'){echo 'selected';}?> >October</option>
				<option value="11" <?php if(date('m')=='11'){echo 'selected';}?> >November</option>
				<option value="12" <?php if(date('m')=='12'){echo 'selected';}?> >December</option>
			</select>
			
			<label for="<?php echo $tsField;?>_day<?php echo $the_id;?>">Day:</label>
			<input type="text" id="<?php echo $tsField;?>_day<?php echo $the_id;?>" name="<?php echo $tsField;?>_day<?php echo $the_id;?>" size="2" maxlength="2" value="<?php echo date('d');?>" />
			
			<label for="<?php echo $tsField;?>_year<?php echo $the_id;?>">Year:</label>
			<input type="text" id="<?php echo $tsField;?>_year<?php echo $the_id;?>" name="<?php echo $tsField;?>_year<?php echo $the_id;?>" size="4" maxlength="4" value="<?php echo date('Y');?>" />
			
			<br />
			
			<label for="<?php echo $tsField;?>_time<?php echo $the_id;?>">Time: (hh:mm)</label>
			<input type="text" id="<?php echo $tsField;?>_time<?php echo $the_id;?>" name="<?php echo $tsField;?>_time<?php echo $the_id;?>" size="5" maxlength="5" value="<?php echo date('g:i',strtotime("now -3 minutes"));?>" />
			
			<input type="radio" id="<?php echo $tsField;?>_am<?php echo $the_id;?>" name="<?php echo $tsField;?>_ampm<?php echo $the_id;?>" value="am" <?php if(date('a',strtotime("now -3 minutes"))=='am'){echo 'checked';}?> /><label for="<?php echo $tsField;?>_am<?php echo $the_id;?>">am</label>
			
			<input type="radio" id="<?php echo $tsField;?>_pm<?php echo $the_id;?>" name="<?php echo $tsField;?>_ampm<?php echo $the_id;?>" value="pm" <?php if(date('a',strtotime("now -3 minutes"))=='pm'){echo 'checked';}?> /><label for="<?php echo $tsField;?>_pm<?php echo $the_id;?>">pm</label>	
		</fieldset>
        <?php
	endif;
	
	return;
}


//testing functions

function ddprint($var){
	echo '<p><pre>';
	print_r($var);
	echo '</pre></p>';
}
?>