$(function() {
    $('.pb-focusclears').live('focus after-validate',function(evt) {
        var dom = $(this);
        if (!dom.data('origValue')) {
            dom.data('origValue',dom.val());
        }
        if (dom.data('origValue') == dom.val()) {
            dom.val('');
        }
    });
    $('.pb-focusclears').live('blur before-validate',function(evt) {
        var dom = $(this);
        
        if (dom.val() == '') {
            dom.val(dom.data('origValue'));
        }
    });
    $('form').live('submit',function(evt) {
        $(this).find('.pb-focusclears').each(function() {
            var dom = $(this);
            if (dom.data('origValue') == dom.val()) {
                dom.val('');
            }
        });
    });
});
Pimple.addBinding('.pb-focusclears',function() {
    var dom = $(this);
    if (!dom.data('origValue')) {
        dom.data('origValue',dom.val());
    }
});