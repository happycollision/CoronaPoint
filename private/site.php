<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class Site extends DatabaseObject {
	
	protected static $table_name="sites";
	protected static $db_fields = array('id','city','sitePhone','type');
	
	//ids
	public $id;
	public $city;
	public $sitePhone;
	public $type;
	
    public $type_name;
	
	public $available_printers;
	
	public $assigned_techs_ids;
	public $assigned_techs_names;
	public $assigned_techs_names_last;
	public $remaining_techs_ids;
	public $remaining_techs_names;
	public $remaining_techs_names_last;
	
	public static function get_sites($order=''){	//send to functions below
		$sites = static::find_all($order);
		$sites = self::technicians($sites);
		$sites = self::printers($sites);
		$sites = self::site_type($sites);
		return $sites;
	}
	
	private static function technicians($sites){
		global $full_tech_list;
		if(empty($full_tech_list)){
			$sql = "SELECT `id`, `firstName`, `lastName` FROM `techs` WHERE 1 ORDER BY lastName";
			$full_tech_list = static::find_by_sql($sql,false);
		}
		foreach($sites as $site){
			//get list of techs assigned to the site
			$sql = "SELECT tech_id, firstName, lastName 
					FROM  `techs_x_sites` 
					JOIN  `techs` ON tech_id = techs.id
					WHERE site_id={$site->id}
					ORDER BY techs.lastName";
			$techs_assigned = static::find_by_sql($sql,false);
			//ddprint($techs_assigned);

			$site->assigned_techs_ids = array();
			foreach($techs_assigned as $tech_assigned){
				$site->assigned_techs_ids[] = $tech_assigned['tech_id'];
				$site->assigned_techs_names[] = $tech_assigned['firstName'].' '.$tech_assigned['lastName'];
				$site->assigned_techs_names_last[] = $tech_assigned['lastName'].', '.$tech_assigned['firstName'];
			}
			
			foreach($full_tech_list as $possible_tech){
				if(!in_array($possible_tech['id'],$site->assigned_techs_ids)){
					$site->remaining_techs_ids[] = $possible_tech['id'];
					$site->remaining_techs_names[] = $possible_tech['firstName'].' '.$possible_tech['lastName'];
					$site->remaining_techs_names_last[] = $possible_tech['lastName'].', '.$possible_tech['firstName'];
				}
			}
			//ddprint($site);
		}
		return $sites;	
	}
	
	private static function printers($sites){
		global $printers;
		if(empty($printers)) $printers = Printer::find_all('system');
		foreach($sites as $site){ foreach($printers as $printer){
			if($site->id==$printer->site_id && $printer->online == true){
				$site->available_printers[] = $printer;
			}
		}}
	return $sites;
	}
	
	private static function site_type($sites){
		foreach($sites as $site){
			$site->type_name = site_type($site->type); //see functions.php
		}
		return $sites;
	}
	
}


$sites = Site::get_sites('city,type DESC');
	
	

?>