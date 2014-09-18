<?php 
	require_once('../initialize.php');
	    
    $error_code = false;
    if(!empty($_GET['id'])){
        $error_code = ErrorCode::find_by_id($_GET['id']);
    }
    if($error_code===false||!isset($_GET['id'])){
        $session->message('The edit page needs aparameter that wasn\'t set.  Please try again.  Be sure you haven\'t bookmarked the edit page.  You must start here first.  If the problem persists, please contact the application developer.','warning');
        redirect_to('index.php');
    }
	
    FormError::error_check();
	
    $page_title = 'Edit Error Code Information';
    include TEMPLATE_PATH.'header.php';
?>
<div class="header">Edit Error Code Information</div>

<form name="edit_code" action="code_action.php" method="post">

<p>
<fieldset>
<legend>Error Code Information <span class="note"></span></legend>
	<label for="code">Code</label>
	<input type="text" name="code" id="code" value="<?php echo $error_code->code;?>" />

    <br /> 

    <label for="name">Name/Description</label>
	<input type="text" name="name" id="name" value="<?php echo $error_code->name;?>" />
	
    <br /> 
    
    <label for="notes">Notes</label>
	<textarea class="text_area" name="notes" id="notes"><?php echo $error_code->notes;?></textarea>
</fieldset>
</p>

<input type="hidden" name="id" id="id" value="<?php echo $error_code->id; ?>" />
<input type="submit" name="submit" id="submit" class="button" value="Update Error Code" />

</form>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>