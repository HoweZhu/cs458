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
