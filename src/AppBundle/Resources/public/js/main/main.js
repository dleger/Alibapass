function main() {
    
    // ********
    // * Init *
    // ********
    if ($( '#id-company-delete-button' ).length) {
        $( '#id-company-delete-button' ).prop('disabled', true);
    } else if ($( '#id-groups-delete-button' ).length) {
        $( '#id-groups-delete-button' ).prop('disabled', true);
    } else if ($( '#id-users-delete-button' ).length) {
        $( '#id-users-delete-button' ).prop('disabled', true);
        
        $.fn.bootstrapSwitch.defaults.size = 'mini';
        
        $( '#id-user-admin, #id-user-active' ).bootstrapSwitch();
    }
    
    if ($( '#id-entries-table' ).length) { 

        $(".btn-password").tooltip();
        
        var clipboards = new Clipboard('.btn-password');

        clipboards.on('success', function(e) {
            $(e.trigger).attr('title', 'Copied').tooltip('enable').tooltip('fixTitle').tooltip('show');
        });
        
        $( '.btn-password' ).mouseout(function () {
            $( this ).tooltip('disable');
        });
    }
    
    // *************
    // * Vars Init *
    // *************
    var just_after_edit_click = false;
    var nbr_types_li = 0;
    var all_field_type = new Array();
    
    // *********************
    // * Add a new Company *
    // *********************
    $( '#id-site-modal' ).on('shown.bs.modal', function (e) {
        
        if (e.relatedTarget.id === 'id-new-site-button') {
            $( '#id-company-name' ).focus();
        } 
    });
    
    $( '#id-new-site-button' ).click(function () {
        $( '#id-site-form-group-title-div' ).text('New Customer/Site');
        $( '#id-company-create-button' ).css('display', 'inline-block');
    });
    
    $( '#id-company-create-button' ).click(function () {
        
        $.post('/companies/', {
            new_company_name: $( '#id-company-name' ).val(),
            company_action: 'Create'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-company-create-button' ).prop('disabled', true);
                $( '#id-company-name' ).prop('disabled', true);

                if (data['data']['success']) {
                    $.notify('New Customer/Site successfully added', 'success');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    var string_name_error = '';

                    console.log(data['data']);

                    $.each(data['data'], function(key, value) {
                        if (string_name_error.length > 0) {
                            string_name_error += "\n";
                        }

                        if (value['name'] !== undefined) {
                            string_name_error += value['name'];
                        }
                    });

                    if (string_name_error.length > 0) {
                        $( '#id-site-div-form-group' ).addClass('has-error');
                        $( '#id-site-error-div' ).text(string_name_error);
                        $( '#id-site-error-div' ).collapse('show');
                    }
                    
                    $( '#id-company-create-button' ).prop('disabled', false);
                    $( '#id-company-name' ).prop('disabled', false);
                }
            }
        });
        
    });
    
    $( '#id-company-name' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-site-div-form-group' ).hasClass('has-error')) {
            $( '#id-site-div-form-group' ).removeClass('has-error');
            $( '#id-site-error-div' ).text('');
            $( '#id-company-name' ).val('');
            $( '#id-site-error-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    $( '#id-site-modal' ).on('hidden.bs.modal', function () {
        $( '#id-site-div-form-group' ).removeClass('has-error');
        $( '#id-company-name' ).val('');
        $( '#id-site-error-div' ).collapse('hide');
        $( '#id-site-error-div' ).text('');
        $( '#id-company-create-button' ).css('display', 'none');
        $( '#id-company-edit-button' ).css('display', 'none');
    });
    
    $( '#id-company-name' ).keypress(function (e) {
        var key = e.which;
        
        if (key === 13) {
            $( '#id-company-create-button' ).click();
            return false;
        }
    });
    
    // *****************************
    // * Delete selected companies *
    // *****************************
    $( '#id-company-delete-button' ).click(function () {
        bootbox.confirm("Are you sure?", function(result) {
            if (result) {
                delete_companies();
            }
        });
    });
    
    function delete_companies() {
        var checked_companies = new Array();
        var cpt_companies = 0;
        
        $( '#id-companies-form' ).find(':checkbox').each(function () {
            if ( $( this ).is(':checked') ) {
                checked_companies[cpt_companies] = $( this ).val();
                cpt_companies++;
            }
        });
        
        $.post('/companies/', {
            selected_companies: checked_companies,
            company_action: 'Delete'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-company-delete-button' ).prop('disabled', true);
                
                $.notify('Selected Customers/Site Names have been successfully removed', 'success');
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    }
    
    // ************************************************************************
    // * Detect clicks on companies checkboxes and manage delete button state *
    // ************************************************************************
    $( '#id-companies-form' ).find(':checkbox').click(function () {
        if ( $( '#id-companies-form input[type=checkbox]:checked' ).length > 0) {
            $( '#id-company-delete-button' ).prop('disabled', false);
            $( '#id-company-delete-button' ).removeClass('disabled');
        } else {
            $( '#id-company-delete-button' ).prop('disabled', true);
            $( '#id-company-delete-button' ).addClass('disabled');
        }
    });
    
    // *******************************************
    // * Detect clicks on companies edit buttons *
    // *******************************************
    $( '#id-sites-table button[type=button]' ).click(function () {
        just_after_edit_click = true;
        
        $( '#id-site-form-group-title-div' ).text('Edit Customer/Site');
        $( '#id-company-edit-button' ).css('display', 'inline-block');
        
        $( '#id-company-id' ).val( $( this ).data('edit-id') );        
        $( '#id-company-name' ).val($( this ).parent().parent().find('span').text());
    });
    
    // *************************
    // * Edit selected Company *
    // *************************
    $( '#id-company-edit-button' ).click(function () {
        $.post('/companies/', {
            edit_company_id: $( '#id-company-id' ).val(),
            edit_company_name: $( '#id-company-name' ).val(),
            company_action: 'Edit'
        },
        function (data, status) {     
            if (status === 'success') {
                $( '#id-company-edit-button' ).prop('disabled', true);
                $( '#id-company-name' ).prop('disabled', true);

                if (data['data']['success']) {
                    $.notify('Customer/Site successfully edited', 'success');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    var string_name_error = '';

                    $.each(data['data'], function(key, value) {
                        if (string_name_error.length > 0) {
                            string_name_error += "\n";
                        }

                        if (value['name'] !== undefined) {
                            string_name_error += value['name'];
                        }
                    });

                    if (string_name_error.length > 0) {
                        $( '#id-site-div-form-group' ).addClass('has-error');
                        $( '#id-site-error-div' ).text(string_name_error);
                        $( '#id-site-error-div' ).collapse('show');
                    }

                    $( '#id-company-edit-button' ).prop('disabled', false);
                    $( '#id-company-name' ).prop('disabled', false);
                }
            }
        });
    });
    
    // *******************
    // * Add a new Group *
    // *******************
    $( '#id-group-modal' ).on('shown.bs.modal', function (e) {
        
        if (e.relatedTarget.id === 'id-new-group-button') {
            $( '#id-group-name' ).focus();
        } 
    });
    
    $( '#id-new-group-button' ).click(function () {
        $( '#id-group-form-group-title-div' ).text('New Group');
        $( '#id-group-create-button' ).css('display', 'inline-block');
    });
    
    $( '#id-group-create-button' ).click(function () {
        
        $.post('/groups/', {
            new_group_name: $( '#id-group-name' ).val(),
            new_group_description: $( '#id-group-description' ).val(),
            group_action: 'Create'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-group-create-button' ).prop('disabled', true);
                $( '#id-group-name' ).prop('disabled', true);
                $( '#id-group-description' ).prop('disabled', true);
                
                if (data['data']['success']) {
                    $.notify('New Group successfully added', 'success');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    
                    var string_name_error = '';
                    var string_description_error = '';
                    var cpt_err_names = 0;
                    
                    $.each(data['data'], function(key, value) {
                        if (string_name_error.length > 0) {
                            string_name_error += "\n";
                        }
                        
                        if (string_description_error.length > 0) {
                            string_description_error += "\n";
                        }
                        
                        if (value['name'] !== undefined) {
                            string_name_error += value['name'];
                        } else if (value['description'] !== undefined) {
                            string_description_error += value['description'];
                        }
                    });
                    
                    
                    
                    if (string_name_error.length > 0) {
                        $( '#id-group-div-form-name-group' ).addClass('has-error');
                        $( '#id-group-error-name-div' ).text(string_name_error);
                        $( '#id-group-error-name-div' ).collapse('show');
                    }
                    
                    if (string_description_error.length > 0) {
                        $( '#id-group-div-form-description-group' ).addClass('has-error');
                        $( '#id-group-error-description-div' ).text(string_description_error);
                        $( '#id-group-error-description-div' ).collapse('show');
                    }
                    
                    $( '#id-group-create-button' ).prop('disabled', false);
                    $( '#id-group-name' ).prop('disabled', false);
                    $( '#id-group-description' ).prop('disabled', false);
                }
            }
        });
        
    });
    
    $( '#id-group-name' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-name-group' ).hasClass('has-error')) {
            $( '#id-group-div-form-name-group' ).removeClass('has-error');
            $( '#id-group-error-name-div' ).text('');
            $( '#id-group-name' ).val('');
            $( '#id-group-error-name-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    $( '#id-group-description' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-description-group' ).hasClass('has-error')) {
            $( '#id-group-div-form-description-group' ).removeClass('has-error');
            $( '#id-group-error-description-div' ).text('');
            $( '#id-group-description' ).val('');
            $( '#id-group-error-description-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    $( '#id-group-modal' ).on('hidden.bs.modal', function () {
        $( '#id-group-div-form-name-group' ).removeClass('has-error');
        $( '#id-group-div-form-description-group' ).removeClass('has-error');
        $( '#id-group-name' ).val('');
        $( '#id-group-description' ).val('');
        $( '#id-group-error-name-div' ).collapse('hide');
        $( '#id-group-error-name-div' ).text('');
        $( '#id-group-error-description-div' ).collapse('hide');
        $( '#id-group-error-description-div' ).text('');
        $( '#id-group-create-button' ).css('display', 'none');
        $( '#id-group-edit-button' ).css('display', 'none');
    });
    
    $( '#id-group-name, #id-group-description' ).keypress(function (e) {
        var key = e.which;
        
        if (key === 13) {
            $( '#id-group-create-button' ).click();
            return false;
        }
    });
    
    // *********************************************************************
    // * Detect clicks on groups checkboxes and manage delete button state *
    // *********************************************************************
    $( '#id-groups-form' ).find(':checkbox').click(function () {
        if ( $( '#id-groups-form input[type=checkbox]:checked' ).length > 0) {
            $( '#id-groups-delete-button' ).prop('disabled', false);
            $( '#id-groups-delete-button' ).removeClass('disabled');
        } else {
            $( '#id-groups-delete-button' ).prop('disabled', true);
            $( '#id-groups-delete-button' ).addClass('disabled');
        }
    });
    
    // **************************
    // * Delete selected groups *
    // **************************
    $( '#id-groups-delete-button' ).click(function () {
        bootbox.confirm("Are you sure?", function(result) {
            if (result) {
                delete_groups();
            }
        });
    });
    
    function delete_groups() {
        var checked_groups = new Array();
        var cpt_groups = 0;
        
        $( '#id-groups-form' ).find(':checkbox').each(function () {
            if ( $( this ).is(':checked') ) {
                checked_groups[cpt_groups] = $( this ).val();
                cpt_groups++;
            }
        });
        
        $.post('/groups/', {
            selected_groups: checked_groups,
            group_action: 'Delete'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-groups-delete-button' ).prop('disabled', true);
                
                $.notify('Selected Groups have been successfully removed', 'success');
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    }
    
    // ****************************************
    // * Detect clicks on groups edit buttons *
    // ****************************************
    $( '#id-groups-table button[type=button]' ).click(function () {
        just_after_edit_click = true;
        
        $( '#id-group-form-group-title-div' ).text('Edit Group');
        $( '#id-group-edit-button' ).css('display', 'inline-block');
        
        $( '#id-group-id' ).val( $( this ).data('edit-id') );        
        $( '#id-group-name' ).val($( this ).parent().parent().find('span:eq(0)').text());
        $( '#id-group-description' ).val($( this ).parent().parent().find('span:eq(1)').text());
    });
    
    // ************************
    // * Edit selected Groups *
    // ************************
    $( '#id-group-edit-button' ).click(function () {
        $.post('/groups/', {
            edit_group_id: $( '#id-group-id' ).val(),
            edit_group_name: $( '#id-group-name' ).val(),
            edit_group_description: $( '#id-group-description' ).val(),
            group_action: 'Edit'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-group-edit-button' ).prop('disabled', true);
                $( '#id-group-name' ).prop('disabled', true);
                $( '#id-group-description' ).prop('disabled', true);
                
                if (data['data']['success']) {
                    $.notify('Group has been successfully edited', 'success');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    
                    var string_name_error = '';
                    var string_description_error = '';
                    
                    $.each(data['data'], function(key, value) {
                        if (string_name_error.length > 0) {
                            string_name_error += "\n";
                        }
                        
                        if (string_description_error.length > 0) {
                            string_description_error += "\n";
                        }
                        
                        if (value['name'] !== undefined) {
                            string_name_error += value['name'];
                        } else if (value['description'] !== undefined) {
                            string_description_error += value['description'];
                        }
                    });
                    
                    if (string_name_error.length > 0) {
                        $( '#id-group-div-form-name-group' ).addClass('has-error');
                        $( '#id-group-error-name-div' ).text(string_name_error);
                        $( '#id-group-error-name-div' ).collapse('show');
                    }
                    
                    if (string_description_error.length > 0) {
                        $( '#id-group-div-form-description-group' ).addClass('has-error');
                        $( '#id-group-error-description-div' ).text(string_description_error);
                        $( '#id-group-error-description-div' ).collapse('show');
                    }
                    
                    $( '#id-group-edit-button' ).prop('disabled', false);
                    $( '#id-group-name' ).prop('disabled', false);
                    $( '#id-group-description' ).prop('disabled', false);
                }
            }
        });
    });
    
    // ******************
    // * Add a new User *
    // ******************
    $( '#id-user-modal' ).on('shown.bs.modal', function (e) {
        
        if (e.relatedTarget.id === 'id-new-user-button') {
            $( '#id-user-firstname' ).focus();
        } 
    });
    
    $( '#id-new-user-button' ).click(function () {
        $( '#id-group-form-user-title-div' ).text('New User');
        $( '#id-user-create-button' ).css('display', 'inline-block');
        $( '#id-user-active' ).bootstrapSwitch('state', true);
        $( '#id-user-groups' ).selectpicker('val', false);
        $( '#id-user-groups' ).selectpicker('refresh');
    });
    
    $( '#id-user-create-button' ).click(function () {
        
        $.post('/users/', {
            new_user_firstname: $( '#id-user-firstname' ).val(),
            new_user_lastname: $( '#id-user-lastname' ).val(),
            new_user_email: $( '#id-user-email' ).val(),
            new_user_username: $( '#id-user-username' ).val(),
            new_user_groups: $( '#id-user-groups' ).val(),
            new_user_admin: $( '#id-user-admin' ).is(':checked') ? 1 : 0,
            new_user_active: $( '#id-user-active' ).is(':checked') ? 1 : 0,
            user_action: 'Create'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-user-create-button' ).prop('disabled', true);
                $( '#id-user-firstname' ).prop('disabled', true);
                $( '#id-user-lastname' ).prop('disabled', true);
                $( '#id-user-email' ).prop('disabled', true);
                $( '#id-user-username' ).prop('disabled', true);
                $( '#id-user-groups' ).prop('disabled', true);
                $( '#id-user-admin' ).bootstrapSwitch('disabled', true);
                $( '#id-user-active' ).bootstrapSwitch('disabled', true);
                
                if (data['data']['success']) {
                    $.notify('New User successfully added', 'success');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    
                    var string_firstname_error = '';
                    var string_lastname_error = '';
                    var string_email_error = '';
                    var string_username_error = '';
                    
                    $.each(data['data'], function(key, value) {
                        if (string_firstname_error.length > 0) {
                            string_firstname_error += "\n";
                        }
                        
                        if (string_lastname_error.length > 0) {
                            string_lastname_error += "\n";
                        }
                        
                        if (string_email_error.length > 0) {
                            string_email_error += "\n";
                        }
                        
                        if (string_username_error.length > 0) {
                            string_username_error += "\n";
                        }
                        
                        if (value['firstname'] !== undefined) {
                            string_firstname_error += value['firstname'];
                        } else if (value['lastname'] !== undefined) {
                            string_lastname_error += value['lastname'];
                        } else if (value['email'] !== undefined) {
                            string_email_error += value['email'];
                        } else if (value['username'] !== undefined) {
                            string_username_error += value['username'];
                        }
                    });
                    
                    if (string_firstname_error.length > 0) {
                        $( '#id-group-div-form-firstname-user' ).addClass('has-error');
                        $( '#id-user-error-firstname-div' ).text(string_firstname_error);
                        $( '#id-user-error-firstname-div' ).collapse('show');
                    }
                    
                    if (string_lastname_error.length > 0) {
                        $( '#id-group-div-form-lastname-user' ).addClass('has-error');
                        $( '#id-user-error-lastname-div' ).text(string_lastname_error);
                        $( '#id-user-error-lastname-div' ).collapse('show');
                    }
                    
                    if (string_email_error.length > 0) {
                        $( '#id-group-div-form-email-user' ).addClass('has-error');
                        $( '#id-user-error-email-div' ).text(string_email_error);
                        $( '#id-user-error-email-div' ).collapse('show');
                    }
                    
                    if (string_username_error.length > 0) {
                        $( '#id-group-div-form-username-user' ).addClass('has-error');
                        $( '#id-user-error-username-div' ).text(string_username_error);
                        $( '#id-user-error-username-div' ).collapse('show');
                    }
                    
                    $( '#id-user-create-button' ).prop('disabled', false);
                    $( '#id-user-firstname' ).prop('disabled', false);
                    $( '#id-user-lastname' ).prop('disabled', false);
                    $( '#id-user-email' ).prop('disabled', false);
                    $( '#id-user-username' ).prop('disabled', false);
                    $( '#id-user-groups' ).prop('disabled', false);
                    $( '#id-user-admin' ).bootstrapSwitch('disabled', false);
                    $( '#id-user-active' ).bootstrapSwitch('disabled', false);
                }
            }
        });
        
    });
    
    $( '#id-user-firstname' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-firstname-user' ).hasClass('has-error')) {
            $( '#id-group-div-form-firstname-user' ).removeClass('has-error');
            $( '#id-user-error-firstname-div' ).text('');
            $( '#id-user-firstname' ).val('');
            $( '#id-user-error-firstname-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    $( '#id-user-lastname' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-lastname-user' ).hasClass('has-error')) {
            $( '#id-group-div-form-lastname-user' ).removeClass('has-error');
            $( '#id-user-error-lastname-div' ).text('');
            $( '#id-user-lastname' ).val('');
            $( '#id-user-error-lastname-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    $( '#id-user-email' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-email-user' ).hasClass('has-error')) {
            $( '#id-group-div-form-email-user' ).removeClass('has-error');
            $( '#id-user-error-email-div' ).text('');
            $( '#id-user-email' ).val('');
            $( '#id-user-error-email-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    $( '#id-user-username' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-username-user' ).hasClass('has-error')) {
            $( '#id-group-div-form-username-user' ).removeClass('has-error');
            $( '#id-user-error-username-div' ).text('');
            $( '#id-user-username' ).val('');
            $( '#id-user-error-username-div' ).collapse('hide');
        }
        
        just_after_edit_click = false;
    });
    
    
    $( '#id-user-modal' ).on('hidden.bs.modal', function () {
        $( '#id-group-div-form-firstname-user' ).removeClass('has-error');
        $( '#id-group-div-form-lastname-user' ).removeClass('has-error');
        $( '#id-group-div-form-email-user' ).removeClass('has-error');
        $( '#id-group-div-form-username-user' ).removeClass('has-error');
        $( '#id-user-firstname' ).val('');
        $( '#id-user-lastname' ).val('');
        $( '#id-user-email' ).val('');
        $( '#id-user-username' ).val('');
        $( '#id-user-error-firstname-div' ).collapse('hide');
        $( '#id-user-error-firstname-div' ).text('');
        $( '#id-user-error-lastname-div' ).collapse('hide');
        $( '#id-user-error-lastname-div' ).text('');
        $( '#id-user-error-email-div' ).collapse('hide');
        $( '#id-user-error-email-div' ).text('');
        $( '#id-user-error-username-div' ).collapse('hide');
        $( '#id-user-error-username-div' ).text('');
        $( '#id-user-create-button' ).css('display', 'none');
        $( '#id-user-edit-button' ).css('display', 'none');
        $( '#id-user-reset-password-button' ).css('display', 'none');
    });
    
    $( '#id-user-firstname, #id-user-lastname, #id-user-email, #id-user-username' ).keypress(function (e) {
        var key = e.which;
        
        if (key === 13) {
            $( '#id-user-create-button' ).click();
            return false;
        }
    });
    
    // ********************************************************************
    // * Detect clicks on users checkboxes and manage delete button state *
    // ********************************************************************
    $( '#id-users-form' ).find(':checkbox').click(function () {
        if ( $( '#id-users-form input[type=checkbox]:checked' ).length > 0) {
            $( '#id-users-delete-button' ).prop('disabled', false);
            $( '#id-users-delete-button' ).removeClass('disabled');
        } else {
            $( '#id-users-delete-button' ).prop('disabled', true);
            $( '#id-users-delete-button' ).addClass('disabled');
        }
    });
    
    // *************************
    // * Delete selected users *
    // *************************
    $( '#id-users-delete-button' ).click(function () {
        bootbox.confirm("Are you sure?", function(result) {
            if (result) {
                delete_users();
            }
        });
    });
    
    function delete_users() {
        
        var checked_users = new Array();
        var cpt_users = 0;
        
        $( '#id-users-form' ).find(':checkbox').each(function () {
            if ( $( this ).is(':checked') ) {
                checked_users[cpt_users] = $( this ).val();
                cpt_users++;
            }
        });
        
        $.post('/users/', {
            selected_users: checked_users,
            user_action: 'Delete'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-users-delete-button' ).prop('disabled', true);
                
                $.notify('Selected users have been successfully removed', 'success');
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    }
    
    // ***************************************
    // * Detect clicks on users edit buttons *
    // ***************************************
    $( '#id-users-table button[type=button]' ).click(function () {
        just_after_edit_click = true;
        
        $( '#id-group-form-user-title-div' ).text('Edit User');
        $( '#id-user-edit-button' ).css('display', 'inline-block');
        $( '#id-user-reset-password-button' ).css('display', 'inline-block');
        
        $( '#id-user-id' ).val( $( this ).data('edit-id') );        
        $( '#id-user-firstname' ).val($( this ).parent().parent().find('span:eq(0)').text());
        $( '#id-user-lastname' ).val($( this ).parent().parent().find('span:eq(1)').text());
        $( '#id-user-email' ).val($( this ).parent().parent().find('span:eq(2)').text());
        $( '#id-user-username' ).val($( this ).parent().parent().find('span:eq(3)').text());
        
        if ($( this ).parent().parent().find('span:eq(5)').hasClass('glyphicon-ok')) {
            $( '#id-user-active' ).bootstrapSwitch('state', true);
        } else {
            $( '#id-user-active' ).bootstrapSwitch('state', false);
        }
        
        if ($( this ).parent().parent().find('span:eq(6)').hasClass('glyphicon-ok')) {
            $( '#id-user-admin' ).bootstrapSwitch('state', true);
        } else {
            $( '#id-user-admin' ).bootstrapSwitch('state', false);
        }
        
        var array_groups = new Array();
        
        if ($( this ).parent().parent().find('td:eq(5)').data('group-ids')) {
            array_groups = $( this ).parent().parent().find('td:eq(5)').data('group-ids').toString().split(',');
        }
        
        $( '#id-user-groups' ).selectpicker('val', array_groups);
        $( '#id-user-groups' ).selectpicker('refresh');
    });
    
    // ***********************
    // * Edit selected Users *
    // ***********************
    $( '#id-user-edit-button' ).click(function () {
        
        $.post('/users/', {
            edit_user_id: $( '#id-user-id' ).val(),
            edit_user_firstname: $( '#id-user-firstname' ).val(),
            edit_user_lastname: $( '#id-user-lastname' ).val(),
            edit_user_email: $( '#id-user-email' ).val(),
            edit_user_username: $( '#id-user-username' ).val(),
            edit_user_groups: $( '#id-user-groups' ).val(),
            edit_user_admin: $( '#id-user-admin' ).is(':checked') ? 1 : 0,
            edit_user_active: $( '#id-user-active' ).is(':checked') ? 1 : 0,
            user_action: 'Edit'
        },
        function (data, status) {
            if (status === 'success') {                
                $( '#id-user-edit-button' ).prop('disabled', true);
                $( '#id-user-firstname' ).prop('disabled', true);
                $( '#id-user-lastname' ).prop('disabled', true);
                $( '#id-user-email' ).prop('disabled', true);
                $( '#id-user-username' ).prop('disabled', true);
                $( '#id-user-groups' ).prop('disabled', true);
                $( '#id-user-admin' ).bootstrapSwitch('disabled', true);
                $( '#id-user-active' ).bootstrapSwitch('disabled', true);
                
                if (data['data']['success']) {
                    $.notify('User successfully edited', 'success');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    
                    var string_firstname_error = '';
                    var string_lastname_error = '';
                    var string_email_error = '';
                    var string_username_error = '';
                    
                    $.each(data['data'], function(key, value) {
                        if (string_firstname_error.length > 0) {
                            string_firstname_error += "\n";
                        }
                        
                        if (string_lastname_error.length > 0) {
                            string_lastname_error += "\n";
                        }
                        
                        if (string_email_error.length > 0) {
                            string_email_error += "\n";
                        }
                        
                        if (string_username_error.length > 0) {
                            string_username_error += "\n";
                        }
                        
                        if (value['firstname'] !== undefined) {
                            string_firstname_error += value['firstname'];
                        } else if (value['lastname'] !== undefined) {
                            string_lastname_error += value['lastname'];
                        } else if (value['email'] !== undefined) {
                            string_email_error += value['email'];
                        } else if (value['username'] !== undefined) {
                            string_username_error += value['username'];
                        }
                    });
                    
                    if (string_firstname_error.length > 0) {
                        $( '#id-group-div-form-firstname-user' ).addClass('has-error');
                        $( '#id-user-error-firstname-div' ).text(string_firstname_error);
                        $( '#id-user-error-firstname-div' ).collapse('show');
                    }
                    
                    if (string_lastname_error.length > 0) {
                        $( '#id-group-div-form-lastname-user' ).addClass('has-error');
                        $( '#id-user-error-lastname-div' ).text(string_lastname_error);
                        $( '#id-user-error-lastname-div' ).collapse('show');
                    }
                    
                    if (string_email_error.length > 0) {
                        $( '#id-group-div-form-email-user' ).addClass('has-error');
                        $( '#id-user-error-email-div' ).text(string_email_error);
                        $( '#id-user-error-email-div' ).collapse('show');
                    }
                    
                    if (string_username_error.length > 0) {
                        $( '#id-group-div-form-username-user' ).addClass('has-error');
                        $( '#id-user-error-username-div' ).text(string_username_error);
                        $( '#id-user-error-username-div' ).collapse('show');
                    }
                    
                    $( '#id-user-edit-button' ).prop('disabled', false);
                    $( '#id-user-firstname' ).prop('disabled', false);
                    $( '#id-user-lastname' ).prop('disabled', false);
                    $( '#id-user-email' ).prop('disabled', false);
                    $( '#id-user-username' ).prop('disabled', false);
                    $( '#id-user-groups' ).prop('disabled', false);
                    $( '#id-user-admin' ).bootstrapSwitch('disabled', false);
                    $( '#id-user-active' ).bootstrapSwitch('disabled', false);
                }

            }
        });
    });
    
    $( '#id-user-reset-password-button' ).click(function () {
        $( '#id-user-edit-button' ).prop('disabled', true);
        $( '#id-user-firstname' ).prop('disabled', true);
        $( '#id-user-lastname' ).prop('disabled', true);
        $( '#id-user-email' ).prop('disabled', true);
        $( '#id-user-username' ).prop('disabled', true);
        $( '#id-user-groups' ).prop('disabled', true);
        $( '#id-user-admin' ).bootstrapSwitch('disabled', true);
        $( '#id-user-active' ).bootstrapSwitch('disabled', true);
                
        reset_password($( '#id-user-id' ).val());
    });
    
    function reset_password(user_id) {
        
        $.post('/users/', {
            current_user_id: user_id,
            user_action: 'Reset'
        },
        function (data, status) {
            if (status === 'success') {
                $.notify('A new password has been sent to the user', 'success');
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    }

    // ***************************
    // * List Tyoe with Sortable *
    // ***************************
    if ($( '#simpleList' ).length) {
        Sortable.create(simpleList, {/* options */});
    }

    // *********************************************
    // * Dynamically Add a new Field to a new Type *
    // *********************************************
    function add_type_li() {

        var all_field_type_options = '';

        for (var cpt_field_types = 0; cpt_field_types < all_field_type.length; cpt_field_types++) {
            all_field_type_options += '<option value="' + all_field_type[cpt_field_types]['id'] + '">' + all_field_type[cpt_field_types]['name'] + '</option>';
        }

        $('#simpleList').append(
            '<li class="list-group-item" id="li-type-id-' + nbr_types_li + '">' +
                '<button type="button" data-type-id="' + nbr_types_li + '" class="btn btn-danger btn-xs pull-right">-</button>' +
                '<div class="form-group">' +
                    '<label for="id-type-name-' + nbr_types_li + '">Name: </label>' +
                    '<input class="form-control type-names" type="text" id="id-type-name-' + nbr_types_li + '" placeholder="Enter Name" maxlength="255" />' +
                    '<div id="id-type-error-div-' + nbr_types_li + '" class="help-block collapse"></div>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label for="id-type-default-' + nbr_types_li + '">Default Content: </label>' +
                    '<input class="form-control" type="text" id="id-type-default-' + nbr_types_li + '" placeholder="Enter Default Content" maxlength="255" />' +
                '</div>' +
                '<div class="form-group">' +
                    '<label for="id-type-field-type-' + nbr_types_li + '">Field Type: </label>' +
                    '<select class="form-control" id="id-type-field-type-' + nbr_types_li + '" data-type-id="' + nbr_types_li + '">' + all_field_type_options +
                    '</select>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label for="id-type-placeholder-' + nbr_types_li + '">Placeholder: </label>' +
                    '<input class="form-control" type="text" id="id-type-placeholder-' + nbr_types_li + '" placeholder="Enter Placeholder" maxlength="255" />' +
                '</div>' +
            '</li>'
        );

        // ****************************************
        // * Dynamically Remove a Field to a Type *
        // ****************************************
        $( '#simpleList button.pull-right').off('click');

        $( '#simpleList button.pull-right').click(function () {
            $( '#li-type-id-' + $( this ).data('type-id') ).remove();
        });

        $( '#simpleList #id-type-field-type-' + nbr_types_li ).off('change');

        $( '#simpleList #id-type-field-type-' + nbr_types_li ).change(function () {
            if ($( this ).val() === '2') {
                $( '#simpleList #id-type-default-' + $( this ).data('type-id')).prop('disabled', true);
                $( '#simpleList #id-type-placeholder-' + $( this ).data('type-id')).prop('disabled', true);
            } else {
                $( '#simpleList #id-type-default-' + $( this ).data('type-id')).prop('disabled', false);
                $( '#simpleList #id-type-placeholder-' + $( this ).data('type-id')).prop('disabled', false);
            }
        });
    }

    function remove_error_field_list( obj_error ) {
        obj_error.parent().removeClass('has-error');
        obj_error.val('');
        obj_error.parent().find( 'div:eq(0)' ).collapse('hide');
    }

    $( '#id-type-name' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-type-div-form-group' ).hasClass('has-error')) {
            $( '#id-type-div-form-group' ).removeClass('has-error');
            $( '#id-type-error-div' ).text('');
            $( '#id-type-name' ).val('');
            $( '#id-type-error-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-button-add-type-li' ).click(function() {
        nbr_types_li++;

        add_type_li();
    });

    // *******************************************************
    // * Grab data when opening Add a new Field modal window *
    // *******************************************************
    $( '#id-type-modal' ).on('shown.bs.modal', function(e) {

        var current_type_id = 0;

        if (e.relatedTarget.id === 'id-new-type-button') {
            $( '#id-type-form-group-title-div' ).text('Add a New Type of Credential');
        } else {
            current_type_id = e.relatedTarget.getAttribute('data-edit-id');
            $( '#id-type-form-group-title-div' ).text('Edit a Type of Credential');
        }

        $.post('/types/', {
            type_action: 'field_type_list'
        },
        function (data, status) {
            if (status === 'success') {
                all_field_type = data['data'];

                // *************
                // * Edit mode *
                // *************
                if (current_type_id !== 0) {
                    edit_credentials_type(current_type_id);
                } else {
                    $( '#id-button-add-type-li' ).removeClass('hidden');
                    $( '#id-type-create-button' ).css('display', 'inline-block');
                }
            }
        });
    });

    // *******************************
    // * Editing Type of Credentials *
    // *******************************
    function edit_credentials_type(current_id) {
        $.post('/types/', {
            type_action: 'GetType',
            type_id: current_id
        },
        function (data, status) {

            if (status === 'success') {
                $('#id-type-name').val(data['data']['name']);
                $('#id-type-id').val(data['data']['id']);

                $('#id-type-edit-button').css('display', 'inline-block');

                var all_field_type_options = '';

                for (var cpt_field_types = 0; cpt_field_types < all_field_type.length; cpt_field_types++) {
                    all_field_type_options += '<option value="' + all_field_type[cpt_field_types]['id'] + '">' + all_field_type[cpt_field_types]['name'] + '</option>';
                }

                for (var cpt_fields = 0; cpt_fields < data['data']['fields'].length; cpt_fields++) {

                    $('#simpleList').append(
                        '<li class="list-group-item" id="li-type-id-' + data['data']['fields'][cpt_fields]['id'] + '" data-type-edit-id="' + data['data']['fields'][cpt_fields]['id'] + '">' +
                        '<div class="form-group">' +
                        '<label for="id-type-name-' + data['data']['fields'][cpt_fields]['id'] + '">Name: </label>' +
                        '<input class="form-control type-names" type="text" id="id-type-name-' + data['data']['fields'][cpt_fields]['id'] + '" placeholder="Enter Name" value="' + data['data']['fields'][cpt_fields]['value'] + '" maxlength="255" />' +
                        '<div id="id-type-error-div-' + data['data']['fields'][cpt_fields]['id'] + '" class="help-block collapse"></div>' +
                        '</div>' +
                        '<div class="form-group">' +
                        '<label for="id-type-default-' + data['data']['fields'][cpt_fields]['id'] + '">Default Content: </label>' +
                        '<input class="form-control" type="text" id="id-type-default-' + data['data']['fields'][cpt_fields]['id'] + '" placeholder="Enter Default Content" value="' + data['data']['fields'][cpt_fields]['default_value'] + '" maxlength="255" />' +
                        '</div>' +
                        '<div class="form-group">' +
                        '<label for="id-type-field-type-' + data['data']['fields'][cpt_fields]['id'] + '">Field Type: </label>' +
                        '<select class="form-control" id="id-type-field-type-' + data['data']['fields'][cpt_fields]['id'] + '" data-type-id="' + data['data']['fields'][cpt_fields]['id'] + '">' + all_field_type_options +
                        '</select>' +
                        '</div>' +
                        '<div class="form-group">' +
                        '<label for="id-type-placeholder-' + data['data']['fields'][cpt_fields]['id'] + '">Placeholder: </label>' +
                        '<input class="form-control" type="text" id="id-type-placeholder-' + data['data']['fields'][cpt_fields]['id'] + '" placeholder="Enter Placeholder" value="' + data['data']['fields'][cpt_fields]['placeholder'] + '" maxlength="255" />' +
                        '</div>' +
                        '</li>'
                    );

                    $( '#simpleList #id-type-field-type-' + data['data']['fields'][cpt_fields]['id'] ).off('change');

                    $( '#simpleList #id-type-field-type-' + data['data']['fields'][cpt_fields]['id'] ).change(function () {
                        if ($( this ).val() === '2') {
                            $( '#simpleList #id-type-default-' + $( this ).data('type-id')).prop('disabled', true);
                            $( '#simpleList #id-type-placeholder-' + $( this ).data('type-id')).prop('disabled', true);
                        } else {
                            $( '#simpleList #id-type-default-' + $( this ).data('type-id')).prop('disabled', false);
                            $( '#simpleList #id-type-placeholder-' + $( this ).data('type-id')).prop('disabled', false);
                        }
                    });

                    $( '#id-type-field-type-' + data['data']['fields'][cpt_fields]['id'] ).val(data['data']['fields'][cpt_fields]['field_type']).change();
                    $( '#id-type-field-type-' + data['data']['fields'][cpt_fields]['id'] ).prop('disabled', true);
                }
            }
        });
    }

    $( '#id-type-modal' ).on('hidden.bs.modal', function () {
        $( '#id-button-add-type-li' ).addClass('hidden');
        $( '#id-type-create-button' ).css('display', 'none');
        $( '#id-type-edit-button' ).css('display', 'none');
        $( '#id-type-name' ).val('');
        $( '#id-type-form-group-title-div' ).text('');
        $( '#simpleList' ).empty();
    });

    // ************************************
    // * Create a new Type of Credentials *
    // ************************************
    $( '#id-type-create-button' ).click(function() {

        var new_type_name = $( '#id-type-name' ).val();
        var type_array = new Array($( '#simpleList' ).children().length);

        var cpt_fields = 0;
        var cpt_errors = 0;

        $( '.type-names' ).focus(function () {
            remove_error_field_list( $( this ) );
        });

        $( '#simpleList' ).children().each(function () {

            type_array[cpt_fields] = {};

            if ($( this ).find( 'input[type=text]:eq(' + 0 + ')' ).val() === '') {
                cpt_errors++;

                $( this ).find( 'div:eq(0)' ).addClass('has-error');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).text('This value should not be blank');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).collapse('show');
            } else if ($( this ).find( 'input[type=text]:eq(' + 0 + ')' ).val().length < 2) {
                cpt_errors++;

                $( this ).find( 'div:eq(0)' ).addClass('has-error');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).text('This value is too short. It should have 2 characters or more.');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).collapse('show');
            }

            type_array[cpt_fields]['name'] = $( this ).find( 'input[type=text]:eq(' + 0 + ')' ).val();
            type_array[cpt_fields]['default_name'] = $( this ).find( 'input[type=text]:eq(' + 1 + ')' ).val();
            type_array[cpt_fields]['field_type'] = $( this ).find( 'select' ).val();
            type_array[cpt_fields]['placeholder'] = $( this ).find( 'input[type=text]:eq(' + 2 + ')' ).val();
            type_array[cpt_fields]['order'] = cpt_fields + 1;

            cpt_fields++;
        });

        if (cpt_errors === 0) {
            $.post('/types/', {
                new_type_json: JSON.stringify(type_array),
                new_type_name: new_type_name,
                type_action: 'Add'
            },
            function (data, status) {

                $('#id-type-create-button').prop('disabled', true);
                $('#id-type-name').prop('disabled', true);

                if (data['data']['success']) {
                    $.notify('New Type of Credentials successfully added', 'success');

                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    var string_name_error = '';

                    $.each(data['data'], function(key, value) {
                        if (string_name_error.length > 0) {
                            string_name_error += "\n";
                        }

                        if (value['name'] !== undefined) {
                            string_name_error += value['name'];
                        }
                    });

                    if (string_name_error.length > 0) {
                        $('#id-type-div-form-group').addClass('has-error');
                        $('#id-type-error-div').text(string_name_error);
                        $('#id-type-error-div').collapse('show');
                    }

                    $('#id-type-create-button').prop('disabled', false);
                    $('#id-type-name').prop('disabled', false);
                }
            });
        }
    });

    // *******************************
    // * Delete Types of Credentials *
    // *******************************
    $( '#id-type-delete-button' ).click(function () {
        bootbox.confirm("Are you sure?", function(result) {
            if (result) {
                delete_types();
            }
        });
    });

    function delete_types() {

        var checked_types = new Array();
        var cpt_types = 0;

        $( '#id-types-form' ).find(':checkbox').each(function () {
            if ( $( this ).is(':checked') ) {
                checked_types[cpt_types] = $( this ).val();
                cpt_types++;
            }
        });

        $.post('/types/', {
            selected_types: checked_types,
            type_action: 'Delete'
        },
        function (data, status) {

            if (status === 'success') {
                $( '#id-type-delete-button' ).prop('disabled', true);

                $.notify('Selected Types of Credentials have been successfully removed', 'success');

                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    }

    // ********************************************************************
    // * Detect clicks on users checkboxes and manage delete button state *
    // ********************************************************************
    $( '#id-types-form' ).find(':checkbox').click(function () {
        if ( $( '#id-types-form input[type=checkbox]:checked' ).length > 0) {
            $( '#id-type-delete-button' ).prop('disabled', false);
            $( '#id-type-delete-button' ).removeClass('disabled');
        } else {
            $( '#id-type-delete-button' ).prop('disabled', true);
            $( '#id-type-delete-button' ).addClass('disabled');
        }
    });

    // *****************************
    // * Edit Types of Credentials *
    // *****************************
    $( '#id-type-edit-button' ).click(function () {

        var edit_type_name = $( '#id-type-name' ).val();
        var edit_type_id = $( '#id-type-id' ).val();
        var type_array = new Array($( '#simpleList' ).children().length);

        var cpt_fields = 0;
        var cpt_errors = 0;

        $( '.type-names' ).focus(function () {
            remove_error_field_list( $( this ) );
        });

        $( '#simpleList' ).children().each(function () {

            type_array[cpt_fields] = {};

            if ($( this ).find( 'input[type=text]:eq(' + 0 + ')' ).val() === '') {
                cpt_errors++;

                $( this ).find( 'div:eq(0)' ).addClass('has-error');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).text('This value should not be blank');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).collapse('show');
            } else if ($( this ).find( 'input[type=text]:eq(' + 0 + ')' ).val().length < 2) {
                cpt_errors++;

                $( this ).find( 'div:eq(0)' ).addClass('has-error');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).text('This value is too short. It should have 2 characters or more.');
                $( this ).find( 'div:eq(0)' ).find( 'div:eq(0)' ).collapse('show');
            }

            type_array[cpt_fields]['id'] = $( this ).data('type-edit-id');
            type_array[cpt_fields]['name'] = $( this ).find( 'input[type=text]:eq(' + 0 + ')' ).val();
            type_array[cpt_fields]['default_name'] = $( this ).find( 'input[type=text]:eq(' + 1 + ')' ).val();
            type_array[cpt_fields]['placeholder'] = $( this ).find( 'input[type=text]:eq(' + 2 + ')' ).val();
            type_array[cpt_fields]['order'] = cpt_fields + 1;

            cpt_fields++;
        });

        if (cpt_errors === 0) {
            $.post('/types/', {
                edit_type_json: JSON.stringify(type_array),
                edit_type_name: edit_type_name,
                edit_type_id: edit_type_id,
                type_action: 'Edit'
            },
            function (data, status) {
                $('#id-type-create-button').prop('disabled', true);
                $('#id-type-name').prop('disabled', true);

                if (data['data']['success']) {
                    $.notify('Type of credentials successfully edited', 'success');

                    setTimeout(function () {
                        location.reload();
                    }, 1000);

                } else {
                    var string_name_error = '';

                    $.each(data['data'], function(key, value) {
                        if (string_name_error.length > 0) {
                            string_name_error += "\n";
                        }

                        if (value['name'] !== undefined) {
                            string_name_error += value['name'];
                        }
                    });

                    if (string_name_error.length > 0) {
                        $('#id-type-div-form-group').addClass('has-error');
                        $('#id-type-error-div').text(string_name_error);
                        $('#id-type-error-div').collapse('show');
                    }

                    $('#id-type-edit-button').prop('disabled', false);
                    $('#id-type-name').prop('disabled', false);
                }

            });
        }

    });

    // ***********
    // * Entries *
    // ***********

    // *******************
    // * Add a new Entry *
    // *******************
    $( '#id-entry-modal' ).on('shown.bs.modal', function (e) {

        if (e.relatedTarget.id === 'id-new-entry-button' || e.relatedTarget.id === 'id-new-entrybis-button') {
            $( '#id-entry-company-select' ).focus();
            $( '#id-group-form-entry-title-div' ).text('New Entry');
            $( '#id-entry-create-button' ).css('display', 'inline-block');
            $( '#id-entry-groups' ).selectpicker('val', false);
            $( '#id-entry-groups' ).selectpicker('refresh');
            $( '#id-entry-id' ).val('');
        } else {
            $( '#id-group-form-entry-title-div' ).text('Edit Entry');
            $( '#id-entry-edit-button' ).css('display', 'inline-block');
            $( '#id-entry-edit-button' ).prop('disabled', true);

            get_data_for_edit(e.relatedTarget.getAttribute('data-edit-id'));
        }
    });

    $( '#id-entry-modal' ).on('hidden.bs.modal', function(e) {

        $( '#id-entry-company-select' ).val('');
        $( '#id-entry-company-select' ).attr('disabled', false);
        $( '#id-entry-company-select' ).selectpicker('refresh');
        $( '#id-entry-company-name' ).prop('disabled', false);
        $( '#id-entry-entrytypes' ).val('');
        $( '#id-entry-entrytypes' ).attr('disabled', false);
        $( '#id-entry-entrytypes' ).selectpicker('refresh');
        $(' #id-entry-type-fields' ).empty();
        $( '#id-entry-comment' ).val('');
        $( '#id-entry-groups' ).val('');
        $( '#id-entry-groups' ).selectpicker('refresh');
        $( '#id-entry-error-entrytypes-div' ).collapse('hide');
        $( '#id-entry-div-entrytypes-form-group' ).removeClass('has-error');
        $( '#id-entry-error-entrytypes-div' ).text('');
        $( '#id-entry-error-groups-div' ).collapse('hide');
        $( '#id-entry-div-groups-form-group' ).removeClass('has-error');
        $( '#id-entry-error-groups-div' ).text('');
        $( '#id-entry-create-button' ).css('display', 'none');
        $( '#id-entry-edit-button' ).css('display', 'none');
    });

    $( '#id-entry-company-select' ).change(function () {
        if ($( '#id-entry-companies' ).val() !== '') {
            $( '#id-entry-company-name' ).val('');
            $( '#id-entry-company-name' ).prop('disabled', true);

            $( '#id-entry-div-form-group' ).removeClass('has-error');
            $( '#id-entry-error-company-name-div' ).text('');
            $( '#id-entry-error-company-name-div' ).collapse('hide');

            just_after_edit_click = false;
        } else {
            $( '#id-entry-company-name' ).prop('disabled', false);
        }
    });

    $( '#id-entry-create-button' ).click(function () {

        var error_form = 0;

        if ($( '#id-entry-entrytypes' ).val() === '' || $( '#id-entry-entrytypes' ).val() === null) {
            $( '#id-entry-div-entrytypes-form-group' ).addClass('has-error');
            $( '#id-entry-error-entrytypes-div' ).text('Please select an Entry Type.');
            $( '#id-entry-error-entrytypes-div' ).collapse('show');

            error_form++;
        }

        if ($( '#id-entry-groups' ).val() === '' || $( '#id-entry-groups' ).val() === null) {
            $( '#id-entry-div-groups-form-group' ).addClass('has-error');
            $( '#id-entry-error-groups-div' ).text('Please select one or several groups.');
            $( '#id-entry-error-groups-div' ).collapse('show');

            error_form++;
        }

        var array_field_types = new Array();
        var array_field_ids = new Array();
        var array_field_values = new Array;
        var cpt_fields = 0;

        $('#id-entry-type-fields').find('input[type=text]').each(function () {
            array_field_ids[cpt_fields] = $( this ).data('field-id');
            array_field_types[cpt_fields] = $( this ).data('field-type');
            array_field_values[cpt_fields] = $( this ).val();
            cpt_fields++;
        });


        if (error_form === 0) {
            $.post('/entries/', {
                new_company_name: $('#id-entry-company-name').val(),
                id_company: $('#id-entry-company-select').val(),
                id_entry_type: $( '#id-entry-entrytypes' ).val(),
                entry_comment: $( '#id-entry-comment' ).val(),
                entry_groups: $( '#id-entry-groups' ).val(),
                field_ids: array_field_ids,
                field_types: array_field_types,
                field_values: array_field_values,
                entry_action: 'New'
            },
            function (data, status) {
                if (status === 'success') {
                    $('#id-entry-create-button').prop('disabled', true);
                    $('#id-entry-company-name').prop('disabled', true);

                    if (data['data']['success']) {
                        $.notify('New Entry successfully added', 'success');

                         setTimeout(function() {
                            location.reload();
                         }, 1000);

                    } else {
                        var string_name_error = '';

                        $.each(data['data'], function (key, value) {
                            if (string_name_error.length > 0) {
                                string_name_error += "\n";
                            }

                            if (value['name'] !== undefined) {
                                string_name_error += value['name'];
                            }
                        });

                        if (string_name_error.length > 0) {
                            $('#id-entry-div-form-group').addClass('has-error');
                            $('#id-entry-error-company-name-div').text(string_name_error);
                            $('#id-entry-error-company-name-div').collapse('show');
                        }

                        $('#id-entry-create-button').prop('disabled', false);
                        $('#id-entry-company-name').prop('disabled', false);
                    }
                }
            });
        }
    });


    $( '#id-entry-entrytypes' ).change(function () {

        if ($( '#id-entry-entrytypes' ).val() !== '' && $( '#id-entry-entrytypes' ).val() !== null) {
            $( '#id-entry-div-entrytypes-form-group' ).removeClass('has-error');
            $( '#id-entry-error-entrytypes-div' ).text('');
            $( '#id-entry-error-entrytypes-div' ).collapse('hide');

            just_after_edit_click = false;
        } else {
            $( '#id-entry-div-entrytypes-form-group' ).addClass('has-error');
            $( '#id-entry-error-entrytypes-div' ).text('Please select an Entry Type.');
            $( '#id-entry-error-entrytypes-div' ).collapse('show');
        }
    });

    $( '#id-entry-groups' ).change(function () {

        if ($( '#id-entry-groups' ).val() !== '' && $( '#id-entry-groups' ).val() !== null) {
            $( '#id-entry-div-groups-form-group' ).removeClass('has-error');
            $( '#id-entry-error-groups-div' ).text('');
            $( '#id-entry-error-groups-div' ).collapse('hide');

            just_after_edit_click = false;
        } else {
            $( '#id-entry-div-groups-form-group' ).addClass('has-error');
            $( '#id-entry-error-groups-div' ).text('Please select one or several groups.');
            $( '#id-entry-error-groups-div' ).collapse('show');
        }
    });

    // ******************
    // * Get Entry Type *
    // ******************
    $( '#id-entry-entrytypes' ).change(function () {
        $.post('/entries/', {
            entry_action: 'GetData',
            type_id: $( '#id-entry-entrytypes' ).val()
        },
        function (data, status) {

            $('#id-entry-type-fields').empty();

            for (var cpt_fields = 0; cpt_fields < data['data']['fields'].length; cpt_fields++) {

                if (data['data']['fields'][cpt_fields]['field_type'] === 1) {
                    $('#id-entry-type-fields').append(
                        '<div class="form-group has-feedback">' +
                        '<label>' + data['data']['fields'][cpt_fields]['value'] + '</label>' +
                        '<input data-field-type="' + data['data']['fields'][cpt_fields]['field_type'] + '" data-field-id="' + data['data']['fields'][cpt_fields]['id'] + '" type="text" class="form-control" placeholder="' + data['data']['fields'][cpt_fields]['placeholder'] + '" value="' + data['data']['fields'][cpt_fields]['default_value'] + '" />' +
                        '</div>'
                    );
                } else {
                    $('#id-entry-type-fields').append(
                        '<div class="form-group has-feedback">' +
                        '<label>' + data['data']['fields'][cpt_fields]['value'] + '</label>' +
                        '<div class="input-group">' +
                        '<input data-field-type="' + data['data']['fields'][cpt_fields]['field_type'] + '" data-field-id="' + data['data']['fields'][cpt_fields]['id'] + '" id="id-password-field" type="text" class="form-control" placeholder="' + data['data']['fields'][cpt_fields]['placeholder'] + '" value="' + data['data']['fields'][cpt_fields]['default_value'] + '" />' +
                        '<span class="input-group-btn"><button id="id-password-generate-button" class="btn btn-success" type="button">Generate</button></span>' +
                        '</div>' +
                        '</div>'
                    );
                }
            }

            if ($( '#id-password-field' ).length) {
                $('#id-password-generate-button').pGenerator({
                    'bind': 'click',
                    'passwordElement': '#id-password-field',
                    'displayElement': '#id-password-field',
                    'passwordLength': 10,
                    'uppercase': true,
                    'lowercase': true,
                    'numbers': true,
                    'specialChars': false
                });
            }

            // ************************************
            // * Fill in Fields if Edit situation *
            // ************************************
            if ($( '#id-entry-id' ).val() !== '') {
                $('#id-entry-entrytypes').attr('disabled', true);

                $.post('/entries/', {
                    current_entry_id: $( '#id-entry-id' ).val(),
                    entry_action: 'GetFields'
                },
                function (data, status) {
                    if (status === 'success') {

                        for (var cpt_values = 0; cpt_values < data['data']['values'].length; cpt_values++) {
                            $('#id-entry-type-fields').find('[data-field-id="' + data['data']['values'][cpt_values]['id'] + '"]').val(data['data']['values'][cpt_values]['value']);
                        };

                        $( '#id-entry-edit-button' ).prop('disabled', false);

                    }
                });
            }

        });
    });

    // ***************************
    // * Delete selected entries *
    // ***************************
    $( '#id-entries-delete-button' ).click(function () {
        bootbox.confirm("Are you sure?", function(result) {
            if (result) {
                delete_entrie();
            }
        });
    });

    function delete_entrie() {
        var checked_entries = new Array();
        var cpt_entries = 0;

        $( '#id-entries-form' ).find(':checkbox').each(function () {
            if ( $( this ).is(':checked') ) {
                checked_entries[cpt_entries] = $( this ).val();
                cpt_entries++;
            }
        });

        $.post('/entries/', {
            selected_entries: checked_entries,
            entry_action: 'Delete'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-entries-delete-button' ).prop('disabled', true);

                $.notify('Selected Entries have been successfully removed', 'success');

                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    }

    // **********************************************************************
    // * Detect clicks on entries checkboxes and manage delete button state *
    // **********************************************************************
    $( '#id-entries-form' ).find(':checkbox').click(function () {
        if ( $( '#id-entries-form input[type=checkbox]:checked' ).length > 0) {
            $( '#id-entries-delete-button' ).prop('disabled', false);
            $( '#id-entries-delete-button' ).removeClass('disabled');
        } else {
            $( '#id-entries-delete-button' ).prop('disabled', true);
            $( '#id-entries-delete-button' ).addClass('disabled');
        }
    });

    function get_data_for_edit(id_entry) {

        $( '#id-entry-id' ).val(id_entry);

        $.post('/entries/', {
            entry_action: 'GetDataForEdit',
            id_entry: id_entry
        },
        function (data, status) {

            $( '#id-entry-company-select' ).val(data['data']['company']);
            $( '#id-entry-company-select' ).attr('disabled', true);
            $( '#id-entry-company-select' ).selectpicker('refresh');
            $( '#id-entry-company-name' ).attr('disabled', true);

            $( '#id-entry-entrytypes' ).val(data['data']['type']);
            $( '#id-entry-entrytypes' ).selectpicker('refresh');

            $( '#id-entry-entrytypes' ).trigger('change');
            $( '#id-entry-comment' ).val(data['data']['comment']);

            $( '#id-entry-groups' ).selectpicker('val', data['data']['groups'].toString().split(','));
            $( '#id-entry-groups' ).selectpicker('refresh');
        });
    }

    // **************
    // * Edit Entry *
    // **************
    $( '#id-entry-edit-button' ).click(function () {

        var error_form = 0;

        if ($( '#id-entry-groups' ).val() === '' || $( '#id-entry-groups' ).val() === null) {
            $( '#id-entry-div-groups-form-group' ).addClass('has-error');
            $( '#id-entry-error-groups-div' ).text('Please select one or several groups.');
            $( '#id-entry-error-groups-div' ).collapse('show');

            error_form++;
        }

        if (error_form === 0) {

            var array_field_types = new Array();
            var array_field_ids = new Array();
            var array_field_values = new Array;
            var cpt_fields = 0;

            $('#id-entry-type-fields').find('input[type=text]').each(function () {
                array_field_ids[cpt_fields] = $( this ).data('field-id');
                array_field_types[cpt_fields] = $( this ).data('field-type');
                array_field_values[cpt_fields] = $( this ).val();
                cpt_fields++;
            });

            $.post('/entries/', {
                entry_action: 'Edit',
                id_entry_edit: $( '#id-entry-id' ).val(),
                array_field_types: array_field_types,
                array_field_ids: array_field_ids,
                array_field_values: array_field_values,
                edit_groups: $( '#id-entry-groups' ).val(),
                comment_entry_edit: $( '#id-entry-comment' ).val()
            },
            function (data, status) {
                if (status === 'success') {
                    $( '#id-entry-edit-button' ).prop('disabled', true);

                    $.notify('Entry has been successfully edited', 'success');

                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
            });

        }
    });

    // ***************************
    // * Change Settings Details *
    // ***************************
    $( '#id-settings-change-details-button' ).click(function () {

        $( '#id-settings-change-details-button' ).prop('disabled', true);

        $( '#id-settings-firstname' ).prop('disabled', true);
        $( '#id-settings-lastname' ).prop('disabled', true);
        $( '#id-settings-email' ).prop('disabled', true);
        $( '#id-settings-username' ).prop('disabled', true);

        $.post('/settings/', {
            entry_action: 'Details',
            firstname: $( '#id-settings-firstname' ).val(),
            lastname: $( '#id-settings-lastname' ).val(),
            email: $( '#id-settings-email' ).val(),
            username: $( '#id-settings-username' ).val(),
        },
        function (data, status) {

            if (status === 'success') {
                if (data['data']['success']) {
                    $.notify('Details have successfully edited', 'success');

                     setTimeout(function () {
                         location.reload();
                     }, 2000);

                } else {

                    $( '#id-settings-change-details-button' ).prop('disabled', false);

                    var string_firstname_error = '';
                    var string_lastname_error = '';
                    var string_email_error = '';
                    var string_username_error = '';

                    $.each(data['data'], function(key, value) {
                        if (string_firstname_error.length > 0) {
                            string_firstname_error += " ";
                        }

                        if (string_lastname_error.length > 0) {
                            string_lastname_error += " ";
                        }

                        if (string_email_error.length > 0) {
                            string_email_error += " ";
                        }

                        if (string_username_error.length > 0) {
                            string_username_error += " ";
                        }

                        if (value['firstname'] !== undefined) {
                            string_firstname_error += value['firstname'];
                        } else if (value['lastname'] !== undefined) {
                            string_lastname_error += value['lastname'];
                        } else if (value['email'] !== undefined) {
                            string_email_error += value['email'];
                        } else if (value['username'] !== undefined) {
                            string_username_error += value['username'];
                        }
                    });

                    if (string_firstname_error.length > 0) {
                        $( '#id-group-div-form-firstname-settings' ).addClass('has-error');
                        $( '#id-settings-error-firstname-div' ).text(string_firstname_error);
                        $( '#id-settings-error-firstname-div' ).collapse('show');
                    }

                    if (string_lastname_error.length > 0) {
                        $( '#id-group-div-form-lastname-settings' ).addClass('has-error');
                        $( '#id-settings-error-lastname-div' ).text(string_lastname_error);
                        $( '#id-settings-error-lastname-div' ).collapse('show');
                    }

                    if (string_email_error.length > 0) {
                        $( '#id-group-div-form-email-settings' ).addClass('has-error');
                        $( '#id-settings-error-email-div' ).text(string_email_error);
                        $( '#id-settings-error-email-div' ).collapse('show');
                    }

                    if (string_username_error.length > 0) {
                        $( '#id-group-div-form-username-settings' ).addClass('has-error');
                        $( '#id-settings-error-username-div' ).text(string_username_error);
                        $( '#id-settings-error-username-div' ).collapse('show');
                    }

                    $( '#id-settings-firstname' ).prop('disabled', false);
                    $( '#id-settings-lastname' ).prop('disabled', false);
                    $( '#id-settings-email' ).prop('disabled', false);
                    $( '#id-settings-username' ).prop('disabled', false);

                }
            }
        });
    });

    $( '#id-settings-firstname' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-firstname-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-firstname-settings' ).removeClass('has-error');
            $( '#id-settings-error-firstname-div' ).text('');
            $( '#id-settings-firstname' ).val('');
            $( '#id-settings-error-firstname-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-settings-lastname' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-lastname-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-lastname-settings' ).removeClass('has-error');
            $( '#id-settings-error-lastname-div' ).text('');
            $( '#id-settings-lastname' ).val('');
            $( '#id-settings-error-lastname-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-settings-email' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-email-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-email-settings' ).removeClass('has-error');
            $( '#id-settings-error-email-div' ).text('');
            $( '#id-settings-email' ).val('');
            $( '#id-settings-error-email-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-settings-username' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-username-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-username-settings' ).removeClass('has-error');
            $( '#id-settings-error-username-div' ).text('');
            $( '#id-settings-username' ).val('');
            $( '#id-settings-error-username-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    // ****************************
    // * Settings Password Change *
    // ****************************
    $( '#id-settings-change-password-button' ).click(function () {
        $( '#id-settings-change-password-button' ).prop('disabled', true);

        $( '#id-settings-current-password' ).prop('disabled', true);
        $( '#id-settings-new-password' ).prop('disabled', true);
        $( '#id-settings-new-password-again' ).prop('disabled', true);


        $.post('/settings/', {
            entry_action: 'Password',
            current_password: $( '#id-settings-current-password' ).val(),
            new_password: $( '#id-settings-new-password' ).val(),
            password_confirm: $( '#id-settings-new-password-again' ).val()
        },
        function (data, status) {

            if (status === 'success') {
                if (data['data']['success']) {
                    $.notify('Password has been successfully changed', 'success');

                    setTimeout(function () {
                        location.reload();
                    }, 2000);

                } else {

                    $( '#id-settings-change-password-button' ).prop('disabled', false);

                    var string_current_password_error = '';
                    var string_new_password_error = '';
                    var string_password_confirm_error = '';

                    $.each(data['data'], function(key, value) {

                        if (string_current_password_error.length > 0) {
                            string_current_password_error += " ";
                        }

                        if (string_new_password_error.length > 0) {
                            string_new_password_error += " ";
                        }

                        if (string_password_confirm_error.length > 0) {
                            string_password_confirm_error += " ";
                        }

                        if (value['current_password'] !== undefined) {
                            string_current_password_error += value['current_password'];
                        } else if (value['new_password'] !== undefined) {
                            string_new_password_error += value['new_password'];
                        } else if (value['password_confirm'] !== undefined) {
                            string_password_confirm_error += value['password_confirm'];
                        }
                    });

                    if (string_current_password_error.length > 0) {
                        $( '#id-group-div-form-current-password-settings' ).addClass('has-error');
                        $( '#id-settings-error-current-password-div' ).text(string_current_password_error);
                        $( '#id-settings-error-current-password-div' ).collapse('show');
                    }

                    if (string_new_password_error.length > 0) {
                        $( '#id-group-div-form-new-password-settings' ).addClass('has-error');
                        $( '#id-settings-error-new-password-div' ).text(string_new_password_error);
                        $( '#id-settings-error-new-password-div' ).collapse('show');
                    }

                    if (string_password_confirm_error.length > 0) {
                        $( '#id-group-div-form-password-confirm-settings' ).addClass('has-error');
                        $( '#id-settings-error-new-password-again-div' ).text(string_password_confirm_error);
                        $( '#id-settings-error-new-password-again-div' ).collapse('show');
                    }

                    $( '#id-settings-current-password' ).prop('disabled', false);
                    $( '#id-settings-new-password' ).prop('disabled', false);
                    $( '#id-settings-new-password-again' ).prop('disabled', false);

                }
            }
        });
    });

    $( '#id-settings-current-password' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-current-password-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-current-password-settings' ).removeClass('has-error');
            $( '#id-settings-error-current-password-div' ).text('');
            $( '#id-settings-current-password' ).val('');
            $( '#id-settings-error-current-password-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-settings-new-password' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-new-password-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-new-password-settings' ).removeClass('has-error');
            $( '#id-settings-error-new-password-div' ).text('');
            $( '#id-settings-new-password' ).val('');
            $( '#id-settings-error-new-password-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-settings-new-password-again' ).focus(function () {

        if (just_after_edit_click === false && $( '#id-group-div-form-password-confirm-settings' ).hasClass('has-error')) {
            $( '#id-group-div-form-password-confirm-settings' ).removeClass('has-error');
            $( '#id-settings-error-new-password-again-div' ).text('');
            $( '#id-settings-new-password-again' ).val('');
            $( '#id-settings-error-new-password-again-div' ).collapse('hide');
        }

        just_after_edit_click = false;
    });

    $( '#id-search-button' ).click(function () {
        location.assign('/entries/' + $( '#id-search-field' ).val());
    });

    $( '#id-search-field' ).keypress(function (e) {
        var key = e.which;
        if (key == 13) {
            $( '#id-search-button' ).click();
            return false;
        }
    });

    if ($( '#id-search-field' ).length) {
        $('#id-search-field').focus();
    }
}

$( window ).on('load', function() {
    main();
});

