(function(root, factory) {

    if (typeof define === 'function' && define.amd) {
        define(['Mdr/Pages', 'backbone'], function(Pages, Backbone) {
            return factory(Pages, Backbone);
        });
    } else if (typeof exports !== 'undefined') {
        var Pages = require('Mdr/Pages'),
            Backbone = require('backbone');
        module.exports = factory(Pages, Backbone);
    } else {
        factory(root.Mdr.Pages, root.Backbone, root.Mdr.Components.ImageCapture);
    }

})(this, function(Pages, Backbone, ImageCapture){
    "use strict";

    var Otc = Pages.Otc = {};

    // @include ../ImageCaptureDialog.js

    return Pages.Otc;
});