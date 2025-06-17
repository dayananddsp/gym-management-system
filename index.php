<?php
session_start();

if (isset($_SESSION['client_id']) && $_SESSION['role'] === 'client') {
    header("Location: client.php"); 
    exit;
}

if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Client Registration</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .error-msg {
      color: red;
      font-size: 13px;
      margin-top: 5px;
    }
  </style>
</head>
<body class="registration-page">

<form class="registration-form" enctype="multipart/form-data" id="regForm">
  <h2>Client Registration</h2>

  <div class="form-group">
    <label for="full_name">Full Name</label>
    <input type="text" id="full_name" name="full_name">
    <div class="error-msg" id="error-name"></div>
  </div>

  <div class="form-group">
    <label for="email">Email Address</label>
    <input type="email" id="email" name="email">
    <div class="error-msg" id="error-email"></div>
  </div>

  <div class="form-group">
    <label for="diet_plan">Select Diet Plan</label>
    <select id="diet_plan" name="diet_plan">
      <option value="">-- Select Plan --</option>
      <option value="Weight Loss">Weight Loss</option>
      <option value="Muscle Gain">Muscle Gain</option>
      <option value="Maintain">Maintain</option>
    </select>
    <div class="error-msg" id="error-diet"></div>
  </div>

  <div class="form-group">
    <label for="phone">Phone Number</label>
    <input type="text" id="phone" name="phone">
    <div class="error-msg" id="error-phone"></div>
  </div>

  <div class="form-group">
    <label for="profile_pic">Profile Image</label>
    <input type="file" id="profile_pic" name="profile_pic">
    <div class="error-msg" id="error-pic"></div>
  </div>

  <div class="form-group">
    <label for="password">Create Password</label>
    <input type="password" id="password" name="password">
    <div class="error-msg" id="error-password"></div>
  </div>

  <button class="submit-btn" type="submit">Register</button>

  <div class="alreadyuser">
    <p>Already a user? <a href="login.php">Log in</a></p>
  </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function clearErrors() {
  $('.error-msg').text('');
}

function validateForm() {
  clearErrors();
  let isValid = true;

  let name = $('#full_name').val().trim();
  let email = $('#email').val().trim();
  let diet = $('#diet_plan').val().trim();
  let phone = $('#phone').val().trim();
  let pic = $('#profile_pic').val().trim();
  let password = $('#password').val().trim();

  if (name.length < 3) {
    $('#error-name').text("Name must be at least 3 characters.");
    isValid = false;
  }

// Email validation (inline)
if (!(email &&
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) &&
    (email.endsWith('.com') || email.endsWith('.in') || email.endsWith('.co'))
  )
  ) {
    $('#error-email').text("Enter a valid email ending with .com, .in, or .co.");
    isValid = false;
  }

// Diet plan validation
if (!(diet && diet !== "")) {
  $('#error-diet').text("Please select a diet plan.");
  isValid = false;
}

// Phone number validation (exactly 10 digits)
if (!/^\d{10}$/.test(phone)) {
  $('#error-phone').text("Phone must be exactly 10 digits.");
  isValid = false;
}


  if (diet === "") {
    $('#error-diet').text("Please select a diet plan.");
    isValid = false;
  }

  if (!/^\d{10}$/.test(phone)) {
    $('#error-phone').text("Phone must be exactly 10 digits.");
    isValid = false;
  }

  if (!pic) {
    $('#error-pic').text("Please upload a profile image.");
    isValid = false;
  }

  if (!(password.length >= 6 && /[A-Za-z]/.test(password) && /\d/.test(password))) {
    $('#error-password').text("Password must be at least 6 characters and include letters and numbers.");
    isValid = false;
  }


  return isValid;
}

$('#regForm').on('submit', function(e) {
  e.preventDefault();
  if (!validateForm()) return;

  const formData = new FormData(this);
  formData.append('action', 'register_client');

  $.ajax({
    url: 'ajax_handler.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    dataType: 'json',
    success: function(response) {
      alert(response.message);
      if (response.success) {
        $('#regForm')[0].reset();
        clearErrors();
      }
    },
    error: function() {
      alert("Something went wrong.");
    }
  });
});
</script>


</body>
</html>