<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-with");
header("Content-Type: application/json; charset = utf-8");

include "config.php";

$postjson = json_decode(file_get_contents('php://input'), true);

if($postjson['opt'] == 'all_routes'){
   $all_routes = array();
   $routes = array();
   $query = mysqli_query($mysqli, "SELECT 
            routes.id AS route_id, 
            from_point.name As departure, 
            to_point.name As destination, 
            routes.fare FROM routes 
            INNER JOIN end_points from_point ON from_point.id = routes.departure_id 
            INNER JOIN end_points to_point ON to_point.id = routes.destination_id 
            ");
   if($query){
      $total_routes = mysqli_num_rows($query);
      if($total_routes > 0){
         while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
            $all_routes[] = $row;
            $routes[] = array(
               'route_id' => $row['route_id'],
               'departure' => $row['departure'],
               'destination' => $row['destination'],
               'fare' => $row['fare']
            );
         }
       }
      $result = json_encode(array('success' => true, 'routes' => $routes ));

   }
   echo $result;

}