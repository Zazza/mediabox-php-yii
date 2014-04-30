<?php

class ImageController extends Controller
{
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('setCrop', 'addTag', 'getCrops', 'getTags', 'addComment', 'getComments', 'getAllTags', 'getAllCrops', 'selTag', 'selCrop', 'getFsImg'),
                //'roles'=>array('admin'),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionSetCrop() {
        $crop = new ImagesCrops();
        $crop->user_id = Yii::app()->user->id;
        $crop->file_id = $_GET["_id"];
        $crop->description = $_GET["desc"];
        $crop->ws = $_GET["ws"];
        $crop->x1 = $_GET["x1"];
        $crop->x2 = $_GET["x2"];
        $crop->y1 = $_GET["y1"];
        $crop->y2 = $_GET["y2"];
        if ($crop->validate()) {
            $crop->save(false);
        } else {
            print_r($crop->getErrors());
        }
    }

    public function actionAddTag() {
        $tag = new ImagesTags();
        $tag->user_id = Yii::app()->user->id;
        $tag->file_id = $_GET["_id"];
        $tag->tag = $_GET["tag"];
        if ($tag->validate()) {
            $tag->save(false);
        } else {
            print_r($tag->getErrors());
        }
    }

    public function actionGetCrops() {
        $result = array();

        $crops = ImagesCrops::model()->findAll("file_id = :file_id", array(":file_id"=>$_GET["id"]));
        foreach($crops as $crop) {
            $array = array();

            $array["x1"] = $crop->x1;
            $array["x2"] = $crop->x2;
            $array["y1"] = $crop->y1;
            $array["y2"] = $crop->y2;
            $array["ws"] = $crop->ws;
            $array["description"] = $crop->description;

            $result[] = $array;
        }

        echo json_encode($result);
    }

    public function actionGetTags() {
        $result = array();

        $tags = ImagesTags::model()->findAll("file_id = :file_id", array(":file_id"=>$_GET["id"]));
        foreach($tags as $tag) {
            $result[]["tag"] = $tag->tag;
        }

        echo json_encode($result);
    }

    public function actionAddComment() {
        $comment = new ImagesComments();

        $comment->user_id = Yii::app()->user->id;
        $comment->file_id = $_GET["id"];
        $comment->comment = urldecode($_GET["text"]);

        if ($comment->validate()) {
            $comment->save(false);
        } else {
            print_r($comment->getErrors());
        }
    }

    public function actionGetComments() {
        $result = array();

        $comments = ImagesComments::model()->findAll("file_id = :file_id", array(":file_id"=>$_GET["id"]));
        foreach($comments as $comment) {
            $array = array();

            $array["text"] = $comment->comment;
            $array["timestamp"] = $comment->timestamp;

            $result[] = $array;
        }

        echo json_encode($result);
    }

    public function actionGetAllTags() {
        /*
         * var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.imagetags)

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
         */
    }

    public function actionGetAllCrops() {
        /*
         *     var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.crop)

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
         */
    }

    public function actionSelTag() {
        /*
         *             var res = Array()
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
         */
    }

    public function actionSelCrop() {
        /*
         *             var res = Array()
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
         */
    }

    public function actionGetFsImg() {
        /*
         *     var crops_and_tags = _setTagsAndCrops(selected_tags, selected_crops)

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
         */
    }

    /*
     * function _setCrops(selected_crops) {
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
     */
}