<?php 
	require_once('../initialize.php');
	
    $page_title = 'Printers';
	include TEMPLATE_PATH.'header.php';
    $printers = Printer::get_printers();

?>


<div class="header">
        <div class="edit">
        	<a class="button" href="create_printer.php" style="margin-left:2%">Add New Printer</a>
        </div>
    <div class="anchor"></div>
</div><!--header-->
<?php $list_count=0; foreach($printers as $printer){ ++$list_count;?>

<div class="list_item printer" id="printer_<?php echo $printer->id;?>"><div class="padbox">
	<h3 class="printer_name">
        <a href="edit_printer.php?id=<?php echo $printer->id;?>" >
            <?php echo $printer->site_city; if(preg_match('/(detroit|ogden)/i',$printer->site_city)){echo ' <span class="note">'.site_type($printer->site_type).'</span>';}?>: 
            <?php echo $printer->system;?>
            <span class="hover note">Edit Printer</span>
        </a>
    </h3>
	<div class="printer_stats">
        <div class="printer_serial_number"><span class="label">Serial Number: </span><span class="field"><?php echo $printer->serialNumber;?></span></div>
        <div class="printer_description"><span class="label">Description: </span><span class="field"><?php echo $printer->description;?></span></div>
		
	</div>
	
	<div class="uptimes">
		Uptime vs. downtime info will be here.
	</div>
</div><!--padbox--></div><!--list_item-->

<?php if($list_count % 2 == 0 || $list_count==count($printers)){?>
    <div class="anchor"></div>
<?php } ?>

<?php } //approx ln 10 ?>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>