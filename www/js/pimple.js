var Pimple = {
    settings:{
        basePath:'/'
    },
    widget:{},
    _bindings:{},
    _messageTimeout:null,
    _uidCount:0,
    
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
                        if (typeof $(sel)[m] == 'function') {
                            $(elm).filter(sel)[m]();
                            $(elm).find(sel)[m]();
                        } else if (console) {
                            console.log("Failed to init binding: " + m);
                        }
                    }
                    
                } else {
                    if (typeof m == 'function') {
                        $(sel).each(m);
                    } else {
                        if (typeof $(sel)[m] == 'function') {
                            $(sel)[m]();
                        } else if (console) {
                            console.log("Failed to init binding: " + m);
                        }
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
    uid:function() {
        var uid = "pimple-uid-" + Pimple._uidCount;
        Pimple._uidCount++;
        return uid;
    },
    opts: function(elm) {
        var optStr = elm.attr('p:options');
        if (optStr && (optStr.indexOf('[') > -1 || optStr.indexOf('{') || optStr.indexOf('\'')))
            return $.parseJSON(optStr.replace(/'/g,'"'));
        else
            return optStr;
    },
    addMessage: function(text,isError,timeout) {
        var cls = (isError) ? 'error' : 'success';
        $('.pimple-messages')
                    .append('<div class="message '+cls+'">'+text+'</div>');
        return Pimple.showMessages(timeout);
    },
    showMessages: function(timeout) {
        if (!timeout) timeout = 15000;
        $('.pimple-messages')
                    .show();
        if (Pimple._messageTimeout) {
            clearTimeout(Pimple._messageTimeout);
            Pimple._messageTimeout = null;
        }
        Pimple._messageTimeout = setTimeout(function() {
            $('.pimple-messages').fadeOut('fast',function() {
                $('.pimple-messages').html('');
            });
        },timeout);
        return Pimple;
    },
    /**
     * @param string the html to get size of
     * @param element optional element to put html into
     * @param function optional method to apply to clone of element
     */
    getSizeOfHtml: function(html,elm,elmPrepare) {
        var tmp = null;
        if (!elm)
            tmp = $('<div></div>');
        else
            tmp = elm.clone().attr('id','');
        if (elmPrepare) {
            elmPrepare(tmp);
        }
        tmp.html(html);
        tmp.css({
            position:'absolute',
            left:'-9999px'
        }).show();
        $('body').append(tmp);
        var result = {
            height:tmp.height(),
            width:tmp.width()
        }
        tmp.detach();
        return result;
    },
    forceFocus:function(elm) {
        if (!elm) elm = $('body');
        var felm = elm.find('.pimple-focus-element');
        if (felm.length == 0) {
            felm = $('<input class="pimple-focus-element" style="position:absolute;left:-9999px;top:0px;" />')
            elm.append(felm);
        }
        felm.focus();
    },
    forceBlur:function(elm) {
        if (!elm) elm = $('body');
        var felm = elm.find('.pimple-focus-element');
        if (felm.length == 0) {
            felm = $('<input class="pimple-focus-element" style="position:absolute;left:-9999px;top:0px;" />')
            elm.append(felm);
        }
        felm.blur();
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
    if ($p._messageTimeout) {
        clearTimeout($p._messageTimeout);
        $p._messageTimeout = null;
    }
    $p._messageTimeout = setTimeout(function() {
       $('.pimple-messages').fadeOut('fast',function() {
            $('.pimple-messages').html('');
        });
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