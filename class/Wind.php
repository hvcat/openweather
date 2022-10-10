<?php
/* wind model */
class Wind{   
    
    private $itemsTable = "wind";      
    public $w_speed;
    public $w_city_id;
    private $conn;
	
    public function __construct($db){
        $this->conn = $db;
    }	
	
	function read($c_id){	
		
			
		$stmt = $this->conn->prepare("SELECT w_speed FROM ".$this->itemsTable." WHERE w_city_id = ?");
		$stmt->bind_param("i", $c_id);					
				
		$stmt->execute();			
		
		$stmt->bind_result($w_speed);
		
		while ($stmt->fetch()) {
			return ['w_speed' => $w_speed];
		}
		
		return false;
	}
	
	function create(){
		
		$stmt = $this->conn->prepare("
			INSERT INTO ".$this->itemsTable."(`w_speed`, `w_city_id`)
			VALUES(?,?)");
		
		
		$stmt->bind_param("si", $this->w_speed, $this->w_city_id);
		
		if($stmt->execute()){
			return true;
		}
	 
		return false;		 
	}
		
	function update(){
	 
		$stmt = $this->conn->prepare("
			UPDATE ".$this->itemsTable." 
			SET w_speed= ?
			WHERE w_city_id = ?");
	 
		
	 
		$stmt->bind_param("si", $this->w_speed, $this->w_city_id);
		
		if($stmt->execute()){
			return true;
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
	
}
?>