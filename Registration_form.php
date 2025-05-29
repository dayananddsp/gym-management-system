
<!DOCTYPE html>
<html lang="en">
<head>
      <title>Client Registration</title>
      <link rel="stylesheet" href="style.css">
</head>
<body class="registration-page">

  <form class="registration-form" action="Registration_form.php" method="POST" id="regForm">
    <h2>Client Registration</h2>

    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="full_name" required>
    </div>

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required>
    </div>

    <div class="form-group">
      <label for="phone">Phone Number</label>
      <input type="text" id="phone" name="phone" required>
    </div>

    <div class="form-group">
      <label for="diet_plan">Select Diet Plan</label>
      <select id="diet_plan" name="diet_plan" required>
        <option value="">-- Select Plan --</option>
        <option value="Weight Loss">Weight Loss</option>
        <option value="Muscle Gain">Muscle Gain</option>
        <option value="Maintenance">Maintenance</option>
      </select>
    </div>
    <div class="form-group">
      <label for="password">Create Password</label>
      <input type="password" id="password" name="password" required>
    </div>

    <button class="submit-btn submit" type="submit">Register</button>
    <div id="message" style="text-align:center; margin-top:10px;"></div>
  </form>
  <!-- Add this just before your script or inside the <head> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>  
    $('#regForm').on('submit', function(e){
  e.preventDefault();

  const formData = new FormData(this);
  formData.append('action', 'register_client');

  $.ajax({
    url: 'ajax_handler.php',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false, 
    dataType: 'json',
    success: function(response){
      $('#message')
        .text(response.message)
        .css('color', response.success ? 'green' : 'red');
      if (response.success) {
        $('#regForm')[0].reset();
      }
    },
    error: function() {
      $('#message').text('Something went wrong.').css('color', 'red');
    }
  });
});

  </script>

</body>
</html>
