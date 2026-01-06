// Before using this jQuery plugin include the following scripts:
// 1. jquery.disable.text.select.js
// 2. jquery.dimensions.js
// 3. tablesorter.js.

(function($){
    $.warehouse = $.warehouse || {};

    $.fn.dragCols = function(o){
        return new $.warehouse.dragCols(this[0], o);  
    }

    $.warehouse.dragCols = function(table, o){
        this.options = {
            'drop': function(){},
            'params': null,    // By default drop calling without arguments.
            'cols': $('thead tr:first-child th', $(table))
        };
        var o = o || {};
        $.extend(this.options, o);

        // Save this for inner functions.
        var self = this; 

        this.table = table;

        this.dragbox = this.dragcol = null;

        // Get the table th columns.
        var cols = $(this.options.cols, table);

        // Disable text selection for the draggable columns.
        cols.disableTextSelect();

        cols.mousedown(function(e){      
            self.dragcol = $(this).disableTextSelect();
        });

        $(document).mouseup(function(e){
            var target = e.target;
            if (target.tagName.toLowerCase() == 'th' && self.dragbox && target.column != self.dragcol[0].column) {
                // column property was set in tablesorter.js.
                var dstJ = target.column, srcJ = self.dragcol.get(0).column;

                var srcWidth = self.dragcol.innerWidth();

                for (var i = 0, srcCol = dstCol = srcHtml = null; i < self.table.rows.length; i++) {
                    srcCol = $(self.table.rows[i].cells[srcJ]);
                    dstCol = $(self.table.rows[i].cells[dstJ]);

                    srcHtml = srcCol.html();

                    srcCol.html(dstCol.html());
                    dstCol.html(srcHtml);
                }
                
                // Call the callback function after drop. By default this function do nothing.
                var args = [self.table.id, self.dragcol[0].abbr, target.abbr];
                if (self.options.params) {
                    self.options.drop.apply(null, self.options.params.concat(args));
                } else {
                    self.options.drop.apply(null, args); 
                }
            }
            if (self.dragbox) {
                self.dragbox.remove();
            }
            self.dragcol = self.dragbox = null;
         });

        $(document).mousemove(function(e){
            if (!self.dragbox && self.dragcol) {
                if (!$('#dragbox').length){
                    $('<div id="dragbox" style="background: #fff; border: solid 1px #ccc; color: #444; padding: .5em; width: ' + self.dragcol.innerWidth() + 'px; position: absolute; font-size: ' + self.dragcol.css('font-size') + '; font-family: ' + self.dragcol.css('font-family') + '; left: ' + (e.pageX + 10) + 'px; top: ' + (e.pageY + 10) + 'px; display: none;">' + self.dragcol.text() + '</div>').appendTo(document.body).fadeIn('fast', function(){$(this).animate({'opacity': 0.85});});
                }           
                self.dragbox = $('#dragbox');            
            }
            if (self.dragbox) {
                self.dragbox.css({'left': e.pageX + 10, 'top': e.pageY + 10}); 
            }
        });
    }
})(jQuery);
