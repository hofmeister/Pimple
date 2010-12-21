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
            if ($.type(parms) == 'string') {
                url += parms;
            } else {
                var strings = [];
                for(var key in parms) {
                    if ($.type(parms[key]) == 'array') {
                        for(var i = 0; i < parms[key].length;i++) {
                            strings.push(key + "=" + encodeURIComponent(parms[key][i]));
                        }
                    } else
                        strings.push(key + "=" + encodeURIComponent(parms[key]));
                }
                url += strings.join('&');
            }
        }
        return url;
    },
    opts: function(elm) {
        var optStr = elm.attr('p:options');
        if (optStr && (optStr.indexOf('[') > -1 || optStr.indexOf('{') || optStr.indexOf('\'')))
            return $.parseJSON(optStr.replace(/'/g,'"'));
        else
            return optStr;
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
        if (YAHOO.widget && YAHOO.widget.Chart && Pimple.settings.pimplePath)
            YAHOO.widget.Chart.SWFURL = Pimple.settings.pimplePath + "js/plugins/yui/charts/assets/charts.swf";
    }
    Pimple.bind();
    setTimeout(function() {
       $('.pimple-messages').fadeOut();
    },15000);
    $(window).scroll(function() {
       $('.pimple-messages').css('bottom',0 - $(window).scrollTop());
    })
});

/* add various bindings */

$p.addBinding('.js-datepicker',function() {
    var dom = $(this);
    dom.attr('autocomplete','off');
    var opts = $p.opts(dom);
    var format = Pimple.settings.dateFormat
                    .replace(/Y/,'yy')
                    .replace(/y/,'y')
                    .replace(/d/,'dd')
                    .replace(/m/,'mm');
    dom.datepicker({
        dateFormat:format
    });
});