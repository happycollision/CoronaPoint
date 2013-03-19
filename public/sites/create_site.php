<?php 
	require_once('../initialize.php');
	    
    backpost();
    
    FormError::error_check();

    if(isset($_GET['techs'])) {$techs = Tech::get_techs();}
    
    $page_title = 'Create Site';
    include TEMPLATE_PATH.'header.php';
	
?>
<div class="header">Create Site</div>

<form name="create_site" action="site_action.php" method="post">

<p>
<fieldset>
<legend>Site Information <span class="note">All Fields Required</span></legend>
	<label for="city">City in which the site is located</label>
	<input type="text" name="city" id="city" value="<?php if(backpost('city')) backpost('city',true);?>" />
	
    <br /> 
    
    <label for="type">Type of Site</label>
	<select name="type" id="type">
		<option></option>
        <option value="1" <?php if(backpost('type') && backpost('type',false)=='1') echo 'selected' ?> >Campus Print Site</option>
		<option value="2" <?php if(backpost('type') && backpost('type',false)=='2') echo 'selected' ?> >National Print Site</option>
	</select>
    
    <br />
    
    <label for="sitePhone">Primary Phone Number for Print Room</label>
	<input type="text" name="sitePhone" id="sitePhone" value="<?php if(backpost('sitePhone')) backpost('sitePhone',true);?>" />
</fieldset>
</p>

<p>
<fieldset>
<legend>Assigned Technicians <span class="note">Optional</span></legend>
    <p>The ability to assign techs to sites currently exists, but it is not yet a user-friendly function.
    Once that has been resolved, the assignment of techs will work here.  For now, contact the application developer 
    for changes in technician assignment.</p>
</fieldset>
</p>

<input type="submit" name="submit" id="submit" class="button" value="Create Site" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>