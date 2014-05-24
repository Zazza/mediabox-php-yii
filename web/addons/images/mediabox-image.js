define(function (require) {
//define("mediaboxImage", ["jquery"], function($){
    /**
     * Initialize
     */
    var mxFunctions = require('/js/mediabox/mediabox-functions.js');
    var MediaboxFunctions = new mxFunctions();

    var imageFs = require('/js/mediabox/mediabox-image-fs.js');
    var MediaboxImageFs = new imageFs();

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

    var jcrop_api;
    var _id;
    var original_width;
    var original_height;
    var current_width;
    var current_height;

    $("#preview-scroll").css("height", $(window).height() - 67);
    $(window).resize(function() {
        $("#preview-scroll").css("height", $(window).height() - 67);
    });

    //scrollbar
    //$("#preview-scroll").mCustomScrollbar({ scrollInertia:150, advanced:{ updateOnContentResize: true } });

    var methods = {
        init: function( options ) {
            //$("#splitter").fadeOut();
            $("#image-preview").fadeIn();

            $("#fs-second-panel").hide();
            $("#image-second-panel").show();

            $(this).addClass("current");

            bindActive();

            return this;
        },

        close: function() {
            unbindActive();

            $("#fs-second-panel").show();
            $("#image-second-panel").hide();

            $("#image-preview").fadeOut();
            //$("#splitter").fadeIn();
        },

        one: function() {
            $("#preview-div-img").height($(window).height() - 165);

            var margintop = ($(window).height() - 165)/2 - 32;

            $("#preview-div-img").html("<img src='/img/loading.gif' style='margin-top: "+margintop+"px;' />");

            return this;
        },

        next: function() {
            var image = this;

            // flush comment div
            $("#image-comments").html("");

            //$("#preview-div-img").height($(window).height() - 165);

            var margintop = ($(window).height() - 165)/2 - 32;

            $("#preview-div-img")
                .fadeOut(200, function(){
                    $("#preview-div-img").html("<img src='/img/loading.gif' style='margin-top: "+margintop+"px;' />");
                    $(image).image("loadImg");
                })
                .fadeIn(300, function(){
                    // Callback
                });
        },

        prev: function() {
            var image = this;

            // flush comment div
            $("#image-comments").html("");

            //$("#preview-div-img").height($(window).height() - 165);

            var margintop = ($(window).height() - 165)/2 - 32;

            $("#preview-div-img")
                .fadeOut(200, function(){
                    $("#preview-div-img").html("<img src='/img/loading.gif' style='margin-top: "+margintop+"px;' />");
                    $(image).image("loadImg");
                })
                .fadeIn(300, function(){
                    // Callback
                });
        },

        loadImg : function( options ) {
            var src = this;
            var per;

            var _id = $(src).attr("data-id");
            $("#preview-div-img").attr("data-id", _id);
            var uri = config.storage.getFileUri(_id)

            $.loadImage(uri)
                .fail(function(image) {
                    $("#preview-div-img").html("<h3>404: file not found!</h3>");
                })
                .done(function(image) {
                    per = image.height / ($(window).height() - 145);
                    if (image.height > $(window).height()) {
                        current_height = $(window).height() - 145;
                    } else {
                        current_height = image.height;
                    }
                    if (image.height > $(window).height()) {
                        current_width = image.width / per;
                    } else {
                        current_width = image.width;
                    }

                    //IE Bug with image width
                    $(image).width(current_width);
                    $(image).height(current_height);

                    $("#preview-div-img").width(current_width);
                    $("#preview-div-img").height(current_height);

                    $("#preview-div-img").html(image);

                    $(image).Jcrop({
                        onSelect: showCoords,
                        onChange: showCoords,
                        boxWidth: current_width,
                        boxHeight: current_height
                    }, function(){
                        jcrop_api = this;
                    });

                    /**
                     * SET meta information
                     */
                    $("#image-meta .name").html($(src).attr("title"));
                    $("#image-meta .order").html("[" + (parseInt($(".current").index())+parseInt(1)) + "/" + $(".swipebox").length + "]");
                    $("#image-meta .resolution").html(image.width + "x" + image.height);
                    $("#image-meta .size").html($(".current .file_icon_size").text());
                    $("#image-meta .download").attr("href", uri);

                    /**
                     * Get Crops
                     */
                   getCrops(_id);

                    /**
                     * Get Tags
                     */
                    getTags(_id);

                    /**
                     * Show comments
                     */
                    $.ajax({ type: "GET", dataType: "JSON", url: '/image/getComments/', data: "id=" + _id })
                        .done(function(res) {
                            if (res == '') {
                                $("#image-comments").html("Empty");
                            } else {
                                $.each(res, function(key, value){
                                    /*
                                    * Only JS Backend
                                    var timestamp = new Date(value.timestamp);
                                    var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
                                    var time = timestamp.getHours() + ":" + timestamp.getMinutes() + ", " + timestamp.getDate() + "-" + monthNames[timestamp.getMonth()] + "-" + timestamp.getFullYear();

                                    comment(time, value.username, value.text);
                                    */
                                    // PHP Backend
                                    comment(value.timestamp, value.user, value.text);
                                })
                            }
                        })
                });
        },

        preview : function( options ) {
            var xmlhttp;
            var img = this;
            if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else { // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    $(img).attr("src", xmlhttp.responseText);
                }
            };
            xmlhttp.open("GET", 'fm/getThumb/?name='+$(img).attr("data-id") );
            xmlhttp.send(null);
        }
    };

    $.fn.image = function( method ) {
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' not found' );
        }
    };

    $.loadImage = function(url) {
        // Define a "worker" function that should eventually resolve or reject the deferred object.
        var loadImage = function(deferred) {
            var image = new Image();

            // Set up event handlers to know when the image has loaded
            // or fails to load due to an error or abort.
            image.onload = loaded;
            image.onerror = errored; // URL returns 404, etc
            image.onabort = errored; // IE may call this if user clicks "Stop"

            // Setting the src property begins loading the image.
            image.src = url;

            function loaded() {
                unbindEvents();
                // Calling resolve means the image loaded sucessfully and is ready to use.
                deferred.resolve(image);
            }
            function errored() {
                unbindEvents();
                // Calling reject means we failed to load the image (e.g. 404, server offline, etc).
                deferred.reject(image);
            }
            function unbindEvents() {
                // Ensures the event callbacks only get called once.
                image.onload = null;
                image.onerror = null;
                image.onabort = null;
            }
        };

        // Create the deferred object that will contain the loaded image.
        // We don't want callers to have access to the resolve() and reject() methods,
        // so convert to "read-only" by calling `promise()`.
        return $.Deferred(loadImage).promise();
    };

    /**
     * Show select area
     * @param obj
     */
    function overAXIS(obj) {
        var ws = current_width / $(obj).attr("data-ws");
        ws = ws.toFixed(2);

        jcrop_api.setOptions({
            onSelect:    showCoords,
            bgColor:     'black',
            bgOpacity:   .4,
            setSelect:   [$(obj).attr("data-x1") * ws, $(obj).attr("data-y1") * ws, $(obj).attr("data-x2") * ws, $(obj).attr("data-y2") * ws]
        });
    }

    /**
     * Clear select area
     * @returns {boolean}
     */
    function outAXIS() {
        jcrop_api.release();
        jcrop_api.setOptions({ allowSelect: true });

        clearCoords();

        return false;
    }

    function getCrops(_id) {
        $.ajax({ type: "GET", dataType: "JSON", url: '/image/getCrops/', data: "id=" + _id })
            .done(function(res) {

                if (res == "") {
                    $("#image-crops").html("Empty");
                } else {
                    $("#image-crops").html("");
                }

                $.each(res, function(key, value){
                    $("#image-crops").append('<div class="image-crop" data-x1="'+value.x1+'" data-x2="'+value.x2+'" data-y1="'+value.y1+'" data-y2="'+value.y2+'" data-ws="'+value.ws+'">'+value.description+'</div>');
                })

                $("#image-crops").on("mouseover", ".image-crop", function(){
                    overAXIS(this);
                })

                $("#image-crops").on("mouseout", ".image-crop", function(){
                    outAXIS();
                })
            })
    }

    function getTags(_id) {
        $.ajax({ type: "GET", dataType: "JSON", url: '/image/getTags/', data: "id=" + _id })
            .done(function(res) {
                if (res == "") {
                    $("#image-tags").html("Empty");
                } else {
                    $("#image-tags").html("");
                }

                $.each(res, function(key, value){
                    $("#image-tags").append('<div class="image-tag">'+value.tag+'</div>');
                })
            })
    }

    // Crop
    $("#saveCrop").click(function(){
        $("#div-crop-object-text").removeClass("error");

        $("#crop-object").kendoWindow({
            width: "300px",
            height: "150px",
            modal: true,
            title: "Crop object"
        });
        $("#crop-object").data("kendoWindow").center().open();
    });

    $("#clearCrop").click(function(){
        jcrop_api.release();
        jcrop_api.setOptions({ allowSelect: true });

        clearCoords();
    });

    $("#crop-object-save").click(function(){
        var x1 = $("#x1").val();
        var x2 = $("#x2").val();
        var y1 = $("#y1").val();
        var y2 = $("#y2").val();
        var desc = $("#crop-object-text").val();

        if (!x1 || !x2 || !y1 || !y2) {
            $("#crop-object").prepend("<div class='k-block k-error-colored crop-error' style='margin-bottom: -10px'>selection area isn't chosen</div>");
        } else if (!desc) {
            $("#div-crop-object-text").addClass("error");
        } else {
            var _id = $("#preview-div-img").attr("data-id");
            var data = "x1=" + x1 + "&x2=" + x2 + "&y1=" + y1 + "&y2=" + y2 + "&_id=" + _id + "&desc=" + desc + "&ws=" + current_width;

            $.ajax({ type: "GET", url: '/image/setCrop/', data: data })
                .done(function(res) {
                    getCrops($("#preview-div-img").attr("data-id"));
                    $("#crop-object").data("kendoWindow").close();
                })
        }
    });

    $("#crop-object-close").click(function(){
        $("#crop-object").data("kendoWindow").close();
    });
    // END Crop


    // Tag
    $("#addTag").click(function(){
        $("#div-image-tag-text").removeClass("error");

        $("#image-tag").kendoWindow({
            width: "300px",
            height: "150px",
            modal: true,
            title: "Add tag"
        });
        $("#image-tag").data("kendoWindow").center().open();
    });

    $("#image-tag-save").click(function(){
        var tag = $("#image-tag-text").val();

        if (tag) {
            var _id = $("#preview-div-img").attr("data-id");
            var data = "_id=" + _id + "&tag=" + tag;

            $.ajax({ type: "GET", url: '/image/addTag/', data: data })
                .done(function(res) {
                    getTags($("#preview-div-img").attr("data-id"));
                    $("#image-tag").data("kendoWindow").close();
                })
        } else {
            $("#div-image-tag-text").addClass("error");
        }
    });

    $("#image-tag-close").click(function(){
        $("#image-tag").data("kendoWindow").close();
    });
    // END Tag

    $("#show-comment-window").click(function(){
        $("#new-comment-window").kendoWindow({
            width: "358px",
            height: "190px",
            modal: true,
            title: "Comment"
        });
        $("#new-comment-window").data("kendoWindow").center().open();
    });
    $("#image-comment-close").click(function(){
        $("#new-comment-window").data("kendoWindow").close();
    });

    function showCoords(c) {
        $("#x1").val(c.x);
        $("#y1").val(c.y);
        $("#x2").val(c.x2);
        $("#y2").val(c.y2);
    };

    function clearCoords() {
        $("#x1").val('');
        $("#y1").val('');
        $("#x2").val('');
        $("#y2").val('');
    };

    function comment(time, username, text) {
        var templateContent = $("#imageCommentsTemplate").html();
        var template = kendo.template(templateContent);

        var data = [
            {
                time:   time,
                text:   decodeURIComponent(text),
                user:   username
            }
        ];

        var result = kendo.render(template, data);
        $("#image-comments").append(result);
    }

    $("#image-comment-save").click(function(){
        var _id = $("#preview-div-img").attr("data-id");
        $.ajax({ type: "GET", url: '/image/addComment/', data: "id=" + _id + "&text=" + encodeURIComponent($("#image-comment-editor").val()) })
            .done(function(res) {
                var timestamp = new Date();
                var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
                var time = timestamp.getHours() + ":" + timestamp.getMinutes() + ", " + timestamp.getDate() + "-" + monthNames[timestamp.getMonth()] + "-" + timestamp.getFullYear();

                comment(time, "fff", $("#image-comment-editor").val());

                $("#new-comment-window").data("kendoWindow").close();
            })
    });

    $("#image-preview").on("click", "#back", function(){
        $(this).image("close");
        $("div.current").removeClass("current");

        MediaboxImageFs.getTagsAndCrops();
    });

    function imgNext() {
        var cur_id = 0; var next = "";
        var imgs = $(".dfile[data-type='image']");

        $.each(imgs, function(key, value) {
            if ($(value).attr("data-id") == $(".current").attr("data-id")) {
                cur_id = key;
            }
        });

        if (cur_id == imgs.length-1) {
            next = imgs[0];
        } else {
            var id = parseInt(cur_id) + parseInt(1);
            next = imgs[id];
        }

        $(next).image("next");

        $(".current").removeClass("current");
        $(next).addClass("current");
    }
    function imgPrev() {
        var cur_id = 0; var prev = "";
        var imgs = $(".dfile[data-type='image']");

        $.each(imgs, function(key, value) {
            if ($(value).attr("title") == $(".current").attr("title")) {
                cur_id = key;
            }
        });

        if (cur_id == 0) {
            prev = imgs[imgs.length-1];
        } else {
            var id = parseInt(cur_id) - parseInt(1);
            prev = imgs[id];
        }

        $(prev).image("prev");

        $(".current").removeClass("current");
        $(prev).addClass("current");
    }

    function bindActive() {
        /*
        $(window).bind('keyup', function(e){
            e.preventDefault();
            e.stopPropagation();
            if (e.keyCode == 37){
                imgPrev();
            }
            else if (e.keyCode==39){
                imgNext();
            }
//            else if (e.keyCode == 27) {
//                $(this).image("close");
//                $("div.current").removeClass("current");
//            }
            else if (e.keyCode == 70) {
                //Fullscreen
                $(this).swipebox();
                //fullscreen();
            }
        });
        */
    }

    function unbindActive() {
        /*
        $(window).unbind('keyup');
        */
    }

    $("#image-preview").on("click", "#next", function(){
        imgNext();
    });
    $("#image-preview").on("click", "#prev", function(){
        imgPrev();
    });

    /*
    $("#image-preview").on("click", "#zoomIn", function(){

    });
    $("#image-preview").on("click", "#zoomOut", function(){

    });
    */
});