Minder_View_Caption = $.inherit(
    Minder_View_Container,
    {
        getValue: function() {
            var fieldValue = this._model.getFieldValue(this.getName());
            return (!fieldValue) ? '' : fieldValue;
        },
        
        getTitle: function() {
            var title = this.getSetting('title');

            return (title === null) ? '' : title;
        },

        getCaption: function() {
            var caption = this.getSetting('caption');
            return (caption === null) ? '' : caption;
        }
    },
    {

    }
);