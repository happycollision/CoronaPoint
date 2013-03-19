<?php 
	require_once('../initialize.php');
	
	$calls = Call_Edit::edit_calls($_GET['id']);
    
    messages('Escalated calls require additional information.  Please supply this information below to continue.','warning');
    
    
    $page_title = 'Escalated Call Additional Information';
    if(count($calls)>1){$page_title = 'Escalated Calls Additional Information';}
	include TEMPLATE_PATH.'header.php';
?>


<?php
	if(!empty($calls)): 
?>
<form name="edit_calls" id="edit_calls_form" action="call_action.php" method="post" >

	<div class="header">
        <div class="edit">
        	<input class="button" type="submit" name="submit_all" id="submit_all" value="Save All Calls" />
        </div>
	
	<?php foreach($calls as $call): ?>
        Editing Call with Vendor Control Number: <?php echo $call->UMR_VendorControlNum;?>
    </div>
	<!--header-->
    
    <div class="call <?php $call->class_css();?>" id="call-<?php echo $call->id;?>">
    	<div class="edit">
        	
    	</div><!--edit-->
        <div class="row row_1">
        	<span class="field site">
        		<?php echo $call->site.': '.$call->generalProblem;?>
        		<span class="note">Call opened on <?php echo date('m/d/Y',$call->tsCalled); ?></span>
            </span>
        <div class="anchor"></div></div><!--row_1-->
        
        <div class="row row_2">
			<span class="label">Escalated To:</span>
			<input type="text" id="escalatedTo_<?php echo $call->id;?>" name="escalatedTo_<?php echo $call->id;?>" value="<?php echo $call->escalatedTo;?>" ><br />

			<?php 
				if($call->tsEscalated==0){
					easy_date_time('tsEscalated', $call,'When:',true);
				}else{
					easy_date_time('tsEscalated', $call,'When:');
				}
			?>

        <div class="anchor"></div></div><!--row_2-->
        
            
        <div class="right">
        	<input class="button" type="submit" name="submit_single_<?php echo $call->id;?>" id="submit_single_<?php echo $call->id;?>" value="Save This Call" />
        </div>
            
       <div class="anchor"></div>&nbsp;
    </div>
    <!--call-<?php echo $call->id;?>-->
    
	<div class="header">

<?php endforeach; ?>
        <div class="edit">
        	<input class="button" type="submit" name="submit_all" id="submit_all_bottom" value="Save All Calls" />
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