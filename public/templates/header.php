<?php
	if(!$session->is_logged_in() && !isset($is_login_page)){
		$session->message("You need to be logged in to access that page.");
		redirect_to(URL.'/login.php');
	}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php if(isset($page_title)){echo "Corona Point- $page_title";}else{echo 'Corona Point';}?></title>
<link href="<?php echo URL;?>/templates/style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="main">

<h1 class="page_title"><?php //if(isset($page_title))echo $page_title;?>Corona Point <span class="note">v0.8</span></h1>
<?php messages();?>

<div id="top_nav"><?php include TEMPLATE_PATH.'nav.php';?></div>
<div class="list_holder<?php if(in_url('edit_')) echo ' editing';?>">
