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