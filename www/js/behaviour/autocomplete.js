$p.addBinding('.pb-autocomplete',function() {
    var dom = $(this);
    dom.attr('autocomplete','off');
    var opts = $p.opts(dom);
    dom.autocomplete($.extend({
        source:opts.source,
        minLength:2
    },opts.opts));
});