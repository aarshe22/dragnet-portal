<?php
$title = 'User Management - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-users me-2"></i>User Management</h1>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus me-1"></i>New User
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="users-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="user_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="user_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="user_role" name="role">
                            <option value="Guest">Guest</option>
                            <option value="ReadOnly">ReadOnly</option>
                            <option value="Operator">Operator</option>
                            <option value="Administrator">Administrator</option>
                            <option value="TenantOwner">TenantOwner</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadUsers();
});

function loadUsers() {
    $.get('/api/admin/users', function(users) {
        const tbody = $('#users-table tbody');
        if (users.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">No users found</td></tr>');
            return;
        }
        
        const html = users.map(user => `
            <tr>
                <td>${user.email}</td>
                <td><span class="badge bg-info">${user.role}</span></td>
                <td>${user.last_login ? new Date(user.last_login).toLocaleString() : 'Never'}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        tbody.html(html);
    });
}

function showCreateModal() {
    $('#userModalTitle').text('New User');
    $('#userForm')[0].reset();
    $('#user_id').val('');
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

function editUser(id) {
    $.get(`/api/admin/users/${id}`, function(user) {
        $('#userModalTitle').text('Edit User');
        $('#user_id').val(user.id);
        $('#user_email').val(user.email);
        $('#user_role').val(user.role);
        new bootstrap.Modal(document.getElementById('userModal')).show();
    });
}

function saveUser() {
    const id = $('#user_id').val();
    const data = {
        email: $('#user_email').val(),
        role: $('#user_role').val()
    };
    
    const url = id ? `/api/admin/users/${id}` : '/api/admin/users';
    const method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function() {
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadUsers();
        },
        error: function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
    });
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: `/api/admin/users/${id}`,
            method: 'DELETE',
            success: function() {
                loadUsers();
            }
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

