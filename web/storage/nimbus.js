define(function (require) {
    "use strict";

    var MediaboxStorage = function(url) {
        this.getFileUri = function(id) {
            return getFileUri(id)
        }
        function getFileUri(id) {
            var uri = url;
            var is_nimbus_client = $("#is_nimbus_client").val();

            var session = JSON.parse($.cookie('xvid.session'));
            var token = session ? encodeURIComponent(session.key) : '';
            var mbclientUrlData = "?access_token=" + token  + "&master_key=" + window.name;
            uri += "/files/" + id + mbclientUrlData;

            return uri;
        };

        this.sendFile = function() {
            return return url + '/save/';
        };

        this.remove = function() {
            return url + '/remove/';
        };
    }

    return MediaboxStorage;
});