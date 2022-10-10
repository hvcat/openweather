<?php
/* city model */
class Items{   
    
    private $itemsTable = "city";
    public $c_city_name;   
    public $created_at; 
	public $updated_at; 
	// public $temp;
	// public $wind;
    private $conn;
	
    public function __construct($db){
        $this->conn = $db;
    }	
	
	function read(){
		
		$stmt = $this->conn->prepare("SELECT c_id, c_city_name, updated_at FROM ".$this->itemsTable." WHERE c_city_name = ?");
		$stmt->bind_param("s", $this->c_city_name);					
				
		$stmt->execute();			
		
		$stmt->bind_result($c_id, $c_city_name, $updated_at);
		
		while ($stmt->fetch()) {
			return ['c_id' => $c_id, 'c_city_name' => $c_city_name, 'updated_at' => $updated_at];
		}
		
		return false;
	}
	
	function create(){
		
		$stmt = $this->conn->prepare("
			INSERT INTO ".$this->itemsTable."(`c_city_name`, `created_at`, `updated_at`)
			VALUES(?,?,?)");
		
		
		$stmt->bind_param("sss", $this->c_city_name, $this->created_at, $this->updated_at);
		
		if (!$this->read()){
			if($stmt->execute()){
				return $this->conn->insert_id;
			}
		}
	 
		// return false;
		return false;		
	}
		
	function update($c_id){
	 
		$stmt = $this->conn->prepare("
			UPDATE ".$this->itemsTable." 
			SET updated_at = ?
			WHERE c_id = ?");
	 
		$stmt->bind_param("si", $this->updated_at, $c_id);
		
		if($stmt->execute()){
			return true;
		}
	 
		return false;
	}
	
	function is_exist(){
		
		$stmt = $this->conn->prepare("SELECT c_id, c_city_name FROM ".$this->itemsTable." WHERE c_city_name = ?");
		$stmt->bind_param("s", $this->c_city_name);					
				
		$stmt->execute();			
		
		$stmt->bind_result($c_id, $c_city_name);
		
		while ($stmt->fetch()) {
			return $c_id;
		}
		
		return false;
	}
	
	/* function delete(){
		
		$stmt = $this->conn->prepare("
			DELETE FROM ".$this->itemsTable." 
			WHERE id = ?");
			
		$this->id = htmlspecialchars(strip_tags($this->id));
	 
		$stmt->bind_param("i", $this->id);
	 
		if($stmt->execute()){
			return true;
		}
	 
		return false;		 
	} */
	
	/* function setTemp($c_id){
		
	} */
	
}
?>