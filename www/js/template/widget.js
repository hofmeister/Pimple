$p.Widget = function(template,container) {
	var newDate = new Date;
	this.guid = newDate.getTime();
	$p.Widget.windows[this.guid] = this;
	this.template = template;
	this.container = $(container);
	this.data = {};
};

$p.Widget.windows = {};

$p.getWidget = function(g) {
	return $p.Widget.windows[g];
}

$p.Widget.prototype = {
	setData: function(data) {
		this.data = data;
	},
	setJSON: function(url) {
		var c = this;
		$.ajax({
		    type: 'GET',
		    url: url,
		    dataType: 'json',
		    success: function(d) {
		    	c.setData(d);
		    },
		    async: false
		});
	},
	render: function(fn) {
        this.container.html(this.template(this.data, this.guid));
		if(fn!=null) {
			fn(this.data);
		}
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
}

$p.WidgetList = $p.Widget;
$p.WidgetList.prototype = $.extend($p.Widget.prototype,{
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
			this.data.rowsPerPage = (this.data.rowsPerPage==null) ? this.data.totalRows : this.data.rowsPerPage;
			this.data.totalPages = Math.ceil(this.data.origTotalRows/this.data.rowsPerPage);
            this.setPage(0);
		}
	},
    removeRow:function(path,value) {
        for(var i = 0; i < this.data.rows.length;i++) {
            var v = this.getDataByPath(path, this.data.rows[i]);
            if (value == v) {
                this.data.rows.splice(i,1);
            }
        }
        return null;
    },
    addRow:function(row) {
        this.data.rows.push(row);
    },
    setPaging: function(rowsPerPage) {
		this.data.totalPages = Math.ceil(this.data.origTotalRows/rowsPerPage);
		this.data.rowsPerPage = rowsPerPage;
		this.data.totalRows = (this.data.origTotalRows > rowsPerPage) ? rowsPerPage : this.data.origTotalRows;
		this.data.currentPageIndex = 0;
	},
	viewPage: function(pageIndex, fn) {
		this.setPage(pageIndex);
		this.render(fn);
		return false;
	},
    getPage:function() {
        return this.data.currentPageIndex;
    },
    getTotalPages:function() {
        return this.data.totalPages;
    },
	setPage: function(pageIndex) {
		var start = this.data.totalRows*pageIndex;
		var end = ((start+this.data.rowsPerPage) > this.data.origTotalRows) ? this.data.origTotalRows : (start+this.data.rowsPerPage);
		var newRows = new Array();
		for(var i=start;i<end;i++) {
			newRows.push(this.rows[i]);
		}
		this.data.totalRows = newRows.length;
		this.data.currentPageIndex = parseInt(pageIndex);
		this.data.rows = newRows;
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
