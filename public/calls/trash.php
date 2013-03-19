<?php 
	require_once('../initialize.php');
	
	
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
		}
		
		//Now we have all the form data we need to confirm the UMR in the database.  Time to save.
		//we need to establish an object for the call.
		$call = Call::find_by_id($the_call_id);
		
        
        
		//now we need to give the proper value for whatever field we are changing.
		$failure = 'Error CT028: The database can not be altered in that way.  If the problem persists, please contact the web application creator.';
		switch ($db_field) {
			case 'dispatchDo_revive':
				$call->tsDeleted = NULL;
				if($call->save()){
					messages("The call with ID number $call->id has been revived.", 'success');
					unset($call);
				}else{
					messages($failure, 'error');
				}
				break;
			default:
				messages($failure,'error');
				break;
		}
		
	}
	//ddprint($_SERVER);
	
	
	
	
	global $pagination;
	$pagination = new Pagination('deleted_calls');
	$pageSQL = "
		LIMIT {$pagination->per_page}
		OFFSET {$pagination->offset()}
	";
	$calls = Call::find_by_sql("
		SELECT * FROM calls 
		WHERE tsDeleted IS NOT NULL 
		ORDER BY dispatchStatus DESC, UMR_Closed, tsCalled DESC $pageSQL
		");
	$calls = Call::populate_info($calls);	
    
    $page_title = 'Trash: Calls';
	$subtitle = 'Viewing Deleted Calls';
	include TEMPLATE_PATH.'header.php';
?>

	<div class="header">
        <div class="edit">
        	<div class="heading">Dispatch Status</div>
        </div>
        <span class="subtitle"><?php if(isset($subtitle)) echo $subtitle;?></span>
        <a class="note" href="index.php">Return to Active Calls</a>
        	<div class="status">UMR Status</div>
    </div><!--header-->

<?php if(!empty($calls)):  ?>   
<?php foreach($calls as $call_series => $call):?>

    <div class="call <?php $call->class_css();?>" id="call-<?php echo $call->id;?>">
    	
    	
    	<div class="edit">
    		<?php $call->dispatch_status();?>
            <div style="text-align:center">
        		<form id="form_dispatchDo_<?php echo $call->id;?>" action="trash.php?<?php $call->form_action_url_params();?>" method="post">
		        	
		        	<input type="submit" class="button" name="dispatchDo_revive_<?php echo $call->id;?>" 
		        	id="dispatchDo_revive_<?php echo $call->id;?>" value="Revive" />
		        	
		        	
	        	</form>
        	</div>
    	</div><!--edit-->
        
        <div class="row row_1">
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
        
        
        <div class="anchor"></div>
    </div>
    <!--call-<?php echo $call->id;?>-->
    

<?php endforeach;?>

<?php	
else: echo '<p style="margin:20px;">There are no deleted calls. You should be proud of your record keeping prowess.  No, really, I am impressed.</p>';
endif;
?>


<?php include TEMPLATE_PATH.'footer.php'; ?>