(function(){
    global.modules.Test = function(){
        $("button").click(function(){            
            global.view("/views/sample-footer.html", {content: "Sample Footer"} ).loadView(function(footer){
                global.view("/views/sample.html", {
                    content: "Sample content",
                    footer: footer,
                }).renderAt($("body"), 
                    function(){
                        alert("before rendering");
                    },
                    function(){
                        alert('after rendering');
                    }
                );
            });
        });
    }
})();