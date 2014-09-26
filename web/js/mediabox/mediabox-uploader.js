define(function (require) {
    var loadImage = require('loadImage');
    var mxFunctions = require('/js/mediabox/mediabox-functions.js');
    var MediaboxFunctions = new mxFunctions()

    var MediaboxConfiguration = require('/js/mediabox/configuration.js');
    var config = new MediaboxConfiguration();

    $(document).ready(function() {

        function uploadDropdownShow(count) {
            if ($(".upload-isset").css("display") == "none") {
                $(".upload-empty").hide();
                $(".upload-isset").show();
            }

            $(".uploadCount").html(count);
        }

        function uploadDropdownHide() {
            var count = parseInt($(".uploadCount").html()) - 1;
            $(".uploadCount").html(count);

            if (count == 0) {
                $(".upload-empty").show();
                $(".upload-isset").hide();
            }
        }

        $("#files").kendoUpload({
            showFileList: false,
            select: function(e) {
                var files = e.files;

                $.each(files, function(key, file) {
                    upload(file);
                });

                uploadDropdownShow(files.length);
            }
        });

        var dropzone = document.getElementById("adv-menu-upload");
        dropzone.ondragover = dropzone.ondragenter = function(event) {
            event.stopPropagation();
            event.preventDefault();
        }

        dropzone.ondrop = function(event) {
            event.stopPropagation();
            event.preventDefault();

            var filesArray = event.dataTransfer.files;
            for (var i=0; i<filesArray.length; i++) {
                var file = {
                    "name": filesArray[i]["name"],
                    "size": filesArray[i]["size"],
                    "extension": filesArray[i]["name"].substr(filesArray[i]["name"].lastIndexOf('.')),
                    "rawFile": filesArray[i]
                };

                upload(file);
            }
        }

        function addThumb(file, res) {
            loadImage(
                file.rawFile,
                function (img) {
                    $.ajax({ type: "POST", url: '/fm/thumb/', data: {id: res.id, data: img.toDataURL().replace(/data:image\/png;base64,/, '')} })
                        .done(function(){ MediaboxFunctions.addFileToFS(res); })
                },
                {
                    maxHeight: 80,
                    canvas: true
                }
            )
        }

        function toHex(str) {
            var hex = '';
            for(var i=0;i<str.length;i++) {
                hex += ''+str.charCodeAt(i).toString(16);
            }
            return hex;
        }

        function upload(file) {
            var file;
            var hex = toHex($("#current_path_string").val() + file.name);

            // Overlay uploader
            var templateContent = $("#fileUploadTemplate").html();
            var template = kendo.template(templateContent);
            var data = [
                { name: file.name, id: hex }
            ];
            var result = kendo.render(template, data);
            $(".perc").append(result);


            // Dropdown uploader
            var templateContent = $("#fileUploadDropdownTemplate").html();
            var template = kendo.template(templateContent);
            var data = [
                { name: file.name, id: hex }
            ];
            var result = kendo.render(template, data);
            $(".percDropdown").append(result);

            //Storage save file
            config.storage.sendFile($("#current_path_string").val(), file.rawFile, function(response){

                var pc = parseInt(response.loaded / response.total * 100);
                if (!isNaN(pc)) {
                    $(".upload-status-progress", ".u_" + hex).css("width", pc);
                }

                if (response.result) {

                    $(".upload-status-progress", ".u_" + hex).css("width", "100%");
                    uploadDropdownHide();

                    var extension = MediaboxFunctions.getExtension(file.name);
                    var type = MediaboxFunctions.getType(extension);

                    var data = {
                        file: file.name,
                        size: file.size,
                        type: type
                    }

                    $.ajax({ type: "GET", url: '/fm/upload/', dataType: "JSON", data: data})
                        .done(function(res) {

                            if (type == "image") {
                                addThumb(file, res);
                            }

                            if (type != "image") {
                                MediaboxFunctions.addFileToFS(res);
                            }
                        });

                    return true;
                }
            });
        }

        $(".perc").on("click", ".uploaderRemove", function(){
            $(this).closest(".k-upload-files").remove();

            uploadDropdownHide();
        })
    });
})