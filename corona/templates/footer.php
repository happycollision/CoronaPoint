<div id="lower_pagination"><?php if(isset($pagination))$pagination->page_links();?></div>

</div><!--list_holder-->

<?php include TEMPLATE_PATH.'nav.php';?>

</div><!--main-->

</body>
</html>
<?php if(isset($database)) { $database->close_connection(); } ?>