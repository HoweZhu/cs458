<?php

// --- Connect to DB

$db = new mysqli('cs458.ndesilets.com', '', '', 'fars');

if($db->connect_errno > 0){
    die("Unable to connect to database: [" . $db->connect_error . "]");
}

// --- Check params

$county = $_GET['county'];
$sex = $_GET['sex'];
$drinking = $_GET['drinking'];
$race = $_GET['race'];

if($county != ""){
    $where[] = " COUNTY = '" . mysql_real_escape_string($county) . "'";
}

if($sex != ""){
    $where[] = " SEX = '" . mysql_real_escape_string($sex) . "'";

}

if($drunk == "false"){
    $where[] = " DRINKING = 0";
}else if($drunk == "true"){
    $where[] = " DRINKING > 0";
}

if($race != ""){
    $where[] = " RACE = '" . mysql_real_escape_string($race) . "'";
}

$where_clause = implode(' AND ', $where);

// --- Execute query

$query = "SELECT ST_CASE, COUNTY, SEX, DRINKING, RACE FROM person WHERE STATE=(SELECT id FROM state_id WHERE NAME = 'Oregon')";
if(!empty($where_clause)){
    $query .= " AND $where_clause"; 
}

$results = [];
if($result = $db->query($query)){
    while($row = $result->fetch_assoc()){
        array_push($results, $row);
    }

    $result->free();
}

$db->close();

echo json_encode($results);

?>
