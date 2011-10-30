(function(){
    global.config = {
        get: function(url, callback) {
            $.ajax({
                type: "GET",
                url: global.config.endpoint+url,
                dataType: "json",
                error: function(ajax, statusCode, errorMsg){
                    var err = new Error(errorMsg+" [code]:"+statusCode);
                    err.statusCode = statusCode;
                    callback(err);
                },
                success: function(data) {
                    callback(null, data);
                }
             });
        }
    };
    global.config.endpoint = "";
})();
