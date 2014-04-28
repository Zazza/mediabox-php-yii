importClass(com.mongodb.Mongo, com.mongodb.jvm.BSON)

document.executeOnce('/sincerity/classes/')
document.executeOnce('/sincerity/templates/')
document.executeOnce('/mediabox-data/data-session/')

var Xvid = Xvid || {}

AppResource = Sincerity.Classes.define(function() {

    var Public = {}

    Public.handleInit = function(conversation) {
        conversation.addMediaTypeByName('text/html')
        conversation.addMediaTypeByName('text/plain')
    }

    Public.handleGet = function(conversation, context) {
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

        if (action == "volume") {
            session_set("volume", conversation.query.get("level"))
            return true
        } else if (action == "types") {
            return session_set("types", "other="+conversation.query.get("other")+"&image="+conversation.query.get("image")+"&video="+conversation.query.get("video")+"&music="+conversation.query.get("music"))
        } else if (action == "view") {
            session_set("view", conversation.query.get("view"))
            return true
        }
    }

    return Public
}())