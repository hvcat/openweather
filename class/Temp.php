<?php
/* temperature model */
class Temp{   
    
    private $itemsTable = "temp";
    public $t_temp_max;   
    public $t_temp_min; 
	public $t_date_time;
	public $t_city_id;
    private $conn;
	
    public function __construct($db){
		$this->conn = $db;
    }	
	
	function read($c_id){	
		
			
		$stmt = $this->conn->prepare("SELECT t_temp_max, t_temp_min, t_date_time FROM ".$this->itemsTable." WHERE t_city_id = ?");
		$stmt->bind_param("i", $c_id);					
				
		$stmt->execute();			
		
		$stmt->bind_result($t_temp_max, $t_temp_min, $t_date_time);
		
		while ($stmt->fetch()) {
			return ['t_temp_max' => $t_temp_max, 't_temp_min' => $t_temp_min, 't_date_time' => $t_date_time];
		}
		
		return false;
	}
	
	function create(){
		
		$stmt = $this->conn->prepare("
			INSERT INTO ".$this->itemsTable."(`t_temp_max`, `t_temp_min`, `t_date_time`, `t_city_id`)
			VALUES(?,?,?,?)");
		
		
		$stmt->bind_param("sssi", $this->t_temp_max, $this->t_temp_min, $this->t_date_time, $this->t_city_id);
		
		if($stmt->execute()){
			return true;
		}
	 
		return false;		 
	}
		
	function update(){
	 
		$stmt = $this->conn->prepare("
			UPDATE ".$this->itemsTable." 
			SET t_temp_max= ?, t_temp_min = ?, t_date_time = ?
			WHERE t_city_id = ?");
	 
		
	 
		$stmt->bind_param("sssi", $this->t_temp_max, $this->t_temp_min, $this->t_date_time, $this->t_city_id);
		
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