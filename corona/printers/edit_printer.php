<?php 
	require_once('../initialize.php');
	

	$printer = false;
    if(!empty($_GET['id'])){
        $printer = Printer::find_by_id($_GET['id']);
    }
    if($printer===false||!isset($_GET['id'])){
        $session->message('The edit page needs a parameter that wasn\'t set.  Please try again.  Be sure you haven\'t bookmarked the edit page.  You must start here first.  If the problem persists, please contact the application developer.','warning');
        redirect_to('index.php');
    }

    FormError::error_check();
    
    $page_title = 'Edit Printer';
    include TEMPLATE_PATH.'header.php';    
?>
<div class="header">Edit Printer</div>
<form name="edit_printer" action="printer_action.php" method="post">

<p>
<fieldset>
<legend>Printer Information <span class="note">All Fields Required</span></legend>
	<label for="system">System Number</label>
	<input type="text" name="system" id="system" value="<?php echo $printer->system;?>" />
	
    <br />
    
	<label for="serialNumber">Serial Number</label>
	<input type="text" name="serialNumber" id="serialNumber" value="<?php echo $printer->serialNumber;?>" />
	
    <br />
    
    <label for="site_id">Site</label>
	<select name="site_id" id="site_id">
		<option></option>
        <?php foreach($sites as $site){
            $selected = ($printer->site_id==$site->id) ? 'selected' : '';
            echo "<option value=\"{$site->id}\" $selected >{$site->city}: {$site->type_name}</option> \n";
        }?>
	</select>
    
</fieldset>
</p>

<p>
<fieldset>
<legend>Additional Information <span class="note">Optional</span></legend>
    <label for="description">Description</label>
    <textarea name="description" id="description" ><?php echo $printer->description;?></textarea>
    
    <br />
    
    <label for="online">Online?</label>
    <input type="checkbox" name="online" id="online" value="1" <?php if($printer->online==true) echo 'checked';?> />
    
</fieldset>
</p>

<input type="hidden" name="id" id="id" value="<?php echo $printer->id; ?>" />
<input type="submit" name="submit" id="submit" class="button" value="Update Printer" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>