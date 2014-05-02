define(function (require) {
    "use strict";

    var $ = require('jquery');
    var MediaboxFunctions = require('/js/mediabox/mediabox-functions.js');
    var mediaboxFunctions = new MediaboxFunctions();

    var MediaboxImageFs = function() {

        this.loadImgFs = function() {
            loadImgFs();
        }
        function loadImgFs() {
            var fs;

            $("#fm_folders").html("");
            $("#fm_files").html("");

            $("#fm_images_structure").html("");
            $.ajax({ type: "GET", url: '/image/getFsImg/', dataType: "JSON" })
                .done(function(res) {
                    $.each(res, function(key, value) {
                        var templateContent = $("#imageGridTemplate").html();
                        var template = kendo.template(templateContent);

                        var timestamp = new Date(value["date"]);
                        var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
                        var time = timestamp.getHours() + ":" + timestamp.getMinutes() + ", " + timestamp.getDate() + "-" + monthNames[timestamp.getMonth()] + "-" + timestamp.getFullYear();

                        var size;
                        if ((value["size"] / 1024 / 1024) > 1) {
                            size = (value["size"] / 1024 / 1024).toFixed(2) + " Mb";
                        } else if ((value["size"] / 1024) > 1) {
                            size = (value["size"] / 1024).toFixed(2) + " Kb";
                        } else {
                            size = value["size"] + " Б";
                        };

                        var data = [
                            {
                                id:         value["id"],
                                name:       value["name"],
                                shortname:  value["shortname"],
                                date:       time,
                                size:       size,
                                ico:        value["ico"],
                                ext:        value["ext"],
                                href:       mediaboxFunctions.getFileUri(value["id"]) //$("#storage").val()+"/get/?id=" + value["id"]
                            }
                        ];

                        var result = kendo.render(template, data);

                        $("#fm_files").append(result);

                    })

                    $(".fm_ajax-loader").hide();
                    $(".swipebox").swipebox();
                })
        }

        this.getTagsAndCrops = function() {
            $.ajax({ type: "GET", url: '/image/getAllTags/', dataType: "JSON" })
                .done(function(res) {
                    $(".left-section #allTags").html("");
                    $.each(res, function(key, value) {
                        $(".left-section #allTags").append("<span class='tagAll k-content'>" + value + "</span> ");
                    });

                    $(".left-section #allCrops").html("");
                    $.ajax({ type: "GET", url: '/image/getAllCrops/', dataType: "JSON" })
                        .done(function(res) {
                            $.each(res, function(key, value) {
                                $(".left-section #allCrops").append("<span class='cropAll k-content'>" + value + "</span> ");
                            });
                        })
                });
        }
    }

    return MediaboxImageFs;
});