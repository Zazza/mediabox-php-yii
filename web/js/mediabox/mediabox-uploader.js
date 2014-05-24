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

            $(".uploadCount").text(count);
        }

        function uploadDropdownHide() {
            var count = $(".uploadCount").text()-1;
            $(".uploadCount").text(count);

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

        function sendFile(id, file) {
            //need to have a common api to upload ---getFileUri(value["id"])
            uri = config.storage.sendFile();

	        var xhr = new XMLHttpRequest();
            xhr.open("POST", uri, true);
            var fd = new FormData();

            if (xhr.upload) {
                // Overlay uploader
                var templateContent = $("#fileUploadTemplate").html();
                var template = kendo.template(templateContent);
                var data = [
                    { name: file.name, id: id }
                ];
                var result = kendo.render(template, data);
                $(".perc").append(result);


                // Dropdown uploader
                var templateContent = $("#fileUploadDropdownTemplate").html();
                var template = kendo.template(templateContent);
                var data = [
                    { name: file.name, id: id }
                ];
                var result = kendo.render(template, data);
                $(".percDropdown").append(result);


                xhr.upload.addEventListener("progress", function(e) {
                    var pc = parseInt(e.loaded / e.total * 100);
                    $(".upload-status-progress", ".u_" + id).css("width", pc);
                }, false);


                xhr.onreadystatechange = function(e) {
                    if (xhr.readyState == 4) {
                        $(".upload-status-progress", ".u_" + id).css("width", "100%");

                        uploadDropdownHide();
                    }
                };

                $(".perc").on("click", ".uploaderRemove", function() {
                    xhr.abort();
                });


                fd.append('files', file);
                fd.append('id', id);
                fd.append('name', file.name);
                fd.append('filename', file);
                xhr.send(fd);
            }

            // if remote save success
            return true;
        }

        function addThumb(file, res) {
            loadImage(
                file.rawFile,
                function (img) {
                    $.ajax({ type: "POST", url: '/fm/thumb/' + res.id + '/', data: {data: img.toDataURL().replace(/data:image\/png;base64,/, '')} })
                        .done(function(){ MediaboxFunctions.addFileToFS(res); })
                },
                {
                    maxHeight: 80,
                    canvas: true
                }
            )
        }

        function upload(file) {
            var file;

            var extension = MediaboxFunctions.getExtension(file.name);
            var type = MediaboxFunctions.getType(extension);

            $.ajax({ type: "GET", url: '/fm/upload/', dataType: "JSON", data: "file=" + file.name + "&size=" + file.size})
                .done(function(res) {
                    if (type == "image")
                        addThumb(file, res);

                    if (!sendFile(res.id, file.rawFile)) {
                        //removeFile(file.name);
                    } else {
                        if (type != "image") {
                            MediaboxFunctions.addFileToFS(res);
                        }
                    }
                });
        }

        $(".perc").on("click", ".uploaderRemove", function(){
            MediaboxFunctions.removeFileByName($(this).attr("data-id"));
            $(this).closest(".k-upload-files").remove();

            uploadDropdownHide();
        })
    });
})