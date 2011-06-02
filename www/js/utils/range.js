if (Pimple) {
	(function() {
		if (!Pimple.utils)
			Pimple.utils = {};
		var tmp = $('<div class="offscreen"></div>');
		Pimple.utils.range = {
			getCurrent:function() {
				try {
					var selection = rangy.getSelection();
					if (!selection) return false;
					return selection.getRangeAt(0);
				} catch(er) {
					return null;
				}
			},
			/**
			 * Get html contents of range
			 */
			getHtml:function(range) {
				if (!range) range = this.getCurrent();
				if (!range) return "";
				tmp.html('');
				tmp[0].appendChild(range.cloneContents());
				return tmp.html();
			},
			getNodes: function(range) {
				if (!range) range = this.getCurrent();
				if (!range) return [];
				tmp.html('');
				tmp[0].appendChild(range.cloneContents());
				//return tmp[0].childNodes;
				var nodes = [];
				for(var i = 0; i < tmp[0].childNodes.length;i++) {
					nodes.push(tmp[0].childNodes[i]);
				}
				return nodes;
			},
			/**
			 * Expand range to nearest "breakby" on either side
			 */
			expand: function(range,breakBy) {
				
				var origRange = range.cloneRange();
				if (!breakBy) throw "breakBy is a required argument";

				var nodes = this.getNodes(range);
				if (nodes.length == 2 && nodes[0].nodeType == 3 && nodes[0].nodeValue == '') {
					range.setStart(range.endContainer,0);
				}

				var text = this.getHtml(range);

				try {
					var max = 10;
					var i = 0;
					var breakNext = false;
					while(true) {
						try {
							if (text.length > 0
								&& (breakBy.test(text.substr(0,1)))) {
								range.setStart(range.startContainer,range.startOffset+1);

								breakNext = true;
								text = this.getHtml(range);
								continue;
							} else if (breakNext) {
								break;
							}
							range.setStart(range.startContainer,range.startOffset-1);
						} catch(e) {
							break;
						}
						text = this.getHtml(range);
						i++;
						if (i > max) break;
					}

					text = this.getHtml(range);

					i = 0;
					breakNext = false;
					while(true) {
						try {
							if (text.length > 0
								&& (breakBy.test(text.substr(text.length-1)))) {
								range.setEnd(range.endContainer,range.endOffset-1);
								breakNext = true;
								text = this.getHtml(range);
								continue;
							} else if (breakNext) {
								break;
							}
							range.setEnd(range.endContainer,range.endOffset+1);
						} catch(e) {
							break;
						}

						text = this.getHtml(range);
						i++;
						if (i > max) break;
					}


					text = this.getHtml(range);
				} catch(e) {
					range.setEnd(origRange.endContainer,origRange.endOffset);
					range.setStart(origRange.startContainer,origRange.startOffset);
				}
			}
		};
	})();
} else {
	throw "Pimple js framework not found in utils/range.js";
}