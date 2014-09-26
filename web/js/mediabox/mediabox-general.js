define(function (require) {
    "use strict";

    var $ = require('jquery');
    
    require('kendo/kendo.web.min');
    var mxFunctions = require('/js/mediabox/mediabox-functions.js');
    var MediaboxFunctions = new mxFunctions();

    $(document).ready(function() {
        $("#splitter").on("click", ".fm_sel", function() {
            $(this).removeClass("fm_sel").addClass("fm_unsel");
            $(".fm_unsellabel").removeClass("fm_unsellabel").addClass("fm_sellabel").addClass("f-file-select");
        });

        $("#splitter").on("click", ".fm_unsel", function() {
            $(this).removeClass("fm_unsel").addClass("fm_sel");
            $(".fm_sellabel").removeClass("fm_sellabel").addClass("fm_unsellabel").removeClass("f-file-select");
        });

        $("#fs-all").on("click", ".fs-view", function(){
            $("#mediabox-view").val($(this).attr("data-id"));
            $.ajax({ type: "GET", url: '/fm/view/', data: "view=" + $(this).attr("data-id"), cache: false })
                .done(function(res) {
                    if (window.location.pathname == "/")
                        MediaboxFunctions.chdir($("#start_dir").val());

                    if (window.location.pathname == "/trash/")
                        MediaboxFunctions.trash($("#start_dir").val());
                });
        });

        $("#fs-all").on("click", ".fs-sort", function(){
            $("#mediabox-sort").val($(this).attr("data-id"));
            $.ajax({ type: "GET", url: '/fm/sort/', data: "type=" + $(this).attr("data-id"), cache: false })
                .done(function(res) {
                    if (window.location.pathname == "/")
                        MediaboxFunctions.chdir($("#start_dir").val());

                    if (window.location.pathname == "/trash/")
                        MediaboxFunctions.trash($("#start_dir").val());
                });
        });




        $("#preview-scroll").on("click", "#advanced-overlay-back", function(){
            $("#adv-menu-upload").hide();
            $("#adv-menu-buffer").hide();
            $("#advanced-overlay").fadeOut();
        });



        $(".fs-container-div").on("dblclick", ".dfile", function(e){
            MediaboxFunctions.openFile(this);
        });
        $(".fs-container-div").on("click", ".file-open-link", function(e){
            var file = $(this).closest(".dfile");
            MediaboxFunctions.openFile(file);
        });




        $(".check-type").change(function(){
            var sList = Array();

            $(".check-type").each(function() {
                var sThisVal = $(this).val() + "=" + (this.checked ? 1 : 0);
                sList[sList.length] = sThisVal;
            });

            $.ajax({ type: "GET", url: '/fm/types/', data: sList.join("&"), cache: false })

            MediaboxFunctions.chdir($("#start_dir").val());
        });

    });



});