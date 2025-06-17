<?php
session_start();

if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_id']) && $_COOKIE['role'] === 'admin') {
    $_SESSION['admin_id'] = $_COOKIE['admin_id'];
    $_SESSION['role'] = 'admin';
}

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <style>
            .chat-box {
                height: 300px;
                overflow-y: auto;
                background: #f1f1f1;
                padding: 10px;
            }
        </style>
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
                            <input type="hidden" id="editClientId" name="id">
                            <div class="mb-3">
                                <label for="editFullName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="editFullName" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="editPhone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="editPhone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="editDietPlan">Diet Type</label>
                                <select name="diet_plan" class="form-control" id="editDietPlan" required>
                                    <option value="">Select Diet Type</option>
                                    <option value="Maintain">Maintain</option>
                                    <option value="Weight Loss">Weight Loss</option>
                                    <option value="Muscle Gain">Muscle Gain</option>
                                </select>
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
        <div class="container-fluid">
            <div class="sidebar">
                <h4 class="text-center">Admin Panel</h4>
                <a href="#" id="showClients">Clients</a>
                <a href="#" id="showAddDiet">Add Diet</a>
                <a href="#" id="showAllDiet">Show Diet Plan</a>
                <a href="#" id="showMessages">Messages</a>
                <a href="logout.php">Logout</a>
            </div>
            <div class="main">
                <div class="card" id="clientsSection">
                    <div><h2>Clients List</h2>
                    <button class="btn btn-danger" id="deleteSelectedClientBtn">Delete Selected</button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <td><input type="checkbox" class="row-check" id="selectAll"></td>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Diet Plan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="clientTableBody"></tbody>
                    </table>
                </div>

                <div class="card" id="addDietSection" style="display:none;">
                    <h2>Add Diet Plan</h2>
                    <form id="dietPlanFormSidebar">
                        <div class="mb-3">
                            <label class="form-label">Diet Type</label>
                            <select name="diet_type" class="form-control" required>
                                <option value="">Select Diet Type</option>
                                <option value="Maintain">Maintain</option>
                                <option value="Weight Loss">Weight Loss</option>
                                <option value="Muscle Gain">Muscle Gain</option>
                            </select>
                        </div>
                        <div id="mealSectionsSidebar"></div>
                        <button type="button" class="btn btn-success" id="addMealSidebar">+ Add Meal</button>
                        <br><br>
                        <button type="submit" class="btn btn-primary">Save Diet</button>
                    </form>
                </div>

                <!-- show diet -->
                <div class="card" id="allDietPlansSection" style="display:none;">
                    <h2>All Diet Plans</h2>
                    <div id="allDietData"></div>
                    <button id="deleteSelectedMealsBtn" class="btn btn-danger mt-3">Delete Selected Meals</button>
                </div>

                <div class="card" id="messagesSection" style="display:none;">
                    <h2>Messages from Clients</h2>
                    <div class="row">
                        <div class="col-md-3 border-end" style="max-height: 100%; overflow-y: auto;">
                            <h5>Clients</h5>
                            <ul class="list-group" id="clientList">
                                <?php
                                $clients = $conn->query("SELECT id, full_name FROM register");
                                while ($row = $clients->fetch_assoc()) {
                                    echo '<li class="list-group-item client-item" data-id="' . $row['id'] . '">' . htmlspecialchars($row['full_name']) . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <div class="chat-box mb-2 border rounded bg-white" id="adminChatBox"></div>
                            <div class="d-flex">
                                <input type="text" id="adminMessageInput" class="form-control me-2" placeholder="Type a reply..." required>
                                <button class="btn btn-success" id="adminSendBtn">Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function () { 

        // Active client
        $(document).on('click', '.Active-btn', function () {
        const clientId = $(this).data('id');    

        if (!confirm("Are you sure you want to activate this client?")) return;

            $.post('ajax_handler.php', {
                action: 'active_client',
                id: clientId
            }, function (res) {
                alert(res.message);
                if (res.success) loadClient(); // refresh table
            }, 'json');
        });

        //Delete Selected
        $('#deleteSelectedClientBtn').on('click', function () {
            const selected = [];

            $('.row-check:checked').each(function(){
                selected.push($(this).val());
            });
            if(selected.lenght === 0) {
                alert('Please select a client to delete');
                return;
            }
            if(!confirm("Are you sure you want to delete seleted clients?")) return;

            $.ajax({
                type: 'POST',
                url: 'ajax_handler.php',
                data:{ action:'delete_clients',ids: selected},
                success: function(res){ 
                    alert(res.message);
                    if(res.success) loadClient();
                },
                error:function(){
                    alert('Error deleting client');
                }
            });
        });

        // delete meals 
        $('#deleteSelectedMealsBtn').on('click', function () {
        const selected = [];

        $('#allDietData li').each(function () {
        const cb = $(this).find('input.meal-check');
        if (cb.is(':checked')) {
        selected.push({
            diet_type: cb.data('type'),
            meal_time: cb.data('time'),
            meal_item: cb.data('item'),
            meal_qty: cb.data('qty')
            }); 
        }
        });

        if (selected.length === 0) {
            alert("Please select at least one meal to delete.");
            return;
        }

        if (!confirm("Are you sure you want to delete selected meals?")) return;

        $.ajax({
            url: 'ajax_handler.php',
            type: 'POST',
            data: JSON.stringify({ action: 'delete_selected_meals', meals: selected }),
            contentType: 'application/json',
            success: function (res) {
            alert(res.message);
            if (res.success) loadAllDietPlans();
            },
            error: function () {
            alert('Server error while deleting.');
            }
        });
        });

        // tap functions
        $('#showClients').on('click', function () {
            $('#clientsSection').show();
            $('#addDietSection').hide();
            $('#messagesSection').hide();
            $('#allDietPlansSection').hide();
        });

        $('#showAddDiet').on('click', function () {
            $('#clientsSection').hide();
            $('#addDietSection').show();
            $('#messagesSection').hide();
            $('#allDietPlansSection').hide();
            $('#mealSectionsSidebar').empty();
            addMealRowSidebar();
        });

        $('#showMessages').on('click', function () {
            $('#clientsSection').hide();
            $('#addDietSection').hide();
            $('#messagesSection').show();
            $('#allDietPlansSection').hide();
        });
        $('#showAllDiet').on('click', function (){
            $('#messagesSection').hide();
        });

        function addMealRowSidebar() {
            $('#mealSectionsSidebar').append(`
                <div class="row g-2 mb-2 meal-row">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="meal_time[]" placeholder="Time" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="meal_item[]" placeholder="Meal Item" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="meal_qty[]" placeholder="Qty" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger w-100" onclick="$(this).closest('.meal-row').remove()">-</button>
                    </div>
                </div>
            `);
        }

        $('#addMealSidebar').on('click', function () {
            addMealRowSidebar();
        });

        $('#dietPlanFormSidebar').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize() + '&action=save_diet_plan';

            $.post('ajax_handler.php', formData, function (response) {
                alert(response.message);
                if (response.success) {
                    $('#dietPlanFormSidebar')[0].reset();
                    $('#mealSectionsSidebar').empty();
                }
            }, 'json');
        });

        // show diet 
        $('#showAllDiet').on('click', function () {
            $('#clientsSection, #addDietSection, #messagesSection').hide();
            $('#allDietPlansSection').show();
            loadAllDietPlans();
        });

        function loadAllDietPlans() {
        $.ajax({
            type: 'GET',
            url: 'ajax_handler.php',
            data: { action: 'load_diet_plans' },
            dataType: 'json',
            success: function (res) {
            if (res.success) {
                const container = $('#allDietData');  
                container.empty();

                Object.entries(res.data).forEach(([dietType, meals]) => {
                const mealList = meals.map(m =>
                `<li><input type="checkbox" class="row-check">
                <td>${m.time}</td> - <td>${m.item}</td> <td>(${m.qty})</td></li>`).join('');

                container.append(`
                    <div class="mb-4">
                    <h4 class="text-primary">${dietType}</h4>
                    <input type="checkbox" class="select-all-check" style="margin-right:10px">Meal</h5>
                    <ul>${meals.map(m => `
                    <li>
                    <input type="checkbox" class="meal-check"
                        data-type="${dietType}"
                        data-time="${m.time}"
                        data-item="${m.item}"
                        data-qty="${m.qty}" style="margin-right:6px">
                    ${m.time} - ${m.item} (${m.qty})
                    </li>
                    `).join('')}
                    </ul>
                    </div>
                `);
                });
            } else {
                $('#allDietData').html(`<div class="text-danger">Failed to load data.</div>`);
            }
            },
            error: function () {
            $('#allDietData').html(`<div class="text-danger">Server error. Please try again.</div>`);
            }
        });
        }

        function loadClient() {
            $.ajax({
                method: 'POST',
                url: 'ajax_handler.php',
                data: { action: 'fetch_client' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        let html = '';
                        response.data.forEach(client => {
                            html += `<tr>
                                <td><input type="checkbox" class="row-check" value="${client.id}"></td>
                                <td>${client.id}</td>
                                <td>${client.full_name}</td>
                                <td>${client.email}</td>
                                <td>${client.phone}</td>
                                <td>${client.diet_plan}</td>
                                <td>`;

                                if (client.is_active == 1) {
                                html += `
                                    <button class="btn btn-primary edit-btn" data-id="${client.id}" data-bs-toggle="modal" data-bs-target="#gymModal">Update</button>
                                    <button class="btn btn-danger delete-btn" data-id="${client.id}">Delete</button>`;
                            } else {
                                html += `
                                    <button class="btn btn-success Active-btn" data-id="${client.id}">Activate</button>`;
                            }
                            html += `</td></tr>`;
                        });
                        $('#clientTableBody').html(html);
                    } else {
                        $('#clientTableBody').html('<tr><td colspan="7">No clients found.</td></tr>');
                    }
                }
            });
        }
        loadClient();

        $(document).on('click', '.edit-btn', function () {
            const clientId = $(this).data('id');
            $.post('ajax_handler.php', { action: 'get_single_client', id: clientId }, function (res) {
                if (res.success) {
                    $('#editClientId').val(res.data.id);
                    $('#editFullName').val(res.data.full_name);
                    $('#editEmail').val(res.data.email);
                    $('#editPhone').val(res.data.phone);
                    $('#editDietPlan').val(res.data.diet_plan);
                } else {
                    alert('Failed to load data.');
                }
            }, 'json');
        });

        $('#editClientForm').on('submit', function (e) {
            e.preventDefault();
            const formData = {
                action: 'update_client',
                id: $('#editClientId').val(),
                full_name: $('#editFullName').val(),
                email: $('#editEmail').val(),
                phone: $('#editPhone').val(),
                diet_plan: $('#editDietPlan').val()
            };
            $.post('ajax_handler.php', formData, function (res) {
                alert(res.message);
                if (res.success) {
                    $('#gymModal').modal('hide');
                    loadClient();
                }
            }, 'json');
        });

        $(document).on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            if (confirm('Delete this client?')) {
                $.post('ajax_handler.php', { action: 'delete_client', id }, function (res) {
                    alert(res.message);
                    if (res.success) loadClient();
                }, 'json');
            }
        });

        $(document).on('change', '.select-all-check', function () {
            let container = $(this).closest('.mb-4');
            container.find('.meal-check').prop('checked', this.checked);
        });

        $('#selectAll').on('click', function () {
            $('.row-check').prop('checked', this.checked);
        });


        $('#clientList').html(`
            <?php
            $clients = $conn->query("
                SELECT r.id, r.full_name, COUNT(m.id) as unread_count
                FROM register r
                LEFT JOIN messages m ON r.id = m.client_id AND m.is_read = 0 AND m.sender = 'client'
                GROUP BY r.id
            ");
            while ($row = $clients->fetch_assoc()):
            ?>
            <li class="list-group-item client-item" data-id="<?= $row['id'] ?>">
                <?= htmlspecialchars($row['full_name']) ?>
                <?php if ($row['unread_count'] > 0): ?>
                <?php endif; ?>
            </li>
            <?php endwhile; ?>
        `)
        
        // message function fecth message
        let selectedClientId = null;
        $('.client-item').on('click', function () {
            $('.client-item').removeClass('active');
            $(this).addClass('active');

            selectedClientId = $(this).data('id');
            loadChat();
            if (!window.chatInterval) {
                window.chatInterval = setInterval(loadChat, 3000);
            }
        });

        // function loadChat() {
        //     if (!selectedClientId) return;
            
        //     // Mark messages as read when loading chat
        //     $.post('ajax_handler.php', {
        //         client_id: selectedClientId
        //     }, function() {
        //         // Then fetch and display messages
        //         fetch('ajax_handler.php?action=fetch_chat&client_id=' + selectedClientId)
        //             .then(res => res.json())
        //             .then(data => {
        //                 const chatBox = document.getElementById('adminChatBox');
        //                 chatBox.innerHTML = data.map(m => `
        //                     <div class="mb-2">
        //                         <div class="p-2 rounded ${m.sender === 'admin' ? 'bg-success text-white text-end' : 'bg-light'}">
        //                             <strong>${m.sender === 'admin' ? 'You' : 'Client'}:</strong> ${m.message}
        //                             ${m.is_read === '0' && m.sender === 'admin' ? ' <small>(unread)</small>' : ''}
        //                         </div>
        //                     </div>
        //                 `).join('');
        //                 chatBox.scrollTop = chatBox.scrollHeight;
        //             });
        //     });
        // }

        function loadChat() {
            if (!selectedClientId) return;
            fetch('ajax_handler.php?action=fetch_chat&client_id=' + selectedClientId)
                .then(res => res.json())
                .then(data => {
                    const chatBox = document.getElementById('adminChatBox');
                    chatBox.innerHTML = data.map(m => `
                        <div class="mb-2">
                            <div class="p-2 rounded ${m.sender === 'admin' ? 'bg-success text-white text-end' : 'bg-light'}">
                                <strong>${m.sender === 'admin' ? 'You' : 'Client'}:</strong> ${m.message}
                            </div>
                        </div>
                    `).join('');
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        $('#adminSendBtn').on('click', function () {
            const msg = $('#adminMessageInput').val().trim();
            if (!msg || !selectedClientId){
                alert('Please select a client and enter a message.');
            }
            if(msg.length > 500){
                alert('Message too long. Please keep it under 500 characters.');
            }

            const formData = new FormData();
            formData.append('action', 'send_chat');
            formData.append('message', msg);
            formData.append('sender', 'admin');
            formData.append('client_id', selectedClientId);

            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                $('#adminMessageInput').val('');
                loadChat();
            });
        });

    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
