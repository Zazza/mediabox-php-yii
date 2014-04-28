define(function (require) {
    "use strict";

    var $ = require('jquery');

    require('mediabox/app/mediabox/mediabox-general');

    $(document).ready(function() {
        if ($("#mediabox-check-type-other").val())
            $(".check-type[name='check-type-other']").attr('checked', true);
        if ($("#mediabox-check-type-image").val())
            $(".check-type[name='check-type-image']").attr('checked', true);
        if ($("#mediabox-check-type-video").val())
            $(".check-type[name='check-type-video']").attr('checked', true);
        if ($("#mediabox-check-type-music").val())
            $(".check-type[name='check-type-music']").attr('checked', true);
    });

});