<?php
// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class Printer extends DatabaseObject {
	
	protected static $table_name="printers";
	protected static $db_fields = array('id','site_id','system','description','serialNumber','online');
	
	//database
	public $id;
	public $site_id;
	public $system;
	public $description;
	public $serialNumber;
	public $online;
    
    //calculated
    public $site_city;
    public $site_type;
	
	public static function get_printers($order=''){	//send to functions below
		//$printers = static::find_all($order);  ## removed to do a custom query call, $order depricated
        $sql = 'SELECT s.city as site_city, p.*, s.type as site_type
                FROM printers as p
                LEFT JOIN sites as s
                ON p.site_id=s.id
                ORDER BY site_city, site_type, p.system';
		$printers = self::find_by_sql($sql);
		
        //now send the object array through the calculators
        //$printers = self::site_cities($printers); ## No need for this since the sql created a column with proper name for site_cities
		return $printers;
	}
    
    private static function site_cities($printers){
        global $sites;
        foreach($printers as $printer){
            $site_city = $sites[$printer->site_id]->city;
        }
    }
    
    
	public static function this_printer($printer_id){//  NOT USED CURRENTLY
		global $database;
		//make sure $printer_id is a text string
		if(is_array($printer_id)){
			$printer_id = implode(',',$printer_id);
		}
		$sql = "
			SELECT c.printer_id, c.dateTimeCalled, c.dateTimeClosed
			FROM calls as c
			WHERE c.printer_id IN({$printer_id})
			ORDER BY c.printer_id, c.dateTimeCalled
		";
		$result_set = $database->query($sql);
		while ($row = $database->fetch_array($result_set)) {
		  $calls[] = $row;
		}
		//put data into arrays. Each array is a printer with arrays of date opened and date closed
		foreach($calls as $call){
			$printers[$call['printer_id']]['opened'][] = $call['dateTimeCalled'];
			$printers[$call['printer_id']]['closed'][] = $call['dateTimeClosed'];
		}
		//ddprint($printers);
		
	}
	
	
}
?>