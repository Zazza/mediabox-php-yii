//importClass(com.mongodb.Mongo, com.mongodb.jvm.BSON)

document.executeOnce('/sincerity/classes/')
document.executeOnce('/sincerity/templates/')
document.executeOnce('/mediabox-data/data-session/')
document.executeOnce('/mediabox-data/data-audio/')

var Xvid = Xvid || {}

AudioResource = Sincerity.Classes.define(function() {

    var Public = {}

    Public.handleInit = function(conversation) {
        conversation.addMediaTypeByName('text/html')
        conversation.addMediaTypeByName('text/plain')
    }

    Public.handlePost = function(conversation, context) {
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

        if (action == "save-list") {
            if (entity = conversation.entity.text)
                var tracks = entity.split("&")
            else
                var tracks = ""

            return setTracksToPlaylist(getUsername(), session_get("playlist"), tracks)
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

        if (action == "create-list") {
            var name = conversation.query.get("name")

            session_set("playlist", createList(getUsername(), name))

            return true
        } else if (action == "show-list") {
            return showList(getUsername())
        } else if (action == "get-tracks-list") {
            return getTracksFromPlaylist(getUsername(), session_get("playlist"))
        } else if (action == "set-playlist") {
            var playlist_id = conversation.query.get("playlist-id")
            session_set("playlist", playlist_id)

            return true
        } else if (action == "delete-playlist") {
            var playlist_id = conversation.query.get("playlist-id")
            return deletePlaylist(getUsername(), playlist_id)
        }
    }

    return Public
}())