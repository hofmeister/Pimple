jQuery.fn.wysiwyg = function(opts) {
    this.each(function() {
        /* vars */
        var dom = $(this);
        var fid = dom.attr('id');
        dom.html(HTML.clean(dom.html()));
        
        CKEDITOR.replace(fid, {
            toolbar : [
                ['Maximize','PasteFromWord'],['Vessel','Location','Date'],
                ['Bold', 'Italic','Strike','Underline', 'NumberedList', 'BulletedList', 'Link','Unlink'],
                ['TextColor','BGColor'],'/',
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Format','Font','FontSize'],
            ],
            removePlugins:'clipboard,resize,print,preview,pagebreak,forms',
            contentsCss:[Pimple.settings.basePath + 'css/site.css',Pimple.settings.basePath + 'css/wysiwyg.css'],
            height : dom.height(),
            width : dom.width()+17,
            tabSpaces : 4
        });
    });
};