<?php 
	require_once('../initialize.php');

	FormError::error_check();
	
    $page_title = 'Add New Technician';
    include TEMPLATE_PATH.'header.php';
	
    backpost();
?>


<div class="header">Add New Technician</div>

<form name="create_tech" action="tech_action.php" method="post">

<p>
<fieldset>
<legend>Tech Information <span class="note">First two fields required</span></legend>
	<p>
    <label for="firstName">First Name</label>
	<input type="text" name="firstName" id="firstName" value="<?php if(backpost('firstName')) backpost('firstName',true);?>" />
	<br /> 
    <label for="lastName">Last Name</label>
	<input type="text" name="lastName" id="lastName" value="<?php if(backpost('lastName')) backpost('lastName',true);?>" />
	</p>
    
    <p>
    <label for="cellPhone">Cell Phone</label>
	<input type="text" name="cellPhone" id="cellPhone" value="<?php if(backpost('cellPhone')) backpost('cellPhone',true);?>" />
	<br /> 
    <label for="homePhone">Home Phone</label>
	<input type="text" name="homePhone" id="homePhone" value="<?php if(backpost('homePhone')) backpost('homePhone',true);?>" />
    </p>
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

<input type="submit" name="submit" id="submit" class="button" value="Add Technician" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>