<?php if(!isset($is_login_page)){
    $this_uri = $_SERVER['REQUEST_URI'];
    //echo $this_uri;
?>
<div class="nav">
	<span class="inner_nav">
    <a href="<?php echo URL . '/dashboard/';?>" <?php if(in_url('/dashboard/')) echo 'class="current_page"';?>>Dashboard</a>

	<a href="<?php echo URL . '/calls/';?>" <?php if(in_url('/calls/')) echo 'class="current_page"';?>>Calls</a>

	<a href="<?php echo URL . '/sites/';?>" <?php if(in_url('/sites/')) echo 'class="current_page"';?>>Sites</a>

	<a href="<?php echo URL . '/techs/';?>" <?php if(in_url('/techs/')) echo 'class="current_page"';?>>Technicians</a>

	<a href="<?php echo URL . '/printers/';?>" <?php if(in_url('/printers/')) echo 'class="current_page"';?>>Printers</a>

	<a href="<?php echo URL . '/codes/';?>" <?php if(in_url('/codes/')) echo 'class="current_page"';?>>Error Codes</a>
    </span>
	<a class="right" href="<?php echo URL . '/logout.php';?>">Log Out</a>
</div>
<?php }?>