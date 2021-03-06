<?php

session_start();
include("/var/www/html/code/php/AC.php");
$user_name = check_logged();

$storelocation = "/var/www/html/data/ABCD/Scores/data/";

$action = "start";
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
//TODO: add private/public difference
if ($action == "save" && isset($_POST['content']) && isset($_POST['name'])) {
    $temp = array();
    $temp["content"] = $_POST['content'];
    $permission = "public";
    if(isset($_POST['source'])){
        $temp["source"] = $_POST['source'];
    }
    if (isset($_POST['permission'])) {
        $permission = $_POST['permission'];
    }
    $temp["permission"] = $permission;

    // lets sanitize the name here - only what is allowed in R is allowed, this makes sure the filename is unique
    $temp["name"]    = strtolower($_POST['name']);
    $chars = "0-9a-z_";
    $pattern = "/[^".$chars."]/";
    $temp['name'] = preg_replace($pattern, ".", $temp["name"]);
    
    if (isset($_POST['description'])) {
        $temp['description'] = $_POST['description'];
    }	    
    if (file_exists($storelocation.$user_name) != 1) {
        mkdir("/var/www/html/data/ABCD/Scores/data/".$user_name, 0777);
    }
    //May have problems for overwriting
    file_put_contents("/var/www/html/data/ABCD/Scores/data/".$user_name."/".$_POST['name'].".json", json_encode($temp));
    
    if (isset($_POST['data'])) {
        file_put_contents("/var/www/html/data/ABCD/Scores/data/".$user_name."/".$_POST['name'].".raw", $_POST['data']);   
        shell_exec("Rscript /var/www/html/applications/Scores/R/transfer.R ". "/var/www/html/data/ABCD/Scores/data/".$user_name."/".$_POST['name'].".raw");
    }
    echo(json_encode($temp));
} else if ($action == "load") {
    $dirname = $storelocation.$user_name."/*.json";

    #if($user_name == "admin")
    #    $dirname = "/var/www/html/applications/Scores/data/*/*.json";
    $files = glob($dirname);
    // syslog(LOG_EMERG,$files);
    $rt = array();
    $names = array();
    foreach ($files as $file) {
        $calculation = json_decode(file_get_contents($file), true);
        $calculation['user'] = $user_name;
        $names[] = $calculation['name'];
        if (!isset($calculation['permission'])) {
            $calculation['permission'] = "public";
        }
           
        $rt[] = $calculation;
    }
    // add public scores from other users
    $users = glob("/var/www/html/data/ABCD/Scores/data/*", GLOB_ONLYDIR);
    foreach($users as $userdir) {
        $user = basename($userdir);
        // only add scores defined by the current user or by admin
        if ($user != $user_name && $user != "admin")
            continue;
        
        $files = glob("/var/www/html/data/ABCD/Scores/data/".$user."/*.json");
        foreach ($files as $file) {
            $calculation = json_decode(file_get_contents($file), true);
            if (isset($calculation['permission']) && $calculation["permission"] == "public") {
                // don't add if it already exists (this will not add the current user's calculations another time
                if (!in_array($calculation['name'], $names)) {
                    $calculation['user'] = ($user == "admin"?"public":$user);
                    if (!isset($calculation['permission'])) {
                        $calculation['permission'] = "public";
                    }
                    $rt[] = $calculation;
                }
            }
        }
    }

    echo json_encode($rt);
} else if ($action == "delete" && isset($_POST['name'])) {
    // syslog(LOG_EMERG,$action);
    $dirname = $storelocation.$user_name."/".$_POST['name'].".*";
    $files = glob($dirname);
    //syslog(LOG_EMERG,$files);
    foreach($files as $file) {
        unlink($file);
    }
    echo "success";
} else if ($action == "read-spreadsheet" && isset($_POST['name'])) {
  $dirname = $storelocation.$user_name."/".$_POST['name'].".raw";
  $files = glob($dirname);
  foreach($files as $file) {
    echo (file_get_contents($file));
  }
}

?>
