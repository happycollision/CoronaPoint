<?php 
	require_once('../initialize.php');
	$populate = TRUE;
	
		if(isset($_POST['search_string'])||$search->persistant){
			$populate = FALSE; //keep from getting all calls when all we want is search

			if(isset($_POST['search_string'])) $sql = Search::sql($_POST['search_string']);
			if($search->persistant) $sql = Search::sql($search->persistant);

		}
	if($_POST != null){  //Then a UMR has been confirmed, call has been closed, or has been escallated.
		//There will only be one element in the array, but we actually need the key (not the value) so we will use 'foreach'.		
		if(count($_POST)<2){
            foreach($_POST as $k => $v){
                //If we are confirming, escalating, or closing, there is only one key value pair, else we are saving a note.
                    $the_post = $k;
            }
            //Now to find the id of the call (the format is name_of_key_id), so let's remove the final '_' and the following number
            //first let's find the position of the final '_'
            $pos = strrpos($the_post, '_');
            //now let's get the id
            $the_call_id = substr($the_post, $pos+1);
            //then the field that needs to be changed
            $db_field = substr($the_post, 0, $pos);
		}elseif(isset($_POST['note'])){
            //ddprint($_POST);
            //we are saving a note
            $db_field = 'note';
            $the_call_id = $_POST['note_call_id'];
            $the_value = $_POST['note'];
        }else{
        	//nothing for now.  The $db_filed variable has not been set
        	$db_field = '';
        }
		//Now we have all the form data we need to confirm the UMR in the database.  Time to save.
		//we need to establish an object for the call.
		if(isset($the_call_id))$call = Call::find_by_id($the_call_id);
		
        
        
		//now we need to give the proper value for whatever field we are changing.
		$failure = 'Error CI024: The database can not be altered in that way.  If the problem persists, please contact the web application creator.';
		switch ($db_field) {
			case 'UMR_Closed':
			    $call->UMR_Closed = 1;
			    if($call->save()){
			    	messages("The call UMR with ID number {$call->id} has been confirmed.",'success');
			    	unset($call);
				}else{
					messages($failure,'error');
				}
			    break;
            case 'UMR_Open':
                $call->UMR_Closed = 0;
                if($call->save()){
                    messages("The call UMR with ID number {$call->id} has been unconfirmed.", 'warning');
                    unset($call);
                }else{
					messages($failure,'error');
                }
                break;
			case 'dispatchDo_close':
				$call->dispatchStatus = 1;
                if(!$call->UMR_tsClosed > 0) $call->UMR_tsClosed = time();
			    if($call->save()){
				    messages("The call with ID number {$call->id} has been closed.",'success');
				    unset($call);
				}else{
					messages($failure,'error');
				}
				break;
			case 'dispatchDo_escalate':
				$call->dispatchStatus = 3;
			    if($call->save()){
				    messages("The call with ID number {$call->id} has been escalated.",'success');
				    unset($call);
				}else{
					messages($failure,'error');
				}
				break;
            case 'note':
                //echo $the_call_id.' ----- '.$db->escape_value($the_value);
                $db->query("
                    INSERT INTO notes (call_id, note, ts, user_id) VALUES ('".$the_call_id."','".$db->escape_value($the_value)."',".time().",".$user->id.");
                ");
                break;
			default:
				//messages($failure,'error');
				
				//we'll have to rely on other errors to hold up, since search throws a wrench into this model.
				break;
		}
		
	}
	//ddprint($_SERVER);
	if($populate == TRUE) {
		$calls = Call::all_calls();
	}else{//is a search
		$calls = Call::find_by_sql($sql);
		$calls = Call::populate_info($calls);
	}
	//ddprint($calls);
	
    $page_title = 'Calls';
	include TEMPLATE_PATH.'header.php';
?>

	<div class="header">
        <div class="edit">
        	<div class="heading">Dispatch Status</div>
        </div>
		<a href="create_call.php" class="button">New Call</a>
		<a href="trash.php" class="button">View Deleted Calls</a>
		<div class="expand_all"><?php Call::show_all();?></div>
		<div class="status">UMR Status</div>
		<form class="right" name="search" id="search" action="index.php?search=on" method="post">
			<?php if(isset($_GET['search'])) echo '<a href="index.php">All Calls</a>';?>
			<input class="text" type="search" name="search_string" id="search_string" value="<?php echo $search->persistant;?>"/>
			<input type="submit" class="button" value="Search" name="search_submit" id="search_submit"/>
			<div class="hover search_explanation">Enter a date, and search will look for calls opened near that date.  Any other input will search Vendor and Customer Control Numbers.</div>
		</form>
        <div class="anchor"></div>
    </div><!--header-->

<?php if(!empty($calls)):  ?>   
<?php foreach($calls as $call_series => $call):?>

    <div class="call <?php $call->class_css();?>" id="call-<?php echo $call->id;?>">
    	
    	
    	<div class="edit">
    		<?php $call->dispatch_status();?>
        	<?php if($call->dispatchStatus!=1) { ?>
            <div style="text-align:center">
        		<form id="form_dispatchDo_<?php echo $call->id;?>" action="index.php?<?php $call->form_action_url_params();?>" method="post">
		        	
		        	<input type="submit" class="button" name="dispatchDo_close_<?php echo $call->id;?>" 
		        	id="dispatchDo_close_<?php echo $call->id;?>" value="Close" />
		        	
		        	<?php if($call->dispatchStatus==3) {?>
		        	<a href="#" class="button inactive" id="dispatchDo_escalate_<?php echo $call->id;?>" />Escalate</a>
		        	<?php }else{ ?>
		        	<input type="submit" class="button <?php if($call->dispatchStatus==3) echo 'inactive';?>" name="dispatchDo_escalate_<?php echo $call->id;?>" 
		        	id="dispatchDo_escalate_<?php echo $call->id;?>" value="Escalate" />
		        	<?php } ?>
		        	
	        	</form>
        	</div>
            <?php } ?>
    	</div><!--edit-->
        
        <div class="row row_1">
        	<div class="expander"><?php $call->expand_link();?></div>
       		<?php $call->UMRStatus();?>
        	
            <?php if($call->UMR_Closed==0):?>
        	<a class="field site_problem" href="edit_call.php?id=<?php echo $call->id; ?>" id="site_problem_<?php echo $call->id;?>">
       			<span class="label"><?php echo $call->site;?>:</span>
        		<span class="data"><?php echo $call->generalProblem;?></span>
        		<span class="hover note">&nbsp;&nbsp;Edit Call</span>
        	</a><!--field site_problem-->
            <?php else: ?>
            <span class="field site_problem">
                <span class="label"><?php echo $call->site;?>:</span>
        		<span class="data"><?php echo $call->generalProblem;?></span>
            </span>
            <?php endif;?>
            
            <?php if($call->dispatchStatus==4){?>
            	<span class="label deferred">Call Deferred Until <?php echo date('F j \a\t g:ia',$call->tsDeferredEnd). ' by '.$call->deferredBy;?>
            <?php }?>
            
        <div class="anchor"></div></div><!--row_1-->
        
        <div class="row row_2">
			<span class="field open_date half" id="open_date_<?php echo $call->id;?>">
				<span class="label">Call Opened:</span>
				<span class="data">
					<span class="date"><?php echo $call->date_opened;?></span>
					<span class="time"><?php echo $call->time_opened;?></span>
					<?php if($call->dispatchStatus!=1 || ($call->dispatchStatus==1 && $call->UMR_Closed)) {?>
                        <span class="duration"><?php $call->duration();?></span>
                    <?php } ?>
				</span><!--data-->
			</span><!--field open_date half-->
			
            <span class="field tech quarter" id="tech_<?php echo $call->id;?>">
            	<span class="label">Tech:</span>
            	<span class="data"><?php echo $call->tech;?></span>
            </span><!--field tech quarter-->
            <span class="field printer quarter" id="printer_<?php echo $call->id;?>">
            	<span class="label">Printer:</span>
            	<span class="data"><?php echo $call->printer();?></span>
            </span><!--field printer quarter-->
        <div class="anchor"></div></div><!--row_2-->
        
        
		<div class="row row_3 hide">
			<span class="field close_date half" id="close_date_<?php echo $call->id;?>">
				<span class="label">Call Escalated:</span>
				<span class="data">
					<?php
						if($call->tsEscalated > 0){
							echo $call->easy->tsEscalated['date_time'] . ' to ' . $call->escalatedTo;
						}else{
							echo 'Never';
						}
					?>
				</span><!--data-->
			</span><!--field close_date half-->
			
            <span class="field operator quarter" id="operator_<?php echo $call->id;?>">
            	<span class="label">Operator:</span>
            	<span class="data"><?php echo $call->operatorName;?></span>
            </span><!--field tech quarter-->
            <span class="field phone quarter" id="phone_<?php echo $call->id;?>">
            	<span class="label">Callback Phone:</span>
            	<span class="data"><?php echo $call->call_back_phone;?></span>
            </span><!--field printer quarter-->
			
		<div class="anchor"></div></div><!--row_3-->
		
		<?php if($call->tsDeferredEnd > 0){?>
		<div class="row row_4 hide">
			<span class="field deferredStart quarter" id="deferredStart_<?php echo $call->id;?>">
				<span class="label">Deferred From:</span>
				<span class="data"><?php echo $call->easy->tsDeferredStart['date_time'];?></span>
			</span><!--field deferredStart quarter-->
			<span class="field deferredEnd quarter" id="deferredEnd_<?php echo $call->id;?>">
				<span class="label">Deferred Until:</span>
				<span class="data"><?php echo $call->easy->tsDeferredEnd['date_time'];?></span>
			</span><!--field deferredEnd quarter-->
			<span class="field deferredBy quarter" id="deferredBy_<?php echo $call->id;?>">
				<span class="label">Deferred By:</span>
				<span class="data"><?php echo $call->deferredBy;?></span>
			</span><!--field deferredBy quarter-->
		<div class="anchor"></div></div><!--row_4-->
		<?php }?>
		
		<div class="UMR hide">
			<div class="UMR_title">UMR Details 
			<?php if($call->UMR_Closed==0):?>
			<span class="note"><a href="edit_call.php?id=<?php echo $call->id; ?>">Edit</a></span>
			<?php endif;?></div>
				<div class="row">
					<span class="label">Vender Control #:</span> <span class="text"><?php echo $call->UMR_VendorControlNum;?></span> 
					<span class="label">Customer Control #:</span> <span class="text"><?php echo $call->UMR_CustomerControlNum;?></span> 
				
					<span class="label">System:</span> Printer <?php echo $call->printer();?>
				</div>
				
				<div class="row"><span class="label">Explanation of Problem:</span> <?php echo $call->UMR_Explanation_Edit;?></div>
				
				<div class="label">Action Taken by Vendor</div>
				<div class="text_area"><?php echo $call->UMR_ActionTaken;?></div>
				
            		<div class="row cause"><span class="label">General Cause of Incident:</span><div class="text_area">
            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" id="UMR_GeneralCause_r1_<?php echo $call->id; ?>" 
            			value="1" disabled <?php if($call->UMR_GeneralCause==1) echo 'checked';?> />NTF
            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" id="UMR_GeneralCause_r2_<?php echo $call->id; ?>" 
            			value="2" disabled <?php if($call->UMR_GeneralCause==2) echo 'checked';?> />Hardware
            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" id="UMR_GeneralCause_r3_<?php echo $call->id; ?>" 
            			value="3" disabled <?php if($call->UMR_GeneralCause==3) echo 'checked';?> />Supply
            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" id="UMR_GeneralCause_r4_<?php echo $call->id; ?>" 
            			value="4" disabled <?php if($call->UMR_GeneralCause==4) echo 'checked';?> />Temperature
            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" id="UMR_GeneralCause_r5_<?php echo $call->id; ?>" 
            			value="5" disabled <?php if($call->UMR_GeneralCause==5) echo 'checked';?> />Operator
            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" id="UMR_GeneralCause_r6_<?php echo $call->id; ?>" 
            			value="6" disabled <?php if($call->UMR_GeneralCause==6) echo 'checked';?> />Power
            		</div></div>
				
				<div class="row"><span class="label">CE Notified:</span><span class="date_time"><span class="text"><?php echo $call->easy->UMR_tsCENotified['date_time']; ?></span></span></div>
				
				<div class="row"><span class="label">CE Arrived:</span><span class="date_time"><span class="text"><?php echo $call->easy->UMR_tsCEArrived['date_time']; ?></span></span></div>
				
				<div class="row"><span class="label">Sys Returned to IRS:</span><span class="date_time"><span class="text"><?php echo $call->easy->UMR_tsClosed['date_time']; ?></span></span></div>
				
				<div class="label">Error Codes: <span class="note"></span></div>
				<div class="text_area"><?php
					if(is_array($call->UMR_ErrorCodes)){
						foreach($call->UMR_ErrorCodes as $code){
							$codes[] = "{$code->code}<span class=\"error_code_name\">{$code->name}</span>";
						}
						$all_codes = implode('</span> <span class="error_code">',$codes);
						echo '<span class="error_code">'.$all_codes.'</span>';
					}
				?></div>
				
				<div class="row"><span class="label">Total Call Time:</span> <?php echo $call->UMR_total_call_time;?></div>
				
				<div class="row"><span class="label">Call Incurred Downtime:</span> <input type="checkbox" <?php if($call->UMR_IsDowntime==1)echo 'checked';?> disabled />
				
				<span class="label">Deferred Time: <span class="note"> (hh:mm)</span></span> <span class="text"><?php echo $call->UMR_DeferredTime_nice; ?></span></div>
				
				<div class="row"><span class="label">Total Downtime:</span> <span style="color:#900"><?php echo $call->UMR_total_down_time;?></span>
					<?php //this section will display a button if the UMR Status is not closed, otherwise, it will display a message that the call is closed
						if($call->UMR_Closed!=1){
							?>
							<span class="right">
								<form id="form_UMR_Confirm_<?php echo $call->id?>" name="form_UMR_Confirm_<?php echo $call->id?>" action="index.php?<?php $call->form_action_url_params();?>" method="post" >
									<input type="submit" class="button" name="UMR_Closed_<?php echo $call->id ?>" id="UMR_Closed_<?php echo $call->id; ?>" value="Confirm UMR" />
								</form>
							</span>
							<?php
						}else{
							?>
							<span class="right umr_confirmed">
                                UMR Confirmed
                                <br>
                                <form id="form_UMR_Confirm_<?php echo $call->id?>" name="form_UMR_Confirm_<?php echo $call->id?>" action="index.php?<?php $call->form_action_url_params();?>" method="post" >
									<input type="submit" class="button" name="UMR_Open_<?php echo $call->id ?>" id="UMR_Open_<?php echo $call->id; ?>" value="Unconfirm UMR" />
								</form>
                            </span>
							<?php
						}
					?>
					
				</div>
		
		
		</div><!--UMR-->
        
        <div class="call_notes hide">
            <?php if(isset($_GET['note']) && $_GET['note']==$call->id){?>
            <div class="notes_title">Notes</div>
            <form id="form_addNote_<?php echo $call->id;?>" name="form_addNote_<?php echo $call->id;?>" action="index.php?<?php $call->form_action_url_params('-note');?>" method="post">
                <textarea class="text_area" name="note" id="note"></textarea>
                <input type="hidden" name="note_call_id" id="note_call_id" value="<?php echo $call->id;?>" />
                <input type="submit" class="button" name="submit_note_<?php echo $call->id;?>" id="submit_note_<?php echo $call->id;?>" value="Save Note" />
            </form>
            <?php }else{ ?>
            <div class="notes_title">Notes
            <a class="add_note button" href="?<?php $call->form_action_url_params("note=$call->id");?>">Add a Note</a></div>
            <?php }?>
            <?php
                if(is_array($call->notes)){ foreach($call->notes as $note){?>
                    <div class="single_note">
                        <?php echo $note['note'];?>
                        <div class="note"><?php echo $note['user_name']?></div>
                    </div>
                <?php }}
            ?>
                
        
        </div><!--call_notes-->
		
		<div class="footnote hide">Call entered into system by <?php echo $call->created_by_user;?> on <?php echo format_date_string("F jS, \a\\t g:ia",$call->tsCreated);?></div>
        <div class="anchor"></div>
    </div>
    <!--call-<?php echo $call->id;?>-->
    

<?php endforeach;?>

<?php	
else: 
	if(isset($_POST['search_string'])) {
		echo '<p style="margin:20px;">There are no calls matching your search. Currently, search will determine if you have entered a date then check for calls opened near that date.  If the search determines you have not entered a date, it will check whatever you searched on and try to find an exact match in either the Vendor Control Number or in the Customer Control Number.</p>';
	}else{
		echo '<p style="margin:20px;">There are no calls. You might want to <a href="create_call.php">create a new one</a>.</p>';
	}
endif;
?>


<?php include TEMPLATE_PATH.'footer.php'; ?>