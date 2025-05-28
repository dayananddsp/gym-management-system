<?php
include 'db.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$action = $_POST['action'] ??'';

switch ($action) {  
    case 'register_client':
    $name = $_POST["full_name"] ?? '';
    $email = $_POST["email"] ?? '';
    $phone = $_POST["phone"] ?? '';
    $diet_plan = $_POST["diet_plan"] ?? '';
    $password =password_hash($_POST["password"],PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO register(full_name,email,phone,diet_plan,password) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssss",$name,$email,$phone,$diet_plan,$password);

    if($stmt->execute()){
        
        echo json_encode(["success" => true, "message" => "Registration successful."]);
    }else{ 
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
        $stmt->close();
        $conn->close();
        break;

    default:
        echo json_encode(["success"=> false,"message"=> "Invalid Action"]);
        break;
}
}

?>