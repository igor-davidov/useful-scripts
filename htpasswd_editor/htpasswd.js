$(function(){
    $('button.create_new_user').on('click', function(e){
        e.preventDefault();
        var target_user = $('input[name="uname"]').val();
        var target_password = $('input[name="upwd"]').val();
        if(target_user.length < 4){
            alert('Username must be at least 4 characters long.');
        } else {
            var lettersOnly = /^[a-zA-Z]+$/;
            var isValidUser = lettersOnly.test(target_user);
            if(isValidUser === false){
                alert('Username can contain only letters.');
            } else {
                if(target_password.indexOf('\\') > -1){
                    alert('Please do not use backslash in password.');
                } else {
                    if(target_password.length < 6){
                        alert('Minimum password length is 6 characters.');
                    } else {
                        $('#userform').submit();
                    }
                }
            }
        }
    });
    $('button.delete_user').on('click', function(e){
        e.preventDefault();
        var target_user = $(this).data('user');
        var is_locked = $(this).data('locked');
        if(is_locked == 'yes'){
            alert('Cannot delete locked user.');
        } else {
            var confirm_delete = confirm('Are you sure you want to delete >> ' + target_user + ' <<');
            if(confirm_delete === true){
                var script_file = $('input[name="script_file"]').val();
                window.location = script_file + '?a=del&u=' + target_user;
            }
        }
    });
    $('button.update_password').on('click', function(e){
        e.preventDefault();
        var target_user = $(this).data('user');
        var new_password = prompt('Please enter new password for >> ' + target_user + ' <<\nRules: Do not use backslash, minimum 6 characters long.', '');
        if(new_password != null){
            if(new_password.indexOf('\\') > -1){
                alert('Please do not use backslash.');
            } else {
                if(new_password.length < 6){
                    alert('Minimum password length is 6 characters.');
                } else {
                    var encodedPassword = encodeURIComponent(window.btoa(new_password));
                    var script_file = $('input[name="script_file"]').val();
                    window.location = script_file + '?a=up&u=' + target_user + '&p=' + encodedPassword;
                }
            }
        }
    });
    if($('#notify').length){
        setTimeout(function(){ $("#notify").animate({ height: 0, opacity: 0 }, 'slow'); }, 3500);
    }
});