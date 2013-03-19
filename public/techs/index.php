<?php 
	require_once('../initialize.php');
	
    $page_title = 'Technicians';
	include TEMPLATE_PATH.'header.php';
    
    $techs = Tech::get_techs();
?>

<div class="header">
    <a class="button" href="create_tech.php" style="margin-left:2%">Add New Technician</a>
</div><!--list_header-->
<?php $list_count=0; foreach($techs as $tech){ ++$list_count;?>

<div class="list_item tech two_thirds" id="tech_<?php echo $tech->id;?>"><div class="padbox">
	<h3 class="tech_name">
        <a href="edit_tech.php?id=<?php echo $tech->id;?>" >
            <?php echo $tech->name;?> 
            <span class="hover note">Edit Tech</span>
        </a>
    </h3>
	<div class="tech_stats">
        <div class="tech_phone"><span class="label">Cell Phone: </span><span class="field"><?php echo $tech->cellPhone;?></span></div>
        <div class="tech_phone"><span class="label">Home Phone: </span><span class="field"><?php echo $tech->homePhone;?></span></div>
		
		<ol class="tech_assignments"><span class="label">Assigned to Sites:</span>
			<?php if(is_array($tech->assigned_to)): foreach($tech->assigned_to as $site_name){?>
			<li><?php echo $site_name;?></li>
			<?php } endif;?>
		</ol>
	</div>
	
</div><!--padbox--></div><!--list_item-->

<?php if($list_count % 3 == 0 || $list_count==count($techs)){?>
    <div class="anchor"></div>
<?php } ?>

<?php } //approx ln 10?>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>