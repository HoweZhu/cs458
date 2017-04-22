<?php

// --- Connect to DB

$db = new mysqli('cs458.ndesilets.com', '', '', 'fars');

if($db->connect_errno > 0){
    die("Unable to connect to database: [" . $db->connect_error . "]");
}

// --- Check params

if(count($_GET) == 0){
    echo "No GET parameters.";
    exit(0);
}

$year = $_GET['year'] ? $_GET['year'] : null;

// --- Execute query

$stmt = $db->prepare("SELECT ST_CASE, LATITUDE, LONGITUD FROM accident WHERE STATE=(SELECT id FROM state_id WHERE NAME = 'Oregon') AND YEAR=?");
if($stmt){
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $stmt->bind_result($st_case, $lat, $long);
}else{
    echo "wat";
    exit(0);
}

$results = [];
while($stmt->fetch()){
    $coordinates = array(
        "id" => $st_case,
        "lat" => $lat,
        "long" => $long
    );
    array_push($results, $coordinates);
}

$stmt->close();
$db->close();

echo json_encode($results);

?>
