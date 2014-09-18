<?php 
	require_once('../initialize.php');
		
	if(empty($_POST)) {
		$session->message('Error PA005: Nothing was sent to the database. Apologies for needing to send you back to the list view. Please try again, though. If the problem persists, contact the application developer with the error number.','error');
		redirect_to('index.php');
	}
    $required_fields = array(
    	'site_id'=>'Site',
    	'system'=>'System Number',
    	'online'=>'Online Status'
    	);
    $printer = new Printer;
    
    required_fields_spinner($printer, $required_fields);
    
	if(count($form_errors) > 0){
		$_SESSION['form_errors'] = $form_errors;
		if(isset($_POST['id'])){
			redirect_to("edit_printer.php?id={$_POST['id']}");
		}else{
			$_SESSION['POST'] = $_POST;
			redirect_to('create_printer.php');
		}
	}
    if($printer->save()){
        $session->message('Printer information saved successfully.','success');
        redirect_to('index.php');
    }else{
        if(isset($_POST['id'])){
            $session->message('Warning PA019: It seems that no changes were made. If you are sure you altered the information below and this warning persists, contact the application developer with this warning number and the conditions of the problem.', 'warning');
            redirect_to("edit_printer.php?id={$_POST['id']}");
        }
        $session->message('Error PA022: There was a problem saving the printer. Please try again. If the problem persists, contact the application developer with this error number and the conditions of the problem.', 'error');
        $_SESSION['POST'] = $_POST;
        redirect_to('create_printer.php');
    }