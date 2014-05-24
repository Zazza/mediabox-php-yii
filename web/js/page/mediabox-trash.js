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
    var imageFs = require('/js/mediabox/mediabox-image-fs.js');
    var MediaboxImageFs = new imageFs();
    require('kendo/kendo.slider.min');
    require('kendo/kendo.treeview.min');
    require('mediaboxImage');
    require('Jcrop');
    require('mediaboxPlayer');
    require('mediaboxVideo');
    require('mediaelement');


    $(document).ready(function() {
        MediaboxFunctions.trash($("#start_dir").val());

        /**
         * Context Menu
         */
            // file
        $(".structure").on("contextmenu", ".dfile", function(e) {
            $("#folder-trash-context-menu").hide();

            $("#file-trash-context-menu").attr("data-id", $(this).attr("data-id"));

            $("#file-trash-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#file-trash-context-menu").on("click", "a", function() {
            var file_id = $("#file-trash-context-menu").attr("data-id");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'open': {
                    MediaboxFunctions.openFile($(".dfile[data-id=" + file_id + "]"))
                    break
                };
                case 'download': {
                    window.location.href =  config.storage.getFileUri(file_id)
                    break
                };
                case 'restore': {
                    restore("file[]=" + file_id)
                    break
                };
                case 'remove': {
                    removeFile(file_id)
                    break
                }
            };
        });
        //folder
        $(".structure").on("contextmenu", ".ddir", function(e) {
            $("#file-trash-context-menu").hide();

            $("#folder-trash-context-menu").attr("data-id", $(this).attr("data-id"));

            $("#folder-trash-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#folder-trash-context-menu").on("click", "a", function() {
            var dir_id = $("#folder-trash-context-menu").attr("data-id");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'restore': {
                    restore("folder[]=" + dir_id)
                    break
                };
                case 'remove': {
                    removeDir(dir_id)
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
        $("#trash-remove-all").click(function(){
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
            $(".ddir > div").each(function() {
                var fname = $(this).parent().attr("data-id");

                removeDir(fname);
            });

            $(".dfile > div").each(function() {
                var fname = $(this).parent().attr("data-id");

                removeFile(fname);
            });


            $("#rm-window").data("kendoWindow").close();
        });

        function remove(e) {
            var files = e.files;

            $.each(files, function(key, file) {
                removeFile(file.name)
            });
        }

        function removeFile(id) {
            var file_id = id;

            $.ajax({ type: "GET", url: '/fm/remove/', data: "id=" + id })
                .done(function(res) {
                    MediaboxFunctions.removeByStorage(id);

                    // Remove from FS Structure
                    $(".dfile[data-id='"+file_id+"']").fadeOut();
                })
        }

        function removeDir(id) {
            $.ajax({ type: "GET", url: "/fm/rmFolder/", data: "id=" + id })
                .done(function(res) {
                    // Remove folders from fs structure
                    $(".ddir[data-id='"+id+"']").fadeOut();
                });
        }
        // END Full Remove

        $("#trash-restore-all").click(function(){
            var data;

            $(".ddir > div").each(function() {
                //if ($(this).hasClass("fm_sellabel")) {
                    data += "&folder[]=" + $(this).parent().attr("data-id");
                //}
            });

            $(".dfile > div").each(function() {
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
                // Remove from FS Structure
                //$(".dfile[data-id='"+id+"']").fadeOut();

                var arr = data.split("&")

                for(var i=0;i<arr.length;i++) {
                    var arr_parts = arr[i].split("=")

                    if (arr_parts[0] == "file[]") {
                        $(".dfile[data-id='"+arr_parts[1]+"']").fadeOut();
                    } else if (arr_parts[0] == "folder[]") {
                        $(".ddir[data-id='"+arr_parts[1]+"']").fadeOut();
                    }
                }
            })
    }
});
