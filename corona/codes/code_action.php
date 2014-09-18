<?php 
	require_once('../initialize.php');
		
	if(empty($_POST)) {
		$session->message('Error ECA005: Nothing was sent to the database. Apologies for needing to send you back to the list view. Please try again, though. If the problem persists, contact the application developer with the error number.','error');
		redirect_to('index.php');
	}
    $required_fields = array(
    	'code'=>'Code',
    	);
    $error_code = new ErrorCode;
    
    required_fields_spinner($error_code, $required_fields);
    
    if(!ErrorCode::unique_codes($error_code->code,false)){
    	$the_form_error = new FormError(array(
    		'error_message'=>"It seems the code $error_code->code already exists in the database, so there is no need to create it again."
    		));
    	$form_errors[] = $the_form_error;
    }
    
    //capitalize the code
    $error_code->code = strtoupper($error_code->code);

	if(count($form_errors) > 0){
		$_SESSION['form_errors'] = $form_errors;
		if(isset($_POST['id'])){
			redirect_to("edit_code.php?id={$_POST['id']}");
		}else{
			$_SESSION['POST'] = $_POST;
			redirect_to('create_code.php');
		}
	}

    if($error_code->save()){
        $session->message('Error code information saved successfully.','success');
        redirect_to('index.php');
    }else{
        if(isset($_POST['id'])){
            $session->message('Warning ECA019: It seems that no changes were made. If you are sure you altered the information below and this warning persists, contact the application developer with this warning number and the conditions of the problem.', 'warning');
            redirect_to("edit_code.php?id={$_POST['id']}");
        }
        $session->message('Error ECA045: There was a problem saving the error code information. Please try again. If the problem persists, contact the application developer with this error number and the conditions of the problem.', 'error');
        $_SESSION['POST'] = $_POST;
        redirect_to('create_code.php');
    }