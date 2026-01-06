(function(Minder2){

    Minder2.Controller.Element.DatePicker = function(name) {
        Minder2.Controller.Element.call(this, name);
    };

    Minder2.Controller.Element.DatePicker.prototype = new Minder2.Controller.Element();
    Minder2.Controller.Element.DatePicker.prototype.constructor = Minder2.Controller.Element.DatePicker;

    Minder2.Controller.Element.DatePicker.prototype.init = function() {
        var
            defaultDateFormat = 'yy-mm-dd';

        Minder2.Controller.Element.prototype.init.call(this);

        this._getHtmlElement().each(function(){
            var
                $this= $(this),
                dateFormat = $this.attr('data-date-format') || defaultDateFormat;

            $(this).datepicker({'dateFormat' : dateFormat});
        });
    }
})(Minder2);