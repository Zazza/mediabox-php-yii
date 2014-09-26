define(function (require) {
    "use strict";

    var MediaboxConfiguration = function() {
        require('/config/ico.js');
        this.mimetypes = mimetypes;

        require('/config/extensions.js');
        this.extension = extension;

        require('/config/mimetypes.js');
        this.mediaTypes = mediaTypes;

        require('/config/session.js');
        this.session = session;

        require('/config/storage.js');
        var func = require('/storage/storage.js');
        this.storage = new func(url, client_id, client_secret);
    };

    return MediaboxConfiguration;
});