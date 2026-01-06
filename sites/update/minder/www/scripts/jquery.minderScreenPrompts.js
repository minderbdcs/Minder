(function($){
    const PROMPTS = 'prompts';

    var
        methods = {
            'init': function(prompts) {
                prompts = prompts || [];
                return $(this).data(PROMPTS, prompts);
            },

            'getPrompts': function() {
                var result = $(this).data(PROMPTS);
                return result || [];
            },

            'getPrompt': function(code) {
                code = String(code);
                return methods.getPrompts.call(this).filter(function(prompt){return String(prompt.code) == code;}).shift();
            },

            'showPrompt': function(code, data) {
                data = data || [];
                var prompt = methods.getPrompt.call(this, code),
                    text;

                if (prompt) {
                    text = prompt.prompt;
                    data.forEach(function(item){
                        text = text.replace(item.placeholder, item.value);
                    });
                    $(this).text(text);
                } else {
                    methods.hidePrompt.call(this);
                }

                return this;
            },

            'hidePrompt': function() {
                $(this).text('');
                return this;
            }
        };

    $.fn.minderScreenPrompts = function(methodOrSettings) {
        if ($.isFunction(methods[methodOrSettings])) {
            return methods[methodOrSettings].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            return methods.init.apply(this, Array.prototype.slice.call(arguments, 0));
        }
    };

})(jQuery);