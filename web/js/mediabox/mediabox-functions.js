define(function (require) {
    "use strict";

    require('shifty');
    require('webtoolkit');

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

    var MediaboxFunctions = function() {
        this.openFile = function(file) {
            openFile(file);
        }
        function openFile(file) {
            var type = $(file).attr("data-type");

            var ext = $(file).attr("title");
            ext = ext.substring(ext.lastIndexOf('.')+1);

            if (type == "image") {
                $(file).image("init").image("one").image("loadImg");
            } else if (type == "audio") {
                $(".fs-track-current").removeClass("fs-track-current").removeClass("icon-pause").addClass("icon-play");
                $(".icon-play", file).addClass("fs-track-current");

                $(file).player("load").player("play");
            } else if (type == "video") {
                $(file).video("init");
            } else if (type == "all") {
                //stop the browser from following
                //e.preventDefault();
                window.location.href = config.storage.getFileUri($(file).attr("data-id"));
            }
        };

        this.formatDate = function(date) {
            formatDate(date);
        }
        function formatDate(date) {
            /*
            * JS Backend
            *
            var timestamp = new Date(date);
            var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
            if (timestamp.getMinutes() < 10) {
                var min = '0' + timestamp.getMinutes();
            } else {
                var min = timestamp.getMinutes() + '';
            }
            return timestamp.getHours() + ":" + min + ", " + timestamp.getDate() + "-" + monthNames[timestamp.getMonth()] + "-" + timestamp.getFullYear();
            */
            // PHP Backend
            return date;
        }

        this.formatSize = function(byteSize) {
            formatSize(byteSize);
        }

        function formatSize(byteSize) {
            var size;
            if ((byteSize / 1024 / 1024) > 1) {
                size = (byteSize / 1024 / 1024).toFixed(2) + " Mb";
            } else if ((byteSize / 1024) > 1) {
                size = (byteSize / 1024).toFixed(2) + " Kb";
            } else {
                size = byteSize + " Б";
            };

            return size;
        }

        function shiftyAdd() {
            $('.dfile > div').shifty({
                className: 'f-file-select',
                select: function(el){
                    el.removeClass("fm_unsellabel").addClass("fm_sellabel");
                },
                unselect: function(el){
                    el.removeClass("fm_sellabel").addClass("fm_unsellabel");
                }
            });
        }

        this.addFolderToFS = function(value) {
            addFolderToFS(value);
        }
        function addFolderToFS(value) {
            var viewList = $("#view-selector").data("kendoDropDownList");
            if ($("#mediabox-view").val() == "list") {
                var templateContent = $("#dirListTemplate").html();
            } else if ($("#mediabox-view").val() == "grid") {
                var templateContent = $("#dirGridTemplate").html();
            }
            var template = kendo.template(templateContent);

            var foldername = Url.decode(value["name"]);
            if (foldername.length > 20) {
                var shortname = foldername.substr(0, 10) + ".." + foldername.substr(foldername.length - 2);
            } else {
                var shortname = foldername;
            }

            var data = [
                {
                    id: value["id"],
                    name: foldername,
                    shortname: shortname,
                    date: formatDate(value["date"])
                }
            ];

            var result = kendo.render(template, data);

            $("#fm_folders").append(result);

            shiftyAdd();
        }

        this.removeByStorage = function(value) {
            removeByStorage(value);
        }

        function removeByStorage(id) {
            // To do for nimbus client 
        	$.ajax({ type: "get", url: config.storage.remove, data: "id=" + id, dataType: "JSONP" });
        }

        this.removeFileByName = function(value) {
            removeFileByName(value)
        }
        function removeFileByName(name) {
            $.ajax({ type: "GET", url: '/fm/removeFileByName/', data: "name=" + name })
                .done(function(res) {
                    var id = res;

                    removeByStorage(id);

                    // Remove from FS Structure
                    $(".dfile[data-id='"+id+"']").fadeOut();
                })
        }

        this.getIco = function(type) {
            return getIco(type)
        }
        function getIco(type) {
            //mediatypes /config/ico.js

            return config.mediaTypes[type];
        }

        this.getType = function(needle) {
            return getType(needle)
        }
        function getType(needle) {
            //extension /config/extensions.js
            var extension = config.extension

            var found = false, part, key;
            for (part in extension) {
                for (key in extension[part]) {
                    if ( (extension[part][key] === needle) || (extension[part][key] == needle) ) {
                        found = part;
                        break;
                    }
                }
            }

            return found;
        }

        this.getExtension = function(filename) {
            return getExtension(filename)
        }
        function getExtension(filename) {
            return filename.substr(filename.lastIndexOf(".")+1);
        }

        this.getMimetype = function(extension) {
            return getMimetype(extension)
        }
        function getMimetype(extension, type) {
            //mimetypes /config/mimetypes.js

            if (config.mimetypes[extension]) {
                return config.mimetypes[extension];
            } else {
                return type + "/" + extension;
            }
        }

        this.addFileToFS = function(value) {
            addFileToFS(value)
        }
        function addFileToFS(value) {
            var viewList = $("#view-selector").data("kendoDropDownList");

            var filename = Url.decode(value["name"]);
            if (filename.length > 20) {
                var shortname = filename.substr(0, 10) + ".." + filename.substr(filename.length - 2);
            } else {
                var shortname = filename;
            }
            var extension = getExtension(filename);
            var type = getType(extension);

            if (type == "image") {
                var ico = "fm/getThumb/?name=" +  value["id"];
            } else {
                var ico = getIco(type);
            }

            var mimetype = getMimetype(extension, type);

            if ($("#mediabox-view").val() == "list") {
                if (type == "image") {
                    var templateContent = $("#imageListTemplate").html();
                } else {
                    var templateContent = $("#fileListTemplate").html();
                }
            } else if ($("#mediabox-view").val() == "grid") {
                if (type == "image") {
                    var templateContent = $("#imageGridTemplate").html();
                } else {
                    var templateContent = $("#fileGridTemplate").html();
                }
            }
            var template = kendo.template(templateContent);

            var data = [
                {
                    id:         value["id"],
                    name:       filename,
                    shortname:  shortname,
                    date:       formatDate(value["date"]),
                    size:       formatSize(value["size"]),
                    ico:        ico,
                    ext:        extension,
                    mimetype:   mimetype,
                    type:       type,
                    href:       config.storage.getFileUri(value["id"])
                }
            ];

            var result = kendo.render(template, data);

            if ($(".check-type[name='check-type-music']").is(':checked')) {
                if (value["type"] == "audio") {
                    $("#fm_files").append(result);
                }
            }
            if ($(".check-type[name='check-type-video']").is(':checked')) {
                if (value["type"] == "video") {
                    $("#fm_files").append(result);
                }
            }
            if ($(".check-type[name='check-type-image']").is(':checked')) {
                if (value["type"] == "image") {
                    $("#fm_files").append(result);
                }
            }
            if ($(".check-type[name='check-type-other']").is(':checked')) {
                if ( (value["type"] != "image") && (value["type"] != "video") && (value["type"] != "audio") ) {
                    $("#fm_files").append(result);
                }
            }

            shiftyAdd();
        }

        this.chdir = function(start_id) {
            $("#fm_folders").html("");
            $("#fm_files").html("");

            $(".fm_ajax-loader").show();

            $("#start_dir").val(start_id);

            $.ajax({ type: "GET", url: '/fm/getTypesNum/', dataType: "JSON", data: "id=" + start_id })
                .done(function(res) {
                    $.each(res, function(key, value) {
                        if(key == "other") {
                            $("#typeOther").html(value);
                        }
                        if(key == "image") {
                            $("#typeImage").html(value);
                        }
                        if(key == "video") {
                            $("#typeVideo").html(value);
                        }
                        if(key == "audio") {
                            $("#typeAudio").html(value);
                        }

                        if(key == "path") {
                            $("#current_path").html(value);
                        }
                    });
                })

            var fs;
            $.ajax({ type: "GET", url: '/fm/chdir/', dataType: "JSON", data: "id=" + start_id, cache: false })
                .done(function(res) {
                    var size = 0;
                    $.each(res, function(key, value) {
                        if (value["obj"] == "folder") {
                            addFolderToFS(value);
                        } else if (value["obj"] == "file") {
                            addFileToFS(value);

                            size += parseInt(value["size"]);
                        }
                    })

                    $(".sizeFiles").text(formatSize(size));

                    $(".fm_file_li:odd").addClass("f-list-alt");

                    $(".dfile").kendoDraggable({
                        hint: function(e) {
                            return e.clone();
                            //return $(".dfile").clone();
                        }
                    });

                    // Swipebox bind
                    $(".slideshow").unbind('click');
                    $("#fullscreen").unbind('click');

                    $(".fm_ajax-loader").hide();
                    $(".swipebox").swipebox();

                    shiftyAdd();
                })
        }

        this.trash = function(start_id) {
            $("#fm_folders").html("");
            $("#fm_files").html("");

            $(".fm_ajax-loader").show();

            //$("#start_dir").val(start_id);



            var fs;
            $.ajax({ type: "GET", url: '/fm/getTrash/', dataType: "JSON", data: "id=" + start_id, cache: false })
                .done(function(res) {
                    var size = 0;
                    $.each(res, function(key, value) {
                        if (value["obj"] == "folder") {
                            addFolderToFS(value);
                        } else if (value["obj"] == "file") {
                            addFileToFS(value);

                            size += parseInt(value["size"]);
                        }
                    })

                    $(".sizeFiles").text(formatSize(size));

                    $(".fm_file_li:odd").addClass("f-list-alt");

                    $(".fm_ajax-loader").hide();

                    shiftyAdd();
                })
        }
    }

    return MediaboxFunctions;
});