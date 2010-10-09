Pimple.validators = {
    'v-required':function(formItem,value) {
        return value.length > 0;
    },
    'v-optional':function(formItem,value) {
        return true;
    },
    'v-min':function(formItem,value,args) {
        if (!value) return true; //If no value - its valid unless required
        var min = parseFloat(args[0]);
        var type = args[1];

        switch(type) {
            case 'int':
            case 'float':
            case 'double':
                return parseFloat(value) >= min;
            default:
                return value.length >= min;
        }
    },
    'v-captcha':function(formItem,value,args) {
        return Pimple.validators['v-required'](formItem, value);
    },
    'v-max':function(formItem,value,args) {
        if (!value) return true; //If no value - its valid unless required
        var max = parseFloat(args[0]);
        var type = args[1];

        switch(type) {
            case 'int':
            case 'float':
            case 'double':
                return parseFloat(value) <= max;
            default:
                return value.length <= max;
        }
    },
    'v-equal':function(formItem,value,args) {
        var equal = $(formItem[0].form.elements[args[0]]);
        if (!equal.data('v-equaled')) {
            equal.data('v-equaled',true);
            equal.bind('change blur keyup',function() {
                formItem.trigger('change');
            });
        }
        return value == equal.val();
    },
    'v-email':function(formItem,value,args) {
        if (!value) return true; //If no value - its valid unless required
        var regexp = /[A-Å0-9\._\-\+]+@[A-Å0-9\._\-]+\.[A-Å0-9\._\-]{2,}/i;
        return regexp.test(value);
    },
    'v-regexp':function(formItem,value,args) {
        if (!value) return true; //If no value - its valid unless required
        var regexp = new RegExp(args[0],'gi');
        return regexp.test(value);
    }
};

jQuery.fn.validate = function(show) {
    var elm = $(this);
    elm.trigger('before-validate');
    var dom = elm.closest('.form-item');
    var classes = dom.attr('class').split(' ');
    var domValids = [];
    var args = {};
    $.each(classes,function() {
        if (this.substr(0,2) == 'v-' && this != 'v-enabled') {
            var parts = this.split('[');
            var css = parts.shift();
            args[css] = [];
            if (parts.length > 0) {
                var argStr = parts.shift();
                argStr = argStr.substr(0,argStr.length-1);
                args[css] = argStr.split(',');
            }

            domValids.push(css);
        }
    });
    var hasValidated = (dom.hasClass('error') || dom.hasClass('valid'));
    for(var i = 0; i < domValids.length; i++) {
        var css = domValids[i];
        var validator = Pimple.validators[css];
        if (!validator) throw "Invalid validator: " + css;
        if (!validator(elm,elm.val(),args[css])) {
            if (hasValidated || show) {
                dom.removeClass('valid').addClass('error');
                dom.find('.error').html(dom.find('.error-' + css.substr(2)).html())
            }
            elm.trigger('after-validate');
            return false;
        } else {
            if (dom.hasClass('error'))
                dom.removeClass('error').addClass('valid');
        }
    }
    elm.trigger('after-validate');
    return true;  
};

jQuery.fn.clearValidation = function() {
    var elm = $(this);
    var dom = elm.closest('.form-item');
    dom.removeClass('error');
    dom.removeClass('valid');
};

jQuery.fn.validation = function() {
    this.each(function() {
        var dom = $(this);
        dom.find('input,select,textarea').bind('change blur keyup validate',function() {
            $(this).validate();
        });
    });
};
Pimple.addBinding('.v-enabled','validation');