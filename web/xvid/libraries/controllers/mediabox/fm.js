document.executeOnce('/sincerity/classes/')
document.executeOnce('/sincerity/templates/')
document.executeOnce('/mediabox-data/data-fm/')
document.executeOnce('/mediabox-data/data-session/')
document.executeOnce('/mediabox-data/data-buffer/')

FmResource = Sincerity.Classes.define(function() {

    var Public = {}

    Public.handleInit = function(conversation) {
        conversation.addMediaTypeByName('text/html')
        conversation.addMediaTypeByName('text/plain')
    }

    Public.handlePost = function(conversation) {
        var session = null
        var cookie = conversation.getCookie('xvid.session')
        if (null !== cookie) {
            cookie = Sincerity.JSON.from(decodeURIComponent(cookie.value))
            session = Xvid.Authorization.getSessionByKey(cookie.key)
            if (null !== session) {
                if (!session.isAuthorized()) {
                    return
                } else {
                    session_start(session.data.username)
                    setUsername(session.data.username)
                }
            }
        }

        var action = conversation.locals.get('action')

        if (action == "copy") {
            var entity = conversation.entity.text
            var res = Array()
            var bufferArray = Array()
            var tmp
            var arr
            var arr_parts

            if (entity) {
                arr = entity.split("&")

                var buffer = getBuffer(getUsername())
                if (buffer) {
                    bufferArray = JSON.parse(buffer)

                    for ( var key in bufferArray ) {
                        res[res.length] = JSON.stringify(bufferArray[key])
                    }
                }

                for(var i=0;i<arr.length;i++) {
                    var arr_parts = arr[i].split("=")
                    if (!bufferExist(buffer, arr_parts[1])) {
                        if (arr_parts[0] == "file") {
                            res[res.length] = getFile(getUsername(), arr_parts[1]);
                        } else if (arr_parts[0] == "folder") {
                            res[res.length] = getFolder(getUsername(), arr_parts[1]);
                        }
                    }
                }

                var result = "[" + res.join(",") + "]"
                setBuffer(getUsername(), result)

                return result
            }
        } else if (action == "scan") {
            conversation.mediaTypeName = "application/json"

            var data = conversation.entity.text
            return importRemote(getUsername(), data)
        } else if (action == "search") {
            var text = conversation.entity.text

            return search(text)
        } else if (action == "restore") {
            var entity = conversation.entity.text

            var arr = entity.split("&")

            for(var i=0;i<arr.length;i++) {
                var arr_parts = arr[i].split("=")

                if (arr_parts[0] == "file") {
                    restoreFile(getUsername(), arr_parts[1])
                } else if (arr_parts[0] == "folder") {
                    restoreFolder(getUsername(), arr_parts[1])
                }
            }

            return true
        }
    }

    Public.handleGet = function(conversation) {
        var session = null
        var cookie = conversation.getCookie('xvid.session')
        if (null !== cookie) {
            cookie = Sincerity.JSON.from(decodeURIComponent(cookie.value))
            session = Xvid.Authorization.getSessionByKey(cookie.key)
            if (null !== session) {
                if (!session.isAuthorized()) {
                    return
                } else {
                    session_start(session.data.username)
                    setUsername(session.data.username)
                }
            }
        }

        var action = conversation.locals.get('action')
        var buffer = conversation.getCookie("buffer")

        // Set files sort type
        var sort = session_get("sort")
        if (sort == "") {
            session_set("sort", "name")
            var sort = "name"
        }

        //var fsMenu = session_get("fs_menu")
        var current_directory = session_get("current_directory")

        if (action == "fs") {
            var id = conversation.query.get("id")
            if ( (!id) || (id == "") ) {
                return '[{"text": "Upload", "id": "0", "expanded": true, "hasChildren": true, "spriteCssClass": "rootfolder"}]'
            }

            return fs(getUsername(), id)
        } else if (action == "getTypesNum") {
            return getType(getUsername(), conversation.query.get("id"));
        } else if (action == "chdir") {
            var id = conversation.query.get("id")
            if ( (!id) || (id == "") ) {
                id = 0
            }

            // Save current_directory
            session_set("current_directory", id)

            return getFiles(getUsername(), id, sort);
        } else if (action == "upload") {
            return uploadFile(getUsername(), conversation.query.get("file"), conversation.query.get("size"), conversation.query.get("extension"), current_directory);
        } else if (action == "create") {
            return addFolder(getUsername(), conversation.query.get("name"), current_directory);

        } else if (action == "getTrash") {
            return getTrash(getUsername(), sort);
        } else if (action == "fileToTrash") {
            return fileToTrash(getUsername(), conversation.query.get("id"));
        } else if (action == "folderToTrash") {
            return folderToTrash(getUsername(), conversation.query.get("id"))

        } else if (action == "remove") {
            return removeFile(getUsername(), conversation.query.get("id"));
        } else if (action == "rmFolder") {
            return rmFolder(getUsername(), conversation.query.get("id"))
        } else if (action == "removeFileByName") {
            return removeFileByName(getUsername(), conversation.query.get("name"), current_directory);

        } else if (action == "getThumb") {
            var data = getThumb(getUsername(), conversation.query.get("name"))
            return conversation.setResponseBinary(data, 'image/png')
        } else if (action == "buffer") {
            var buffer = getBuffer(getUsername())

            return buffer
        } else if (action == "past") {
            var buffer

            if (buffer = getBuffer(getUsername())) {
                bufferPast(getUsername(), buffer, current_directory)

                setBuffer(getUsername(), "")
            }

            return true
        } else if (action == "deleteFileFromBuffer") {
            var res = Array()
            var buffer = getBuffer(getUsername())
            var bufferArray = JSON.parse(buffer)
            for ( var key in bufferArray ) {
                if (conversation.query.get("id") != bufferArray[key].id) {
                    res[res.length] = JSON.stringify(bufferArray[key])
                }
            }

            var result = "[" + res.join(",") + "]"
            setBuffer(getUsername(), result)

            return result
        } else if (action == "clearBuffer") {
            setBuffer(getUsername(), "");

            return true
        } else if (action == "sort") {
            session_set("sort", conversation.query.get("type"))
            return true
        }
    }

    return Public
}())