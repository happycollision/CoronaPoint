<?php 
	require_once('../initialize.php');
		
	if(empty($_POST)) {
		$session->message('Error SA005: Nothing was sent to the database. Apologies for needing to send you back to the list view. Please try again, though. If the problem persists, contact the application developer with the error number.','error');
		redirect_to('index.php');
	}
    $required_fields = array(
    	'city'=>'City',
    	'type'=>'Type of Site',
    	'sitePhone'=>'Print Room Phone'
    	);
    $site = new Site;
    
    required_fields_spinner($site, $required_fields);
    
	if(count($form_errors) > 0){
		$_SESSION['form_errors'] = $form_errors;
		if(isset($_POST['id'])){
			redirect_to("edit_site.php?id={$_POST['id']}");
		}else{
			$_SESSION['POST'] = $_POST;
			redirect_to('create_site.php');
		}
	}

    if($site->save()){
        $session->message('Site information saved successfully.','success');
        redirect_to('index.php');
    }else{
        if(isset($_POST['id'])){
            $session->message('Warning SA019: It seems that no changes were made. If you are sure you altered the information below and this warning persists, contact the application developer with this warning number and the conditions of the problem.', 'warning');
            redirect_to("edit_site.php?id={$_POST['id']}");
        }
        $session->message('Error SA045: There was a problem saving the site. Please try again. If the problem persists, contact the application developer with this error number and the conditions of the problem.', 'error');
        $_SESSION['POST'] = $_POST;
        redirect_to('create_site.php');
    }