<?php
/**
* Format Class
*/
class Format{
 public function formatDate($date){
  return date('F j, Y, g:i a', strtotime($date));
 }

 public function textShorten($text, $limit = 400){
  $text = $text. " ";
  $text = substr($text, 0, $limit);
  $text = substr($text, 0, strrpos($text, ' '));
  $text = $text.".....";
  return $text;
 }

 public function validation($data){
  $data = trim($data);
  $data = stripcslashes($data);
  $data = htmlspecialchars($data);
  return $data;
 }

 public function title(){
  $path = $_SERVER['SCRIPT_FILENAME'];
  $title = basename($path, '.php');
  //$title = str_replace('_', ' ', $title);
  if ($title == 'index') {
   $title = 'home';
  }elseif ($title == 'contact') {
   $title = 'contact';
  }
  return $title = ucfirst($title);
 }

  // New security validation methods
 public function sanitizeNumber($input) {
  $input = $this->validation($input);
  return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
 }
 
 public function validatePositiveInt($input) {
  return filter_var($input, FILTER_VALIDATE_INT, 
      array('options' => array('min_range' => 1)));
 }
 
 public function sanitizeString($input) {
  $input = $this->validation($input);
  return filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
 }
 
 public function escapeString($input, $link) {
  $input = $this->validation($input);
  return mysqli_real_escape_string($link, $input);
 }
}
?>