<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class Tech extends DatabaseObject {
	
	protected static $table_name="techs";
	protected static $db_fields = array('id','firstName','lastName', 'cellPhone', 'homePhone');
	
	//ids
	public $id;
	public $firstName;
	public $lastName;
    public $cellPhone;
    public $homePhone;
	
    //calculated
    public $name;
	public $assigned_to; //array with site id as keys and site cities as values
    
    
	public static function get_techs(){	//send to functions below
		$techs = static::find_all();
		$techs = self::assigned($techs);
        $techs = self::full_name($techs);
		return $techs;
	}
	
	private static function assigned($techs){
		global $sites;
		if(empty($sites)) $sites = Site::get_sites('city,type');
		foreach($techs as $tech){
			foreach($sites as $site){
                if(!array_search($tech->id,$site->assigned_techs_ids)===false){
                    $tech->assigned_to[$site->id] = $site->city.', '.$site->type_name;
                }
            }
			//ddprint($tech);
		}
		return $techs;	
	}
    
    private static function full_name($techs){
        foreach($techs as $tech){
            $tech->name = $tech->firstName . ' ' . $tech->lastName;
        }
        return $techs;
    }
	
}
	

?>