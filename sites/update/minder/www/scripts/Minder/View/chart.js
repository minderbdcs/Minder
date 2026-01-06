Minder_View_Chart = $.inherit(
    Minder_View_Container,
    {
        setModel: function(model) {
            this.__base(model);

            $(model).bind(Minder_Model_ChartScreen.chartUpdatedEvent + '.' + this.__self.eventNamespace, this.onChartUpdated);

            return this;
        },

        getChartUrl: function() {
            return this._model.getFieldValue('chartUrl') + '/' + Math.random();
        },

        onChartUpdated: function(evt) {
            if (evt.sender == this) return;

            this._$renderedContent.find('img').attr('src', this.getChartUrl());
            this._$renderedContent.filter('img').attr('src', this.getChartUrl());
        }
    },
    {
        //static member
        eventNamespace: 'Minder_View_Chart'
    }
);