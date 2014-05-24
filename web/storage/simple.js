define(function (require) {
    "use strict";

    var MediaboxStorage = function(url) {
        this.getFileUri = function(id) {
            return url + '/get/?id=' + id;
        };

        this.sendFile = function() {
            return url + '/save/';
        };

        this.remove = function() {
            return url + '/remove/';
        };
    };

    return MediaboxStorage;
});