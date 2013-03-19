<?php 
	require_once('../initialize.php');
	$page_title = 'Sites';
	include TEMPLATE_PATH.'header.php';
?>

<div class="header">
    <a class="button" href="create_site.php" style="margin-left:2%">Add New Site</a>
</div><!--list_header-->
<?php $list_count=0; foreach($sites as $site){ ++$list_count;?>

<div class="list_item site" id="site_<?php echo $site->id;?>"><div class="padbox">
	<h3 class="site_name">
        <a href="edit_site.php?id=<?php echo $site->id;?>" >
            <?php echo $site->city;?>: 
            <?php echo $site->type_name;?>
            <span class="hover note">Edit Site</span>
        </a>
    </h3>
	<div class="site_stats">
        <div class="site_phone"><span class="label">Phone: </span><span class="field"><?php echo $site->sitePhone;?></span></div>
		<span class="label">Printers:</span>
        <ol class="site_printers">
			<?php if(is_array($site->available_printers)): foreach($site->available_printers as $printer){?>
			<li><?php 
                    echo $printer->system;
                    if(!empty($printer->serialNumber)) echo ' SN:' . $printer->serialNumber;
                    if(!empty($printer->description)) echo '<br />' . $printer->description;
                ?>
            </li>
			<?php } endif;?>
		</ol>
        <span class="label">Technicians Assigned to Site:</span>
		<ol class="site_technicians">
			<?php if(is_array($site->assigned_techs_names)): foreach($site->assigned_techs_names as $tech){?>
			<li><?php echo $tech;?></li>
			<?php } endif;?>
		</ol>
	</div>
	
	<div class="uptimes">
		Uptime vs. downtime info will be here.
	</div>
</div><!--padbox--></div><!--list_item-->

<?php if($list_count % 2 == 0 || $list_count==count($sites)){?>
    <div class="anchor"></div>
<?php } ?>

<?php } //approx ln 10?>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>