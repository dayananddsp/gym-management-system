    <?php
    header('Content-Type: application/json');
    include 'db.php';

    // 🔧 Accept action from either POST or GET
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_POST['action'] ?? $_GET['action'] ?? $input['action'] ?? '';

    switch ($action) {
        case 'register_client':
            $name = trim($_POST["full_name"] );
            $email = trim($_POST["email"]);
            $phone = trim($_POST["phone"]);
            $diet_plan = trim($_POST["diet_plan"]);
            $password_raw = $_POST["password"];
            $errors = [];

            // 🔍 Server-side validation
            if (strlen($name) < 3) {
                $errors[] = "Name must be at least 3 characters.";
            }

            if (substr($email, -4) !== '.com') {
                $errors[] = "Email must end with .com.";
            }

            if (!preg_match('/^\d{10}$/', $phone)) {
                $errors[] = "Phone must be 10 digits.";
            }

            if (empty($diet_plan)) {
                $errors[] = "Please select a diet plan.";
            }

            if (!preg_match('/^(?=(?:[^a-zA-Z]*[a-zA-Z]){4}[^a-zA-Z]*$)(?=(?:\D*\d){4}\D*$).{8}$/', $password_raw)) {
                $errors[] = "Password must contain exactly 4 letters and 4 numbers (8 characters total).";
            }

            $profile_pic = '';
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($_FILES['profile_pic']['type'], $allowed_types)) {
                    $errors[] = "Only JPG, PNG files are allowed.";
                } else {
                    $upload_dir = "uploads/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $tmp_name = $_FILES['profile_pic']['tmp_name'];
                    $filename = basename($_FILES['profile_pic']['name']);
                    $target_file = $upload_dir . uniqid() . "_" . $filename;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $profile_pic = $target_file;
                    } else {
                        $errors[] = "Failed to upload image.";
                    }
                }
            } else {
                $errors[] = "Profile picture is required.";
            }

            // Return early if validation fails
            if (!empty($errors)) {
                echo json_encode(["success" => false, "message" => implode("<br>", $errors)]);
                exit;
            }

            // Hash password & insert
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO register (full_name, email, phone, diet_plan, profile_pic, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $diet_plan, $profile_pic, $password);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Registration successful."]);
            } else {
                echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
            }

            $stmt->close();
            break;


        case 'fetch_client':
            $result = $conn->query("SELECT * FROM register");
            $clients = [];
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row; // includes is_active
            }
            echo json_encode(["success" => true, "data" => $clients]);
            break;


        case 'delete_client':
            $id = $_POST['id'] ?? '';
            $stmt = $conn->prepare("DELETE FROM register WHERE id = ?");
            $stmt->bind_param("i", $id);
           $success = $stmt->execute();
            echo json_encode([
                "success" => $success,
                "message" => $success ? "..." : "..."
            ]);
            break;

        case 'get_single_client':
            $id = $_POST['id'] ?? '';
            $stmt = $conn->prepare("SELECT * FROM register WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode(["success" => true, "data" => $result->fetch_assoc()]);
            break;

        case 'update_client':
            $id = $_POST['id'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $diet_plan = $_POST['diet_plan'] ?? '';
            $stmt = $conn->prepare("UPDATE register SET full_name = ?, email = ?, phone = ?, diet_plan = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $diet_plan, $id);
            echo json_encode([
                "success" => $stmt->execute(),
                "message" => $stmt->execute() ? "Client updated successfully." : "Update failed."
            ]);
            break;

        case 'update_profile_pic':
            $client_id = (int)$_POST['client_id'];

            if (!isset($_FILES['profile_pic'])) {
                echo json_encode(['success' => false, 'message' => 'No file']);
                exit;
            }

            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/profile_' . $client_id . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filename);

            $stmt = $conn->prepare("UPDATE register SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $client_id);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['success' => true, 'new_path' => $filename]);
            break;

        case 'save_diet_plan':
            $diet_type = $_POST['diet_type'] ?? '';
            $meal_times = $_POST['meal_time'] ?? [];
            $meal_items = $_POST['meal_item'] ?? [];
            $meal_qtys  = $_POST['meal_qty'] ?? [];

            if (!$diet_type || empty($meal_times)) {
                echo json_encode(['success' => false, 'message' => 'Missing diet data.']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO diet_meals (diet_type, meal_time, meal_item, meal_qty) VALUES (?, ?, ?, ?)");
            $success = true;
            for ($i = 0; $i < count($meal_times); $i++) {
                $stmt->bind_param("ssss", $diet_type, $meal_times[$i], $meal_items[$i], $meal_qtys[$i]);
                if (!$stmt->execute()) {
                    $success = false;
                    break;
                }
            }
            $stmt->close();
            echo json_encode(["success" => $success, "message" => $success ? "Diet plan saved." : "Failed to save diet plan."]);
            break;

        case 'send_chat':
            $msg = $_POST['message'] ?? '';
            $sender = $_POST['sender'] ?? '';
            $client_id = (int)$_POST['client_id'];
            if ($msg && $sender && $client_id) {
                $stmt = $conn->prepare("INSERT INTO messages (client_id, sender, message) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $client_id, $sender, $msg);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Missing input"]);
            }
            break;

        case 'fetch_chat':
            $client_id = (int)($_GET['client_id'] ?? 0);
            $stmt = $conn->prepare("SELECT sender, message FROM messages WHERE client_id = ? ORDER BY sent_at ASC");
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC)); 
            break;

        case 'load_diet_plans':
            $stmt = $conn->query("
                SELECT diet_type, meal_time, meal_item, meal_qty
                FROM diet_meals
                ORDER BY diet_type, meal_time
            ");

            $grouped = [];

            while ($row = $stmt->fetch_assoc()) {
                $grouped[$row['diet_type']][] = [
                    'time' => $row['meal_time'],
                    'item' => $row['meal_item'],
                    'qty'  => $row['meal_qty']
                ];
            }
            echo json_encode(['success' => true, 'data' => $grouped]);
            break;

        case 'delete_selected_meals':
            $input = json_decode(file_get_contents('php://input'), true);
            $meals = $input['meals'] ?? [];

            if (empty($meals)) {
                echo json_encode(['success' => false, 'message' => 'No meals selected']);
                break;
            }

            $success = true;
            $stmt = $conn->prepare("DELETE FROM diet_meals WHERE diet_type = ? AND meal_time = ? AND meal_item = ? AND meal_qty = ?");

            if (!$stmt) {
                echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
                break;
            }

            foreach ($meals as $meal) {
                $stmt->bind_param("ssss", $meal['diet_type'],$meal['meal_time'], $meal['meal_item'], $meal['meal_qty']);
                if (!$stmt->execute()) {
                    $success = false;
                    break;
                }
            }
            $stmt->close();
            echo json_encode([
                "success" => $success,
                "message" => $success ? "Meals deleted successfully." : "Failed to delete meals."
            ]);
            break;

        case 'delete_clients':
            $ids = $_POST['ids'] ?? [];

            if (empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'No client selected.']);
                break;
            }

            $ids = array_map('intval', $ids); // sanitize input
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $types = str_repeat('i', count($ids)); // all integers
            $sql = "DELETE FROM register WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param($types, ...$ids);
                $success = $stmt->execute();
                $stmt->close();

                echo json_encode([
                    'success' => $success,
                    'message' => $success ? "Clients deleted successfully." : "Failed to delete clients."
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Prepare failed: " . $conn->error
                ]);
            }
            break;

        case 'active_client':
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Invalid client ID.']);
                break;
            }

            $stmt = $conn->prepare("UPDATE register SET is_active = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Client activated successfully.' : 'Activation failed.'
            ]);
            break;

    }
