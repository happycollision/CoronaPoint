<?php

// This is a helper class to make paginating 
// records easy.
class Pagination {
	
	public $current_page;
	public $per_page;
	public $total_count;
	
	//special vars for calls, since some are live and some are in the trash
	
	public $live_calls;
	public $deleted_calls;

	public function __construct($countable=NULL){
		//$countable is any one of the switch cases below used to determine
		//whether or not pagination is necessary
		
		//populate the calls tabel info vars
		global $db;
		$result = $db->query('
			SELECT count( * ) - count( tsDeleted ) AS live, 
				count( tsDeleted ) AS trashed
			FROM calls');
		while($row = $db->fetch_array($result)){
			$this->live_calls = $row['live'];
			$this->deleted_calls = $row['trashed'];
		}
		
		
		$page = !empty($_GET['page'])? $page = $_GET['page'] : 1;
		$per_page = 20;
		$total_count = 0;
		if(!empty($countable)){
			switch($countable):
				case is_array($countable)://an object array passed in for counting
					$total_count = count($countable);
					break;
				case is_numeric($countable)://the actual number of elements known
					$total_count = $countable;
					break;
				case 'live_calls':
					$total_count = $this->live_calls;
					break;
				case 'deleted_calls':
					$total_count = $this->deleted_calls;
					break;
				default://the table name is passed in
					$total_count = DatabaseObject::count_all($countable);
					break;
			endswitch;
		}
			
		$this->current_page = $page;
		$this->per_page = $per_page;
		$this->total_count = $total_count;
	}

	public function offset() {
		// Assuming 20 items per page:
		// page 1 has an offset of 0    (1-1) * 20
		// page 2 has an offset of 20   (2-1) * 20
		//   in other words, page 2 starts with item 21
		return ($this->current_page - 1) * $this->per_page;
	}

	public function total_pages() {
		return ceil($this->total_count/$this->per_page);
	}
	
	public function previous_page() {
		return $this->current_page - 1;
	}
	
	public function next_page() {
		return $this->current_page + 1;
	}
	
	public function has_previous_page() {
		return $this->previous_page() >= 1 ? true : false;
	}
	
	public function has_next_page() {
		return $this->next_page() <= $this->total_pages() ? true : false;
	}

	public function page_links(){
		if($this->total_pages()==1)return;
		$this_doc = $_SERVER['PHP_SELF']; //need to find the document path here
		$output = '';
		//Previous
		if($this->has_previous_page()){
			$output.= "<a href=\"{$this_doc}?page={$this->previous_page()}\">Prevous Page</a> ";
		}
		//Pages with links
		for($i=1; $i <= $this->total_pages(); $i++){
			if($i==$this->current_page){
				$output.= "<span class=\"current_page_number\">{$i}</span> ";
			}else{
				$output.= "<a href=\"{$this_doc}?page={$i}\">{$i}</a> ";
			}
		}
		//Next
		if($this->has_next_page()){
			$output.= "<a href=\"{$this_doc}?page={$this->next_page()}\">Next Page</a> ";
		}
		
		$output.='';
		echo $output;
	}

}

?>