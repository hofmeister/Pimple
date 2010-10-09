jQuery.fn.list = function() {
    this.each(function() {
        var dom = $(this);
        dom.bind('add',function(evt,elm,val) {
            if (!val) return;
            var first = dom.children()[0];
            var row = dom.children().has(elm);
            var newElm = $(first).clone();
            row.before(newElm);
            Pimple.bind(newElm);
            newElm.trigger('value',[newElm,val]);
        });
        dom.bind('remove',function(evt,elm) {
            dom.children().has(elm).detach();
        });
    });
};
Pimple.addBinding('.pw-list','list');