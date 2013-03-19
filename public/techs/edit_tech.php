<?php 
	require_once('../initialize.php');
	    
    $tech = false;
    if(!empty($_GET['id'])){
        $tech = Tech::find_by_id($_GET['id']);
    }
    if($tech===false||!isset($_GET['id'])){
        $session->message('The edit page needs aparameter that wasn\'t set.  Please try again.  Be sure you haven\'t bookmarked the edit page.  You must start here first.  If the problem persists, please contact the application developer.','warning');
        redirect_to('index.php');
    }
	
    FormError::error_check();
	
    $page_title = 'Edit Technician Information';
    include TEMPLATE_PATH.'header.php';
?>
<div class="header">Edit Technician Information</div>

<form name="edit_tech" action="tech_action.php" method="post">

<p>
<fieldset>
<legend>Tech Information <span class="note"></span></legend>
	<label for="firstName">First Name</label>
	<input type="text" name="firstName" id="firstName" value="<?php echo $tech->firstName;?>" />
	
    <label for="lastName">Last Name</label>
	<input type="text" name="lastName" id="lastName" value="<?php echo $tech->lastName;?>" />
	
    <br /> 
    
    <label for="cellPhone">Cell Phone</label>
	<input type="text" name="cellPhone" id="cellPhone" value="<?php echo $tech->cellPhone;?>" />
	
    <label for="homePhone">Home Phone</label>
	<input type="text" name="homePhone" id="homePhone" value="<?php echo $tech->homePhone;?>" />
    
</fieldset>
</p>

<p>
<fieldset>
<legend>Site Assignment <span class="note">Optional</span></legend>
    <p>The ability to assign techs to sites currently exists, but it is not yet a user-friendly function.
    Once that has been resolved, the assignment of techs will work here.  For now, contact the application developer 
    for changes in technician assignment.</p>
</fieldset>
</p>

<input type="hidden" name="id" id="id" value="<?php echo $tech->id; ?>" />
<input type="submit" name="submit" id="submit" class="button" value="Update Technician" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>