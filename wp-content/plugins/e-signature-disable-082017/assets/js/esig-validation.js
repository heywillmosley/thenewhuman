
var esig_validation = {
    is_email: function (email_address) {
        var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        if (filter.test(email_address)) {
            return true;
        } else {
            return false;
        }
    },
    is_string: function (input_string) {

        if (input_string !="") {
            return true;
        }
        return false;
    }
};


