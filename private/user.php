<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class User extends DatabaseObject {
	
	protected static $table_name="users";
	protected static $db_fields = array('id','type_id','email','cellPhone','homePhone','smsAlertOn','smsAlertTo','emailAlertOn','username','password','firstName','lastName','preferences');
	
	public $id;
	public $username;
	public $password;
	public $firstName;
	public $lastName;
	public $type_id;
	public $email;
	public $cellPhone;
	public $homePhone;
	public $smsAlertOn;
	public $smsAlertTo;
	public $emailAlertOn;
	public $preferences;
	
	public $user_type;
	
	//find the name of the type of user.  Add it to vars or recall it from vars.
	public function user_type(){
		if (isset($this->user_type)) return $this->user_type;
		global $database;
		$sql = "
			SELECT name FROM user_types WHERE id = {$this->type_id}
		";
		$type_name = self::find_value_by_sql($sql);
		if(!empty($type_name)){
			$this->user_type = $type_name;
			return $this->user_type;
		}else{
			return 'User Type not defined.';
		}
	}
	
	public function full_name() {
		if(isset($this->first_name) && isset($this->last_name)) {
			return $this->first_name . " " . $this->last_name;
		} else {
			return "";
		}
	}

	public static function authenticate($username="", $password="") {
		global $database;
		$username = $database->escape_value($username);
		$password = $database->escape_value($password);
		$hashed_password = sha1($password);
		
		$sql  = "SELECT * FROM users ";
		$sql .= "WHERE username = '{$username}' ";
		$sql .= "AND password = '{$password}' ";
		$sql .= "LIMIT 1";
		$result_array = self::find_by_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
	}

}


if(isset($_SESSION['user_id'])){
	$user = User::find_by_id($_SESSION['user_id']);
}
?>