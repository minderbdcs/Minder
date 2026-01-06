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