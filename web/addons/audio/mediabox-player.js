define(function (require) {
    /**
     * Initialize
     */
    var player;
    var mediaElement = "mediaElement";
    require('kendo/kendo.slider.min');
    require('kendo/kendo.menu.min');
    require('kendo/kendo.upload.min');

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

    var methods = {
        init: function( options ) {
            var track = this;
            var data_id = $(track).attr('data-id');
            var uri = config.storage.getFileUri($(track).attr("data-path"), $(track).attr("data-name"));


            $("#" + mediaElement).attr("src", uri);
            $("#" + mediaElement).attr("type", 'audio/' + $(track).attr('data-ext'));

            player = new MediaElement(mediaElement, {
                plugins: ['flash'],
                success: function(player){

                    player.volume = $("#volume").val()/100;

                    var onStart = function() {
                        var dsec = parseInt(player.duration - Math.floor(player.duration / 60)*60);
                        if (!isNaN(dsec)) {
                            if (dsec < 10) {
                                dsec = "0" + dsec;
                            }
                            var duration = Math.floor(player.duration / 60) + ":" + dsec;
                        } else {
                            var duration = "--:--";
                        }

                        $("#current-track-duration").text(duration);

                        $("#mx-pos-slider-div").click(function(e){
                            var x = (e.pageX - $(this).offset().left)/$(this).width() * 100;

                            $("#mx-pos-slider-div").html("<div style='width: " + x + "%; height: 5px; padding: 1px; background-color: #004499;'></div>");

                            var cur_pos = (e.pageX - $(this).offset().left)/$(this).width() * player.duration;
                            player.setCurrentTime(cur_pos);
                        });
                    };
                    var onTimeupdate = function() {
                        var cur_pos =  player.currentTime / player.duration * 100;
                        $("#mx-pos-slider-div").html("<div style='width: " + cur_pos + "%; height: 5px; padding: 1px; background-color: #004499;'></div>");

                        var sec = parseInt(player.currentTime - Math.floor(player.currentTime / 60)*60);
                        if (!isNaN(sec)) {
                            if (sec < 10) {
                                sec = "0" + sec;
                            }
                            var currentTime = Math.floor(player.currentTime / 60) + ":" + sec;
                        } else {
                            var currentTime = "--:--";
                        }

                        $("#current-track-time").text(currentTime);
                    };
                    var onEnd = function() {
                        var currenttrack = $(".playlist-track-current");

                        if ($(".fs-track-current").width() > 0) {
                            $(".fs-track-current").removeClass("icon-pause").addClass("icon-play").removeClass("fs-track-current");
                        }

                        $(".track").removeClass("playlist-track-current");

                        if ($(currenttrack).next().length) {
                            $(currenttrack).next().addClass("playlist-track-current");
                            $(".playlist-track-current").player("load").player("play");
                        } else {
                            $("#mx-pos-slider-div").html("<div style='width: 0%; height: 5px; padding: 1px; background-color: #004499;'></div>");
                            $("#current-track-time").text("--:--");

                            var current = $(".track-pause");
                            $("i", current).removeClass("icon-pause").addClass("icon-play");
                            $(current).removeClass("track-pause").addClass("track-play");
                        }
                    };

                    //player.addEventListener('progress', onStart);
                    player.addEventListener('canplay', onStart);
                    player.addEventListener('timeupdate', onTimeupdate);
                    player.addEventListener('ended', onEnd);

                    //player.load();
                    player.play();
                },
                error : function(player) {
                    console.log('medialement problem is detected: %o', player);
                }
            });

            return this;
        },
        load: function( options ) {
            var track = this;
            var data_id = $(track).attr('data-id');
            var uri = config.storage.getFileUri($(track).attr("data-path"), $(track).attr("data-name"));

            $(".track").removeClass("playlist-track-current");
            $(track).addClass("playlist-track-current");

            if (!player) {
                $(track).player("init");
            } else {
                player.pause();
                player.setSrc([{ src: uri, type: 'audio/' + $(track).attr('data-ext') }]);
                //player.load();
            }

            return this
        },
        get: function( options ) {
            return player;
        },
        play: function() {
            player.play();

            if ($(".fs-track-current").width() > 0) {
                $(".fs-track-current").removeClass("icon-play").addClass("icon-pause");
            }

            var current = $(".track-play");
            $("i", current).removeClass("icon-play").addClass("icon-pause");
            $(current).removeClass("track-play").addClass("track-pause");

            $("#current-track").text(decodeURIComponent($(this).attr("title").replace(/\+/g, ' ')));
        },
        pause: function() {
            player.pause();

            if ($(".fs-track-current").width() > 0) {
                $(".fs-track-current").removeClass("icon-pause").addClass("icon-play");
            }

            var current = $(".track-pause");
            $("i", current).removeClass("icon-pause").addClass("icon-play");
            $(current).removeClass("track-pause").addClass("track-play");
        },

        stop: function() {
            player.setCurrentTime(0);
            player.pause();

            if ($(".fs-track-current").width() > 0) {
                $(".fs-track-current").removeClass("icon-pause").addClass("icon-play");
            }

            var current = $(".track-pause");
            $("i", current).removeClass("icon-pause").addClass("icon-play");
            $(current).removeClass("track-pause").addClass("track-play");

            $("#mx-pos-slider-div").html("<div style='width: 0%; height: 5px; padding: 1px; background-color: #004499;'></div>");
            $("#current-track-time").text("--:--");
        }
    };
    $.fn.player = function( method ) {
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' not found' );
        }
    };
});

$(document).ready(function() {

    $("#volume").kendoSlider({
        orientation: "vertical",
        min: 0,
        max: 100,
        value: $("#volume-level").val(),
        smallStep: 1,
        largeStep: 20,
        showButtons: false,
        slide: function(e) {
            var player = $(".playlist-track-current").player("get");
            player.volume = e.value/100;
        },
        change: function(e) {
            $.ajax({type:"GET",url: "/audio/volume/",data:"level="+e.value});
            $("#volume-level").val(e.value);
            var player = $(".playlist-track-current").player("get");
            player.volume = e.value/100;
        }
    });

    function trackToPlaylist(id, path, ext, title) {
        $("#pl-audio").append("<div class='track' data-ext='" + ext + "' data-id='" + id + "' data-path='" + path + "' data-name='" + title + "' title='" + decodeURIComponent(title).replace(/\+/g, ' ') + "'><div class='track-title'>" + decodeURIComponent(title).replace(/\+/g, ' ') + "</div><div class='track-delete' title='Delete track'><i class='icon-minus' style='font-size: 10px;'></i></div></div>");
    }

    function savePlaylist() {
        var data = new Array();
        $("#pl-audio > .track").each(function() {
            data[data.length] = "track[]=" + $(this).attr("data-id")
        });

        $.ajax({ type: "POST", url: '/audio/saveList/', dataType: "JSON", data: data.join("&"), cache: false });
    }

    $.ajax({ type: "GET", url: '/audio/getTracksList/', dataType: "JSON", cache: false })
        .done(function(data) {

            var mxFunctions = require('/js/mediabox/mediabox-functions.js');
            var MediaboxFunctions = new mxFunctions();

            $.each(data, function( key, value ) {
                trackToPlaylist(value.id, value.path, MediaboxFunctions.getExtension(value.name), value.name);
            });
        });

    $("#pl-audio").on("click", ".track-delete", function() {
        var track_id = $(this).closest(".track").attr("data-id");
        $(".track[data-id='"+track_id+"']").remove();
        savePlaylist();
    });

    $("#pl-audio").on("click", ".track-title", function() {
        var $this = $(this).closest(".track");
        $($this).player("load").player("play");

        $("#playerMenu").attr("data-id",  $($this).attr("data-id"));

        if ($(".fs-track-current").width() > 0) {
            $(".fs-track-current").removeClass("fs-track-current").removeClass("icon-pause").addClass("icon-play");
        }
    });

    $("#player-playlists-control").on("click", ".toPlaylist", function(){
        var data = new Array();

        $(".dfile[data-type='audio']").each(function() {
            if ($("div", this).hasClass("fm_sellabel")) {
                trackToPlaylist($(this).attr("data-id"), $("#current_path_string").val(), $(this).attr("data-ext"), $(this).attr("title"));
            };
        });

        savePlaylist();
    });

    $("#player-playlists-control").on("click", ".clearPlaylist", function(){
        if (confirm("Really, remove all tracks from playlist?")) {
            $("#pl-audio").html("");

            savePlaylist();
        }
    });

    $("#player-controls").on("click", ".track-play", function(){
        $(this).player("play");
    });

    $("#player-controls").on("click", ".track-pause", function(){
        $(this).player("pause");
    });

    $("#player-controls").on("click", "#track-stop", function(){
        $(this).player("stop");
    });

    $("#player-controls").on("click", "#track-prev", function(){
        var current_track = $("#playerMenu").attr("data-id");
        var prev = $(".track[data-id='"+current_track+"']").prev(".track");
        prev.player("load").player("play");

        $("#playerMenu").attr("data-id",  prev.attr("data-id"));
    });

    $("#player-controls").on("click", "#track-next", function(){
        var current_track = $("#playerMenu").attr("data-id");
        var next = $(".track[data-id='"+current_track+"']").next(".track");
        next.player("load").player("play");

        $("#playerMenu").attr("data-id",  next.attr("data-id"));
    });


    $("#player-playlists-control").on("click", "#create-new-playlist", function(){
        $("#new-playlist-window").kendoWindow({
            width: "300px",
            height: "120px",
            modal: true,
            title: "New playlist"
        });
        $("#new-playlist-window").data("kendoWindow").center().open();
    });
    $("#new-playlist-close").click(function(){
        $("#new-playlist-window").data("kendoWindow").close();
    });
    $("#new-playlist-save").click(function(){
        $.ajax({ type: "GET", url: '/audio/createList/', data: "name=" + $("#new-playlist-name").val(), cache: false })
            .done(function(res) {

                $("#pl-audio").html("");

                $.ajax({ type: "GET", url: '/audio/GetTracksList/', dataType: "JSON", cache: false })
                    .done(function(data) {

                        var mxFunctions = require('/js/mediabox/mediabox-functions.js');
                        var MediaboxFunctions = new mxFunctions();

                        $.each(data, function(key, value) {
                            trackToPlaylist(value.id, value.path, MediaboxFunctions.getExtension(value.name), value.name);
                        });
                    });

                $("#new-playlist-window").data("kendoWindow").close();
            })
    });

    $("#close-playlist").click(function(){
        $.ajax({ type: "GET", url: '/audio/setPlaylist/', cache: false })
            .done(function(data) {
                $("#pl-audio").html("");
                    $.ajax({ type: "GET", url: '/audio/GetTracksList/', dataType: "JSON", cache: false })
                        .done(function(data) {

                            var mxFunctions = require('/js/mediabox/mediabox-functions.js');
                            var MediaboxFunctions = new mxFunctions();

                            $.each(data, function(key, value) {
                                trackToPlaylist(value.id, value.path, MediaboxFunctions.getExtension(value.name), value.name);
                            });
                        });
            });
    });

    $("#show-playlists").click(function(){
        $("#user-playlists").html("");

        $.ajax({ type: "GET", url: '/audio/showList/', dataType: "JSON", cache: false })
            .done(function(data) {
                $.each(data, function(key, value) {
                    $("#user-playlists").append("<div class='row-fluid'><div class='span7'><a href='#' class='playlist-item' data-id='" + value.id + "'>" + value.name + "</a></div><div class='span5'><a class='delete-playlist' data-id='" + value.id + "'>Delete</a></div></div>");
                });
            })
    });

    $("#show-playlist").click(function(){
        $("#pl-audio").html("");

        $.ajax({ type: "GET", url: '/audio/getTracksList/', dataType: "JSON", cache: false })
            .done(function(data) {
                $.each(data, function(key, value) {
                    trackToPlaylist(value.id, value.path, MediaboxFunctions.getExtension(value.name), value.name);
                });
            })
    });

    $("#user-playlists").on("click", ".playlist-item", function(){
        $.ajax({ type: "GET", url: '/audio/setPlaylist/', data: "playlist-id=" + $(this).attr("data-id"), cache: false })
            .done(function(data) {
                $("#pl-audio").html("");
                $.ajax({ type: "GET", url: '/audio/getTracksList/', dataType: "JSON", cache: false })
                    .done(function(data) {

                        var mxFunctions = require('/js/mediabox/mediabox-functions.js');
                        var MediaboxFunctions = new mxFunctions();

                        $.each(data, function( key, value ) {
                            trackToPlaylist(value.id, value.path, MediaboxFunctions.getExtension(value.name), value.name);
                        });
                    });
            });
    });

    $("#user-playlists").on("click", ".delete-playlist", function(){
        if (confirm('Really delete playlist?')) {
            $.ajax({ type: "GET", url: '/audio/deletePlaylist/', data: "playlist-id=" + $(this).attr("data-id"), cache: false })
                .done(function(){
                    $.ajax({ type: "GET", url: '/audio/GetTracksList/', dataType: "JSON", cache: false })
                        .done(function(data) {

                            var mxFunctions = require('/js/mediabox/mediabox-functions.js');
                            var MediaboxFunctions = new mxFunctions();

                            $.each(data, function(key, value) {
                                trackToPlaylist(value.id, value.path, MediaboxFunctions.getExtension(value.name), value.name);
                            });
                        });
                });
        }
    });

    function getDuration() {
        var d_sec = parseInt(audio.duration - Math.floor(audio.duration / 60)*60);
        if (!isNaN(d_sec)) {
            if (d_sec < 10) {
                d_sec = "0" + d_sec;
            }
            var d = Math.floor(audio.duration / 60) + ":" + d_sec;
        } else {
            var d = "--:--";
        }

        $(".track[data-id='"+audio.src+"'] > .track-duration").html(d);
    }

    $("#pl-audio").kendoDropTarget({
        drop: function(e) {
            if (e.draggable.currentTarget.attr("data-type") == 'audio') {
                trackToPlaylist(
                    e.draggable.currentTarget.attr("data-id"),
                    $("#current_path_string").val(),
                    e.draggable.currentTarget.attr("data-ext"),
                    e.draggable.currentTarget.attr("title")
                );
            }

            savePlaylist();
        }
    });

});
