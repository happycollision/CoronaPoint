<?php

class FormError {
	
	public $error_message;
	public $field_id;
	public $field_name;
	public $object_id;
		
	public function FormError($vars=''){
		if(!empty($vars) && is_array($vars)){
			foreach($vars as $key => $var){
				$this->$key = $var;
			}
		}
	}
	
	public static function error_check(){
		global $form_errors;
		if(isset($_SESSION['form_errors'])){
			$form_errors = $_SESSION['form_errors'];
			unset($_SESSION['form_errors']);
			
			foreach($form_errors as $error){
				if($error->error_message == null){
					$error->error_message = "The $error->field_name was incorrectly entered. Please modify and try again.";
				}
				$all_messages[] = $error->error_message;
			}
			messages(implode("<br />",$all_messages),'warning');
			return true;
		}
		return false;
	}
	
}
global $form_errors;
	
?>