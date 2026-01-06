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

    Otc.ImageCaptureDialog = (function(Backbone, ImageCapture, $){
    
        var IMAGE_NAME_MAP = ['Front', 'Top', 'Side'];
    
        return Backbone.View.extend({
            initialize: function(options){
                this._active = false;
                this._messages = options.messages || new Mdr.Components.Messages.MessagesView();
    
                this._initDialog();
                this.camera = new ImageCapture.View({el: this.$('.camera')});
                this.images = [];
                this.tool = null;
    
                this.listenTo(Backbone, 'capture-image-request', this.onCaptureImageRequest);
                this.listenTo(Backbone, 'close-capture-image-request', this.onCloseCaptureImageRequest);
                this._messages.$el.bind('remove', this.onUiMessagesRemove);
            },
    
            onUiMessagesRemove: function(evt) {
                evt.preventDefault();
                return false;
            },
    
            onCloseCaptureImageRequest: function() {
                if (this._active) {
                    this.$el.dialog('close');
                }
            },
    
            onCaptureImageRequest: function(tool) {
                if (this._active) {
                    this.$el.dialog('close');
                } else {
                    Backbone.trigger('close-opened-dialogs-request');
                    this.tool = tool;
                    this.$el.dialog('open');
                }
            },
    
            _initDialog: function() {
                var width = this.$el.css('width'),
                    height = this.$el.css('height');
    
                this.$el.css('width', 'auto');
                this.$el.css('height', 'auto');
    
                this.$el.dialog({
                    buttons: {
                        GET: $.proxy(this.onCaptureButtonClick, this),
                        SAVE: $.proxy(this.onSaveButtonClick, this),
                        CLOSE: $.proxy(this.onCloseButtonClick, this)
                    },
                    width: width,
                    height: height,
                    autoOpen: false,
                    position: [0, 65],
                    buttonStyle: 'otc-dialog-button'
                })
                    .bind('dialogopen', $.proxy(this.onDialogOpen, this))
                    .bind('dialogclose', $.proxy(this.onDialogClose, this));
            },
    
            onCaptureButtonClick: function() {
                if (this.images.length > 2) {
                    this.images = [];
                }
    
                this.images.push({
                    name: this.tool.id + '_' + this.images.length + '.png',
                    image: this.camera.captureImage()
                });
    
                this._renderCapturedImages();
            },
    
            _renderCapturedImage: function(image) {
                this.$('.captured-image-container').append('<div><h3>' + image.name + '</h3><div><img src="' + image.image + '" alt="no image" /></div></div>');
            },
    
            _renderCapturedImages: function() {
                this.$('.captured-image-container').empty();
    
                this.images.forEach($.proxy(this._renderCapturedImage, this));
            },
    
            _saveImageCallback: function(response) {
                this._messages.showResponseMessages(response);
    
                if (response.errors && response.errors.length > 0) {
                    return;
                }
    
                Backbone.trigger('tool-images-saved');
    
                this.$el.dialog('close');
            },
    
            onSaveButtonClick: function() {
                this._messages.clearAll();
                $.post(
                    '/minder/otc-service/save-image',
                    {tool: this.tool, images: this.images},
                    $.proxy(this._saveImageCallback, this),
                    'json'
                );
            },
    
            onCloseButtonClick: function() {
                this.$el.dialog('close');
            },
    
            onDialogOpen: function(event, ui) {
                this._active = true;
                this._messages.clearAll();
                this.images = [];
                this._renderCapturedImages();
                $(event.target).show();
    
                if (this.tool) {
                    this.camera.startVideoCapture();
                }
            },
    
            onDialogClose: function() {
                this._active = false;
                this.tool = null;
                this.camera.stopVideoCapture();
            }
        });
    
    })(Backbone, ImageCapture, jQuery);

    return Pages.Otc;
});