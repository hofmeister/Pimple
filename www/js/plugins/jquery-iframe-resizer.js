/**
 * @author Stephane Roucheray
 * @extends jquery
 *
 * Changed quite a lot sinze Stephane made something fairly odd... /Henrik Hofmeister
 */

jQuery.fn.iframeResize = function(options){
	var settings = jQuery.extend({
		width: "fill",
		height: "auto",
		autoUpdate : true
	}, options);
	var filler = 30;

	this.each(function() {
        var frame = $(this);
        var body = frame.contents().find("body");
        var interval = null;
        frame.css('overflow','hidden');

        var resize = function() {
            body = frame.contents().find("body");
            frame.css("width",  settings.width  == "fill" ? "100%" : parseInt(settings.height));
            var autoheight = 0;
            try {
                autoheight = body.height() + filler;
                frame.css("height", settings.height == "auto" ? autoheight : parseInt(settings.height));
                console.log(body);
                console.log(body.height());
            } catch(e) {
                console.log(e);
                frame.css('overflow','auto');
                frame.css('height','600px');
                clearInterval(interval);
            }
        };
        frame.bind("load",resize);

		if (settings.autoUpdate) {
            frame.bind("load",function() {
                if (interval) {
                    clearInterval(interval);
                    interval = null;
                }
                if ($.browser.msie) {
                    frame.attr("scrolling", "auto");
                }
                interval = setInterval(resize, 1000);
            });
			
		}
        resize();
    });
};
