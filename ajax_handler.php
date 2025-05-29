<?php
include 'db.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
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
        break;

    case 'fetch_client':
        $query = "SELECT * FROM register";
        $result = mysqli_query($conn, $query);

        $clients = [];
        while ($row = mysqli_fetch_assoc($result)){
            $clients[] = $row;
        }
    
    echo json_encode(["success"=> true, "data" => $clients]);
    exit;

    case 'delete_client':
        $id = $_POST['id'] ?? '';
        $stmt = $conn->prepare('DELETE FROM register Where id = ?');
        $stmt->bind_param('i',$id);
        echo json_encode([
            "success" => $stmt->execute(),
            "message" => $stmt->execute() ? "Delete successfully." : "Delete Failed."
        ]);
        $stmt->close();
        break;
    
    case 'get_single_client':
        $id = intval($POST['id']);
        $stmt = $conn->prepare("SELECT * FROM register Where id = ?");
        $stmt->execute(['id']);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => $data ? true : false,'data'=> $data]);
        exit;
        
    }
}
?>