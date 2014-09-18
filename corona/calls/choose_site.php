<?php 
	require_once('../initialize.php');
	
	if(isset($_GET['change'])){
		$page_title = 'Edit Call: Change Site';
	}else{
		$page_title = 'New Call: Choose Site';
	}
	
	include TEMPLATE_PATH.'header.php';

?>

<div class="header"><?php echo $page_title;?></div>
<?php 
if(!isset($sites)) $sites = Site::get_sites();
if(!isset($techs)) $techs = Tech::get_techs();
    if(count($techs) < 1){
        echo '<div style="width:60%;margin:24px auto;">There are no technicians to choose from. <a href="../techs/create_tech.php">Add a technician.</a></div>';
    }
    if(count($sites) < 1){
        echo '<div style="width:60%;margin:24px auto;">There are no sites to choose from. <a href="../sites/create_site.php">Create a site.</a></div>';
    }
    if(isset($_GET['change'])){ //we are changing the site of a previously created call
    	$href = 'edit_call.php?id='.$_GET['id'].'&site=';
    }else{
    	$href = 'create_call.php?site=';
    }
	if(count($sites) > 0  && count($techs) > 0){
        foreach($sites as $site){
            $natcamp = $site->type_name;
            echo '<div class="list_item choose_site"><a href="'.$href.$site->id.'">'."<strong>$site->city:</strong> $natcamp".'</a></div>';
        }
        echo '<div class="anchor"></div>';
    }
?>

<?php 	include TEMPLATE_PATH.'footer.php'; ?>