define(function (require) {
    "use strict";

    var MediaboxConfiguration = function() {
        require('/config/ico.js');
        this.mimetypes = mimetypes;

        require('/config/extensions.js');
        this.extension = extension;

        require('/config/mimetypes.js');
        this.mediaTypes = mediaTypes;

        var func = require('/storage/simple.js');
        this.storage = new func('http://storage');
    };

    return MediaboxConfiguration;
});