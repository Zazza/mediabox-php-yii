define(function (require) {
    "use strict";

    var $ = require('jquery');

    var bootstrap = require('bootstrap');

    var mxVar = require('/js/mediabox/mediabox-var.js');

    var swipebox = require('swipebox');
    var mediaboxUploader = require('mediaboxUploader');
    var mxFunctions = require('/js/mediabox/mediabox-functions.js');

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

    var MediaboxFunctions = new mxFunctions();
    require('kendo/kendo.slider.min');
    require('kendo/kendo.treeview.min');
    require('mediaboxImage');
    require('Jcrop');
    require('mediaboxPlayer');
    require('mediaboxVideo');
    require('mediaelement');


    $(document).ready(function() {
        //MediaboxFunctions.trash($("#start_dir").val());

        /**
         * Context Menu
         */
            // file
        $(".structure").on("contextmenu", ".dfile_trash", function(e) {
            $("#folder-trash-context-menu").hide();

            $("#file-trash-context-menu").attr("data-id", $(this).attr("data-id"));
            $("#file-trash-context-menu").attr("data-name", $(this).attr("data-name"));
            $("#file-trash-context-menu").attr("data-path", $(this).attr("data-path"));

            $("#file-trash-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#file-trash-context-menu").on("click", "a", function() {
            var file_id = $("#file-trash-context-menu").attr("data-id");
            var file_name = $("#file-trash-context-menu").attr("data-name");
            var file_path = $("#file-trash-context-menu").attr("data-path");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'restore': {
                    restore("file[]=" + file_id)
                    break
                };
                case 'remove': {
                    removeFile(file_id, file_name, file_path)
                    break
                }
            };
        });
        //folder
        $(".structure").on("contextmenu", ".ddir_trash", function(e) {
            $("#file-trash-context-menu").hide();

            $("#folder-trash-context-menu").attr("data-id", $(this).attr("data-id"));
            $("#folder-trash-context-menu").attr("data-name", $(this).attr("data-name"));
            $("#folder-trash-context-menu").attr("data-path", $(this).attr("data-path"));

            $("#folder-trash-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#folder-trash-context-menu").on("click", "a", function() {
            var dir_id = $("#folder-trash-context-menu").attr("data-id");
            var dir_name = $("#folder-trash-context-menu").attr("data-name");
            var dir_path = $("#folder-trash-context-menu").attr("data-path");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'restore': {
                    restore("folder[]=" + dir_id)
                    break
                };
                case 'remove': {
                    removeDir(dir_id, dir_name, dir_path)
                    break
                }
            };
        });
        // Close context menus
        $("body").on("click", "", function() {
            $("#file-trash-context-menu").hide();
            $("#folder-trash-context-menu").hide();
        });

        // Full Remove
        $("body").on("click", "#trash-remove-all", function(){
            win_delete();
        });

        function win_delete() {
            $("#rm-window").kendoWindow({
                width: "300px",
                height: "120px",
                modal: true,
                title: "Delete"
            });

            $("#rm-window").data("kendoWindow").center().open();
        }

        $("#rm-window-no").click(function(){
            $("#rm-window").data("kendoWindow").close();
        });

        $("#rm-window-yes").click(function(){
            $(".ddir_trash > div").each(function() {
                var dir_id = $(this).parent().attr("data-id");
                var dir_name = $(this).parent().attr("data-name");
                var dir_path = $(this).parent().attr("data-path");

                removeDir(dir_id, dir_name, dir_path);
            });

            $(".dfile_trash > div").each(function() {
                var file_id = $(this).parent().attr("data-id");
                var file_name = $(this).parent().attr("data-name");
                var file_path = $(this).parent().attr("data-path");

                removeFile(file_id, file_name, file_path);
            });


            $("#rm-window").data("kendoWindow").close();
        });

        function removeFile(id, fname, path) {
            var file_id = id;

            //Storage removal file
            var error = config.storage.removeFile(path, fname, function(response){
                if (response.result) {
                    $.ajax({ type: "GET", url: '/fm/remove/', data: "id=" + id })
                        .done(function(res) {
                            MediaboxFunctions.removeByStorage(id);

                            // Remove from FS Structure
                            $(".dfile_trash[data-id='"+file_id+"']").fadeOut();
                        })
                }
            });
        }

        function removeDir(id, fname, path) {
            //Storage removal folder
            var error = config.storage.removeFolder(path, fname, function(response){
                if (response.result) {
                    $.ajax({ type: "GET", url: "/fm/rmFolder/", data: "id=" + id })
                        .done(function(res) {
                            // Remove folders from fs structure
                            $(".ddir_trash[data-id='"+id+"']").fadeOut();
                        });
                }
            });
        }
        // END Full Remove

        $("body").on("click", "#trash-restore-all", function(){
            var data;

            $(".ddir_trash > div").each(function() {
                //if ($(this).hasClass("fm_sellabel")) {
                    data += "&folder[]=" + $(this).parent().attr("data-id");
                //}
            });

            $(".dfile_trash > div").each(function() {
                //if ($(this).hasClass("fm_sellabel")) {
                    data += "&file[]=" + $(this).parent().attr("data-id");
                //}
            });

            restore(data);
        });
    })

    function restore(data) {
        $.ajax({ type: "POST", url: '/fm/restore/', data: data })
            .done(function(res) {
                var arr = data.split("&")

                for(var i=0;i<arr.length;i++) {
                    var arr_parts = arr[i].split("=")

                    if (arr_parts[0] == "file[]") {
                        $(".dfile_trash[data-id='"+arr_parts[1]+"']").fadeOut();
                    } else if (arr_parts[0] == "folder[]") {
                        $(".ddir_trash[data-id='"+arr_parts[1]+"']").fadeOut();
                    }
                }
            })
    }
});
