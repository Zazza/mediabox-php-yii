define(function (require) {
    "use strict";

    var $ = require('jquery');
    require('shifty');

    var MediaboxFunctions = function() {
    	this.getFileUri = function(id) {
    		return getFileUri(id)
        }
        function getFileUri(id) {
            var uri = $("#storage").val();
            var is_nimbus_client = $("#is_nimbus_client").val();
            if (is_nimbus_client == "True") {
            	var session = JSON.parse($.cookie('xvid.session'));
            	var token = session ? encodeURIComponent(session.key) : '';
                var mbclientUrlData = "?access_token=" + token  + "&master_key=" + window.name;
                uri += "/files/" + id + mbclientUrlData;
            }
            else {
                uri += '/get/?id=' + id;
            }
            return uri;
        };


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
                window.location.href = getFileUri($(file).attr("data-id"));
                //window.location.href = $("#storage").val()+"/get/?id=" + $(file).attr("data-id");
            }
        };

        this.formatDate = function(date) {
            formatDate(date);
        }
        function formatDate(date) {
            var timestamp = new Date(date);
            var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
            if (timestamp.getMinutes() < 10) {
                var min = '0' + timestamp.getMinutes();
            } else {
                var min = timestamp.getMinutes() + '';
            }
            return timestamp.getHours() + ":" + min + ", " + timestamp.getDate() + "-" + monthNames[timestamp.getMonth()] + "-" + timestamp.getFullYear();
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
            $('.fm_file > div').shifty({
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

            var data = [
                {
                    name: decodeURI(value["name"]),
                    shortname: decodeURI(value["shortname"]),
                    id: value["id"],
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
        	$.ajax({ type: "get", url: $("#storage").val() + '/remove/', data: "id=" + id, dataType: "JSONP" });
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

        this.addFileToFS = function(value) {
            addFileToFS(value)
        }
        function addFileToFS(value) {
            var viewList = $("#view-selector").data("kendoDropDownList");
            if ($("#mediabox-view").val() == "list") {
                var templateContent = $("#fileListTemplate").html();
            } else if ($("#mediabox-view").val() == "grid") {
                if (value["type"] == "image") {
                    var templateContent = $("#imageGridTemplate").html();
                } else {
                    var templateContent = $("#fileGridTemplate").html();
                }
            }
            var template = kendo.template(templateContent);

            var data = [
                {
                    id:         value["id"],
                    name:       value["name"],
                    shortname:  value["shortname"],
                    date:       formatDate(value["date"]),
                    size:       formatSize(value["size"]),
                    ico:        value["ico"],
                    ext:        value["ext"],
                    type:       value["type"],
                    href:       getFileUri(value["id"]) //$("#storage").val()+"/get/?id=" + value["id"]
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
            $("#fm_images").html("");
            $("#fm_video").html("");
            $("#fm_audio").html("");

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

                    $(".fm_ajax-loader").hide();
                    $(".swipebox").swipebox();

                    shiftyAdd();
                })
        }

        this.trash = function(start_id) {
            $("#fm_folders").html("");
            $("#fm_files").html("");
            $("#fm_images").html("");
            $("#fm_video").html("");
            $("#fm_audio").html("");

            $(".fm_ajax-loader").show();

            $("#start_dir").val(start_id);

            $.ajax({ type: "GET", url: '/fm/getTypesNum/', dataType: "JSON", data: "id=" + start_id })
                .done(function(res) {
                    $.each(res, function(key, value) {
                        if(key == "all") {
                            $("#typeAll").html(value);
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
                            $("#current_path").html("<b>trash</b>");
                        }
                    });
                })

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
                    $(".dfile").kendoDraggable({
                        hint: function(e) {
                            return e.clone();
                            //return $(".dfile").clone();
                        }
                    });

                    $(".fm_ajax-loader").hide();
                    $(".swipebox").swipebox();

                    shiftyAdd();
                })
        }
    }

    return MediaboxFunctions;
});