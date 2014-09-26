define(function (require) {
    "use strict";

    var MediaboxStorage = function(url, client_id, client_secret) {
        this.auth = function(session, func) {
            var request = {
                client_id: encodeURIComponent(client_id),
                client_secret: encodeURIComponent(client_secret),
                session: session
            }

            requestGet(url + '/api/access/', request, func);
        }

        this.createFolder = function(path, name, func) {
            var request = {
                path: encodeURIComponent(path),
                name: encodeURIComponent(name),
                token: $("#access_token").val()
            }

            requestGet(url + '/api/createFolder/', request, func);
        };

        this.removeFolder = function(path, name, func) {
            var request = {
                path: encodeURIComponent(path),
                name: encodeURIComponent(name),
                token: $("#access_token").val()
            }

            requestGet(url + '/api/removeFolder/', request, func);
        };

        this.getFileUri = function(path, name) {
            return url + '/api/get/?path=' + encodeURIComponent(path) + "&name=" + encodeURIComponent(name) + "&token=" + $("#access_token").val();
        };

        this.sendFile = function(path, file, func) {
            var uri = url + '/api/save/';

            var xhr = new XMLHttpRequest();

            xhr.open("POST", uri, true);
            var fd = new FormData();

            if (xhr.upload) {

                xhr.upload.addEventListener("progress", function(e) {
                    func(e);
                }, false);

                xhr.onreadystatechange = function(e) {
                    if (xhr.readyState == 4) {
                        func(JSON.parse(xhr.responseText));
                    }
                };

                $(".perc").on("click", ".uploaderRemove", function() {
                    xhr.abort();
                });

                fd.append('files', file);
                fd.append('name', encodeURIComponent(file.name));
                fd.append('path', encodeURIComponent(path));
                fd.append('token', $("#access_token").val());
                xhr.send(fd);
            }
        }

        this.removeFile = function(path, name, func) {
            var request = {
                path: path,
                name: encodeURIComponent(name),
                token: $("#access_token").val()
            }

            requestGet(url + '/api/remove/', request, func);
        };

        this.move = function(path, data, func) {
            var request = {
                path: encodeURIComponent(path),
                data: data,
                token: $("#access_token").val()
            }

            requestGet(url + '/api/move/', request, func);
        };

        this.rename = function(path, oldname, newname, func) {
            var request = {
                path: encodeURIComponent(path),
                old_name: encodeURIComponent(oldname),
                new_name: encodeURIComponent(newname),
                token: $("#access_token").val()
            }

            requestGet(url + '/api/rename/', request, func);
        };

        function requestGet(url, data, func) {
            $.ajax({
                url: url,
                data: data,
                contentType: "application/json",
                dataType: 'jsonp',
                success: function(data) {
                    func(data);
                }
            });
        }

        function requestPost(data) {
            $.ajax({
                url: url,
                data: data,
                contentType: "application/json",
                dataType: 'jsonp',
                success: function(data) {
                    return data;
                }
            });
        }
    };

    return MediaboxStorage;
});