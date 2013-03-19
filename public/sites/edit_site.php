<?php 
	require_once('../initialize.php');
	

	$site = false;
    if(!empty($_GET['id'])){
        $site = Site::find_by_id($_GET['id']);
    }
    if($site===false||!isset($_GET['id'])){
        $session->message('The edit page needs a parameter that wasn\'t set.  Please try again.  Be sure you haven\'t bookmarked the edit page.  You must start here first.  If the problem persists, please contact the application developer.','warning');
        redirect_to('index.php');
    }
    
    FormError::error_check();    
    
    $page_title = 'Edit Site';
    include TEMPLATE_PATH.'header.php';    
?>
<div class="header">Edit Site</div>

<form name="edit_site" action="site_action.php" method="post">

<p>
<fieldset>
<legend>Site Information</legend>
	<label for="city">City in which the site is located</label>
	<input type="text" name="city" id="city" value="<?php echo $site->city; ?>" />
	
    <br /> 
    
    <label for="type">Type of Site</label>
	<select name="type" id="type">
		<option></option>
        <option value="1" <?php if($site->type=='1') echo 'selected' ?> >Campus Print Site</option>
		<option value="2" <?php if($site->type=='2') echo 'selected' ?> >National Print Site</option>
	</select>
    
    <br />
    
    <label for="sitePhone">Primary Phone Number for Print Room</label>
	<input type="text" name="sitePhone" id="sitePhone" value="<?php echo $site->sitePhone; ?>" />
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

<input type="hidden" name="id" id="id" value="<?php echo $site->id; ?>" />
<input type="submit" name="submit" id="submit" class="button" value="Update Site" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>