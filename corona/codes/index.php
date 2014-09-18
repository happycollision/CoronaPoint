<?php 
	require_once('../initialize.php');
	
    $page_title = 'Error Codes';
	include TEMPLATE_PATH.'header.php';
    
    $error_codes = ErrorCode::find_all();
?>

<div class="header">
    <a class="button" href="create_code.php" style="margin-left:2%">Add New Error Code</a>
</div><!--list_header-->
<?php $list_count=0; foreach($error_codes as $error_code){ ++$list_count;?>

<div class="list_item code two_thirds" id="code_<?php echo $error_code->id;?>"><div class="padbox">
	<h3 class="code">
        <a href="edit_code.php?id=<?php echo $error_code->id;?>" >
            <?php echo $error_code->code.': '.$error_code->name;?> 
            <span class="hover note">Edit Error Code</span>
        </a>
    </h3>
	<div class="code_notes">
        <div class="code_notes"><span class="label">Notes: </span><span class="text_area"><?php echo $error_code->notes;?></span></div>		
	</div>
	
</div><!--padbox--></div><!--list_item-->

<?php if($list_count % 3 == 0 || $list_count==count($error_codes)){?>
    <div class="anchor"></div>
<?php } ?>

<?php } //approx ln 10?>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>