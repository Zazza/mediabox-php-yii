importClass(com.mongodb.Mongo, com.mongodb.jvm.BSON)

document.executeOnce('/sincerity/classes/')
document.executeOnce('/sincerity/templates/')
document.executeOnce('/mediabox-data/data-image/')

ImageResource = Sincerity.Classes.define(function() {

    var Public = {}

    Public.handleInit = function(conversation) {
        conversation.addMediaTypeByName('text/html')
        conversation.addMediaTypeByName('text/plain')
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

        var selected_tags = conversation.getCookie("selected_tags")
        if (!selected_tags) {
            selected_tags = conversation.createCookie("selected_tags")
        }
        var selected_crops = conversation.getCookie("selected_crops")
        if (!selected_crops) {
            selected_crops = conversation.createCookie("selected_crops")
        }

        if (action == "setCrop") {
            return setCrop(
                getUsername(),
                conversation.query.get("_id"),
                conversation.query.get("desc"),
                conversation.query.get("ws"),
                conversation.query.get("x1"),
                conversation.query.get("x2"),
                conversation.query.get("y1"),
                conversation.query.get("y2")
            );
        } else if (action == "addTag") {
            return addTag(
                getUsername(),
                conversation.query.get("_id"),
                conversation.query.get("tag")
            );
        } else if (action == "getCrops") {
            return getCrops(conversation.query.get("id"));
        } else if (action == "getTags") {
            return getTags(conversation.query.get("id"));
        } else if (action == "addComment") {
            return addComment(
                conversation.query.get("id"),
                conversation.query.get("text")
            );
        } else if (action == "getComments") {
            return getComments(conversation.query.get("id"));
        } else if (action == "getAllTags") {
            var allTags = getAllTags(getUsername(), selected_crops, selected_tags)

            var res = Array()
            var flag

            var selected_tagsArray = JSON.parse(selected_tags.value)
            for ( var tag in allTags ) {
                flag = false
                for ( var key in selected_tagsArray ) {
                    if (allTags[tag] == decodeURIComponent(selected_tagsArray[key].tag)) {
                        flag = true
                    }
                }

                if (flag) {
                    res[res.length] = '{"tag": "<b>'+allTags[tag]+'</b>"}'
                } else {
                    res[res.length] = '{"tag": "'+allTags[tag]+'"}'
                }
            }

            return "[" + res.join(",") + "]"
        } else if (action == "getAllCrops") {
            var allCrops = getAllCrops(getUsername(), selected_crops, selected_tags)

            var res = Array()
            var flag

            var selected_cropsArray = JSON.parse(selected_crops.value)
            for ( var crop in allCrops ) {
                flag = false
                for ( var key in selected_cropsArray ) {
                    if (allCrops[crop] == decodeURIComponent(selected_cropsArray[key].crop)) {
                        flag = true
                    }
                }

                if (flag) {
                    res[res.length] = '{"description": "<b>'+allCrops[crop]+'</b>"}'
                } else {
                    res[res.length] = '{"description": "'+allCrops[crop]+'"}'
                }
            }

            return "[" + res.join(",") + "]"
        } else if (action == "selTag") {
            var res = Array()
            var flag = false

            var selected_tagsArray = JSON.parse(selected_tags.value)
            for ( var key in selected_tagsArray ) {
                if (conversation.query.get("tag") == decodeURIComponent(selected_tagsArray[key].tag))
                    flag = true
                else
                    res[res.length] = '{"tag": "'+selected_tagsArray[key].tag+'"}'
            }

            if (!flag)
                res[res.length] = '{"tag": "'+encodeURIComponent(conversation.query.get("tag"))+'"}'

            selected_tags.value = "[" + res.join(",") + "]"
            selected_tags.maxAge = -1
            selected_tags.path = "/"
            selected_tags.save()

            return selected_tags.value

        } else if (action == "selCrop") {
            var res = Array()
            var flag = false

            var selected_cropsArray = JSON.parse(selected_crops.value)
            for ( var key in selected_cropsArray ) {
                if (conversation.query.get("crop") == decodeURIComponent(selected_cropsArray[key].crop))
                    flag = true
                else
                    res[res.length] = '{"crop": "'+selected_cropsArray[key].crop+'"}'
            }
            if (!flag)
                res[res.length] = '{"crop": "'+encodeURIComponent(conversation.query.get("crop"))+'"}'

            selected_crops.value = "[" + res.join(",") + "]"
            selected_crops.maxAge = -1
            selected_crops.path = "/"
            selected_crops.save()

            return selected_crops.value
        } else if (action == "getFsImg") {
            return getFsImg(getUsername(), selected_crops, selected_tags)
        }
    }

    return Public
}())