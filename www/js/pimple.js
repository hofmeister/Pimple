var Pimple = {
    settings:{
        basePath:'/'
    },
    widget:{},
    _bindings:{},
    addBinding: function(selector,method){
        
        if (!this._bindings[selector]) {
            this._bindings[selector] = [];
        }
        this._bindings[selector].push(method);
    },
    bind:function(elm) {

        for(var sel in this._bindings) {
            for(var x = 0; x < this._bindings[sel].length;x++) {
                var m = this._bindings[sel][x];
                if (elm) {
                    if (typeof m == 'function') {
                        $(elm).filter(sel).each(m);
                        $(elm).find(sel).each(m);
                    } else {
                        $(elm).filter(sel)[m]();
                        $(elm).find(sel)[m]();
                    }
                    
                } else {
                    if (typeof m == 'function') {
                        $(sel).each(m);
                    } else {
                        $(sel)[m]();
                    }
                }
            }
        }
    },
    url:function(controller,action,parms) {
        var url = Pimple.settings.basePath;
        if (controller)
            url += encodeURIComponent(controller) + "/";
        if (action)
            url += encodeURIComponent(action) + "/";
        if (parms) {
            url += '?';
            for(var key in parms) {
                url += key + "=" + encodeURIComponent(parms[key]);
            }
        }
        return url;
    },
    opts: function(elm) {
        var optStr = elm.attr('p:options');
        var opts = $.parseJSON(optStr.replace(/'/g,'"'));
        return opts;
    }
};
window.$p = Pimple;
// Get user selection text on page
$.fn.selectRange = function(start, end) {
        return this.each(function() {
                if(this.setSelectionRange) {
                        this.focus();
                        this.setSelectionRange(start, end);
                } else if(this.createTextRange) {
                        var range = this.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', end);
                        range.moveStart('character', start);
                        range.select();
                }
        });
};

$(function() {
    if (window.YAHOO != undefined) {
        if (YAHOO.widget && YAHOO.widget.Chart)
            YAHOO.widget.Chart.SWFURL = "http://yui.yahooapis.com/2.8.1/build/charts/assets/charts.swf";
    }
    Pimple.bind();
    setTimeout(function() {
       $('.pimple-messages').fadeOut();
    },5000);
    $('body').scroll(function() {
       $('.pimple-messages').css('bottom',1);
       $('.pimple-messages').css('bottom',0);
    })
});