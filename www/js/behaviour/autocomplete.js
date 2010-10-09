$p.addBinding('.pb-autocomplete',function() {
    var dom = $(this);
    dom.attr('autocomplete','off');
    var opts = $p.opts(dom);
    dom.autocomplete(opts.url,$.extend({
        autoFill:true,
        minChars:3
    },opts.opts));
});