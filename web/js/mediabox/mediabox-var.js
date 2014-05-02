define(function (require) {
    "use strict";

    var $ = require('jquery');

    require('/js/mediabox/mediabox-general.js');

    $(document).ready(function() {
        if ($("#mediabox-check-type-other").val() == 1)
            $(".check-type[name='check-type-other']").attr('checked', true);
        if ($("#mediabox-check-type-image").val() == 1)
            $(".check-type[name='check-type-image']").attr('checked', true);
        if ($("#mediabox-check-type-video").val() == 1)
            $(".check-type[name='check-type-video']").attr('checked', true);
        if ($("#mediabox-check-type-music").val() == 1)
            $(".check-type[name='check-type-music']").attr('checked', true);
    });

});