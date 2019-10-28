<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-with");
header("Content-Type: application/json; charset = utf-8");

include "config.php";

$postjson = json_decode(file_get_contents('php://input'), true);

// Card Application process
if ($postjson['req'] == 'apply_card') {
    date_default_timezone_set("Africa/Nairobi");
    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");

    $query1 = mysqli_query($mysqli, "INSERT INTO cards SET
      user_id = '$postjson[user_id]',
      status = 0,
      created_at = '$created_at',
      updated_at = '$updated_at'
      ");

    if ($query1) {
        $query2 = mysqli_query($mysqli, "SELECT * FROM cards WHERE user_id = '$postjson[user_id]' ");
        $check = mysqli_num_rows($query2);

        if ($check > 0) {
            $data = mysqli_fetch_array($query2);

            $card_id = $data['id'];

            $query3 = mysqli_query($mysqli, "INSERT INTO commuters SET
            user_id = '$postjson[user_id]',
            contact = '$postjson[contact]',
            address = '$postjson[address]',
            card_id = $card_id,
            created_at = '$created_at',
            updated_at = '$updated_at'
         ");

            if ($query3) {
                $result = json_encode(array('success' => true));
            } else {
                $result = json_encode(array('success' => false, 'msg' => 'Could not create commuter after creating card.'));
            }

        } else {
            $result = json_encode(array('success' => false, 'msg' => 'Could not find newly created card.'));
        }
    } else {
        $result = json_encode(array('success' => false, 'msg' => 'Could not even create card.'));
    }

    echo $result;

}

// Retreiving card information
else if ($postjson['req'] == 'card-info') {
    $card_data = array();

    $card_check = mysqli_query($mysqli, "SELECT * FROM cards WHERE user_id = '$postjson[user_id]' ");
    $check_results = mysqli_num_rows($card_check);
    if ($check_results > 0) {
        // This means that the user has already applied for a card

        $card = mysqli_fetch_array($card_check);

        // Then Check the status
        switch ($card['status']) {
            case 0:
                $card_status = "Applied";
                break;
            case 1:
                $card_status = "Granted";
                break;
            case 2:
                $card_status = "Rejected";
                break;
            case 3:
                $card_status = "Blocked";
                break;
            case 4:
                $card_status = "Granted";
                break;
            default:
                $card_status = "Default";
                break;
        }

        $card_data[] = array(
            'card_id' => $card['id'],
            'unique_id' => $card['unique_id'],
            'balance' => $card['balance'],
            'passcode' => $card['passcode'],
            'status' => $card['status'],
        );

        $card_number = $card['unique_id'];

        // passing values for last top up of the card
        if($card['last_topup'] == NULL){
            $last_topup ="Not yet topped up.";
        }
        else{
            $last_topup = $card['last_topup'];
        }

        // Passing values for last use of the card
        if($card['last_used'] == NULL){
            $last_used = "Not yet used.";
        }
        else{
            $last_used = $card['last_used'];
        }
        

        $result = json_encode(array(
            'success' => true,
            'commuter' => true,
            'cardInfo' => $card_data,
            'cardStatus' => $card_status,
            'card_number' => $card_number,
            'last_topup' => $last_topup,
            'last_used' => $last_used)
        );
    }

    // Incase the user has not yet applied for a card
    else {
        $result = json_encode(array(
            'success' => true,
            'commuter' => false)
        );
    }

    echo $result;
}

// Retrieving card funds information
else if ($postjson['req'] == 'card-funds') {
    $card_data = array();

    $card_check = mysqli_query($mysqli, "SELECT * FROM cards WHERE user_id = '$postjson[user_id]' ");
    $check_results = mysqli_num_rows($card_check);
    if ($check_results > 0) {
        // This means that the user has already applied for a card

        $card = mysqli_fetch_array($card_check);
        $card_data[] = array(
            'card_id' => $card['id'],
            'unique_id' => $card['unique_id'],
            'balance' => $card['balance'],
            'passcode' => $card['passcode'],
            'status' => $card['status'],
        );

        $card_number = $card['unique_id'];

        $result = json_encode(array(
            'success' => true,
            'commuter' => true,
            'cardInfo' => $card_data,
            'card_number' => $card_number)
        );
    }

    // Incase the user has not yet applied for a card
    else {
        $result = json_encode(array(
            'success' => true,
            'commuter' => false)
        );
    }

    echo $result;

}

// Authenticate the card
else if ($postjson['req'] == 'pin-check') {
    $pin_check = mysqli_query($mysqli, "SELECT * FROM cards WHERE unique_id = '$postjson[unique_id]'
                                       AND passcode = '$postjson[card_pin]' ");
    $check_result = mysqli_num_rows($pin_check);
    if ($check_result > 0) {
        $result = json_encode(array(
            'success' => true,
            'msg' => 'Pin is correct')
        );
    } else {
        $result = json_encode(array(
            'success' => false,
            'msg' => 'Pin is NOT correct')
        );
    }

    echo $result;
}

// Card recharge simulation
else if ($postjson['req'] == 'recharge') {
    date_default_timezone_set("Africa/Nairobi");
    $updated_at = date("Y-m-d H:i:s");
    $last_topup = date("Y-m-d H:i:s");

    $recharge_query = mysqli_query($mysqli, "UPDATE cards SET
      balance = balance + '$postjson[amount]',
      updated_at = '$updated_at',
      last_topup = '$last_topup'
      WHERE unique_id = '$postjson[unique_id]'
   ");

    if ($recharge_query) {
        $result = json_encode(array(
            'success' => true,
            'msg' => 'Succesful recharge.')
        );
    } else {
        $result = json_encode(array(
            'success' => false,
            'msg' => 'Unsuccessful recharge.')
        );
    }

    echo $result;
}

// Pin update
else if ($postjson['req'] == 'change_pin') {
    date_default_timezone_set("Africa/Nairobi");
    $updated_at = date("Y-m-d H:i:s");

    $pin1 = $postjson['pin1'];
    $pin2 = $postjson['pin2'];

    $pin_query = mysqli_query($mysqli, "UPDATE cards SET
         passcode = '$pin1',
         updated_at = '$updated_at'
         WHERE unique_id = '$postjson[unique_id]'
      ");

    if ($pin_query) {
        $result = json_encode(array(
            'success' => true,
            'msg' => 'Succesful pin update.')
        );
    } else {
        $result = json_encode(array(
            'success' => false,
            'msg' => 'Unsuccessful pin update.')
        );
    }

    echo $result;
}

// card-history function

else if($postjson['req'] == 'card-history'){

    $card_check = mysqli_query($mysqli, "SELECT * FROM cards WHERE user_id = '$postjson[user_id]' ");
    $check_results = mysqli_num_rows($card_check);
    if ($check_results > 0) {
        // This means that the user has already applied for a card

        $card = mysqli_fetch_array($card_check);
        $card_id = $card['id'];

        // After retrieving the cardId now we check it all against trips tables
        $all_trips = array();
        $trips = array();
        $trips_check = mysqli_query($mysqli, "SELECT 
                            trips.id, trips.route_id, unique_id, 
                            number_plate, payment, trips.updated_at as timestamp, 
                            from_point.name as departure, to_point.name as destination 
                            FROM trips 
                            INNER JOIN routes ON trips.route_id = routes.id 
                            INNER JOIN cards ON trips.card_id = cards.id 
                            INNER JOIN buses ON trips.bus_id = buses.id 
                            INNER JOIN end_points from_point ON routes.departure_id = from_point.id 
                            INNER JOIN end_points to_point ON routes.destination_id = to_point.id 
                            WHERE trips.card_id = '$card_id'
                            ORDER BY trips.created_at DESC ");
        if($trips_check){
            $total_trips = mysqli_num_rows($trips_check);
            if($total_trips > 0){
                while ($row = mysqli_fetch_array($trips_check, MYSQLI_ASSOC)){
                    $all_trips[] = $row;
                    $trips[] = array(
                        'timestamp' => $row['timestamp'],
                        'departure' => $row['departure'],
                        'destination' => $row['destination'],
                        'fare' => $row['payment'],
                        'bus' => $row['number_plate']
                    );
                }
                $result = json_encode(array(
                    'success' => true,
                    'has_tripped' => true, 
                    'trips' => $trips,
                    'commuter' => true,
                    'card_id' => $card_id )
                );
            }
            else{
                // Meaning that the card was found to have no trips
                // The difference is set by JSON variable 'has_tripped'
                $result = json_encode(array(
                    'success' => true, 
                    'has_tripped' => false,
                    'commuter' => true,
                    'card_id' => $card_id )
                );

            }
            
        }
        
    }

    echo $result;
}
