<?php 
$filepath = realpath(dirname(__FILE__));
include_once ($filepath.'/../config/config.php');
?>

<?php
Class Database{
 public $host   = DB_HOST;
 public $user   = DB_USER;
 public $pass   = DB_PASS;
 public $dbname = DB_NAME;
 
 public $link;
 public $error;
 
 public function __construct(){
  $this->connectDB();
 }
 
private function connectDB(){
 $this->link = new mysqli($this->host, $this->user, $this->pass, 
  $this->dbname);
 if(!$this->link){
   $this->error ="Connection fail".$this->link->connect_error;
  return false;
 }
 }
 
 // SECURE: Prepared statement implementation with parameter binding
public function select($query, $params = [], $types = ''){
    $stmt = $this->link->prepare($query);
    if(!$stmt){
        //Generic error message without exposing details
        die("Prepare failed: " . $this->link->error);
    }
    
    if(!empty($params)){
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        return $result;
    } else {
        return false;
    }
}

// Parameterized insertion
public function insert($query, $params = [], $types = ''){
    $stmt = $this->link->prepare($query);
    if(!$stmt){
        die("Prepare failed: " . $this->link->error);
    }
    
    if(!empty($params)){
        $stmt->bind_param($types, ...$params);
    }
    
    $insert_row = $stmt->execute();
    if($insert_row){
        return $stmt->insert_id; // Return inserted ID safely
    } else {
        return false;
    }
}

// Parameterized update 
public function update($query, $params = [], $types = ''){
    $stmt = $this->link->prepare($query);
    if(!$stmt){
        die("Prepare failed: " . $this->link->error);
    }
    
    if(!empty($params)){
        $stmt->bind_param($types, ...$params);
    }
    
    $update_row = $stmt->execute();
    if($update_row){
        return $stmt->affected_rows;
    } else {
        return false;
    }
}

// Parameterized deletion
public function delete($query, $params = [], $types = ''){
    $stmt = $this->link->prepare($query);
    if(!$stmt){
        die("Prepare failed: " . $this->link->error);
    }
    
    //Safe parameter binding for DELETE operations
    if(!empty($params)){
        $stmt->bind_param($types, ...$params);
    }
    
    $delete_row = $stmt->execute();
    if($delete_row){
        return $stmt->affected_rows;
    } else {
        return false;
    }
}

// For backward compatibility - but you should migrate to prepared statements
public function query($query){
    $result = $this->link->query($query) or 
       die($this->link->error.__LINE__);
    return $result;
}
 
}