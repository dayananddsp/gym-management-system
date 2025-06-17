<?php
session_start();
include 'db.php';

if (!isset($_SESSION['client_id']) && isset($_COOKIE['client_id'])) {
    $_SESSION['client_id'] = $_COOKIE['client_id'];
    $_SESSION['role'] = $_COOKIE['role'];
}

if (!isset($_SESSION['client_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['client_id'];

// Fetch client info
$stmt = $conn->prepare("SELECT * FROM register WHERE id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch diet meals
$diet_meals = [];
if ($client['diet_plan']) {
    $stmt = $conn->prepare("SELECT * FROM diet_meals WHERE diet_type = ?");
    $stmt->bind_param("s", $client['diet_plan']);
    $stmt->execute();
    $diet_meals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Profile picture
$profilePicPath = (!empty($client['profile_pic']) && file_exists($client['profile_pic']))
    ? htmlspecialchars($client['profile_pic'])
    : 'uploads/default-profile.png'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { display: flex; min-height: 100vh; background: #f8f9fa; }
    .sidebar {
      width: 240px; background: #343a40; color: white; padding-top: 20px; flex-shrink: 0;
    }
    .sidebar a {
      color: white; padding: 12px 20px; display: block; text-decoration: none;
    }
    .sidebar a:hover { background: #495057; }
    .main-content { flex: 1; padding: 20px; }
    .profile-pic {
      width: 120px; height: 120px; object-fit: cover;
      border-radius: 50%; border: 2px solid #007bff;
    }
    .chat-box {
      height: 300px; overflow-y: auto; background: #f1f1f1; padding: 10px;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4 class="text-center mb-4">Client Panel</h4>
  <a href="#" onclick="showTab('profile')">Profile</a>
  <a href="#" onclick="showTab('diet')">Diet</a>
  <a href="#" onclick="showTab('messages')">Messages</a>
  <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <h3 class="mb-4">Welcome, <?= htmlspecialchars($client['full_name']) ?></h3>

  <!-- Profile Tab -->
  <div id="profileTab">
    <div class="row">
      <div class="col-md-4 text-center">
        <img src="<?= $profilePicPath ?>" class="profile-pic mb-3" id="editprofile" alt="Profile Picture">
        <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="profile_pic" id="fileInput" accept="image/*" required>
        <button type="submit">Upload</button>
      </form>
      </div>
      <div class="col-md-8">
        <p><strong>Email:</strong> <?= htmlspecialchars($client['email']) ?></p>
        <prong>Phone:</strong> <?= htmlspecialchars($client['phone']) ?></p>
        <p><strong>Diet Plan:</strong> <?= htmlspecialchars($client['diet_plan']) ?></p>
      </div>
    </div>
  </div>

  <!-- Diet Tab -->
  <div id="dietTab" style="display:none;">
    <h5>Your Diet Plan for <?= htmlspecialchars($client['diet_plan']) ?></h5>
    <?php if ($diet_meals): ?>
      <ul class="list-group">
        <?php foreach ($diet_meals as $meal): ?>
          <li class="list-group-item">
            <strong><?= htmlspecialchars($meal['meal_time']) ?>:</strong>
            <?= htmlspecialchars($meal['meal_item']) ?> (<?= htmlspecialchars($meal['meal_qty']) ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="alert alert-warning">No diet plan assigned yet.</div>
    <?php endif; ?>
  </div>

  <!-- Messages Tab -->
  <div id="messagesTab" style="display:none;">
    <h5>Chat with Admin</h5>
    <div class="chat-box mb-2 border rounded bg-white" id="chatBox"></div>
    <div class="d-flex">
      <input type="text" id="messageInput" class="form-control me-2" placeholder="Type your message..." required>
      <button class="btn btn-primary" id="sendMessage">Send</button>
    </div>
  </div>
</div>

<script>

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'update_profile_pic');
    formData.append('client_id', clientId);

    fetch('ajax_handler.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('editprofile').src = data.new_path + '?' + new Date().getTime(); // Update img
        alert('Profile updated');
      } else {
        alert(data.message || 'Upload failed');
      }
    });
  });
 
const clientId = <?= $_SESSION['client_id'] ?>;
const tabs = ['profile', 'diet', 'messages'];

function showTab(tab) {
  tabs.forEach(t => {
    document.getElementById(t + 'Tab').style.display = 'none';
  });
  document.getElementById(tab + 'Tab').style.display = 'block';
}

// Load chat messages
function loadChat() {
  fetch('ajax_handler.php?action=fetch_chat&client_id=' + clientId)
    .then(res => res.json())
    .then(data => {
      const chatBox = document.getElementById('chatBox');
      if (!Array.isArray(data) || data.length === 0) {
        chatBox.innerHTML = `<div class="text-muted text-center">Start chatting with admin...</div>`;
        return;
      }
      chatBox.innerHTML = data.map(msg => `
        <div class="mb-2">
          <div class="p-2 rounded ${msg.sender === 'client' ? 'bg-primary text-white text-end' : 'bg-light'}">
            <strong>${msg.sender === 'client' ? 'You' : 'Admin'}:</strong> ${msg.message}
          </div>
        </div>
      `).join('');
      chatBox.scrollTop = chatBox.scrollHeight;
    }).catch(err => {
      console.error("Chat load failed:", err);
    });
}

// Send message
document.getElementById('sendMessage').addEventListener('click', function () {
  const message = document.getElementById('messageInput').value.trim(); 

  if (message.length > 500) {
    alert("Message too long. Please keep it under 500 characters.");
    return;
  }
  if (  
  !message ||                      
  /^[^A-Za-z0-9]+$/.test(message)       
  ) {
    alert("Message must contain at least one letter and one number, and cannot be only symbols.");
    return;
  }

  const formData = new FormData();
  formData.append('action', 'send_chat');
  formData.append('message', message);
  formData.append('sender', 'client');
  formData.append('client_id', clientId);

  fetch('ajax_handler.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(json => {
    if (json.success) {
      document.getElementById('messageInput').value = '';
      loadChat();
    } else {
      alert(json.message || "Message failed to send.");
    }
  })
  .catch(err => console.error("Send error:", err));
});

// Initialize
showTab('profile');
loadChat();
setInterval(loadChat, 3000);
</script>
</body>
</html>
