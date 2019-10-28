<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-with");
header("Content-Type: application/json; charset = utf-8");

include "config.php";

$postjson = json_decode(file_get_contents('php://input'), true);

if($postjson['aksi'] == 'add_register'){
   $password = md5($postjson['password']);
   //dates
   date_default_timezone_set("Africa/Nairobi");
   $created_at = date("Y-m-d H:i:s");
   $updated_at = date("Y-m-d H:i:s");

   $query = mysqli_query($mysqli, "INSERT INTO users SET 
      fname = '$postjson[fname]',
      lname = '$postjson[lname]',
      email = '$postjson[email]',
      avatar = 'user_default.jpg',
      password = '$password',
      role_id = 4,
      username = '$postjson[username]',
      created_at = '$created_at',
      updated_at = '$updated_at'

      ");

   if($query){
      $result = json_encode(array('success' => true));
   }
   else{
      $result = json_encode(array('success' => false, 'msg' => 'Error, please try again.'));
   }

   echo $result;
}

else if($postjson['aksi'] == 'login'){
   $password = md5($postjson['password']);

   $query = mysqli_query($mysqli, "SELECT * FROM users WHERE username = '$postjson[username]' AND password = '$password' ");
   $check = mysqli_num_rows($query);

   if($check > 0 ){
      $data = mysqli_fetch_array($query);
      $datauser = array(
         'user_id' => $data['id'],
         'fname' => $data['fname'],
         'lname' => $data['lname'],
         'username' => $data['username'],
         'email' => $data['email'],
         'password' => $data['password']
      );

      
      if($query){
         $result = json_encode(array('success' => true, 'result' => $datauser ));
      }
      else{
         $result = json_encode(array('success' => false, 'msg' => 'Error, please try again.'));
      }

   }
   else{
      $result = json_encode(array('success' => false, 'msg' => 'No user with such credentials found.'));
   }


   echo $result;
}

else if($postjson['aksi'] == 'profile'){
   $profile = array();
   $query = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM users WHERE id = '$postjson[user_id]' "));

   $profile[] = array(
      'user_id' => $query['id'],
      'fname' => $query['fname'],
      'lname' => $query['lname'],
      'username' => $query['username'],
      'email' => $query['email'],
      'password' => $query['password']
   );

      
   if($query){
      // if the query to pull out the user's profile goes through
      // Then check if he/she is a commuter(I mean has a card already)
      // Checking if user has a card

      $card_check = mysqli_query($mysqli, "SELECT * FROM cards WHERE user_id = '$postjson[user_id]' ");
      $check_results = mysqli_num_rows($card_check);
      if($check_results > 0 ){
         $card = mysqli_fetch_array($card_check);
         $card_data = array(
            'card_id' => $card['id'],
            'unique_id' => $card['unique_id'],
            'balance' => $card['balance'],
            'passcode' => $card['passcode'],
            'status' => $card['status']
         );

         if($card_check){
            // Incase he/she is found to own a card then get it's data and bundle them up in JSON Obj
            $result = json_encode(array(
               'success' => true, 
               'profiles' => $profile,
               'commuter' => true, 
               'passengerInfo' => $card_data )
            );
         }
         else{
            //
         }
      }
      else{
         if($card_check){
            // Incase he/she has no card, then nothing to do here
            $result = json_encode(array(
               'success' => true, 
               'profiles' => $profile,
               'commuter' => false )
            );
         }
         else{
            //
         }
      }

   }
   else{
      $result = json_encode(array('success' => false));
   }

   echo $result;

}

else if($postjson['aksi'] == 'update_profile'){
   //dates
   date_default_timezone_set("Africa/Nairobi");
   $updated_at = date("Y-m-d H:i:s");

   $query = mysqli_query($mysqli, "UPDATE users SET
      fname = '$postjson[fname]',
      lname = '$postjson[lname]',
      updated_at = '$updated_at'
      WHERE id = '$postjson[user_id]'
   ");

      
   if($query){
      $result = json_encode(array('success' => true ));
   }
   else{
      $result = json_encode(array('success' => false));
   }

   echo $result;
}
