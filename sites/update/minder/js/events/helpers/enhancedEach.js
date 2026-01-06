module.exports = function(context, options) {
    var index,
        content = "";

    for (index = 0; index < context.length; index++) {
        content += options.fn({data: context[index], last: (index + 1 == context.length)});
    }

    return content;
};