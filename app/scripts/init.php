<?php

function fetchTable($file) {
    return array_map(function($row){
        return array_map(function($cell){
            return json_decode($cell);
        }, (array)json_decode($row));
    }, file($file));
}
function setTable($file, $row) {
    //["user"=>"$token", "action"=>"get", "timestamp"=>date("Y-m-d H:i:s"), "content"=>""]
    $combine = array_combine(["user", "action", "timestamp", "content"], array_map(function($cell){
        return json_encode($cell);
    }, $row));
    $table = fetchTable($file);
    $br = (!empty($table)) ? "\n" : null;
    file_put_contents(
        $file, 
        $br.json_encode($combine),
        FILE_APPEND
    );
    return count($table);
}
function fetchRowsByColumnValue($table, $column, $value) {
    $return = array_column($table, $column);
    $return = array_filter($return, function ($v_) use ($value) { return $v_ == $value; });
    $return = array_keys($return);
    $return = array_combine($return, $return);
    $return = array_map(function ($v_) use ($table) { return $table[$v_]; }, $return);
    return $return;
}
function arr2std($array){
    return json_decode(json_encode($array));
}

$ip = $_SERVER['REMOTE_ADDR'];
$token = hash('sha256', $ip);
for ($i=0; $i < strlen(ip2long($ip)); $i++) { 
    $token = hash('sha256', $token);
}

$fName = 'base/token.txt';
setTable($fName, ["$token", "get", date("Y-m-d H:i:s"), $_SERVER['REQUEST_URI']]);
$baseTokens = fetchTable($fName);

setcookie('token', $token);
?>