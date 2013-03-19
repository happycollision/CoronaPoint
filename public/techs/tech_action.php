<?php 
	require_once('../initialize.php');
		
	if(empty($_POST)) {
		$session->message('Error TA005: Nothing was sent to the database. Apologies for needing to send you back to the list view. Please try again, though. If the problem persists, contact the application developer with the error number.','error');
		redirect_to('index.php');
	}
    $required_fields = array(
    	'firstName'=>'First Name',
    	'lastName'=>'Last Name'
    	);
    $tech = new Tech;
    
    required_fields_spinner($tech, $required_fields);

	if(count($form_errors) > 0){
		$_SESSION['form_errors'] = $form_errors;
		if(isset($_POST['id'])){
			redirect_to("edit_tech.php?id={$_POST['id']}");
		}else{
			$_SESSION['POST'] = $_POST;
			redirect_to('create_tech.php');
		}
	}

    if($tech->save()){
        $session->message('Technician information saved successfully.','success');
        redirect_to('index.php');
    }else{
        if(isset($_POST['id'])){
            $session->message('Warning TA019: It seems that no changes were made. If you are sure you altered the information below and this warning persists, contact the application developer with this warning number and the conditions of the problem.', 'warning');
            redirect_to("edit_tech.php?id={$_POST['id']}");
        }
        $session->message('Error TA045: There was a problem saving the technician\'s information. Please try again. If the problem persists, contact the application developer with this error number and the conditions of the problem.', 'error');
        $_SESSION['POST'] = $_POST;
        redirect_to('create_tech.php');
    }