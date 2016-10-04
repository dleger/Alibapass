function login() {

    $( '#id-login-button' ).click(function (e) {
        e.preventDefault();

        $.post('/login/', {
                login_action: 'login',
                username: $( '#id-login-username' ).val(),
                password: $( '#id-login-password' ).val()
            },
            function (data, status) {
                if (data['data'] === 'success') {
                    location.assign('/entries/');
                } else {
                    if (data['data'] === 'disabled') {
                        $( '.form-group').addClass('has-error');
                        $( '#id-credentials-error' ).text('Account disabled');
                        $( '#id-credentials-error' ).collapse('show');
                    } else if (data['data'] === 'wrong') {
                        $( '.form-group').addClass('has-error');
                        $( '#id-credentials-error' ).text('Wrong Credentials');
                        $( '#id-credentials-error' ).collapse('show');
                    } else {
                        $( '.form-group').addClass('has-error');
                        $( '#id-credentials-error' ).text('Your IP has been banned for the next 2 hours.');
                        $( '#id-credentials-error' ).collapse('show');
                    }
                }
            });
    });

    $( 'a[href="#reset"]' ).click(function (e) {
        e.preventDefault();

        $( '.form-group').removeClass('has-error');
        $( '#id-credentials-error' ).collapse('hide');
        $( '.panel-body:eq(0)' ).addClass('hidden');
        $( '.panel-body:eq(1)' ).removeClass('hidden');
    });

    $( 'a[href="#login"]' ).click(function (e) {
        e.preventDefault();

        $( '.form-group').removeClass('has-error');
        $( '#id-reset-error' ).collapse('hide');
        $( '.panel-body:eq(1)' ).addClass('hidden');
        $( '.panel-body:eq(0)' ).removeClass('hidden');
    });

    $( '#id-login-username, #id-login-password, #id-login-email' ).click(function () {
        if ($( '.form-group').hasClass('has-error')) {
            $( '.form-group').removeClass('has-error');
            $( '#id-credentials-error' ).collapse('hide');
            $( '#id-reset-error' ).collapse('hide');
        }
    });

    $( '#id-reset-password-first, #id-reset-password-second' ).click(function () {
        if ($( '.form-group').hasClass('has-error')) {
            $( '.form-group').removeClass('has-error');
            $( '#id-password-error' ).collapse('hide');
        }
    });


    $( '#id-reset-button' ).click(function (e) {
        e.preventDefault();

        $.post('/login/', {
            login_action: 'reset',
            email: $( '#id-login-email' ).val()
        },
        function (data, status) {

            if (data['data']['success'] == 'success') {
                $.notify('An Email has been successfully sent to you. Please check your Emails.', 'success');

                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                $('.form-group').addClass('has-error');
                $('#id-reset-error').text(data['data']['success']);
                $('#id-reset-error').collapse('show');
            }
        });
    });

    $( '#id-change-button' ).click(function (e) {

        e.preventDefault();

        $.post('/login/', {
            login_action: 'change',
            token: $( '#id-token' ).val(),
            password_one: $( '#id-reset-password-first' ).val(),
            password_second: $( '#id-reset-password-second' ).val()
        },
        function (data, status) {

            if (data['data']['success'] == 'success') {
                $.notify('Your Password has been successfully changed', 'success');

                setTimeout(function() {
                    location.assign('/login/');
                }, 2000);
            } else {
                $('.form-group').addClass('has-error');
                $('#id-password-error').text(data['data']['success']);
                $('#id-password-error').collapse('show');
            }
        });
    });
}

$( window ).on('load', function() {
    login();
});

