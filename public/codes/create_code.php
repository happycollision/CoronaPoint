<?php 
	require_once('../initialize.php');

	FormError::error_check();
	
    $page_title = 'Add New Error Code';
    include TEMPLATE_PATH.'header.php';
	
    backpost();
?>


<div class="header">Add New Error Code</div>

<form name="create_code" action="code_action.php" method="post">

<p>
<fieldset>
    <label for="code">Code (required)</label>
	<input type="text" name="code" id="code" value="<?php if(backpost('code')) backpost('code',true);?>" />
	<br /> 
    <label for="name">Name/Description</label>
	<input type="text" name="name" id="name" value="<?php if(backpost('name')) backpost('name',true);?>" />
	<br /> 
    <label for="notes">Notes</label>
	<textarea class="text_area" name="notes" id="notes"><?php if(backpost('notes')) backpost('notes',true);?></textarea>
</fieldset>
</p>


<input type="submit" name="submit" id="submit" class="button" value="Add Error Code" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>