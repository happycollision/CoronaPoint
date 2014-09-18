<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class DatabaseObject {
	// Common Database Methods
	public static function find_all($order=NULL, $paginate_string=NULL) {
		if(!empty($order)) $order = ' ORDER BY '.$order;
		return static::find_by_sql("SELECT * FROM ".static::$table_name.$order.' '.$paginate_string);
  }
  
  public static function find_by_id($id=0) {
    $result_array = static::find_by_sql("SELECT * FROM ".static::$table_name." WHERE id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  
  public static function find_by_ids($id=0) {
    $result_array = static::find_by_sql("SELECT * FROM ".static::$table_name." WHERE id IN({$id})");
		return !empty($result_array) ? $result_array : false;
  }
  
  public static function find_by_sql($sql="",$return_object_array=true) {
    global $database;
    $result_set = $database->query($sql);

    if($return_object_array==true){
		$object_array = array();
		while ($row = $database->fetch_array($result_set)) {
		  $object_array[] = static::instantiate($row);
		}
        //check to see if all the objects in the array have an id variable
        $all_have_ids = true;
        foreach($object_array as $object){
            if(!property_exists($object,'id')){
                $all_have_ids = false;
            }
        }
        //now make a new object array where the array keys indicate the id of the object
        if($all_have_ids){
            foreach($object_array as $object){
                $new_object_array[$object->id] = $object;
            }
        }
        
		return (isset($new_object_array)) ? $new_object_array : $object_array;
	}else{
		$array = array();
		while($row = $database->fetch_array($result_set)){
			foreach($row as $key => $value){
				$record[$key] = $value;
			}
			$array[] = $record;
		}
		return $array;
	}
    
  }

	public static function count_all($table=NULL) {
		global $database;
		if(empty($table)) $table = static::$table_name;
		$sql = "SELECT COUNT(*) FROM ".$table;
		$result_set = $database->query($sql);
		$row = $database->fetch_array($result_set);
		return array_shift($row);
	}

	public static function find_value_by_sql($sql="") {
		global $database;
		$result_set = $database->query($sql);
		$row = $database->fetch_array($result_set);
		return !empty($row) ? array_shift($row) : false;
	}

	private static function instantiate($record) {
		// Could check that $record exists and is an array
		$class_name = get_called_class();
		$object = new $class_name;
		// Simple, long-form approach:
		// $object->id 				= $record['id'];
		// $object->username 	= $record['username'];
		// $object->password 	= $record['password'];
		// $object->first_name = $record['first_name'];
		// $object->last_name 	= $record['last_name'];
		
        // More dynamic, short-form approach:
        foreach($record as $attribute=>$value){
            if($object->has_attribute($attribute)) {
                $object->$attribute = $value;
            }
        }
        return $object;
	}
	
	public function has_attribute($attribute) {
	  // We don't care about the value, we just want to know if the key exists
	  // Will return true or false
	  return array_key_exists($attribute, $this->attributes());
	}

	protected function attributes($clean=false) { 
        // $clean=false will allow for generated values to be pulled, so when sanitized_attributes()
        // asks for $clean to be TRUE, we will only use the $db_fields property to prevent errors on
        // save() actions.
        
		// this function will return an array of attribute names and their values
	  $attributes = array();
      if($clean){$fields = static::$db_fields;}
      if(!$clean){$fields = array_keys(get_object_vars($this));}
	  foreach($fields as $field) {
	    if(property_exists($this, $field)) {
	      $attributes[$field] = $this->$field;
	    }
	  }
	  return $attributes;
	}
	
	protected function sanitized_attributes() {
	  global $database;
	  $clean_attributes = array();
	  // sanitize the values before submitting
	  // Note: does not alter the actual value of each attribute
	  foreach($this->attributes(true) as $key => $value){
	    $clean_attributes[$key] = $database->escape_value($value);
	  }
	  return $clean_attributes;
	}
	
	public function save() {
	  // A new record won't have an id yet.
	  return isset($this->id) ? $this->update() : $this->create();
	}
	
	public function create() {
		global $database;
		// Don't forget your SQL syntax and good habits:
		// - INSERT INTO table (key, key) VALUES ('value', 'value')
		// - single-quotes around all values
		// - escape all values to prevent SQL injection
		$attributes = $this->sanitized_attributes();
	    $sql = "INSERT INTO ".static::$table_name." (";
		$sql .= join(", ", array_keys($attributes));
	    $sql .= ") VALUES (";
	    foreach($attributes as $key => $attribute){
	    	if($attribute===''){
	    		$attributes[$key] = 'NULL';
	    	}else{
	    		$attributes[$key] = "'$attribute'";
	    	}
	    }
	    //ddprint($attributes);
		$sql .= join(", ", array_values($attributes));
		$sql .= ")";
		//echo $sql;
	    if($database->query($sql)) {
	    	$this->id = $database->insert_id();
	    	return true;
	    } else {
	    	return false;
	    }
	}

	public function update() {
	  global $database;
		// Don't forget your SQL syntax and good habits:
		// - UPDATE table SET key='value', key='value' WHERE condition
		// - single-quotes around all values
		// - escape all values to prevent SQL injection
		$attributes = $this->sanitized_attributes();
		$attribute_pairs = array();
		foreach($attributes as $key => $value) {
			if($value===''){
		    	$attribute_pairs[] = "{$key}=NULL";
			}else{
				$attribute_pairs[] = "{$key}='{$value}'";
			}
		}
		$sql = "UPDATE ".static::$table_name." SET ";
		$sql .= join(", ", $attribute_pairs);
		$sql .= " WHERE id=". $database->escape_value($this->id);
	  $database->query($sql);
	  return ($database->affected_rows() == 1) ? true : false;
	}

	public function delete() {
		global $database;
		// Don't forget your SQL syntax and good habits:
		// - DELETE FROM table WHERE condition LIMIT 1
		// - escape all values to prevent SQL injection
		// - use LIMIT 1
	  $sql = "DELETE FROM ".static::$table_name;
	  $sql .= " WHERE id=". $database->escape_value($this->id);
	  $sql .= " LIMIT 1";
	  $database->query($sql);
	  return ($database->affected_rows() == 1) ? true : false;
	
		// NB: After deleting, the instance of User still 
		// exists, even though the database entry does not.
		// This can be useful, as in:
		//   echo $user->first_name . " was deleted";
		// but, for example, we can't call $user->update() 
		// after calling $user->delete().
	}

}
