<?php

// --- Connect to DB

$db = new mysqli('cs458.ndesilets.com', '', '', 'fars');

if($db->connect_errno > 0){
    die("Unable to connect to database: [" . $db->connect_error . "]");
}

// --- Check params

$year = $_GET['year'];
$county = $_GET['county'];
$drunk = $_GET['drunk'];
$fatalities = $_GET['fatalities'];
$sex = $_GET['sex'];

if($year != ""){
    $where[] = " YEAR = '" . mysql_real_escape_string($year) . "'";
}

if($county != ""){
    $where[] = " COUNTY = (SELECT GSA_CODE FROM county WHERE COUNTY_NAME = '" . mysql_real_escape_string($county) . "' LIMIT 1)";

}

if($drunk == "false"){
    $where[] = " DRUNK_DR = 0";
}else if($drunk == "true"){
    $where[] = " DRUNK_DR > 0";
}

if($fatalities != ""){
    $where[] = " FATALS = '" . mysql_real_escape_string($fatalities) . "'";
}

$where_clause = implode(' AND ', $where);

// --- Execute query

$query = "SELECT ST_CASE, LATITUDE, LONGITUD FROM accident WHERE STATE=(SELECT id FROM state_id WHERE NAME = 'Oregon')";
if(!empty($where_clause)){
    $query .= " AND $where_clause"; 
}

$results = array();
if($result = $db->query($query)){
    while($row = $result->fetch_assoc()){
        $results[$row["ST_CASE"]] = array(
            "LATITUDE" => $row["LATITUDE"],
            "LONGITUD" => $row["LONGITUD"]
        );
    }

    $result->free();
}

// --- All this garbage below is because my raspberry pi is too slow as a DB server
// --- and can't process joins quick enough

// If must filter by gender
if($sex != ""){
    $gender = mysql_real_escape_string($sex);
    $query = "SELECT ST_CASE FROM person WHERE SEX = " . $gender;
    $genderResults = array();
    if($result = $db->query($query)){
        while($row = $result->fetch_assoc()){
            array_push($genderResults, $row["ST_CASE"]);
        }

        $result->free();
    }

    // Add results that are in gender results and also results to new array
    // (Set of all ST_CASE) intersect (Set of all ST_CASE where gender = whatever)
    $filteredPairs = array();
    for($i = 0; $i < count($genderResults); $i++){
        $result = $results[$genderResults[$i]];
        if($result != null){
            array_push($filteredPairs, $results[$genderResults[$i]]);
        }
    }

    echo json_encode($filteredPairs);
}else{

    // Convert obj to array of keyval pairs
    $pairs = array();
    foreach($results as $key => $value){
        array_push($pairs, array(
            "LATITUDE" => $value["LATITUDE"],
            "LONGITUD" => $value["LONGITUD"]
        ));
    }
    echo json_encode($pairs);
}

?>
