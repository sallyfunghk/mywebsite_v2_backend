<?php 

// Set the timezone to Hong Kong
date_default_timezone_set('Asia/Hong_Kong');

// Allow from any origin
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Headers: *");
//header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Max-Age: 86400');
header('Content-type: application/json');

require_once 'config.php';


// Takes raw data from the request and convert to php object
$json = file_get_contents('php://input');
$request = json_decode($json);

// Request data validation
if( request_data_validation($request) === False ){
    exit;
}

// Connect to database
$host = constant('DB_HOST');
$username = constant('DB_USERNAME');
$password = constant('DB_PASSWORD');
$database = constant('DB_DATABASE');

$db_connect = new mysqli($host, $username, $password, $database);

if($db_connect->connect_error){
    $response = array(
        'success' => False,
        'err_code' => 81,
        'message' => 'DB Error: DB connection failed.',
    );

    echo json_encode($response);
    exit;
}

// Insert data to database
$create_on = date("Y-m-d H:i:s");

$sql = "INSERT INTO `contact_form` (`first_name`, `last_name`, `email`, `phone_num`, `company_name`, `comment`, `create_on`) 
VALUES ('{$request->first_name}', '{$request->last_name}', '{$request->email}', '{$request->phone_num}', '{$request->company_name}', '{$request->comment}', '{$create_on}')";

if ($db_connect->query($sql) === False) {

    $response = array(
        'success' => False,
        'err_code' => 82,
        'message' => 'DB Error: Insert DB failed.',
    );
    echo json_encode($response);
    return;
} 
else 
{
    $response = array(
        'success' => True,
        'err_code' => 0,
        'message' => 'Submitted',
    );

    echo json_encode($response);
    return;
}
  
$db_connect->close();



function request_data_validation($request){

    $request_fields = array(
        'first_name'=>array(
            'required'=>True,
        ), 
        'last_name'=>array(
            'required'=>True,
        ), 
        'email'=>array(
            'required'=>True,
            'regex'=>'/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/',
        ), 
        'phone_num'=>array(
            'required'=>False,
        ), 
        'company_name'=>array(
            'required'=>False,
        ), 
        'comment'=>array(
            'required'=>True,
        ), 
    );

    foreach( $request_fields as $request_field => $field_validate ){

        if( $field_validate['required'] ){

            if( $request->{$request_field} == '' ){
                $response = array(
                    'success' => False,
                    'err_code' => 71,
                    'message' => "Invalid input: {$request_field} is required.",
                );
                echo json_encode($response);
                return False;
            }

        }

        if( isset( $field_validate['regex'] ) ){

            if( !preg_match($field_validate['regex'], $request->{$request_field}) ){
                $response = array(
                    'success' => False,
                    'err_code' => 72,
                    'message' => "Invalid input: {$request_field} format incorrect.",
                );
                echo json_encode($response);
                return False;
            }

        }
        
    }


    return True;

}
