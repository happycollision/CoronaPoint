<?php 
	require_once('../initialize.php');
	
	FormError::error_check();
	
    $page_title = 'Create Printer';
    include TEMPLATE_PATH.'header.php';
	
    backpost();
    
?>
<div class="header">Create Printer</div>

<?php if(is_object(current($sites))):?>
<form name="create_printer" action="printer_action.php" method="post">

<p>
<fieldset>
<legend>Printer Information <span class="note">All Fields Required</span></legend>
	<label for="system">System Number</label>
	<input type="text" name="system" id="system" value="<?php if(backpost('system')) backpost('system',true);?>" />
	
    <br />
    
	<label for="serialNumber">Serial Number</label>
	<input type="text" name="serialNumber" id="serialNumber" value="<?php if(backpost('serialNumber')) backpost('serialNumber',true);?>" />
	
    <br />
    
    <label for="site_id">Site</label>
	<select name="site_id" id="site_id">
		<option></option>
        <?php foreach($sites as $site){
            $selected = (backpost('site_id') && backpost('site_id',false)=="{$site->id}") ? 'selected' : '';
            echo "<option value=\"{$site->id}\" $selected >{$site->city}: {$site->type_name}</option> \n";
        }?>
	</select>
    
</fieldset>
</p>

<p>
<fieldset>
<legend>Additional Information <span class="note">Optional</span></legend>
    <label for="description">Description</label>
    <textarea name="description" id="description" ><?php if(backpost('description')) backpost('description',true);?></textarea>
    
    <br />
    
    <label for="online_read_only">Online?</label>
    <input type="checkbox" name="online_read_only" id="online_read_only" checked disabled />
    <span class="note">Newly created printers are online by default. You may alter this parameter later.</span>
    
</fieldset>
</p>

<input type="hidden" name="online" id="online" value="1" />
<input type="submit" name="submit" id="submit" class="button" value="Create Printer" />

</form>
<?php else:?>
<div style="width:60%;margin:24px auto;">There are no sites to choose from.  <a href="../sites/create_site.php">Add one</a>.</div>
<?php endif;?>
<?php 	include TEMPLATE_PATH.'footer.php'; ?>