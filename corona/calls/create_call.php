<?php 
	require_once('../initialize.php');
	
    backpost();
	if(empty($_GET['site']) && !backpost('site_id')){
		redirect_to('choose_site.php');
	}
	
	$site_id = (backpost('site_id')) ? backpost('site_id',false) : $_GET['site'];
    
    if(!isset($sites)) $sites = Site::get_sites();
	$the_site = $sites[$site_id];
	
	FormError::error_check();
	
    $page_title = 'Create New Call';
	include TEMPLATE_PATH.'header.php';
?>


<div class="header">
<form name="create_call" action="call_action.php" method="post">

Creating Incident for: 
<h3><?php echo $the_site->city.': '. $the_site->type_name;?></h3><a href="choose_site.php">Change Site</a>
</div><!--header-->

<p>
<fieldset>
<legend>Call ID Numbers</legend>
<label for="UMR_VendorControlNum">Vendor Control Number</label>
<input type="text" name="UMR_VendorControlNum" id="UMR_VendorControlNum" value="<?php if(backpost('UMR_VendorControlNum')) backpost('UMR_VendorControlNum',true); ?>" />
<br />
<label for="UMR_CustomerControlNum">Customer Control Number</label>
<input type="text" name="UMR_CustomerControlNum" id="UMR_CustomerControlNum" value="<?php if(backpost('UMR_CustomerControlNum')) backpost('UMR_CustomerControlNum',true); ?>" />
</fieldset>
</p>

<p>
<fieldset>
<legend>Nature of Problem</legend>
	<label for="printer_id">Printer Affected:</label>
	<?php
	$html = null;
			$html .= "<select name=\"printer_id\" id=\"printer_id\" >";
			$html .= "<option></option>";
			
			//ddprint($site->available_printers);
			for($i=0; $i < count($the_site->available_printers); $i++){
                if(backpost('printer_id') && $the_site->available_printers[$i]->id == backpost('printer_id', false)){
                    $selected = 'selected';
                }else{
                    $selected = '';
                }
				$html .= "<option value=\"{$the_site->available_printers[$i]->id}\" $selected ";
				$html .= ">{$the_site->available_printers[$i]->system}</option>";
			}
			
			$html .= "</select>";
			
			echo $html;
	?>
	<br />


	<label for="generalProblem">General Description of Problem</label>
	<input type="text" name="generalProblem" id="generalProblem" value="<?php if(backpost('generalProblem')) backpost('generalProblem',true); ?>" />
</fieldset>
</p>

<p>
<?php easy_date_time('tsCalled',backpost(),'Date/Time Call Received');?>
</p>

<p>
<fieldset>
<legend>Personnel</legend>
	<label for="tech_id">Technician Assigned</label>
	<select name="tech_id" id="tech_id">
		<option></option>
		
		<?php for($i=0; $i < count($the_site->assigned_techs_ids); $i++){ ?>
			<option value="<?php echo $the_site->assigned_techs_ids[$i]; ?>" <?php if(backpost('tech_id',false)==$the_site->assigned_techs_ids[$i]) echo 'selected';?>>
				<?php echo $the_site->assigned_techs_names_last[$i]; ?>
			</option>
		<?php }
		if(count($the_site->assigned_techs_ids)>0){?>
			<option></option>
		<?php }
		for($i=0; $i < count($the_site->remaining_techs_ids); $i++){ ?>
			<option value="<?php echo $the_site->remaining_techs_ids[$i]; ?>" <?php if(backpost('tech_id',false)==$the_site->remaining_techs_ids[$i]) echo 'selected';?>>
				<?php echo $the_site->remaining_techs_names_last[$i]; ?>
			</option>
		<?php } ?>
	</select>
	
	<br />
	
	<label for="operatorName">Operator Name</label>
	<input type="text" name="operatorName" id="operatorName" value="<?php backpost('operatorName',true);?>" />
	
	<br />
	
	<label for="callBackPhone">Call Back Number: (leave blank for default site number)</label>
	<input type="text" name="callBackPhone" id="callBackPhone" value="<?php backpost('callBackPhone',true);?>" />
</fieldset>
</p>

<input type="hidden" name="createdByUser_id" value="<?php echo $user->id;?>" />
<input type="hidden" name="site_id" value="<?php echo $the_site->id;?>" />
<input type="hidden" name="dispatchStatus" value="2" />
<input type="submit" name="submit_new_call" id="submit" class="button" value="Create Call" />
<br /><br />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>