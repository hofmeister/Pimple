$p.addBinding('.pb-tokeninput',function() {
    var dom = $(this);
    dom.attr('autocomplete','off');
    var opts = $p.opts(dom);
    dom.tokenInput(opts.url,opts);
});