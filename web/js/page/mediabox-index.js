define(function (require) {
    "use strict";

    var $ = require('jquery');

    var bootstrap = require('bootstrap');

    var mxVar = require('/js/mediabox/mediabox-var.js');

    var templateContent = $("#fsTemplate").html();
    $("#splitter").html(templateContent);

    var swipebox = require('swipebox');
    var mediaboxUploader = require('mediaboxUploader');
    var mxFunctions = require('/js/mediabox/mediabox-functions.js');
    var MediaboxFunctions = new mxFunctions();

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

    require('kendo/kendo.slider.min');
    require('kendo/kendo.treeview.min');
    require('mediaboxImage');
    require('mediaboxTrash');
    require('Jcrop');
    require('mediaboxPlayer');
    require('mediaboxVideo');
    require('mediaelement');

    require('mousewheel');
    require('jscrollpane');
    require('up');

    $(document).ready(function() {

        if (typeof $("#LoginForm_session") == "object") {
            $("#LoginForm_session").val(config.session);
        }

        MediaboxFunctions.getParam("access_token", function(result){ $("#access_token").val(result); });

        $("#LoginForm").on("click", "#LoginForm_submit", function(){
            var data = {
                login: $("#LoginForm_username").val(),
                password: $("#LoginForm_password").val(),
                session: $("#LoginForm_session").val()
            };
            $.ajax({ type: "POST", url: "/site/login/" + "?noCache=" + (new Date().getTime()) + Math.random(), data: data, dataType: "json" })
                .done(function(result) {
                    if (result.error) {
                        $("#LoginForm_error").show();
                    } else {
                        config.storage.auth($("#LoginForm_session").val(), function(response){
                            if (response.result) {
                                MediaboxFunctions.setParam("access_token", response.access_token, function(response){
                                    document.location.href = "/";
                                });
                            } else {
                                document.location.href = "/";
                            }


                        });
                    }
                });
        });

        $("#left-menu").on("click", ".left-menu-section", function(){
            if ($(this).attr("data-id") == "treeview") {
                $("#treeview").show();
                $("#left-player").hide();
            }
            if ($(this).attr("data-id") == "player") {
                $("#treeview").hide();
                $("#left-player").show();
            }
        });

        var fs = new kendo.data.HierarchicalDataSource({
            transport: {
                read: function(options) {
                    if (options.data.id) {
                        var data = {id: options.data.id};
                    } else {
                        var data = {};
                    }
                    $.ajax({ type: "GET", url: "/fm/fs/" + "?noCache=" + (new Date().getTime()) + Math.random(), data: data, dataType: "json" })
                        .done(function(res) {
                            options.success(res);

                            /*$('#treeview').jScrollPane();*/
                        });
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
//        $("#file-topmenu").show();

        MediaboxFunctions.chdir($("#start_dir").val());

        $("#mx-topmenu").on("click", "#newDirWin", function(){
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

                //Storage create folder
                config.storage.createFolder($("#current_path_string").val(), fname, function(response){
                    if (response.result) {
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
                            .fail(function(res) { console.log(res.responseText); })
                    }
                });

                $("#new-folder-window").data("kendoWindow").close();
            }

            return true;
        });





        $("body").on("click", ".mediabox-show", function(){
            $("#mx-main").show();
            $(".mx-advanced").hide();
        });

        // Uploader
        $("body").on("click", ".upload", function(){
            uploaderShow();
        });
        $("body").on("click", "#advanced-uploader-back", function(){
            uploaderHide();
        });

        function uploaderShow() {
            $("#mx-main").hide();
            $(".mx-advanced").hide();

            $("#advanced-uploader").show();
        }

        function uploaderHide() {
            $("#mx-main").show();
            $(".mx-advanced").hide();

            $("#advanced-uploader").hide();
        }

        $("body").on("click", ".buffer", function(){
            $("#mx-main").hide();
            $(".mx-advanced").hide();

            $("#advanced-buffer").show();
        });

        $("body").on("click", "#advanced-buffer-back", function(){
            $("#advanced-buffer").hide();
            $(".mx-advanced").hide();

            $("#mx-main").show();
        });

        $("body").on("click", ".settings-show", function(){
            $("#mx-main").hide();
            $(".mx-advanced").hide();

            $("#advanced-settings").show();
        });

        $("body").on("click", "#advanced-settings-back", function(){
            $("#mx-main").show();
            $(".mx-advanced").hide();

            $("#advanced-settings").hide();
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
            $("#file-context-menu").attr("data-name", $(this).attr("data-name"));

            $("#file-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#file-context-menu").on("click", "a", function() {
            var file_id = $("#file-context-menu").attr("data-id");
            var file_name = $("#file-context-menu").attr("data-name");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'open': {
                    MediaboxFunctions.openFile($(".dfile[data-id=" + file_id + "]"))
                    break
                };
                case 'download': {
                    window.location.href = config.storage.getFileUri($("#current_path_string").val(), file_name)
                    break
                };
                case 'rename': {
                    renameFile(file_name, file_id)
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
            $("#folder-context-menu").attr("data-name", $(this).attr("data-name"));

            $("#folder-context-menu").css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });

            return false;
        });
        $("#folder-context-menu").on("click", "a", function() {
            var dir_id = $("#folder-context-menu").attr("data-id");
            var dir_name = $("#folder-context-menu").attr("data-name");
            var role = $(this).attr("data-role");

            switch(role) {
                case 'open': {
                    MediaboxFunctions.chdir(dir_id);
                    break
                };
                case 'rename': {
                    renameFolder(dir_name, dir_id)
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


        $("#mx-topmenu").on("click", ".copy", function(){
            copyFiles();
        });

        $("#mx-topmenu").on("click", ".past", function(){
            pastFiles();
        });

        $("#mx-topmenu").on("click", ".delete", function(){
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

    function renameFile(file_name, file_id) {
        $("#old_filename").val(file_name);
        $("#file_id").val(file_id);

        $("#rename_filename").val(file_name);

        $("#rename-file-window").kendoWindow({
            width: "300px",
            height: "124px",
            modal: true,
            title: "New file name:"
        });
        $("#rename-file-window").data("kendoWindow").center().open();
    }

    $("#rename-file-ok").click(function(){
        var path = $("#current_path_string").val();
        var old_name = $("#old_filename").val();
        var new_name = $("#rename_filename").val();

        //Storage rename file
        config.storage.rename(path, old_name, new_name, function(response){
            if (response.result) {
                $.ajax({ type: "GET", url: "/fm/renameFile/", data: "id=" + $("#file_id").val() + "&name=" + $("#rename_filename").val() })
                    .done(function(res) {
                        $(".dfile").attr("data-name", new_name);
                        $(".dfile").find(".fname").text(new_name);
                        $(".dfile").find(".fname_li").text(new_name);

                        $("#rename-file-window").data("kendoWindow").close();
                    });
            }
        });
    });

    $("#rename-file-cancel").click(function(){
        $("#rename-file-window").data("kendoWindow").close();
    });

    function renameFolder(dir_name, dir_id) {
        $("#old_foldername").val(dir_name);
        $("#folder_id").val(dir_id);

        $("#rename_foldername").val(dir_name);

        $("#rename-folder-window").kendoWindow({
            width: "300px",
            height: "124px",
            modal: true,
            title: "New folder name:"
        });
        $("#rename-folder-window").data("kendoWindow").center().open();
    }

    $("#rename-folder-ok").click(function(){
        var path = $("#current_path_string").val();
        var old_name = $("#old_foldername").val();
        var new_name = $("#rename_foldername").val();

        //Storage rename folder
        config.storage.rename(path, old_name, new_name, function(response){
            if (response.result) {
                $.ajax({ type: "GET", url: "/fm/renameFolder/", data: "id=" + $("#folder_id").val() + "&name=" + $("#rename_foldername").val() })
                    .done(function(res) {
                        $(".ddir").attr("data-name", new_name);
                        $(".ddir").find(".file-open-link").text(new_name);

                        $("#rename-folder-window").data("kendoWindow").close();
                    });
            }
        });
    });

    $("#rename-folder-cancel").click(function(){
        $("#rename-folder-window").data("kendoWindow").close();
    });


    function bufferPast(value) {
        var templateContent = $("#bufferFileTemplate").html();
        var template = kendo.template(templateContent);

        var filename = decodeURIComponent((value["name"]+'').replace(/\+/g, '%20'));
        var extension = MediaboxFunctions.getExtension(filename);
        var type = MediaboxFunctions.getType(extension);
        var ico = MediaboxFunctions.getIco(type);

        var data = [
            {
                id:         value["id"],
                name:       filename,
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
        $.ajax({ type: "GET", url: "/fm/getMoveFiles/", dataType: "JSON" })
            .done(function(data) {

                //Storage move file
                config.storage.move($("#current_path_string").val(), data, function(response){
                    if (response.result) {

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
        });
    }
});
