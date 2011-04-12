var Pimple = {
    E_NONE:0,
    E_FATAL:1,
    E_ERROR:2,
    E_WARNING:3,
    E_DEBUG:4,
    E_INFO:5,
    logLevel:2,
    settings:{
        basePath:'/'
    },
    widget:{},
    _bindings:{},
    _messageTimeout:null,
    _uidCount:0,
    _action:'',
    _controller:'',
    _parms:'',
    _init:false,
    _onInit:[],
    init: function() {
        var i;
        if (arguments.length > 0) {
            for(i = 0;i < arguments.length;i++) {
                if (Pimple._init) {
                    arguments[i]();
                } else {
                    Pimple._onInit.push(arguments[i]);
                }
            }
        } else {
            if (Pimple._init) {
                return;
            }
            Pimple._init = true;
            for(i = 0;i < Pimple._onInit.length;i++) {
                Pimple._onInit[i]();
            }
        }
    },
    addBinding: function(selector,method){
        
        if (!this._bindings[selector]) {
            this._bindings[selector] = [];
        }
        this._bindings[selector].push(method);
    },
    setLogLevel:function(lvl) {
        this.logLevel = lvl;
    },
    log:function(lvl,msg) {
        if ((typeof msg) == "undefined") {
            msg = lvl;
            lvl = 0;
        }
        if (lvl > this.logLevel) return;
        if ((typeof console) != "undefined" &&
                (typeof console.log) != "undefined" ) {
            var prefix = "";
            switch(lvl) {
                case Pimple.E_DEBUG:
                    prefix = "DBG";
                    break;
                case Pimple.E_INFO:
                    prefix = "INFO";
                    break;
                case Pimple.E_ERROR:
                    prefix = "ERR";
                    break;
                case Pimple.E_FATAL:
                    prefix = "FATAL";
                    break;
                case Pimple.E_WARNING:
                    prefix = "WARN";
                    break;
            }
            if (prefix.length > 0)
                console.log(prefix + ": "+ msg);
            else
                console.log(msg);
        }
    },
    bind:function(elm) {
        
        for(var sel in this._bindings) {
            for(var x = 0; x < this._bindings[sel].length;x++) {
                var m = this._bindings[sel][x];
                if (elm) {
                    $(elm).each(function() {
                        var el = $(this);
                        if (typeof m == 'function') {
                            el.filter(sel).each(m);
                            el.find(sel).each(m);
                        } else {
                            if (typeof $(sel)[m] == 'function') {
                                el.filter(sel)[m]();
                                el.find(sel)[m]();
                            } else {
                                Pimple.log(Pimple.E_FATAL,"Failed to init binding: " + m);
                            }
                        }
                    });
                } else {
                    if (typeof m == 'function') {
                        $(sel).each(m);
                    } else {
                        if (typeof $(sel)[m] == 'function') {
                            $(sel)[m]();
                        } else {
                            Pimple.log(Pimple.E_FATAL,"Failed to init binding: " + m);
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
    getController:function() {
        return Pimple._controller;
    },
    getAction:function() {
        return Pimple._action;
    },
    getParms:function() {
        return Pimple._parms;
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
    addMessage: function(text,isError,timeout,append) {
    	append = (append==null) ? true : false;
        var cls = (isError) ? 'error' : 'success';
    	if(!append && $('.pimple-messages div.message').length > 0) {
    		$('.pimple-messages div.message').first().html(text).show();
    	} else {
    		$('.pimple-messages').append('<div class="message '+cls+'">'+text+'</div>');
    	}
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
     * @param html the html to get size of
     * @param elm optional element to put html into
     * @param elmPrepare optional method to apply to clone of element
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
    getParm:function(name) {
        if (Pimple._parms.indexOf('#') > -1) {
            var prop = Pimple._parms.substr(Pimple._parms.indexOf('#'));
            Pimple._parms = Pimple._parms.substr(0,Pimple._parms.indexOf('#'));
        }
            
        var parts = Pimple._parms.split('&');

        for(var i = 0;i < parts.length;i++) {
            var nameVal = parts[i].split("=");
            if (nameVal[0] == name)
                return nameVal[1];
        }
        return "";
    },
    forceFocus:function(elm) {
        var container = elm;
        if (!elm) {
            elm = $('body');
            container = $(window);
        }
        var felm = elm.find('.pimple-focus-element');
        if (felm.length == 0) {
            felm = $('<input class="pimple-focus-element" style="position:absolute;left:-9999px;top:0px;" />')
            elm.append(felm);
            felm.bind('focus',function() {
                elm.addClass('focussed');
            }).bind('blur',function() {
                elm.removeClass('focussed');
            });
        }
        if (!elm.hasClass("focussed")) {
            felm.css('top',container.scrollTop()+40);
            felm.focus();
        }
        
    },
    forceBlur:function(elm) {
        var container = elm;
        if (!elm) {
            elm = $('body');
            container = $(window);
        }
        var felm = elm.find('.pimple-focus-element');
        if (felm.length == 0) {
            felm = $('<input class="pimple-focus-element" style="position:absolute;left:-9999px;top:0px;" />')
            elm.append(felm);
            felm.bind('focus',function() {
                elm.addClass('focussed');
            }).bind('blur',function() {
                elm.removeClass('focussed');
            });
        }
        if (elm.hasClass("focussed")) {
            felm.css('top',container.scrollTop()+40);
            felm.blur();
        }
    },
    string: {
    	addslashes: function(str) {
    		str=str.replace(/\\/g,'\\\\');
    		str=str.replace(/\'/g,'\\\'');
    		str=str.replace(/\"/g,'\\"');
    		str=str.replace(/\0/g,'\\0');
    		return str;
    	},
    	stripslashes: function(str) {
    		str=str.replace(/\\'/g,'\'');
    		str=str.replace(/\\"/g,'"');
    		str=str.replace(/\\0/g,'\0');
    		str=str.replace(/\\\\/g,'\\');
    		return str;
    	}
    },
    format: {
		date: function(format, str) {
			return dateFormat(new Date(str), format);
		}
	},
	utils: {
		getFirst: function(arr) {
			for(i=0;i<arr.length;i++) {
				if(arr[i] != null) {
					return arr[i];
				}
			}
			return null;
		}
	},
	isset: function (obj){
		return !(typeof obj=='undefined' || obj==null);
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
$(function() {
    if (window.YAHOO != undefined) {
        if (YAHOO.widget && YAHOO.widget.Chart && Pimple.settings.pimplePath)
            YAHOO.widget.Chart.SWFURL = Pimple.settings.pimplePath + "js/plugins/yui/charts/assets/charts.swf";
    }
    Pimple.init();
    /* resolve path */
    var url = location.href.substr(Pimple.settings.basePath.length);
    Pimple._parms = '';
    if (url.indexOf('?') > -1) {
        Pimple._parms = url.substr(url.indexOf('?')+1);
        url = url.substr(0,url.indexOf('?'));
    }
    var parts = url.substr(0,url.length-1).split('/');
    Pimple._controller = parts[0];
    Pimple._action = parts[1];
    /* bind behaviours */
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