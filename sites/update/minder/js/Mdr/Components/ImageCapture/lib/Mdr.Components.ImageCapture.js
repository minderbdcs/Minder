(function(root, factory) {

    if (typeof define === 'function' && define.amd) {
        define(['Mdr/Components', 'backbone'], function(Components, Backbone) {
            return factory(Components, Backbone);
        });
    } else if (typeof exports !== 'undefined') {
        var Components = require('Mdr/Components'),
            Backbone = require('backbone');
        module.exports = factory(Components, Backbone);
    } else {
        factory(root.Mdr.Components, root.Backbone);
    }

})(this, function(Components, Backbone){
    "use strict";

    var ImageCapture = Components.ImageCapture = {};

    ImageCapture.UserMedia = (function(){
        var UserMedia = function() {
            var def = $.Deferred();
    
            this._previous = def.promise();
            def.resolve();
        };
    
        _.extend(UserMedia.prototype, {
    
            /**
             * @callback NavigatorUserMediaErrorCallback
             * @param {MediaStreamError} error
             */
            /**
             * @callback NavigatorUserMediaSuccessCallback
             * @param {MediaStream} stream
             */
    
            /**
             * @param constraints
             * @param {NavigatorUserMediaSuccessCallback} successCallback
             * @param {NavigatorUserMediaErrorCallback} errorCallback
             * @private
             */
            _getUserMedia : function(constraints, successCallback, errorCallback) {
                navigator.getUserMedia = ( navigator.getUserMedia ||
                                            navigator.webkitGetUserMedia ||
                                            navigator.mozGetUserMedia ||
                                            navigator.msGetUserMedia);
    
                if (navigator.getUserMedia) {
                    navigator.getUserMedia(constraints, successCallback, errorCallback);
                } else {
                    errorCallback({name: 'NotSupportedError', message: 'navigator.getUserMedia is not supported.'});
                }
            },
    
            getUserMedia: function(constraints) {
                var def = $.Deferred(),
                    self = this;
    
                this._previous.done(function(){
                    self._getUserMedia(
                        constraints,
                        function(mediaStream){def.resolve(mediaStream);},
                        function(mediaStreamError){def.reject(mediaStreamError);}
                    );
                });
    
                this._previous = def.promise();
    
                return this._previous;
            }
        });
    
        return new UserMedia();
    })();
    ImageCapture.View = (function(Backbone, $){
        return Backbone.View.extend({
    
            initialize: function(options) {
                var $canvas = this.$('canvas'),
                    width,
                    height;
    
                this.$video = this.$('video');
    
                if (this.$video.length < 1) {
                    throw 'ImageCapture.View: Video container not found';
                }
    
                width = this.$video.css('width');
                height = this.$video.css('height');
                this.$captureBtn = this.$('.capture');
                this.$changeSource = this.$('.change-source');
    
                if ($canvas.length < 1) {
                    $canvas = $('<canvas style="display: none" width="' + width + '" height="' + height + '" />');
                    this.$el.append($canvas);
                }
    
                this.ctx = $canvas[0].getContext('2d');
    
                this.$captureBtn.click($.proxy(this._onCaptureClick, this));
                this.$changeSource.click($.proxy(this._onChangeSourceClick, this));
            },
    
            startVideoCapture: function() {
                $.when(this._selectDevice())
                    .then($.proxy(this._onStreamReady, this))
                    .fail($.proxy(this._onStreamError, this));
            },
    
            stopVideoCapture: function() {
                this.$video[0].pause();
    
                if (this.mediaStream) {
                    this.mediaStream.stop();
                }
            },
    
            captureImage: function() {
                if (this.mediaStream) {
                    this.ctx.drawImage(this.$video[0], 0, 0, this.ctx.canvas.width, this.ctx.canvas.height);
                    return this.ctx.canvas.toDataURL('image/webp');
                }
    
                throw 'Device is not ready.';
            },
    
            _onChangeSourceClick: function() {
                $.when(this._selectDevice())
                    .then($.proxy(this._onStreamReady, this))
                    .fail($.proxy(this._onStreamError, this));
            },
    
            _onCaptureClick: function() {
            },
    
            _onStreamReady: function(mediaStream) {
                this.$video[0].src = window.URL.createObjectURL(mediaStream);
                this.$video[0].play();
                this.mediaStream = mediaStream;
            },
    
            _onStreamError: function(mediaStreamError) {
                showErrors(['Cannot use camera for capture: ' + (mediaStreamError.message || mediaStreamError.name)]);
            },
    
            _selectDevice : function(deviceId) {
                this.stopVideoCapture();
    
                return ImageCapture.UserMedia.getUserMedia({video : deviceId ? {optional: [{sourceId: deviceId}]} : true});
            }
        });
    })(Backbone, jQuery);

    return Components.ImageCapture;
});