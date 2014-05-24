define(function (require) {
    "use strict";

    var $ = require('jquery');

    var bootstrap = require('bootstrap');

    var mxVar = require('/js/mediabox/mediabox-var.js');

    var swipebox = require('swipebox');
    var mediaboxUploader = require('mediaboxUploader');
    var mxFunctions = require('/js/mediabox/mediabox-functions.js');
    var MediaboxFunctions = new mxFunctions();

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

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
        MediaboxImageFs.getTagsAndCrops();

        var fs = new kendo.data.HierarchicalDataSource({
            transport: {
                read: {
                    url: "/fm/fs/" + "?noCache=" + (new Date().getTime()) + Math.random(),
                    dataType: "json"
                }
            },
            schema: {}
        });

        $("#treeview").kendoTreeView({
            dataSource: fs,
            select: function(e) {
                if (window.location.pathname != "/") {
                    document.location.href = "/";
                } else {
                    var data = $('#treeview').data('kendoTreeView').dataItem(e.node);
                    MediaboxFunctions.chdir(data.id);
                }
            },
            animation: {
                expand: {
                    duration: 0,
                    hide: false,
                    show: false
                },
                collapse: {
                    duration: 0,
                    show: false
                }
            },
            expand: function(e) {
                var dataItem = this.dataItem(e.node);
                dataItem.loaded(false);
            }
        });

        /**
         * Temporary solution to hide mymediacoder navigation.
         *
         */
        $('#xvidbar .navbar-inner ul.nav ul.dropdown-menu li:first-child').hide()
        /**
         * End temporary solution
         */
        $("#file-topmenu").show();

        MediaboxFunctions.chdir($("#start_dir").val());

        $("#newDirWin").click(function(){
            $("#new-folder-window").kendoWindow({
                width: "300px",
                height: "124px",
                modal: true,
                title: "Folder name:"
            });
            $("#new-folder-window").data("kendoWindow").center().open();
        });

        $("#new-folder-cancel").click(function(){
            $("#new-folder-window").data("kendoWindow").close();
        });

        $("#new-folder-ok").click(function(){
            var fname = $("#fname").val();

            if (fname != "") {
                var treeview = $("#treeview").data("kendoTreeView");

                var data = "name=" + fname;
                $.ajax({ type: "GET", url: "/fm/create/", data: data, dataType: "json" })
                    .done(function(res) {
                        var barDataItem = treeview.dataSource.get(res.parent);
                        var barElement = treeview.findByUid(barDataItem.uid);

                        treeview.append({
                            id: res.id,
                            text: fname,
                            spriteCssClass: "folder"
                        }, barElement);

                        MediaboxFunctions.addFolderToFS(res);
                    })
                    .fail(function(res) { alert(res.responseText); })

                $("#new-folder-window").data("kendoWindow").close();
            }

            return true;
        });








        $("body").on("click", ".upload", function(){
            uploaderShow();
        });

        function uploaderShow() {
            $("#advanced-overlay").fadeIn();
            $("#adv-menu-upload").show();
        }

        $("body").on("click", ".buffer", function(){
            $("#advanced-overlay").fadeIn();
            $("#adv-menu-buffer").show();
        });


        $("#fs").on("dblclick", ".ddir", function(){
            MediaboxFunctions.chdir($(this).attr("data-id"));
        });



        /**
         * Context Menu
         */
            // file
        $(".structure").on("contextmenu", ".dfile", function(e) {
            $("#folder-context-menu").hide();

            $("#file-context-menu").attr("data-id", $(this).attr("data-id"));
            $("#file-context-menu").attr("data-name", $(this).attr("title"));

            $("#file-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#file-context-menu").on("click", "a", function() {
            var file_id = $("#file-context-menu").attr("data-id");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'open': {
                    MediaboxFunctions.openFile($(".dfile[data-id=" + file_id + "]"))
                    break
                };
                case 'download': {
                    window.location.href = config.storage.getFileUri(file_id)
                    break
                };
                case 'copy': {
                    copyAction("file[]=" + file_id)
                    break
                };
                case 'remove': {
                    fileToTrash(file_id)
                    break
                }
            };
        });
        //folder
        $(".structure").on("contextmenu", ".ddir", function(e) {
            $("#file-context-menu").hide();

            $("#folder-context-menu").attr("data-id", $(this).attr("data-id"));

            $("#folder-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#folder-context-menu").on("click", "a", function() {
            var dir_id = $("#folder-context-menu").attr("data-id");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'open': {
                    MediaboxFunctions.chdir(dir_id);
                    break
                };
                case 'copy': {
                    copyAction("folder[]=" + dir_id)
                    break
                };
                case 'remove': {
                    folderToTrash(dir_id)
                    break
                }
            };
        });
        // Close context menus
        $("body").on("click", "", function() {
            $("#file-context-menu").hide();
            $("#folder-context-menu").hide();
        });



        $(".fs-actions").on("click", ".copy", function(){
            copyFiles();
        });

        $(".fs-actions").on("click", ".past", function(){
            pastFiles();
        });

        $(".fs-actions").on("click", ".delete", function(){
            win_to_trash();
        });

        //$("#search-btn").click(function(){
        //    search($("#search-text").val());
        //});

        //$("#search-text").bind('keyup', function(e){
        //    if (e.keyCode == 13){
        //        search($("#search-text").val());
        //    }
        //});
    });

    //function search(text) {
    //    $.ajax({ type: "POST", url: '/fm/search/', dataType: "JSON", data: text, cache: false })
    //        .done(function(res) {
    //            alert(res);
    //        });
    //}

    $(".left-section").on("click", ".toPlaylist", function(){
        $(".dfile").each(function() {
            $("#pl-audio").append("<div class='track' data-ext='"+$(this).attr("data-ext")+"' data-id='" + $(this).attr("data-id") + "' title='"+$(this).attr("title")+"'><div class='track-title'>" + $(this).attr("title") + "</div><div class='track-duration'></div></div>");
        });

        var data = new Array();
        $("#pl-audio > .track").each(function() {
            data[data.length] = "track[]=" + $(this).attr("data-id")
        });

        // Save playlist
        $.ajax({ type: "POST", url: '/audio/saveList/', dataType: "JSON", data: data.join("&"), cache: false })
            .done(function(data) {

            })
        //$("#pl-audio .track:odd").addClass("k-alt");
    });

    // Trash
    $("#move-to-trash-window-no").click(function(){
        $("#move-to-trash-window").data("kendoWindow").close();
    });

    $("#move-to-trash-window-yes").click(function(){
        $(".ddir > div").each(function() {
            if ($(this).hasClass("fm_sellabel")) {
                var fname = $(this).parent().attr("data-id");

                folderToTrash(fname);
            }
        });

        $(".dfile > div").each(function() {
            if ($(this).hasClass("fm_sellabel")) {
                var fname = $(this).parent().attr("data-id");

                fileToTrash(fname);
            }
        });


        $("#move-to-trash-window").data("kendoWindow").close();
    });

    function win_to_trash() {
        $("#move-to-trash-window").kendoWindow({
            width: "300px",
            height: "120px",
            modal: true,
            title: "Move to trash"
        });

        $("#move-to-trash-window").data("kendoWindow").center().open();
    }

    function trash(e) {
        var files = e.files;

        $.each(files, function(key, file) {
            fileToTrash(file.name)
        });
    }

    function fileToTrash(id) {
        var file_id = id;

        $.ajax({ type: "GET", url: '/fm/fileToTrash/', data: "id=" + id })
            .done(function(res) {
                // Remove from FS Structure
                $(".dfile[data-id='"+file_id+"']").fadeOut();
            })
    }

    function folderToTrash(id) {
        $.ajax({ type: "GET", url: "/fm/folderToTrash/", data: "id=" + id })
            .done(function(res) {
                // Remove folders from fs structure
                $(".ddir[data-id='"+id+"']").fadeOut();

                var treeview = $("#treeview").data("kendoTreeView");
                var barDataItem = treeview.dataSource.get(id);
                var barElement = treeview.findByUid(barDataItem.uid);
                treeview.detach(barElement);
            });
    }
    // END Trash



    function bufferPast(value) {
        var templateContent = $("#bufferFileTemplate").html();
        var template = kendo.template(templateContent);

        var filename = decodeURIComponent(value["name"]);
        var shortname =  filename.substr(0, 10) + ".." + filename.substr(filename.length - 2);
        var extension = MediaboxFunctions.getExtension(filename);
        var type = MediaboxFunctions.getType(extension);
        var ico = MediaboxFunctions.getIco(type);

        var data = [
            {
                id:         value["id"],
                shortname:  shortname,
                date:       MediaboxFunctions.formatDate(value["date"]),
                size:       MediaboxFunctions.formatSize(value["size"]),
                ico:        ico,
                obj:        value["obj"]
            } ];

        var result = kendo.render(template, data);

        $("#buffer").append(result);
    }

    function copyFiles() {
        var selfiles = Array();

        $(".ddir").each(function(value) {
            if ($("div", this).hasClass("fm_sellabel")) {
                selfiles[selfiles.length] = "folder[]=" + $(this).attr("data-id");
            }
        });

        $(".dfile").each(function(value) {
            if ($("div", this).hasClass("fm_sellabel")) {
                selfiles[selfiles.length] = "file[]=" + $(this).attr("data-id");
            }
        });

        copyAction(selfiles.join("&"));
    }

    function copyAction(string) {
        $.ajax({ type: "POST", url: "/fm/copy/", data: string, dataType: "JSON" })
            .done(function(res) {
                $("#buffer").html("");

                $(".bufferCount").text(res.length);

                $.each(res, function(i, value) {
                    bufferPast(value);
                });
            })
    }

    $.ajax({ type: "GET", url: "/fm/buffer/", dataType: "JSON" })
        .done(function(res) {
            if (res && res.length) {
                $(".bufferCount").text(res.length);

                $.each(res, function(i, value) {
                    bufferPast(value);
                });
            }
            else {
                $(".bufferCount").text(0);
            }
        });

    $("#clearBuffer").click(function(){
        $.ajax({ type: "GET", url: "/fm/clearBuffer/" })
            .done(function(res){
                $("#buffer").html("");

                $(".bufferCount").text(0);
            })
    });

    $("#buffer").on("click", ".deleteFileFromBuffer", function(){
        $.ajax({ type: "GET", url: "/fm/deleteFileFromBuffer/", data: "id="+$(this).attr("data-id"), dataType: "JSON" })
            .done(function(res){
                $("#buffer").html("");

                $(".bufferCount").text(res.length);

                $.each(res, function(i, value) {
                    bufferPast(value);
                })
            })
    });

    $("#fs-top-menu").on("click", ".one_folder", function(){
        MediaboxFunctions.chdir($(this).attr("data-id"));
    });

    function pastFiles() {
        $.ajax({ type: "GET", url: "/fm/past/" })
            .done(function(res) {
                $("#buffer").html("");
                $(".bufferCount").text(0);

                MediaboxFunctions.chdir($("#start_dir").val());
            })
            .fail(function(res) {
                alert(res.responseText);
            })
    }
});
