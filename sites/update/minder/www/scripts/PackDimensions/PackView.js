var PackView = Backbone.View.extend({

    $template: null,

    initialize: function(options) {
        this.$template = options.$template;
        this.render();

        this.onModelChanged(this.model);

        this.$el.change($.proxy(this.onInputChanged, this));
        this.listenTo(this.model, 'change', this.onModelChanged);
        this.listenTo(this.model, 'remove', this.onModelDeleted);
    },

    onModelDeleted: function() {
        this.remove();
    },

    onModelChanged: function(pack) {
        this.fillData(pack.attributes);
        this.fillData(pack.get('CALCULATED'));

        if (pack.locked) {
            this.$('input, select').attr('disabled', 'disabled');
        } else {
            this.$('input, select').removeAttr('disabled');
        }
    },

    fillData: function(attributes) {
        var attribute,
            val,
            calculatedAttributes = ['VOL', 'TOTAL_VOL', 'TOTAL_WT'];
        for (attribute in attributes) {
            if (attributes.hasOwnProperty(attribute)) {
                val = attributes[attribute];

                if (calculatedAttributes.indexOf(attribute) > -1) {
                    val = (parseFloat(val) || 0).toFixed(4);
                }

                this.$('[name="' + attribute + '"]').val(val);
                this.$('[for="' + attribute + '"]').text(val);

                if (attribute == 'TYPE') {
                    if (attributes[attribute] == 'P') {
                        this.$('[name="PALLET_OWNER"]').show();
                    } else {
                        this.$('[name="PALLET_OWNER"]').hide();
                    }
                }
            }
        }
    },

    onInputChanged: function(evt) {
        var $input = $(evt.target);
        this.model.set($input.attr('name'), $input.val());
    },

    render: function() {
        this.$el.remove();
        this.$el = this.$template.clone();
        this.$el.removeClass('hidden sample_row');
        this.$el.show();
    }
});