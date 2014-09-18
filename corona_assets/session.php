<?php
// A class to help work with Sessions
// In our case, primarily to manage logging users in and out

// Keep in mind when working with sessions that it is generally 
// inadvisable to store DB-related objects in sessions

class Session {
	
	private $logged_in =false;
	public $user_id;
	private $messages =array();
	
	function __construct() {
		session_start();
		$this->check_messages();
		$this->check_login();
		if($this->logged_in) {
		  // actions to take right away if user is logged in
		} else {
		  // actions to take right away if user is not logged in
		}
	}
	
    public function is_logged_in() {
        return $this->logged_in;
    }

    public function login($user) {
        // database should find user based on username/password
        if($user){
          $this->user_id = $_SESSION['user_id'] = $user->id;
          $this->logged_in = true;
        }
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($this->user_id);
        $this->logged_in = false;
    }

	public function message($msg, $type="") {
		$message = array('message'=>$msg,'type'=>$type);
		$_SESSION['messages'][] = $message;
	}
	
	public function non_session_message($msg, $type=''){
		$message = array('message'=>$msg, 'type'=>$type);
		$this->messages[] = $message;
	}
	
	public function get_messages(){
		$output = $this->messages;
		$this->messages = array();
		return $output;
	}

	private function check_login() {
    if(isset($_SESSION['user_id'])) {
      $this->user_id = $_SESSION['user_id'];
      $this->logged_in = true;
    } else {
      unset($this->user_id);
      $this->logged_in = false;
    }
  }
  
	private function check_messages() {
		// Is there a message stored in the session?
		if(isset($_SESSION['messages'])) {
			// Add them as an attribute and erase the stored version
			$this->messages = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
	}
		
}

$session = new Session();
global $session;
?>