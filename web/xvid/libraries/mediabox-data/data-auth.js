
document.executeOnce('/sincerity/cryptography/')
document.executeOnce('/util/collectionNames/')

//var connection = new Mongo()
//var db = connection.getDB('mediabox')

function registration(email, password) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.users)

    var query = {email: email}
    var res = collection.findOne(query);

    if (!res) {
        var doc = {email: email, password: org.apache.commons.codec.digest.DigestUtils.md5Hex(password)}
        collection.insert(doc)

        return true
    } else {
        return false
    }
}

function checkLogin(email, password) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.users)

    var query = {email: email, password: org.apache.commons.codec.digest.DigestUtils.md5Hex(password)}
    var res = collection.findOne(query);

    if (res) {
        return res._id
    }
}
