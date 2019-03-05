var valid_pw = false;

function validate_pw() {
    if (valid_pw === true) {
        return true;
    }
    var password = document.getElementById('pw').value;
    passw0rd.check(password).then(function (res) {
        valid_pw = !res.pwned;
        if (res.pwned) {
            $('#notice').text("This password has previously appeared in a data breach. Please use a more secure alternative.");

            $("#pw").val('');
            $("#pw2").val('');
            $("#hibp").show();
        }
        else
        {
            // submit it
            $("#pw").closest('form').submit()
        }
    });
    return false;
}
