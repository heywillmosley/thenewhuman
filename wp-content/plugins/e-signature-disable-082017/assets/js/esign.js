
var esign = {
    setCookie: function (cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    },
    getCookie: function (name) {
        var pattern = RegExp(name + "=.[^;]*")
        matched = document.cookie.match(pattern)
        if (matched) {
            var cookie = matched[0].split('=')
            return cookie[1]
        }
        return false
    },
    unsetCookie:function(name){
        var d = new Date();
        d.setTime(d.getTime() - (5000*24*60*60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = name + "="+ +"; " + expires;
    },
    is_slv_active:function(){
         if (typeof esig_slv === 'undefined') {
             return false;
         }
         else {
             return true; 
         }
    },
    is_valid_email:function(email){
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        
        if (re.test(email))
         {
               return true;
         }
         else {
             return false;
         }
    }

};
