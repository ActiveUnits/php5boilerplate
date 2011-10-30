(function(){
	var extractValue = function(key, root) {
		if(key.indexOf(".") == -1)
			return root[key];
		else {
			var firstElem = key.split(".").shift();
			return extractValue(firstElem,root[firstElem]);
		}
	};

    var parseTpl = function(tmpl, variables) {
		for (var key in variables) {
			if(tmpl.indexOf('{'+key+'}')>-1){
				tmpl=tmpl.replace(new RegExp('{'+key+'}','g'), extractValue(key,variables));
			}
		}
		return tmpl;
	}

    global.view = function(tmplPath, data) {
        return {
            loadView: function(callback) {
                var handleLoadView = function(tmplData){
                    var view = {
                        renderAt: function(target, before) {
                            var view = $(parseTpl(tmplData, data));
                            if(before)
                                before(view);
                            return $(target).html(view);
                        },
                        appendAt: function(target, before) {
                            var view = $(parseTpl(tmplData, data));
                            if(before)
                                before(view);
                            return $(target).append(view);
                        },
                        toString: function(){
                            return parseTpl(tmplData, data);
                        }
                    };

                    callback(view);
                };
                if(typeof global.view.usememory == "undefined") {
                    $.get(tmplPath, handleLoadView);
                } else {
                    if(typeof global.view.usememory[tmplPath] != "undefined") {
                        handleLoadView(global.view.usememory[tmplPath]);
                    } else {
                        $.get(tmplPath, function(tmplData){
                            global.view.usememory[tmplPath] = tmplData;
                            handleLoadView(tmplData)
                        });
                    }
                }
            },
            appendAt: function(target, before, after) {
                this.loadView(function(view){
                    var dom = view.appendAt(target, before);
                    if(after)
                        after(dom);
                });
            },
            renderAt: function(target, before, after) {
                this.loadView(function(view){
                    var dom = view.renderAt(target, before);
                    if(after)
                        after(dom);
                });
            }
        }
    };
    global.view.usememory = new Array();
})();
