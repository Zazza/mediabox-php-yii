document.executeOnce('/util/collectionNames/')
function setCrop(uid, _id, description, ws, x1, x2, y1, y2) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.crop)

    var doc = {uid: uid, image_id: _id, description: description, ws: ws, x1: x1, x2: x2, y1: y1, y2: y2}
    collection.insert(doc)

    return true
}

function getCrops(id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.crop)

    var query = {image_id: id}
    var crops = collection.find(query).toArray();

    var res = new Array()

    for (var i = 0; i < crops.length; i++) {
        res[i] = '{"x1": "'+crops[i]["x1"]+'", "x2": "'+crops[i]["x2"]+'", "y1": "'+crops[i]["y1"]+'", "y2": "'+crops[i]["y2"]+'", "ws": "'+crops[i]["ws"]+'", "description": "'+crops[i]["description"]+'"}'
    }

    return "[" + res.join(",") + "]";
}

function addTag(uid, _id, tag) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagetags)

    var doc = {uid: uid, image_id: _id, tag: tag}
    collection.insert(doc)

    return true
}

function getTags(id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagetags)

    var query = {image_id: id.toString()}
    var tags = collection.find(query).toArray();

    var res = new Array()

    for (var i = 0; i < tags.length; i++) {
        res[i] = '{"tag": "'+tags[i]["tag"]+'"}'
    }

    return "[" + res.join(",") + "]";
}

function addComment(_id, text) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagecomments)

    var timestamp = new Date();

    var doc = {image_id: _id, timestamp: timestamp, text: decodeURIComponent(text)}
    collection.insert(doc)

    return true
}

function getComments(id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagecomments)

    var query = {image_id: id.toString()}
    var comments = collection.find(query).toArray();

    var res = new Array()

    for (var i = 0; i < comments.length; i++) {
        res[i] = '{"text": "'+encodeURIComponent(comments[i]["text"])+'", "timestamp": "'+comments[i]["timestamp"]+'"}'
    }

    return "[" + res.join(",") + "]";
}


/**
 * Helper
 * @param value
 * @param array
 * @returns {boolean}
 */
function in_array(value, array)
{
    for(var i = 0; i < array.length; i++)
    {
        if(array[i] == value) return true;
    }
    return false;
}

function _setCrops(selected_crops) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.crop)

    var ids = new Array()
    var selected_cropsArray = JSON.parse(selected_crops.value)

    for ( var key in selected_cropsArray ) {
        if (ids.length > 0)
            var query = {"description": decodeURIComponent(selected_cropsArray[key].crop), image_id: {$in: ids}}
        else
            var query = {"description": decodeURIComponent(selected_cropsArray[key].crop)}

        var mongo = collection.find(query).toArray();

        for (var i = 0; i < mongo.length; i++) {
            ids[ids.length] = mongo[i]["image_id"]
        }
    }

    return ids
}

function _setTags(selected_tags) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagetags)

    var ids = new Array()
    var selected_tagsArray = JSON.parse(selected_tags.value)

    for ( var key in selected_tagsArray ) {
        if (ids.length > 0)
            var query = {tag: decodeURIComponent(selected_tagsArray[key].tag), image_id: {$in: ids}}
        else
            var query = {tag: decodeURIComponent(selected_tagsArray[key].tag)}

        var mongo = collection.find(query).toArray();

        for (var i = 0; i < mongo.length; i++) {
            ids[ids.length] = mongo[i]["image_id"]
        }
    }

    return ids
}

function _setTagsAndCrops(selected_tags, selected_crops) {
    var crops_and_tags = new Array()
    var selTags = _setTags(selected_tags)
    var selCrops =  _setCrops(selected_crops)
    if (selTags.length > 0) {
        for(key in selTags) {
            if (selCrops.length > 0) {
                if (in_array(selTags[key], selCrops)) {
                    crops_and_tags[crops_and_tags.length] = selTags[key];
                }
            } else {
                crops_and_tags = selTags;
            }
        }
    } else {
        if (selCrops.length > 0) {
            crops_and_tags = selCrops;
        }
    }

    return crops_and_tags
}

function getAllCrops(uid, selected_crops, selected_tags) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.crop)

    var res = new Array()

    var crops_and_tags = _setTagsAndCrops(selected_tags, selected_crops)

    if (crops_and_tags.length > 0) {
        var query = {uid: uid, image_id: {$in: crops_and_tags}}
        var crops = collection.find(query).toArray();
    } else {
        var query = {uid: uid}
        var crops = collection.find(query).toArray();
    }

    for (var i = 0; i < crops.length; i++) {
        if (!in_array(crops[i]["description"], res)) {
            if (crops[i]["description"])
                res[res.length] = crops[i]["description"]
        }
    }

    return res
}

function getAllTags(uid, selected_crops, selected_tags) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagetags)

    var res = new Array()

    var crops_and_tags = _setTagsAndCrops(selected_tags, selected_crops)

    if (crops_and_tags.length > 0) {
        var query = {uid: uid, image_id: {$in: crops_and_tags}}
        var mongo_tags_res = collection.find(query).toArray()
    } else {
        var query = {uid: uid}
        var mongo_tags_res = collection.find(query).toArray()
    }

    for (var i = 0; i < mongo_tags_res.length; i++) {
        if (!in_array(mongo_tags_res[i]["tag"], res)) {
            if (mongo_tags_res[i]["tag"] != "")
                res[res.length] = mongo_tags_res[i]["tag"]
        }
    }

    return res
}

function getFsImg(uid, selected_crops, selected_tags) {
    var crops_and_tags = _setTagsAndCrops(selected_tags, selected_crops)

    // get files
    var files = new Array()

    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {uid: uid}
    var nodes = collection.find(query).toArray();

    var ico
    var type
    var shortname
    var flag
    var e = new Array()
    for (var i = 0; i < nodes.length; i++) {
        flag = true
        shortname = "";

        if (crops_and_tags.length > 0)
            if (!in_array(nodes[i]["_id"].toString(), crops_and_tags))
                flag = false

        if (flag) {
            if (nodes[i]["type"] == "image") {
                ico = application.globals.get('mediaTypes.image')

                if (nodes[i]["name"].length > 20) {
                    shortname = nodes[i]["name"].substring(0, 10) + ".." + nodes[i]["name"].substring(nodes[i]["name"].lastIndexOf(".")-1)
                } else {
                    shortname = nodes[i]["name"]
                }

                var extension = nodes[i]["name"].substring(nodes[i]["name"].lastIndexOf(".")+1)

                files[files.length] = '{"id": "'+nodes[i]["_id"]+'", "name": "' + nodes[i]["name"] + '", "shortname": "'+shortname+'", "obj": "file", "type": "'+nodes[i]["type"]+'", "size": "'+nodes[i]["size"]+'", "type": "'+nodes[i]["type"]+'", "ico": "'+ico+'", "data": "'+ico+'", "ext": "'+extension+'"}';
            }
        }
    }

    if ( (files.length > 0) && (crops_and_tags.length > 0) ) {
        return "[" + files.join(",") + "]";
    }
}