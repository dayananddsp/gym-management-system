<?php
include 'db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">
    <div class="modal fade" id="gymModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Update Client</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editClientForm">
            <input type="hidden" id="editClientForm">

            <div class="mb-3">
                <label for="editFullName" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="editClientForm" required>
            </div>

            <div class="mb-3">
                <label for="editEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" required>
            </div>

            <div class="mb-3">
                <label for="editPhone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="ediPhone" required>
            </div>

            <div class="mb-3">
                <label for="editDietPlan" class="form-label">Diet Plan</label>
                <input type="text" class="form-control" id="editDietPlan" required>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="editClientForm" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
    <div class="header">
        <h1>Admin Dashbord Gym Management</h1>
    </div>
    <div class="container-fluid">
        <div class="sidebar">
            <!-- <a href="#">Dashboard</a> -->
            <a href="#">Clients</a>
            <a href="#">Diet Plans</a>
            <a href="#">Messages</a>
            <a href="#">New Registrations</a>
            <a href="#">Logout</a>
        </div>
        <div class="main">
            <div class="card">
            <h2>Clients List</h2>
            <table>
                <thead>
                    <tr>
                    <td><input type="checkbox" class="row-check"></td>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Diet Plan</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody id="clientTableBody">
                    <!-- Rows will be loaded here -->
                </tbody>
                </table>
            </div>
        </div>
    </div>
    <script></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function(){
        loadClient();

        function loadClient(){
        $.ajax({
            method: 'POST',
            url: 'ajax_handler.php',
            data: { action:'fetch_client'},
            dataType: 'json',
            success: function(response){
                if (response.success){
                    let html = '';
                    response.data.forEach(client => {
                    html += `<tr>
                        <td><input type="checkbox" class="row-check"></td>
                        <td>${client.id}</td>
                        <td>${client.full_name}</td>
                        <td>${client.email}</td>
                        <td>${client.phone}</td>
                        <td>${client.diet_plan}</td>
                        <td>
                            <button class="edit-btn" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gymModal">Update</button>
                            <button class="delete-btn" data-id="${client.id}">Delete</button>
                        </td>
                    </tr>`;
                    });
                    $('#clientTableBody').html(html);
                }else{
                    $('#clientTableBody').html('<tr><td colspan="5">No clients found.</td></tr>');
                }
            }
        });
    }

    $(document).on('click', 'edit-btn', function(){
        const clientId = $(this).data('id');

        $.ajax({
            method: 'POST',
            url: 'ajax_handler.php',
            data: { action:'get_single_client', id: clientId },
            dataType: 'json',
            success: function(res){    
                if(res.success){
                    $('#editClientId').val(res.data.id);
                    $('#editFullName').val(res.data.full_name);
                    $('#editEmail').val(res.data.email);
                    $('#editPhone').val(res.data.phone);
                    $('#editDietPlan').val(res.data.diet_plan);
                }else{
                    alert('Failed to load Data');
                }
            }
        });
    });

    $(document).on ('click', '.delete-btn', function(){
        const id = $(this).data('id');
        if(confirm('Delete this client?')){
            $.ajax({
                method: 'POST',
                url: 'ajax_handler.php',
                data:{action:'delete_client', id: id},  
                dataType:'json',
                success:function(res){
                    alert(res.message);
                    if(res.message) loadClient();
                }
            });
        }
    });
}); 
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>