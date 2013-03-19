<?php 
	require_once('../initialize.php');
	
	$calls = false;
    if(!empty($_GET['id'])){
        $calls = Call_Edit::edit_calls($_GET['id']);
    }
    if($calls===false||!isset($_GET['id'])){
        $session->message('The edit page needs a parameter that wasn\'t set.  Please try again. <br /><br /> Be sure you haven\'t bookmarked the edit page.  You must start here first.  If the problem persists, please contact the application developer.','warning');
        redirect_to('index.php');
    }

    FormError::error_check();
    
    $page_title = 'Edit Call';
    if(count($calls)>1){$page_title = 'Edit Calls';}
	include TEMPLATE_PATH.'header.php';
?>


<?php
	if(!empty($calls)): 
?>
<form name="edit_calls" id="edit_calls_form" action="call_action.php" method="post" >

	<div class="header">
        <div class="edit">
        	<!--<input class="button" type="submit" name="submit_all" id="submit_all" value="Save All Calls" />-->
        </div>
	
	<?php foreach($calls as $call): if($call->UMR_Closed==0):?>
        Editing Call with Vendor Control Number: <?php echo $call->UMR_VendorControlNum;?>
    </div>
	<!--header-->
    
    
    <div class="call <?php $call->class_css();?>" id="call-<?php echo $call->id;?>">
    	<div class="edit">
        	<input type="checkbox" name="deleteCall_<?php echo $call->id;?>" id="deleteCall+<?php echo $call->id;?>" value="deleteCall_<?php echo $call->id;?>" /><label for="deleteCall_<?php echo $call->id;?>"> Delete this call</label>
    	</div><!--edit-->
        <div class="row row_1">
        	<span class="field site"><?php echo isset($_GET['site']) ? $sites[$_GET['site']]->city : $call->site;?> <span class="note"><a href="choose_site.php?change=1&id=<?php echo $call->id;?>">Change Site</a>
        		(After changing sites, you'll have to re-select a printer)</span>
        	</span>
        	<input type="hidden" name="site_id_<?php echo $call->id;?>" id="site_id_<?php echo $call->id;?>"
        	value="<?php echo isset($_GET['site']) ? $_GET['site'] : $call->site_id; ?>" />
        	
            <div><span class="field description">General Problem:
            	<input type="text" size="50" name="generalProblem_<?php echo $call->id; ?>" id="generalProblem_<?php echo $call->id; ?>" value="<?php echo $call->generalProblem; ?>" />
            </span></div>
        <div class="anchor"></div></div><!--row_1-->
        
        <div class="row row_2">
        	<span class="left_col">
                <span class="field date_logged"><span class="label">Call Opened: </span><br />
                    <?php easy_date_time('tsCalled',$call);?>
                </span>
            </span><!--left_col-->
            
            <span class="right_col">
            	<span class="field tech"><span class="label">Tech: </span>
            		<?php $call->tech_dropdown_static();?>
            	</span><!--field tech-->
            </span><!--right_col-->
        <div class="anchor"></div></div><!--row_2-->
        
        	<div class="row row_3">
                <span class="left_col">
                    <span class="field date_logged"><span class="label">Dispatch Status: </span>
                    <input type="radio" id="dispatchStatus_open_<?php echo $call->id;?>" name="dispatchStatus_<?php echo $call->id;?>" value="2" <?php if($call->dispatchStatus==2){echo 'checked';}?> ><label for="dispatchStatus_open_<?php echo $call->id;?>" >Open</label>
                    <input type="radio" id="dispatchStatus_escalated_<?php echo $call->id;?>" name="dispatchStatus_<?php echo $call->id;?>" value="3" <?php if($call->dispatchStatus==3){echo 'checked';}?> ><label for="dispatchStatus_escalated_<?php echo $call->id;?>" >Escalated</label>
                    <input type="radio" id="dispatchStatus_closed_<?php echo $call->id;?>" name="dispatchStatus_<?php echo $call->id;?>" value="1" <?php if($call->dispatchStatus==1){echo 'checked';}?> ><label for="dispatchStatus_closed_<?php echo $call->id;?>" >Closed</label>
                    <input type="radio" id="dispatchStatus_deferred_<?php echo $call->id;?>" name="dispatchStatus_<?php echo $call->id;?>" value="4" <?php if($call->dispatchStatus==4){echo 'checked';}?> ><label for="dispatchStatus_deferred_<?php echo $call->id;?>" >Deferred</label>
                    
                    <fieldset>
                    <legend>If Escalated</legend>
                    <span class="label">To whom:</span>
                    <input type="text" id="escalatedTo_<?php echo $call->id;?>" name="escalatedTo_<?php echo $call->id;?>" value="<?php echo $call->escalatedTo;?>" ><br />
                    <span class="label">When:</span>
                    <?php easy_date_time('tsEscalated', $call);?>
                    </fieldset>
                    
                    <fieldset>
                    <legend>If Deferred</legend>
                    <span class="label">By whom:</span>
                    <input type="text" id="deferredBy_<?php echo $call->id;?>" name="deferredBy_<?php echo $call->id;?>" value="<?php echo $call->deferredBy;?>" ><br />
                    <span class="label">Starting:</span>
                    <?php easy_date_time('tsDeferredStart', $call);?><div class="anchor"></div>
                    <span class="label">Until:</span>
                    <?php easy_date_time('tsDeferredEnd', $call);?>
                    </fieldset>
                    
                </span>
                </span><!--left_col-->
            	<span class="right_col">
                    <span class="field operator"><span class="label">Operator: </span>
                    	<input type="text" size="25" name="operatorName_<?php echo $call->id; ?>" id="operatorName_<?php echo $call->id; ?>" value="<?php echo $call->operatorName; ?>" />
                    </span><!--field operator-->
                    
                </span><!--right_col-->
            <div class="anchor"></div></div><!--row_3-->
            
        	<div class="row row_3">
                <span class="left_col">
                    <span class="label">Printer: </span>
                    <?php $call->printer_dropdown(isset($_GET['site']) ? $_GET['site'] : null); ?>
                </span><!--left_col-->
            	<span class="right_col">
					<span class="field"><span class="label">Callback Phone: </span>
                    	<input type="text" size="20" name="callBackPhone_<?php echo $call->id; ?>" id="callbackPhone_<?php echo $call->id; ?>" value="<?php echo $call->callBackPhone; ?>" /><span class="note"> (Leave&nbsp;blank&nbsp;for&nbsp;default&nbsp;site&nbsp;phone)</span>
                    </span>
                </span><!--right_col-->
            <div class="anchor"></div></div><!--row_3-->
            
            <div class="UMR edit">
                <div class="UMR_title">Additional UMR Details</div>
            		
            		<div class="row">
            			<span class="label">Vendor Control #:</span>
            			<input class="text" type="text" name="UMR_VendorControlNum_<?php echo $call->id; ?>" id="UMR_VendorControlNum_<?php echo $call->id; ?>" value="<?php echo $call->UMR_VendorControlNum;?>" />

            			<span class="label">Customer Control #:</span>
            			<input class="text" type="text" name="UMR_CustomerControlNum_<?php echo $call->id; ?>" id="UMR_CustomerControlNum_<?php echo $call->id; ?>" value="<?php echo $call->UMR_CustomerControlNum;?>" />
            		</div>
            		
            		<div class="row"><span class="label">Explanation of Problem: <span class="note">(Leave blank to default to "General Problem" above)</span></span> <textarea class="text_area" name="UMR_Explanation_<?php echo $call->id; ?>" id="UMR_Explanation_<?php echo $call->id; ?>" rows="8"><?php echo $call->UMR_Explanation;?> </textarea></div>
            		
            		<div class="row cause"><span class="label">General Cause:</span><div class="text_area">
	            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" 
	            		id="UMR_GeneralCause_r1_<?php echo $call->id; ?>" value="1" <?php if($call->UMR_GeneralCause==1)echo 'checked ';?>/>
	            		<label for="UMR_GeneralCause_r1_<?php echo $call->id; ?>">NTF</label>
	            		
	            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" 
	            		id="UMR_GeneralCause_r2_<?php echo $call->id; ?>" value="2" <?php if($call->UMR_GeneralCause==2)echo 'checked ';?>/>
	            		<label for="UMR_GeneralCause_r2_<?php echo $call->id; ?>">Hardware</label>
	            		
	            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" 
	            		id="UMR_GeneralCause_r3_<?php echo $call->id; ?>" value="3" <?php if($call->UMR_GeneralCause==3)echo 'checked ';?>/>
	            		<label for="UMR_GeneralCause_r3_<?php echo $call->id; ?>">Supply</label>
	            		
	            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" 
	            		id="UMR_GeneralCause_r4_<?php echo $call->id; ?>" value="4" <?php if($call->UMR_GeneralCause==4)echo 'checked ';?>/>
	            		<label for="UMR_GeneralCause_r4_<?php echo $call->id; ?>">Temperature</label>
	            		
	            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" 
	            		id="UMR_GeneralCause_r5_<?php echo $call->id; ?>" value="5" <?php if($call->UMR_GeneralCause==5)echo 'checked ';?>/>
	            		<label for="UMR_GeneralCause_r5_<?php echo $call->id; ?>">Operator</label>
	            		
	            		<input type="radio" name="UMR_GeneralCause_<?php echo $call->id; ?>" 
	            		id="UMR_GeneralCause_r6_<?php echo $call->id; ?>" value="6" <?php if($call->UMR_GeneralCause==6)echo 'checked ';?>/>
	            		<label for="UMR_GeneralCause_r6_<?php echo $call->id; ?>">Power</label>
	            		
            		</div></div>
            		
                    <div class="row"><div class="label">Action Taken by Vendor</div>
                    <textarea class="text_area" type="text" name="UMR_ActionTaken_<?php echo $call->id; ?>" id="UMR_ActionTaken_<?php echo $call->id; ?>" rows="2" /><?php echo $call->UMR_ActionTaken;?></textarea>
                    </div>
                    
                    <div class="row"><span class="label">CE Notified:</span><span class="date_time">
                    	<?php easy_date_time('UMR_tsCENotified', $call);?></span>
                    </div>
                    
                    <div class="row"><span class="label">CE Arrived:</span><span class="date_time">
                    	<?php easy_date_time('UMR_tsCEArrived', $call);?></span>
                    </div>
                    
                    <div class="row"><span class="label">Sys Returned to IRS:</span><span class="date_time">
                    	<?php easy_date_time('UMR_tsClosed', $call);?></span>
                    </div>
                   
                    <div class="row"><div class="label">Error Codes: <span class="note"> (One error code per line)</span></div>
                    <textarea class="text_area" name="UMR_ErrorCodes_<?php echo $call->id; ?>" id="UMR_ErrorCodes_<?php echo $call->id; ?>" cols="80" rows="8"><?php 
						if(is_array($call->UMR_ErrorCodes)){
							foreach($call->UMR_ErrorCodes as $code){
								$codes[] = $code->code;
							}
							$all_codes = implode("\n",$codes); echo $all_codes;
						}
					?></textarea>
                    </div>
                    
                    <!--<div class="row"><span class="label">Total Call Time:</span> 00:45 CALCULATE</div>-->
                    
                    <div class="row"><span class="label">Call Incurred Downtime:</span> <input type="checkbox" name="UMR_IsDowntime_<?php echo $call->id;?>" id="UMR_IsDowntime_<?php echo $call->id;?>" value="1" 
        	<?php
        		if($call->UMR_IsDowntime==1){
        			echo ' checked="checked"';
        		}else{
        			echo ' ';
        		}
        	?> />
                    	<span class="label">Deferred Time: <span class="note"> (hh:mm)</span></span> <input class="text" name="UMR_DeferredTime_<?php echo $call->id; ?>" id="UMR_DeferredTime_<?php echo $call->id; ?>" value="<?php echo $call->UMR_DeferredTime_nice; ?>" />
                    </div>
                    
                    <!--<div class="row"><span class="label">Total Downtime:</span> <span style="color:#900">00:45 CALCULATE</span><input type="submit" name="submit_UMR_<?php echo $call->id;?>" id="submit_UMR_<?php echo $call->id;?>" value="Confirm UMR" /></div>-->
            
            
            </div><!--UMR-->
            
        <div class="right">
        	<input class="button" type="submit" name="submit_single_<?php echo $call->id;?>" id="submit_single_<?php echo $call->id;?>" value="Save This Call" />
        </div>
            
       <div class="anchor"></div>&nbsp;
    </div>
    <!--call-<?php echo $call->id;?>-->
    
	<div class="header">
	
	<?php else: ?>
        Editing Call with Vendor Control Number: <?php echo $call->UMR_VendorControlNum;?>
    </div><!--header-->
    <div class="call <?php $call->class_css();?>" id="call-<?php echo $call->id;?>">
		<div class="edit">
		</div>
        <div class="row row_1">
        	<span class="field site"><?php echo $call->site;?></span>
            <div><span class="field description">General Problem:
            	<span ><?php echo $call->generalProblem; ?></span>
            </span></div>
        <div class="anchor"></div></div><!--row_1-->
        
        <br /><br />
    	<div class="message warning">
    		This call cannot be edited without first unconfirming the UMR.  This can be done on the main call screen.
    	</div><!--message-->
    	<br /><br />

    </div><!--call-->
        	
    
	<div class="header">

<?php endif; endforeach; ?>
        <div class="edit">
        	<!--<input class="button" type="submit" name="submit_all" id="submit_all_bottom" value="Save All Calls" />-->
        </div>
        &nbsp;

</div><!--header-->
</form>

<?php	
	else:
		echo 'There are no calls.';
	endif;
?>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>