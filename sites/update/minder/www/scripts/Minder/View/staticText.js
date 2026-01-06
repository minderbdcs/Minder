Minder_View_StaticText = $.inherit(
    Minder_View_Container,
    {
        _text: '',

        setSettings: function(settings) {


            this._settings = (!!settings) ? settings : {};

            if (this._settings.text)
                this.setText(this._settings.text);

            return this;
        },

        setText: function(text) {
            this._text = text;
            return this;
        },

        getTitle: function() {
            var title = this.getSetting('title');

            return (title === null) ? '' : title;
        },

        getValue: function() {
            return this._text;
        }
    },
    {

    }
);