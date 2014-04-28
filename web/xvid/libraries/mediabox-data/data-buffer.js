document.executeOnce('/util/collectionNames/')
function getBuffer(uid) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.buffer)

    var query = {uid: uid}
    var data = collection.findOne(query);

    if (data)
        return data.data
    else
        return true
}

function setBuffer(uid, data) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.buffer)

    var query = {uid: uid}
    collection.remove(query);

    var doc = {uid: uid, data: data}
    collection.insert(doc)

    return true
}

function bufferPast(uid, buffer, parent) {
    var bufferArray = JSON.parse(buffer)

    var collection_files = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)
    var collection_folders = Util.db.getAppCollection('fs')

    for ( var key in bufferArray ) {
        if (bufferArray[key].obj == "file") {
            var update = {$set: {parent: parent}}
            collection_files.update({_id: {$oid: bufferArray[key].id}}, update, false, false)
        } else if (bufferArray[key].obj == "folder") {
            var update = {$set: {parent: parent}}
            collection_folders.update({_id: {$oid: bufferArray[key].id}}, update, false, false)
        }
    }

    return true
}