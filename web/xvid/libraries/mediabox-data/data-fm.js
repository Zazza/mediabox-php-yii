//importClass(com.mongodb.Mongo, com.mongodb.jvm.BSON)
document.executeOnce('/util/collectionNames/')
function fs(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {uid: uid, parent: id.toString(), trash: {$ne: true}}
    var sort = {name: 1}
    var nodes = collection.find(query).sort(sort).toArray();

    var tree = new Array();

    for (var i = 0; i < nodes.length; i++) {
        tree[i] = '{"text": "' + nodes[i]["name"] + '", "id": "'+nodes[i]["_id"]+'", "hasChildren": '+hasChildren(nodes[i]["_id"])+', "spriteCssClass": "folder"}';
    }

    return "[" + tree.join(",") + "]";
}

function hasChildren(id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {parent: id.toString()}
    var data = collection.find(query).toArray();
    var count = data.length

    if (count == 0) {
        return false
    } else {
        return true
    }
}

function addFolder(uid, name, parent) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var doc = {uid: uid, name: name, parent: parent, trash: false, timestamp: new Date()}
    collection.insert(doc)

    return getFolder(uid, doc._id)
}

function getFolder(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {_id: {$oid: id}, uid: uid}
    var data = collection.findOne(query);

    if (data.name.length > 20) {
        var shortname = data.name.substring(0, 10) + ".." + data.name.substring(data.name.lastIndexOf(".")-1)
    } else {
        var shortname = data.name
    }

    return '{' +
        '"id": "'+data._id+'",' +
        '"name": "'+encodeURIComponent(data.name)+'",' +
        '"shortname": "'+encodeURIComponent(shortname)+'",' +
        '"obj": "folder",' +
        '"date": "' + data.timestamp + '",' +
        '"size": "",' +
        '"ico": "'+application.globals.get('mediaTypes.folder') + '",' +
        '"parent": "'+data.parent+'' +
        '"}';
}

function getFile(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {_id: {$oid: id}, uid: uid}
    var data = collection.findOne(query)

    if (data.name.length > 20) {
        var shortname = data.name.substring(0, 10) + ".." + data.name.substring(data.name.lastIndexOf(".")-1)
    } else {
        var shortname = data.name
    }

    if (application.globals.get('mediaTypes.' + data.type)) {
        var ico = application.globals.get('mediaTypes.' + data.type)
    } else {
        var ico = application.globals.get('mediaTypes.any')
    }

    var extension = data.name.substring(data.name.lastIndexOf(".")+1).toLowerCase()

    if ( (data.type != "image") && (data.type != "audio") && (data.type != "video") ) {
        var type = "all"
    } else {
        var type = data.type
    }

    return '{' +
        '"id": "'+data._id+'",' +
        '"name": "' +encodeURIComponent(data.name)+'",' +
        '"shortname": "'+encodeURIComponent(shortname)+'",' +
        '"obj": "file",' +
        '"type": "'+type+'",' +
        '"size": "'+data.size+'",' +
        '"date": "' + data.timestamp + '",' +
        '"ico": "'+ico + '",' +
        '"src": "fm/getThumb/?name='+data._id+'",' +
        '"ext": "'+extension+'' +
        '"}'
}

function getFiles(uid, id, sort) {
    var files = new Array();

    if (sort == "date") {
        var sort_dir_type = {timestamp: 1}
        var sort_files_type = {timestamp: 1}
    } else if (sort == "name") {
        var sort_dir_type = {name: 1}
        var sort_files_type = {name: 1}
    } else if (sort == "size") {
        var sort_dir_type = {name: 1}
        var sort_files_type = {size: 1}
    }
    

    // Get Dirs
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {uid: uid, parent: id.toString(), trash: {$ne: true}}
    //var sort = {name: 1}
    var nodes = collection.find(query).toArray()

    for (var i = 0; i < nodes.length; i++) {
        shortname = "";

        if (nodes[i]["name"].length > 20) {
            shortname = nodes[i]["name"].substring(0, 10) + ".." + nodes[i]["name"].substring(nodes[i]["name"].length - 3)
        } else {
            shortname = nodes[i]["name"]
        }

        files[files.length] = '{' +
            '"obj": "folder",' +
            '"name": "' + nodes[i]["name"] + '",' +
            '"shortname": "'+shortname+'",' +
            '"id": "'+nodes[i]["_id"]+'",' +
            '"date": "'+nodes[i]["timestamp"]+
            '"}'
    }
    

    // Get Files
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {uid: uid, parent: id.toString(), trash: {$ne: true}}
    //var sort = {name: 1}
    var nodes = collection.find(query).sort(sort_files_type).toArray();
    var ico
    var type
    var shortname
    var ftype

    for (var i = 0; i < nodes.length; i++) {
        shortname = "";

        if (application.globals.get('mediaTypes.' + nodes[i]["type"])) {
            ico = application.globals.get('mediaTypes.' + nodes[i]["type"])
        } else {
            ico = application.globals.get('mediaTypes.any')
        }

        if (nodes[i]["name"].length > 20) {
            shortname = nodes[i]["name"].substring(0, 10) + ".." + nodes[i]["name"].substring(nodes[i]["name"].lastIndexOf(".")-1)
        } else {
            shortname = nodes[i]["name"]
        }

        var extension = nodes[i]["name"].substring(nodes[i]["name"].lastIndexOf(".")+1).toLowerCase()

        files[files.length] = '{' +
                '"id": "'+nodes[i]["_id"]+'",' +
                '"name": "' + nodes[i]["name"] + '",' +
                '"shortname": "'+shortname+'",' +
                '"obj": "file",' +
                '"type": "'+nodes[i]["type"]+'",' +
                '"size": "'+nodes[i]["size"]+'",' +
                '"date": "' + nodes[i]["timestamp"] + '",' +
                '"ico": "'+ico+'",' +
                '"src": "'+ico+'",' +
                '"ext": "'+extension+'"}';
    }

    if (files.length > 0) {
        return "[" + files.join(",") + "]";
    } else {
        return "[]";
    }
}

function getTrash(uid, sort) {
    var files = new Array();

    if (sort == "date") {
        var sort_dir_type = {timestamp: 1}
        var sort_files_type = {timestamp: 1}
    } else if (sort == "name") {
        var sort_dir_type = {name: 1}
        var sort_files_type = {name: 1}
    } else if (sort == "size") {
        var sort_dir_type = {name: 1}
        var sort_files_type = {size: 1}
    }

    // Get Dirs
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {uid: uid, trash: true}
    //var sort = {name: 1}
    var nodes = collection.find(query).sort(sort_dir_type).toArray();

    for (var i = 0; i < nodes.length; i++) {
        shortname = "";

        if (nodes[i]["name"].length > 20) {
            shortname = nodes[i]["name"].substring(0, 10) + ".." + nodes[i]["name"].substring(nodes[i]["name"].length - 3)
        } else {
            shortname = nodes[i]["name"]
        }

        files[files.length] = '{' +
            '"obj": "folder",' +
            '"name": "' + nodes[i]["name"] + '",' +
            '"shortname": "'+shortname+'",' +
            '"id": "'+nodes[i]["_id"]+'",' +
            '"date": "'+nodes[i]["timestamp"]+
            '"}'
    }

    // Get Files
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {uid: uid, trash: true}
    //var sort = {name: 1}
    var nodes = collection.find(query).sort(sort_files_type).toArray();

    var ico
    var type
    var shortname
    var ftype

    for (var i = 0; i < nodes.length; i++) {
        shortname = "";

        if (application.globals.get('mediaTypes.' + nodes[i]["type"])) {
            ico = application.globals.get('mediaTypes.' + nodes[i]["type"])
        } else {
            ico = application.globals.get('mediaTypes.any')
        }

        if (nodes[i]["name"].length > 20) {
            shortname = nodes[i]["name"].substring(0, 10) + ".." + nodes[i]["name"].substring(nodes[i]["name"].lastIndexOf(".")-1)
        } else {
            shortname = nodes[i]["name"]
        }

        var extension = nodes[i]["name"].substring(nodes[i]["name"].lastIndexOf(".")+1).toLowerCase()

        files[files.length] = '{' +
            '"id": "'+nodes[i]["_id"]+'",' +
            '"name": "' + nodes[i]["name"] + '",' +
            '"shortname": "'+shortname+'",' +
            '"obj": "file",' +
            '"type": "'+nodes[i]["type"]+'",' +
            '"size": "'+nodes[i]["size"]+'",' +
            '"date": "' + nodes[i]["timestamp"] + '",' +
            '"ico": "'+ico+'",' +
            '"src": "'+ico+'",' +
            '"ext": "'+extension+'"}';
    }

    if (files.length > 0) {
        return "[" + files.join(",") + "]";
    } else {
        return "[]";
    }
}

function in_array(needle, haystack, strict) {
    var found = false, key, strict = !!strict;

    for (key in haystack) {
        if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
            found = true;
            break;
        }
    }

    return found;
}

function get_type(extension) {
    var config = application.globals.get("extension")
    for (var key in config) {
        if (in_array(extension, config[key])) {
            return config[key][0]
        }
    }

    return 'unknown'
}

function uploadFile(uid, filename, size, extension, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var type = get_type(extension.toLowerCase())
    
    var doc = {uid: uid, parent: id, name: filename, trash: false, size: size, type: type, timestamp: new Date()}
    collection.insert(doc)

    // return json
    if (application.globals.get('mediaTypes.' + type)) {
        ico = application.globals.get('mediaTypes.' + type)
    } else {
        ico = application.globals.get('mediaTypes.any')
    }

    filename = decodeURIComponent(filename)

    if (filename.length > 20) {
        shortname = filename.substring(0, 10) + ".." + filename.substring(filename.lastIndexOf(".")-1)
    } else {
        shortname = filename
    }

    if (type == "image") {
        return '{"id": "'+doc._id+'", "name": "' + filename + '", "shortname": "'+shortname+'", "obj": "file", "type": "'+type+'", "size": "'+size+'", "date": "' + new Date() + '", "ico": "'+ico+'", "src": "fm/getThumb/?name='+doc._id+'", "ext": "'+extension.toLowerCase()+'"}';
    } else {
        return '{"id": "'+doc._id+'", "name": "' + filename + '", "shortname": "'+shortname+'", "obj": "file", "type": "'+type+'", "size": "'+size+'", "date": "' + new Date() + '", "ico": "'+ico+'", "src": "'+ico+'", "ext": "'+extension.toLowerCase()+'"}';
    }
}

function uploadThumb(id, data) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var update = {$push: {data: { $binary: data }}}

    collection.update({_id: {$oid: id}}, update, false, false)

    return true;
}

 

function getThumb(uid, filename) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)
    var query = {uid: uid, _id: {$oid: filename}}
    var file = collection.findOne(query);

    return file.data[0]
}

function getType(uid, id) {
    var type = new Array()
    type["path"] = ""

    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var tmp_id = id.toString()

    while(tmp_id != 0) {
        var query = {uid: uid, _id: {$oid: tmp_id}}
        var tmp = collection.findOne(query)

        if(tmp) {

            tmp_id = tmp.parent.toString()
            type["path"] = " > " + "<a href='#' class='one_folder' data-id="+tmp._id+">"+tmp.name+"</a>" + type["path"]
        } else {
            tmp_id = 0
        }
    }

    type["path"] = "<nobr><a href='#' class='one_folder' data-id='0'>Upload</a>" + type["path"] + "</nobr>"


    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {uid: uid, parent: id.toString(), trash: {$ne: true}}
    var files = collection.find(query).toArray();

    type["all"] = 0
    type["image"] = 0
    type["video"] = 0
    type["audio"] = 0
    type["other"] = 0

    for (var i = 0; i < files.length; i++) {
        if (typeof(files[i]["type"]) == "string") {
            if (files[i]["type"] == "image") {
                type["image"]++
            } else if (files[i]["type"] == "video") {
                type["video"]++
            } else if (files[i]["type"] == "audio") {
                type["audio"]++
            } else {
                type["other"]++
            }

            type["all"]++;
        }
    }

    return '{"all": "'+type["all"]+'", "image": "'+type["image"]+'", "video": "'+type["video"]+'", "audio": "'+type["audio"]+'", "other": "'+type["other"]+'", "path": "'+type["path"]+'"}';
}

// Trash
function fileToTrash(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var where = {uid: uid, _id: {$oid: id}}
    //collection.remove(query);

    var update = {$set: {trash: true}}

    collection.update(where, update, false, false)

    return true
}

function folderToTrash(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var where = {uid: uid, _id: {$oid: id}}
    //collection.remove(query);

    var update = {$set: {trash: true}}

    collection.update(where, update, false, false)

    return true
}

function restoreFile(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var where = {uid: uid, _id: {$oid: id}}
    //collection.remove(query);

    var update = {$set: {trash: false}}

    collection.update(where, update, false, false)

    return true
}

function restoreFolder(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var where = {uid: uid, _id: {$oid: id}}
    //collection.remove(query);

    var update = {$set: {trash: false}}

    collection.update(where, update, false, false)

    return true
}
// END Trash


function removeFile(uid, id) {
    var collection =  Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {uid: uid, _id: {$oid: id}}

    collection.remove(query);

    return true
}

function rmFolder(uid, id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {uid: uid, _id: {$oid: id}}
    collection.remove(query);

    return true
}

function removeFileByName(uid, filename, parent_id) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    var query = {uid: uid, parent: parent_id.toString(), name: filename}

    var res = collection.findOne(query)
    collection.remove(query);

    return res._id
}

function bufferExist(buffer, id) {
    if (buffer) {
        var bufferArray = JSON.parse(buffer)
        for ( var key in bufferArray ) {
            if  (bufferArray[key].id == id) {
                return true
            }
        }
    }

    return false
}



function getFolderId_fullname(uid, fullname) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

    var query = {fullname: fullname, uid: uid}
    var data = collection.findOne(query);

    return data._id;
}

function importRemote(uid, data) {
    var res = Array()
    var data = JSON.parse(data)
    var tmp

    for (var i=0; i<data.length; i++) {

        if (data[i]["obj"] == "file") {
            if (data[i]["level"] == 0) {
                var parent = "0"
            } else {
                var parent = getFolderId_fullname(uid, data[i]["parent"]).toString()
            }

            var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)
            var type = get_type(data[i]["extension"].toLowerCase())
            var doc = {uid: uid, parent: parent, name: data[i]["name"], size: data[i]["size"], type: type, timestamp: new Date()}
            collection.insert(doc)

            //tmp = uploadFile(uid, data[i]["name"], data[i]["size"], data[i]["extension"], parent)
            if (data[i]["thumb"]) {
                uploadThumb(doc._id, data[i]["thumb"])
            }

            res[res.length] = '{"id": "' + doc._id + '", "obj": "file", "fullname": "' + data[i]["parent"] + "/" + data[i]["name"] + '"}'

        } else if (data[i]["obj"] == "folder") {
            if (data[i]["level"] == 0) {
                var parent = "0"
            } else {
                var parent = getFolderId_fullname(uid, data[i]["parent"]).toString()
            }
            //addFolder(uid, data[i]["name"], parent)
            var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

            var doc = {uid: uid, name: data[i]["name"], fullname: data[i]["parent"] + "/" + data[i]["name"], parent: parent, timestamp: new Date()}
            collection.insert(doc)

            //res[res.length] = '{id:' + doc._id + ', obj: "folder", fullname: ' + data[i]["parent"] + "/" + data[i]["name"] + '}'
        }
    }

    return "[" + res.join(",") + "]";
}

function search(text) {
    var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

    if (type == "all") {
        var query = {uid: uid, parent: id.toString()}
    } else {
        var query = {uid: uid, parent: id.toString(), type: type}
    }
    var sort = {name: 1}
    var nodes = collection.find(query).sort(sort).toArray();
}
