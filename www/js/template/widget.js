$p.Event = $p.Class({
    target:null,
    stopped:false,
    initialize:function(target) {
        this.target = target;
    },
    stop:function() {
        this.stopped = true;
    }
});

$p.EventEmitter = $p.Class({
    _events:{},
    bind:function(event,fn) {
        if (!$p.isset(this._events[event]))
            this._events[event] = [];
        this._events[event].push(fn);
    },
    trigger:function(event,parms) {
        if (!$p.isset(this._events[event])) return;
        var evt = new $p.Event(this);
        for(var i = 0;i < this._events[event].length;i++) {
            this._events[event][i].apply(this,[evt,parms]);
        }
    }
});

$p.Widget = $p.Class({
    extend:$p.EventEmitter,
    guid:null,
    template:null,
    container:null,
    data:{},
    initialize:function(template,container) {
        var newDate = new Date();
        this.guid = newDate.getTime();
        $p.Widget._registry[this.guid] = this;
        this.template = template;
        this.container = $(container);
        if (this.container.length == 0)
            $p.log("widget.js: Container not found: "+container);
        if (!$p.isset(template))
            $p.log("widget.js: Template method not found for "+container);
        
    },
	setData: function(data) {
        this.trigger("data",data);
		this.data = data;
	},
	setJSON: function(url,fn) {
		var c = this;
		$.ajax({
		    type: 'GET',
		    url: url,
		    dataType: 'json',
		    success: function(d) {
                c.trigger("json",d);
		    	c.setData(d);
		    },
		    async: false
		});
	},
	render: function(fn) {
        try {
            this.container.html(this.template(this.data, this.guid));
        } catch(e) {
            $p.log($p.E_FATAL,"Error rendering template:"+e);
        }
		if(fn!=null) {
			fn(this.data);
		}
        this.trigger("render");
	},
	getData: function() {
		return this.data;		
	},
    getDataByPath: function(path,data) {
        var parts = path.split('/');
        var d = (data) ? data : this.data;
        for(var i = 0;i < parts.length; i++) {
            var p = parts[i];
            var ix = 0;
            if (p.indexOf("[") > -1) {
                var nameIndex = p.split('[');
                p = nameIndex[0];
                ix = parseInt(nameIndex);
            }
            switch ($.type(d[p])) {
                case 'array':
                    d = d[p][ix];
                    break;
                default:
                    d = d[p];
                    break;
            }
        }
        return d;
    }
});

$p.Widget._registry = {};
$p.getWidget = function(g) {
	return $p.Widget._registry[g];
}

$p.WidgetList = $p.Class({
    extend:$p.Widget,
    cbLimit:5,
    cbFunction:null,
    rows:[],
    initialize:function() {
        
    },
    getRowByValue:function(path,value) {
        for(var i = 0; i < this.rows.length;i++) {
            var v = this.getDataByPath(path, this.rows[i]);
            if (value == v) return this.rows[i];
        }
        return null;
    },
    getRowIndexByValue:function(path,value) {
        for(var i = 0; i < this.rows.length;i++) {
            var v = this.getDataByPath(path, this.rows[i]);
            if (value == v) return i;
        }
        return null;
    },
    getRow:function(i) {
        return this.rows[i];
    },
    getRows:function(i) {
        return this.rows;
    },
    setData: function(data) {
		this.data = data;
        if(this.data.rows != null) {
			this.rows = this.data.rows;
			/* Preset variables */
			this.data.currentPageIndex = (this.data.currentPageIndex!=null) ? parseInt(this.data.currentPageIndex) : 0;
			this.data.totalRows = (this.data.totalRows!=null) ? this.data.totalRows : this.data.rows.length;
			this.data.maxRows = this.data.totalRows;
			this.data.rowsPerPage = (this.data.rowsPerPage==null) ? this.data.totalRows : this.data.rowsPerPage;
			this.data.totalPages = Math.ceil(this.data.origTotalRows/this.data.rowsPerPage);
            this.setPage(0);
		}
	},
    removeRow:function(path,value) {
        var v,i;
        for(i = 0; i < this.data.rows.length;i++) {
            v = this.getDataByPath(path, this.data.rows[i]);
            if (value == v) {
                this.data.rows.splice(i,1);
            }
        }
        for(i = 0; i < this.rows.length;i++) {
            v = this.getDataByPath(path, this.rows[i]);
            if (value == v) {
                var row = this.rows.splice(i,1);
                this.trigger("removeRow",row);
                return row;
            }
        }
        return null;
    },
    setRow:function(index,row) {
        this.rows[index] = row ;
    },
    addRow:function(row) {
        this.rows.push(row);
        this.data.rows.push(row);
    },
    setPaging: function(rowsPerPage) {
		this.data.totalPages = Math.ceil(this.data.origTotalRows/rowsPerPage);
		this.data.rowsPerPage = rowsPerPage;
		this.data.totalRows = (this.data.origTotalRows > rowsPerPage) ? rowsPerPage : this.data.origTotalRows;
		this.setPage(this.data.currentPageIndex);
	},
	viewPage: function(pageIndex, fn) {
		this.setPage(pageIndex);
		this.render(fn);
		return false;
	},
	appendData: function(data) {
		if(!this.data.rows || this.data.rows.length == 0) {
            this.setData(data);
		} else {
			for(var i=0;i<data.rows.length;i++) {
				this.addRow(data.rows[i]);
			}
			this.data.maxRows = this.rows.length;
		}
	},

	clear:function() {
        this.data = {};
        this.rows = [];
    },
    init:function(page) {
        if (!page) page = 0;
        this.clear();
        this.cbFunction(this,0,page);
    },
	setCallback: function(fn, cbLimit) {
        if (cbLimit > 0)
            this.cbLimit = cbLimit;
        this.cbFunction = fn;
	},
    getPageIndex:function() {
        return this.data.currentPageIndex;
    },
    getTotalPages:function() {
        return this.data.totalPages;
    },
	setPage: function(pageIndex) {
        this.trigger("page",pageIndex);
        this.data.rows = [];
        //this.data.totalRows = 0;
		var start = this.data.rowsPerPage*pageIndex;
		var end = ((start+this.data.rowsPerPage) > this.data.origTotalRows) ? this.data.origTotalRows : (start+this.data.rowsPerPage);
		var newRows = [];
        var moreRows = true;
        if (start > (this.rows.length-1) && start < this.data.origTotalRows) {
            //We have more rows - but we have not fetched them yet
            moreRows = false;
        }
        if (this.data.origTotalRows > 0
                && this.cbFunction != null
                && this.rows.length < this.data.origTotalRows) {
            //There are more rows available on the server
            var pagesToEnd = Math.ceil((this.rows.length-end)/this.data.rowsPerPage);
            if (pagesToEnd < 0) pagesToEnd = 0;
            if (pagesToEnd < this.cbLimit) {
                //Trigger the callback
                this.cbFunction(this,this.rows.length,pageIndex);
            }
        }
        if (moreRows) {
            for(var i=start;i<end;i++) {
                if ($p.isset(this.rows[i]))
                    newRows.push(this.rows[i]);
            }
            this.data.totalRows = newRows.length;
            this.data.currentPageIndex = parseInt(pageIndex);
            this.data.rows = newRows;
        }
	},
	setSort: function(field, order) {
		this.data.sortOrder = order.toLowerCase();
		this.rows.sort(function(x,y) {
			var first = x[field];
			if(!isNaN(parseInt(first))) {
				if(order.toLowerCase() == 'desc') {
					return x[field] - y[field];
				}
				return y[field] - x[field];
			} else {
				if(x[field]==y[field]){return 1;}
	            if(order.toLowerCase() == 'asc'){
	                if(x[field]<y[field]){return -1;}
	            }else{
	                if(x[field]>y[field]){return -1;}
	            }
	            return 1;
			}
		});
		this.setPage(this.data.currentPageIndex);
	}
});
