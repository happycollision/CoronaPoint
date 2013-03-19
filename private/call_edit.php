<?php
//die('The site is temporarily down for an upgrade.  Your work may not have been saved.  Expect things to be running again in a few moments.');

// If it's going to need the database, then it's 
// probably smart to require it before we start.
require_once(LIB_PATH.DS.'database.php');

class Call_Edit extends Call {

	public $month_drop_down;
		
	public static function edit_calls($id){
		//make a normal query for a call
		$calls = self::edit_prep($id);
		return $calls;
	}
	
	public function edit_prep($id){
		//$call = parent::find_by_id($id);
    	$calls = static::find_by_sql("SELECT * FROM ".parent::$table_name." WHERE id IN({$id})");
    	global $database;
    	//ddprint($database->last_query);
		$calls = parent::populate_info($calls);
		$calls = self::populate_edit_info($calls);
		foreach($calls as $call){
			$call->expanded = 1;
		}
		return $calls;
	}
		
	public function tech_dropdown_static(){ //creates dropdown for relevant techs for each site within $call loop
		global $sites;
		if(empty($sites)) $sites = Site::get_sites();

		foreach($sites as $site){
			if($site->id == $this->site_id){
				?><select name="tech_id_<?php echo $this->id;?>" id="tech_id_<?php echo $this->id;?>">
					<option></option>
					
					<?php for($i=0; $i < count($site->assigned_techs_ids); $i++){
						?>
						<option value="<?php echo $site->assigned_techs_ids[$i]; ?>"
						<?php if($this->tech_id==$site->assigned_techs_ids[$i]) echo 'selected';?> >
							<?php echo $site->assigned_techs_names_last[$i]; ?>
						</option>
						<?php
					}
					if(count($site->assigned_techs_ids)>0){?>
						<option></option>
					<?php }
						for($i=0; $i < count($site->remaining_techs_ids); $i++){
						?>
						<option value="<?php echo $site->remaining_techs_ids[$i]; ?>"
						<?php if($this->tech_id==$site->remaining_techs_ids[$i]) echo 'selected';?> >
							<?php echo $site->remaining_techs_names_last[$i]; ?>
						</option>
						<?php
					}
				?></select><?php
			}
		}
		return;
	}
	
	public function printer_dropdown($site_id=null){
		global $sites;
		if(empty($sites)) $sites = Site::get_sites();
		if($site_id==null){$site_id = $this->site_id;}
		
		$html = null;
		foreach($sites as $site){ if($site->id==$site_id){
			$html .= "<select name=\"printer_id_{$this->id}\" id=\"printer_id_{$this->id}\" >";
			$html .= "<option></option>";
			
			//ddprint($site->available_printers);
			for($i=0; $i < count($site->available_printers); $i++){
				$html .= "<option value=\"{$site->available_printers[$i]->id}\" ";
				$html .= ($this->printer_id==$site->available_printers[$i]->id) ? 'selected' : '';
				$html .= ">{$site->available_printers[$i]->system} Serial:{$site->available_printers[$i]->serialNumber}</option>";
			}
			
			$html .= "</select>";
			
			echo $html;
		}}
	}
	
	protected function populate_edit_info($calls){ //sends calls to functions below
		//$calls = self::tech_list_static($calls);
		return $calls;
	}
	
}




