/**
 * @author Stephane Roucheray
 * @extends jquery
 */

jQuery.fn.iframeResize = function(options){
	var settings = jQuery.extend({
		width: "fill",
		height: "auto",
		autoUpdate : true
	}, options);
	var filler = 30;

	var resizeIframe = function(event){
		var body = jQuery(this);
        event.data.frame.css("width",  settings.width  == "fill" ? "100%" : parseInt(settings.height));
		event.data.frame.css("height", settings.height == "auto" ? body.outerHeight(true) + filler : parseInt(settings.height));
	}
    

	this.each(function() {
        var frame = $(this);
        frame.css('overflow','hidden');
        var body = frame.contents().find("body");

        var immediateResize = function(){
            var e = jQuery.Event();
            e.data = {};
            e.data.frame = frame;
            resizeIframe.call(body, e);
        }
		//frame.css("overflow", "hidden");

        frame.bind("load", {
            frame: frame
        }, resizeIframe);

		if (settings.autoUpdate) {
			if ($.browser.msie) {
				frame.attr("scrolling", "auto");
				setInterval(immediateResize, 1000);
			}
			else {
				body.bind("DOMSubtreeModified", {
					frame: frame
				}, resizeIframe);
			}
		}
		immediateResize();
    });
};
