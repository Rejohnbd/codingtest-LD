
<table id="datatable" class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
</table>
<script>
    $(document).ready(function () {
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Initialize DataTable
        var table = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("users.data") }}',
                type: 'GET'
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search users...",
                lengthMenu: "_MENU_ users per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                infoEmpty: "No users found",
                infoFiltered: "(filtered from _MAX_ total users)"
            }
        });

        // Open Edit Modal and Load Data
        $(document).on('click', '.editUser', function () {
            var userId = $(this).data('id');
            var userName = $(this).data('name');
            var userEmail = $(this).data('email');
            
            // Populate the edit modal fields
            $('#editUserId').val(userId);
            $('#editName').val(userName);
            $('#editEmail').val(userEmail);
            
            // Show the modal
            $('#editUserModal').modal('show');
        });

        // Update User via AJAX
        $('#updateUserBtn').click(function(e) {
            e.preventDefault();
            
            var userId = $('#editUserId').val();
            var name = $('#editName').val();
            var email = $('#editEmail').val();
            
            // Basic client-side validation
            if (!name.trim()) {
                toastr.error('Name is required', 'Validation Error');
                return;
            }
            
            if (!email.trim()) {
                toastr.error('Email is required', 'Validation Error');
                return;
            }
            
            // Email format validation
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                toastr.error('Please enter a valid email address', 'Validation Error');
                return;
            }
            
            $.ajax({
                url: '/users/' + userId,
                type: 'PUT',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'name': name,
                    'email': email
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success');
                        $('#editUserModal').modal('hide');
                        table.ajax.reload(); // Reload DataTable
                    } else {
                        toastr.error(response.message, 'Error');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = 'Validation errors:\n';
                        $.each(errors, function(key, value) {
                            errorMessage += '- ' + value[0] + '\n';
                        });
                        toastr.error(errorMessage, 'Validation Error');
                    } else {
                        toastr.error('An error occurred while updating the user.', 'Error');
                    }
                }
            });
        });
    });
</script>