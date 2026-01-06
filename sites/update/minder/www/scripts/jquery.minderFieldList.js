(function($) {

    function onFieldClick(evt) {
        var event = $.Event('field-click');

        event.minderFieldListField = null;

        var $_currentTarget = $(evt.currentTarget);
        var $_targetInput = $_currentTarget.find('input');

        if ($_targetInput.length > 0) {
            event.minderFieldListField = {'name' : $_targetInput.attr('name'), 'value': $_targetInput.val()};
        }

        $_currentTarget.trigger(event);
    }

    $.fn.minderFieldList = function() {
        var $_this = $(this);

        $_this.find('ul.fields').remove();
        $_this.append($('<ul class="fields"></ul>')).addClass('field-list');
        $_this.find('ul.fields').undelegate('li', 'click.minder-field-list').delegate('li', 'click.minder-field-list', onFieldClick);
        return $_this;
    };

    $.fn.minderFieldListClear = function() {
        $(this).find('ul.fields li').remove();

        return $(this);
    };

    $.fn.minderFieldListAddFields = function(fields) {
        var $_liContainer = $(this).find('ul.fields');

        $.each(fields, function(index, item) {
            $_liContainer.append($('<li></li>').append($('<input type="text">').attr('name', index).val(item)));
        });

        return $(this);
    };

    $.fn.minderFieldListAddField = function(fieldName, fieldLabel) {
        $(this).find('ul.fields').append($('<li></li>').append($('<input type="text" />').attr('name', fieldName).val(fieldLabel)));
        return $(this);
    };

    $.fn.minderFieldListAddItem = function(item) {
        $(this).find('ul.fields').append(item);
        return $(this);
    };

    $.fn.minderFieldListGetItem = function(fieldName) {
        return $(this).find('ul.fields input[name="' + fieldName + '"]').parent('li');
    };

    $.fn.minderFieldListGetFields = function() {
        var result = [];

        $(this).find('ul.fields input').each(function(index, item){
            result.push({
                        'name' : $(item).attr('name'),
                        'value' : $(item).val()
                    });
        });

        return result;
    };

    $.fn.minderFieldListGetFieldsCount = function() {

        return this.minderFieldListGetFields().length;
    };

    $.fn.minderFieldListGetLastField = function() {
        var lastInput = $(this).find('ul.fields input:last');

        if (lastInput.length > 0) {
            return {'name' : lastInput.attr('name'), 'value': lastInput.val()};
        }

        return null;
    }
})(jQuery);