document.executeOnce('/xvid/util/db/')
document.executeOnce('/sincerity/cryptography/')
document.executeOnce('/util/collectionNames/')

/**
 * UID after session_check
 */
var _uid
var _username
var _token

function uid_get() {
    return _uid
}

function setUsername(username) {
    _username = username
}
function getUsername() {
    return _username
}

function setToken(token) {
    _token = token
}

function session_start(username) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.session)

    var query = {username: username}
    var res = collection.findOne(query)

    if (res) {
        return false
    }
    var doc = {username: username}
    collection.insert(doc)
    return true
}

function session_get(key) {

    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.session)
    var query = {username: _username}
    println(Sincerity.JSON.to(query))
 	var res = collection.findOne(query)
    if (res)
        if (typeof(res) == 'object') {
            if (key in res)
                return res[key]
        }
    
    return false
}

function session_set(key, value) {
    var data = {}
    data[key] = value

    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.session)

    var update = {$set: data}
    collection.update({username: _username}, update, false, false)

    return true
}
