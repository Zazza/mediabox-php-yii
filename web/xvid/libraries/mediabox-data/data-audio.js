document.executeOnce('/util/collectionNames/')

function createList(uid, name) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.playlists)

    var doc = {uid: uid, name: name}
    collection.insert(doc)

    var query = {uid: uid, _id: {$oid: doc._id}}
    var result = collection.findOne(query)

    return doc._id
}

function showList(uid) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.playlists)

    var query = {uid: uid}
    var result = collection.find(query).toArray()

    var res = new Array()

    for (var i = 0; i < result.length; i++) {
        res[i] = '{"id": "'+result[i]["_id"]+'",  "name": "'+result[i]["name"]+'"}'
    }

    return "[" + res.join(",") + "]";
}

function setTracksToPlaylist(uid, playlist_id, tracks) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.playliststracks)

//    if (!collection.exists) {
//        var query = {uid: uid, playlist_id: playlist_id, tracks: tracks}
//        collection.insert(query)
//    } else {
        var where = {uid: uid, playlist_id: playlist_id}
        var query = { $set: {tracks: tracks}}
        collection.update(where, query, true, false)
//    }

    return true
}

function getTracksFromPlaylist(uid, playlist_id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.playliststracks)

    var query = {uid: uid, playlist_id: playlist_id}

    var result = collection.find(query).toArray()

    if (result.length == 0)
        return false




    var data = result[0].tracks

    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var res = new Array()

    for (var i = 0; i < data.length; i++) {

        var query = {_id: {$oid: data[i]}, uid: uid}
        var file = collection.findOne(query)
        res[i] = '{"id": "'+data[i]+'", "name": "'+file.name+'"}'
    }

    return "[" + res.join(",") + "]";
}

function deletePlaylist(uid, playlist_id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.playlists)

    var query = {uid: uid, _id: {$oid: playlist_id}}

    collection.remove(query)


    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.playliststracks)

    var query = {uid: uid, playlist_id: playlist_id}

    collection.remove(query)

    return true
}