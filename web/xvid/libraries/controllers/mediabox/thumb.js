importClass(com.mongodb.Mongo, com.mongodb.jvm.BSON)

document.executeOnce('/sincerity/classes/')
document.executeOnce('/sincerity/templates/')
document.executeOnce('/mediabox-data/data-fm/')

ThumbResource = Sincerity.Classes.define(function() {

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

        var data = conversation.entity.text
        return uploadThumb(conversation.locals.get('_id'), data)
    }

    return Public
}())