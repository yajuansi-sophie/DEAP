<?php
 // make sure user is logged in
 session_start();
 include("../../code/php/AC.php");
 $user_name = check_logged();
 if ($user_name == FALSE) {
   echo "no user name";
   return;  
 }

 $key = "";
 $set = array();
 $code = "";
 $which = "";
 $project_name = "";

 if (isset($_POST['project_name'])) {
   $project_name = $_POST['project_name'];
 } else {
   echo("did not get a project_name");
   return;
 }
 if (isset($_POST['key'])) {
   $key = $_POST['key'];
 } else {
   echo("did not get a key");
   return;
 }
 if (isset($_POST['set'])) {
   $set = $_POST['set'];
 } else {
   echo("did not get a set");
   return;
 }
 if (isset($_POST['code'])) {
   $code = $_POST['code'];
 } else {
   echo("did not get the code");
   return;
 }
 if (isset($_POST['which'])) {
   $which = $_POST['which'];
 } else {
   echo("did not get which one (yes or no)");
   return;
 }
 $fn = "/var/www/html/data/ABCD/Filter/data/filterSets_".$project_name."_".$key.".json";
 $data = array();
 if (is_readable($fn)) {
   $data = json_decode(file_get_contents($fn), TRUE);
 }
 $found = false;
 foreach ($data as &$s) {
   if ($key === $s['key']) {
     $s['sets'] = $set;
     $s['code'] = $code;
     $s['which'] = $which;
     $found = true;
     break;
   }
 }
 if ($found == false) {
    $data[] = array( "key" => $key, "set" => $set, "code" => $code, "which" => $which);
 }
 if (file_put_contents($fn, json_encode( $data )) === FALSE) {
   echo("\nsaving data failed\n");
 };
 echo ("saved as ".$fn." done");

?>
