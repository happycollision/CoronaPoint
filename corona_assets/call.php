<?php
// If it's going to need the database, then it's
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class Call extends DatabaseObject {
	
	protected static $table_name="calls";
	protected static $db_fields = array('id','generalProblem','tsCreated','tsCalled','operatorName','callBackPhone','UMR_Closed','dispatchStatus','escalatedTo','tsEscalated','site_id','printer_id','createdByUser_id','tech_id','UMR_Explanation','UMR_ActionTaken','UMR_tsCENotified','UMR_tsCEArrived','UMR_tsClosed','UMR_VendorControlNum','UMR_IsDowntime','UMR_DeferredTime','UMR_GeneralCause','UMR_CustomerControlNum','tsDeleted','tsDeferredEnd','tsDeferredStart','deferredBy');
	
	//ids
	public $id;
	public $createdByUser_id;
	public $site_id;
	public $printer_id;
	public $tech_id;
	
	//other DB fields
	public $generalProblem;
	public $tsCreated;
	public $tsCalled;
	public $operatorName;
	public $callBackPhone;
	public $UMR_Closed;        //1=closed 0=open
	public $dispatchStatus;    //1=open 2=closed 3=escalated
	public $tsEscalated;
	public $escalatedTo;
	public $tsDeferredEnd;
	public $tsDeferredStart;
	public $deferredBy;
	public $tsDeleted;

	//UMR data in db
	public $UMR_Explanation;
	public $UMR_ActionTaken;
	public $UMR_tsCENotified;
	public $UMR_tsCEArrived;
	public $UMR_tsClosed;
	public $UMR_VendorControlNum;
	public $UMR_CustomerControlNum;
	public $UMR_IsDowntime;
	public $UMR_DeferredTime;
	public $UMR_GeneralCause;	//1=NTF 2=Hardware 3=Supply 4=Temperature 5=Operator 6=Power
	
	//calculated values below this point
	public $UMR_ErrorCodes; //will be an array of all error codes for the call
	public $duration;
	public $expanded;
	public $UMR_Explanation_Edit;	//will be equal to $generalProblem if $UMR_Explanation is NULL, else will be $UMR_Explanation
	public $date_opened;
	public $date_closed;
	public $time_opened;
	public $time_closed;
	public $call_back_phone;
	public $easy;               //will contain lots of translated times
	public $UMR_total_call_time;
	public $UMR_total_down_time;
	public $UMR_DeferredTime_nice; //The readable version of DeferredTime (HH:MM)
    public $notes;
	
	//Names: Names for ids listed at top
	public $created_by_user;
	public $site;
	public $printer;
	public $printerSerial;
	public $tech;
	
	public static function all_calls($orderby='dispatchStatus DESC, UMR_Closed, tsCalled DESC', $paginate = TRUE){// Create object with all calls (on current page by default)
		$pageSQL = null;
		if($paginate == TRUE){
			global $pagination;
			$pagination = new Pagination('live_calls');
			$pageSQL = "
				LIMIT {$pagination->per_page}
				OFFSET {$pagination->offset()}
			";
		}
		
		if(!empty($orderby)){
			$orderby = " ORDER BY $orderby ";
		}

		$sql = "SELECT * FROM calls WHERE tsDeleted IS NULL $orderby $pageSQL";
		$calls = static::find_by_sql($sql);

		$calls = self::populate_info($calls);
		return $calls;
	}
	
	public function edit_prep($id){
		$call = static::find_by_id($id);
		//work around the FOREACH nature of the functions below
		$calls[]=$call;
		$calls = self::populate_info($calls);
		$calls[0]->expanded = 1;
		return $calls[0];
	}
	
	public function duration(){## echos duration. WILL EVENTUALLY HANDLE jQuery FOR TIMER if necessary
		echo $this->duration;
	}
	
	public static function show_all(){// writes the HTML for switching the default view from expanded to hidden
		//first, let's get all the non-show/hide stuff and protect it
		$get_protect = '';
		if(isset($_GET)){
			foreach($_GET as $key => $val){
				if($key != 'expand' && $key != 'expanded' && $key != 'hidden'){
					$get_protect[$key] = "{$key}={$val}";
				}
			}
		}
		if(is_array($get_protect)){
			$get_protect = '&'.implode('&',$get_protect);
		}
		
		if(!isset($_GET['expanded'])&&!isset($_GET['hidden'])){
			echo Call::all_expanded()? '<a href="?expand=0'.$get_protect.'">Hide All</a>':'<a href="?expand=1'.$get_protect.'">Show All</a>';
			return;
		}
		if(isset($_GET['expanded']))$exceptions = explode(',',$_GET['expanded']);
		if(isset($_GET['hidden']))$exceptions = explode(',',$_GET['hidden']);
		global $calls;
		$tipped = 'no';
		if(count($exceptions)>(count($calls)/2)) $tipped = 'yes';
		
		if($tipped=='yes'){
			echo Call::all_expanded()? '<a href="?expand=1'.$get_protect.'">Show All</a>':'<a href="?expand=0'.$get_protect.'">Hide All</a>';
		}else{
			echo Call::all_expanded()? '<a href="?expand=0'.$get_protect.'">Hide All</a>':'<a href="?expand=1'.$get_protect.'">Show All</a>';
		}
		return;
	}
	
	private static function all_expanded(){// returns true if calls on page are expanded by default
		if(!isset($_GET['expand']) || $_GET['expand']==0) return false;
		if($_GET['expand']==1) return true;
	}
	
	public function expand_link(){// writes the HTML for toggling $this call view from expanded to hidden
		//is the page expanded or hidden by default? Define array as opposite
		$array_name = Call::all_expanded()? 'hidden' : 'expanded' ;
		
		//create current url minus the 'expanded' or 'hidden' key
		$new_link = '?';
		if(isset($_GET)){
			foreach($_GET as $key=>$value){
				if($key!=$array_name){
					$new_link.= "{$key}={$value}&";
				}
			}
		}
		
		//create an array of the current non-conformists
		$non_cons = array();
		if(isset($_GET[$array_name])){
			$non_cons= explode(',',$_GET[$array_name]);
		}
		
		//look for the id in the current non-cons
		//if found, let's subtract it
		$action = 'add';
		foreach($non_cons as $key=>$non_con){
			if($non_con == $this->id){
				unset($non_cons[$key]);
				$action = 'subtract';
			}
		}
		//if not found, let's add it
		if($action=='add'){
			$non_cons[]=$this->id;
		}
		//create the new link
		if(count($non_cons)>0){
			$new_link.=$array_name.'='.implode(',',$non_cons);
		}
		
		//decide whether to "show" or "hide"
		if($array_name=='hidden')$text = $action=='add' ? 'Hide' : 'Show';
		if($array_name=='expanded')$text = $action=='subtract' ? 'Hide' : 'Show';
		
		//remove '&' at end of string if there
		$new_link = rtrim($new_link,'&');
		echo "<a href=\"{$new_link}#call-{$this->id}\">{$text}</a>";
		return;
	}
	
	public function form_action_url_params($new_params=''){ //echoes the proper $_GET info into the action value of forms so that page navigation is not affected with button returns
		if(isset($_GET)){
			$url_params = '';
            $final_param_array=array();
            if(!empty($new_params)){
                $new_params_array = explode('&',$new_params);
                foreach($new_params_array as $k => $param_set){
                    if(strstr($param_set,'-')==true){
                        $negative_keys[] = substr($param_set,1);
                        unset($new_params_array[$k]);
                        continue;
                    }
                    $single_param = explode('=',$param_set);
                    $final_param_array[$single_param[0]] = $single_param[1];
                }
            }
			foreach($_GET as $k => $v){
                if(count($final_param_array)>0){ foreach($final_param_array as $new_k => $new_v){
                    if($k==$new_k){
                        $v=$new_v;
                        unset($final_param_array[$new_k]);
                    }
                }}
                $url_params .= "{$k}={$v}&";
            }
            if(count($final_param_array)>0){
                foreach($final_param_array as $k => $v){
                    $url_params .= "{$k}={$v}&";
                }
            }
		}
		$url = $url_params . '#call-' . $this->id;
		
		echo $url;
		return;
	}
	
	public function class_css($echo=TRUE){// creates the string containing class info for the call
		$status = $this->dispatch_status(true,false);
		$expanded = $this->expanded==1? '':'hidden';
		$output = "call {$status} {$expanded}";
		
		if($echo==false) return $output;
		echo $output;
	}
	
	public function dispatch_status($css=FALSE, $echo=TRUE){// returns or echos(default) the CSS class or HTML(default) of the call's status
		switch($this->dispatchStatus){
			case '2':
				$status = 'Open';
				$s = 'open';
				break;
			case '1':
				$status = 'Closed';
				$s = 'closed';
				break;
			case '3':
				$status = 'Escalated';
				$s = 'escalated';
				break;
			case '4':
				$status = 'Deferred';
				$s = 'deferred';
				break;
			default:
				$status = 'Unknown';
				$s = 'unknown';
				break;
		}

		if($css==true){
			if($echo==false) return $s;
			echo $s;
			return;
		}
		
		$output = '<div class="status '.$s.'">'.$status.'</div>';
		
		if($echo==false) return $output;
		echo $output;
	}
	
	public function UMRStatus($css=FALSE, $echo=TRUE){// returns or echos(default) the CSS class or HTML(default) of the call's UMR status
			$approved=false;
		if($this->UMR_Closed==1){
			$status = 'Closed';
			$s = 'closed';
			$approved = true;
		}
		
		if($css==true){
			if($echo==false) return $s;
			echo $s;
			return;
		}
		
		if($approved==true){$output = '<span class="status approval approved"><span>Completed</span></span>';
		}else{
			$output = '<span class="status approval"><span>Needs Attention</span></span>';
		}
		
		if($echo==false) return $output;
		echo $output;
	}
	
	public function printer($echo=TRUE){## (UNFINISHED) Displays printer information For use on Printer Info page, not call page.
		$output = $this->printer;
		if($echo==false) return $output;
		echo $output;
	}
	
	
	################  Calculating functions called when list is first populated  ########################

	
	public static function populate_info($calls){//sends all calls to calculating functions below
		if(is_object(current($calls))){
			$calls = self::call_notes($calls);
			$calls = self::ids_to_name($calls);
			$calls = self::open_for($calls);
			$calls = self::calls_expanded($calls);
			$calls = self::friendly_times($calls);
			$calls = self::callBackPhone($calls);
			$calls = self::general_explanation($calls);
			//$calls = self::error_codes($calls);
			$calls = self::downtime_calc($calls);
			$calls = self::escallation_check($calls);
			$calls = self::deferrment_check($calls);
			$calls = ErrorCode::populate_errors($calls);
			$calls = self::pretty_text($calls);
			
			$tsFields = array('tsCalled','tsEscalated','UMR_tsCENotified','UMR_tsCEArrived','UMR_tsClosed','tsDeferredEnd','tsDeferredStart');
			$calls = readable_dates($calls, $tsFields);  //in functions.php
		
		}

		return $calls;
	}
	private static function pretty_text($calls){//General formating cleanup
		foreach($calls as $call){
			$call->generalProblem = ucwords($call->generalProblem);
		}
		return $calls;
	}
    
    private static function escallation_check($calls){
    	$bad_ids = array();
    	foreach($calls as $call){
    		if($call->dispatchStatus==3 && ($call->escalatedTo==NULL || $call->tsEscalated==0)){
    			$bad_ids[] = $call->id;
    		}
    	}
    	if(count($bad_ids) > 0 && stristr($_SERVER['PHP_SELF'],'/calls/index.php')){
    		$id_string = implode(',',$bad_ids);
    		redirect_to(URL.'/calls/escalated_call.php?id='.$id_string);
    	}
    	return $calls;
    }
    
    private static function deferrment_check($calls){
    	$bad_ids = array();
    	foreach($calls as $call){
    		if($call->dispatchStatus==4 && ($call->deferredBy==NULL || $call->tsDeferredEnd==0|| $call->tsDeferredStart==0)){
    			$bad_ids[] = $call->id;
    		}
    	}
    	if(count($bad_ids) > 0 && stristr($_SERVER['PHP_SELF'],'/calls/index.php')){
    		$id_string = implode(',',$bad_ids);
    		redirect_to(URL.'/calls/deferred_call.php?id='.$id_string);
    	}
    	return $calls;
    }
    
    private static function call_notes($calls){ //brings in notes for all calls
        global $db;
        //lets get just the ids for the calls we need
        foreach($calls as $call){$calls_ids[]=$call->id;}
        $id_string = implode(',',$calls_ids);
        
        $sql="SELECT * FROM notes WHERE call_id IN($id_string)";
        
        $result = $db->query($sql);
        while($row = $db->fetch_array($result)){
            $notes_array[$row['call_id']][$row['id']]['note'] = $row['note'];
            $notes_array[$row['call_id']][$row['id']]['ts'] = $row['ts'];
            $notes_array[$row['call_id']][$row['id']]['user_name'] = $row['user_id']; //temporary value will be changed in function ids_to_name() below.
        }
        foreach($calls as $call){ 
            $call->notes = isset($notes_array[$call->id]) ? $notes_array[$call->id] : ''; 
        }
        return $calls;
    }
	
	private static function ids_to_name($calls){//assigns the "Name" class vars
		global $database;
		//create a multi dimensional array with all ids as keys and all corresponding names as values ie:
		/*
		Array
			[sites] => Array(
				[1] => Detroit
				[2] => Odgen)
			[techs] => Array(
				[1] => Mike DeVito
				[2] => Don Denton)
			[printers] => Array(
				[1] => #51)
			[users] => Array(
				[1] => Don Denton
				[2] => Art Denton
				[3] => Mike DeVito)
		*/
		//Make ONE call to the database. Result will be large, but should still be faster than using multiple calls.
		$result = $database->query("
			SELECT 
				s.id AS 'site id', s.city,
				t.id AS 'tech id', t.firstName AS 'tech first', t.lastName AS 'tech last', 
				p.id AS 'printer id', p.system AS 'printer name', p.serialNumber AS 'printer serial',
				u.id AS 'user id', u.firstName AS 'user first', u.lastName AS 'user last'
			FROM
				sites AS s,
				techs AS t,
				printers AS p,
				users AS u
		");
		
		$id_name = array();
		
		//parse the result set and feed into the $id_name array.
		while ($row = $database->fetch_array($result)) {
						
			$site_id = $row['site id'];
			$city_name = $row['city'];
			$id_name['sites'][$site_id] = $city_name;
			
			$tech_id = $row['tech id'];
			$tech_name = $row['tech first'].' '.$row['tech last'];
			$id_name['techs'][$tech_id] = $tech_name;
			
			$printer_id = $row['printer id'];
			$printer_name = $row['printer name'].' SN: '.$row['printer serial'];
			$id_name['printers'][$printer_id] = $printer_name;
			
			$user_id = $row['user id'];
			$user_name = $row['user first'].' '.$row['user last'];
			$id_name['users'][$user_id] = $user_name;
			
		}//array created.
		
		//use new array to populate values in each call object
		foreach($calls as $call){
			$call->created_by_user = $id_name['users'][$call->createdByUser_id];
			$call->site = $id_name['sites'][$call->site_id];
			$call->printer = $id_name['printers'][$call->printer_id];
			$call->tech = $id_name['techs'][$call->tech_id];
            if(is_array($call->notes)){
                foreach($call->notes as $k => $note){
                    $call->notes[$k]['user_name'] = $id_name['users'][$note['user_name']]; //see function call_notes() above;
                }
            }
		}
		return $calls;
	}
	
	private static function open_for($calls){//calculates time opened into $this->duration
		foreach($calls as $call){
			$start = $call->tsCalled;
			if($call->UMR_tsClosed>0) {
				$end = $call->UMR_tsClosed - $call->UMR_DeferredTime;
				$start = $call->UMR_tsCENotified;
			}else{
				$end = date("Y-m-d H:i:s");
			}
			$diff = dateDiff($start,$end,2);
			$call->duration = $diff;
		}
		return $calls;
	}
	
	private static function calls_expanded($calls){// changes $this->expanded to '1' or '0' accordingly
		//if no exceptions, then the rule
		if(!isset($_GET['expanded'])&&!isset($_GET['hidden'])){
			foreach($calls as $call){
				$call->expanded = Call::all_expanded()? 1: 0;// the rule
			}
			return $calls;
		}
		//if exceptions, obey them
		if(isset($_GET['expanded']))$exceptions = explode(',',$_GET['expanded']);
		if(isset($_GET['hidden']))$exceptions = explode(',',$_GET['hidden']);
		foreach($calls as $call){
			$call->expanded = Call::all_expanded()? 1: 0;// the rule
			
			foreach($exceptions as $id){
				if($call->id == $id) $call->expanded = $call->expanded==1? 0: 1; // the exception
			}
		}
		return $calls;
	}
	
	private static function friendly_times($calls){// creates user friendly times in $this->date_opened, etc.
		foreach($calls as $call){
			$call->date_opened = format_date_string("m/d/Y",$call->tsCalled);
			$call->time_opened = format_date_string("g:ia",$call->tsCalled);
			if(empty($call->tsDispatchClosed)){
				$call->date_closed = $call->time_closed = '';
			}else{
				$call->date_closed = format_date_string("m/d/Y",$call->tsDispatchClosed);
				$call->time_closed = format_date_string("g:ia",$call->tsDispatchClosed);
			}
		}
		return $calls;
	}
		
	private static function callBackPhone($calls){// formats $this->callBackPhone into $this->call_back_phone
		global $sites;
		foreach($calls as $call){
			$output = null;
			if(!empty($call->callBackPhone)){
				$phone = $call->callBackPhone;
				$phone = preg_replace("/[^0-9]/", "", $phone);
				
				if(strlen($phone) == 7)
					$output = preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
				elseif(strlen($phone) == 10)
					$output = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
				else
					$output = $phone;
			}
			if($output == null){
				foreach($sites as $site){ if($site->id == $call->site_id) {
					$output = $site->sitePhone;
				}}
			}
			$call->call_back_phone = $output;
		}
		
		return $calls;
	}
	
	private static function general_explanation($calls){
		foreach($calls as $call){
			$call->UMR_Explanation===NULL ? $call->UMR_Explanation_Edit = $call->generalProblem : $call->UMR_Explanation_Edit = $call->UMR_Explanation;
		}
		return $calls;
	}
	
	private static function error_codes($calls){
		global $database;
		foreach($calls as $call){
			//create array of all call ids
			$all_call_ids[]=$call->id;
		}
		if(isset($all_call_ids) && is_array($all_call_ids)) {
            $all_call_ids_sep = implode(',', $all_call_ids);
        }else{
            $all_call_ids_sep = '0';
        }

		//create array of all needed error code info based on call ids
		$result = $database->query("
			SELECT 
				x.error_code_id AS eid, 
				e.name AS name,
				e.code AS code 
			FROM
				calls_x_error_codes AS x
			LEFT JOIN
				error_codes AS e ON(e.id=x.error_code_id)
			WHERE 
				x.call_id IN($all_call_ids_sep)
		");
		while ($row = $database->fetch_array($result)){
			$called_codes[$row['eid']]= array('code'=>$row['code'],'name'=>$row['name']);
		}
		//ddprint($called_codes);
		
		//create array from calls_x_error_codes using only necessary call ids
		$result = $database->query("
			SELECT * FROM calls_x_error_codes WHERE call_id IN($all_call_ids_sep)
		");
		while ($row = $database->fetch_array($result)){
			$calls_and_codes[$row['call_id']][]=$row['error_code_id'];
		}
		//ddprint($calls_and_codes);
		
		//merge the arrays for each call
		foreach($calls as $call){
			if(isset($calls_and_codes[$call->id])){
				foreach($calls_and_codes[$call->id] as $code_id){
					$call->UMR_Error_Codes[$code_id] = $called_codes[$code_id];
				}
			}
		//ddprint($call->UMR_Error_Codes);
		}
		
		return $calls;
	}
	
	private static function downtime_calc($calls){
		foreach($calls as $call){
			$seconds = $call->UMR_DeferredTime;
			if($call->UMR_tsCENotified > 1 && $call->UMR_tsClosed > $call->UMR_tsCENotified){
				$call->UMR_total_call_time = dateDiff($call->UMR_tsCENotified, $call->UMR_tsClosed);
				
				if($call->UMR_IsDowntime != 1){
					$call->UMR_total_down_time = 'none';
				}elseif($call->UMR_DeferredTime != null && $call->UMR_IsDowntime == 1){
					//DEPRICATED
					/*$def_time_array = explode(':',$call->UMR_DeferredTime);
					$def_time_array[0] = $def_time_array[0]*60*60; //hours to seconds
					if(isset($def_time_array[1])){
						$def_time_array[1] = $def_time_array[1]*60; //minutes to seconds
					}else{
						$def_time_array[1] = 0;
					}
					$seconds = $def_time_array[0] + $def_time_array[1];*/ //DEPRICATED
					
					$call->UMR_total_down_time = dateDiff($call->UMR_tsCENotified, ($call->UMR_tsClosed - $seconds));
				}else{
					$call->UMR_total_down_time = $call->UMR_total_call_time;
				}
			}
			//Let's set a nice, user readable version to echo in the call screens
			$remainder=$seconds/60 % 60;
			$number=explode('.',($seconds / 60 / 60));
			$answer=$number[0];
			$remainder = sprintf("%02d", $remainder);
	
			$call->UMR_DeferredTime_nice = "{$answer}:{$remainder}";
		}
		return $calls;
	}
	
}