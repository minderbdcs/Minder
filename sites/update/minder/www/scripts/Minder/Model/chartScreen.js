Minder_Model_ChartScreen = $.inherit(
    Minder_Model,
    {
        _setFields: function(fields) {
            this.__base(fields);

            if (this.getFieldValue('SS_REFRESH') > 0)
                this._refreshIn(this.getFieldValue('SS_REFRESH'));

            return this;
        },

        _refreshIn: function(seconds) {
            setTimeout(this.updateChart, seconds * 1000);
        },

        updateChart: function() {
            this.notifyChartUpdated();

            if (this.getFieldValue('SS_REFRESH') > 0)
                this._refreshIn(this.getFieldValue('SS_REFRESH'));

            return this;
        },

        notifyChartUpdated: function() {
            this.notify(this.__self.chartUpdatedEvent, this);
        }

    },
    {
        //static members
        chartUpdatedEvent: 'chartUpdatedEvent'
    }
);