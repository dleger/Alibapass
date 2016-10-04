function main() {
    
    // ********
    // * Init *
    // ********
    if ($( '#id-company-delete-button' ).length) {
        $( '#id-company-delete-button' ).prop('disabled', true);
    }
    
    
    // *********************
    // * Add a new Company *
    // *********************
    $( '#id-company-create-button' ).click(function () {
        
        $.post('/companies/', {
            new_company_name: $( '#id-company-name' ).val(),
            company_action: 'Create'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-company-create-button' ).prop('disabled', true);
                $( '#id-company-name' ).prop('disabled', true);
                
                $.notify('Data: ' + data['data'] + '\nStatus: ' + status);
                
                if (data['data'] === 'success') {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $( '#id-company-create-button' ).prop('disabled', false);
                    $( '#id-company-name' ).prop('disabled', false);
                }
            }
        });
        
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
                
                $.notify('Data: ' + data['data'] + '\nStatus: ' + status);
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
    });
    
    // ************************************************************************
    // * Detect clicks on companies checkboxes and manage delete button state *
    // ************************************************************************
    $( '#id-companies-form' ).find(':checkbox').click(function () {
        if ( $( '#id-companies-form input[type=checkbox]:checked' ).length > 0) {
            $( '#id-company-delete-button' ).prop('disabled', false);
        } else {
            $( '#id-company-delete-button' ).prop('disabled', true);
        }
    });
    
    // *******************************************
    // * Detect clicks on companies edit buttons *
    // *******************************************
    $( '#id-companies-ul input[type=button]' ).click(function () {
        $( '#id-company-id-edit' ).val( $( this ).data('edit-id') );        
        $( '#id-company-name-edit' ).val( $( this ).parent().find('span').text() );
    });
    
    // *************************
    // * Edit selected Company *
    // *************************
    $( '#id-company-edit-button' ).click(function () {
        $.post('/companies/', {
            edit_company_id: $( '#id-company-id-edit' ).val(),
            edit_company_name: $( '#id-company-name-edit' ).val(),
            company_action: 'Edit'
        },
        function (data, status) {
            if (status === 'success') {
                $( '#id-company-edit-button' ).prop('disabled', true);
                $( '#id-company-name-edit' ).prop('disabled', true);
                
                $.notify('Data: ' + data['data'] + '\nStatus: ' + status);
                
                if (data['data'] === 'success') {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $( '#id-company-edit-button' ).prop('disabled', false);
                    $( '#id-company-name-edit' ).prop('disabled', false);
                }
            }
        });
    });
    
}

$( window ).on('load', function() {
    main();
});

