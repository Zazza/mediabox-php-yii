require.config({
    waitSeconds: 30,
    paths: {
        jquery: '/js/libs/jquery-1.10.2.min',
        bootstrap: '/js/libs/bootstrap2.0.4',
        kendo: '/js/kenju',
        mediaelement: '/mediaelement/mediaelement-and-player',

        domready: '/js/require.domready',

        Jcrop: '/js/libs/jquery.Jcrop.min',
        json: '/js/libs/jquery.json-2.4.min',
        swipebox: '/js/libs/jquery.swipebox',
        timer: '/js/libs/jquery.timer',
        loadImage: '/js/libs/load-image',
        shifty: '/js/libs/shift.jquery',
        webtoolkit: '/js/libs/webtoolkit.url',

        mediaboxUploader: '/js/mediabox/mediabox-uploader',

        /*
        * Plugins
         */
        mediaboxImage: '/addons/images/mediabox-image',
        mediaboxPlayer: '/addons/audio/mediabox-player',
        mediaboxVideo: '/addons/video/mediabox-video'
    },

    shim:{
        mediaelement: {
          deps: ['jquery'],
          exports: 'mejs'
        },
        mediaboxImage: {
            deps: ['jquery'],
            exports: 'mediaboxImage'
        },
        mediaboxImageFs: {
          deps: ['jquery'],
          exports: 'mediaboxImageFs'
        },
        mediaboxPlayer: {
          deps: ['jquery'],
          exports: 'mediaboxPlayer'
        },
        mediaboxVideo: {
          deps: ['jquery'],
          exports: 'mediaboxVideo'
        },
        timer: {
            deps: ['jquery'],
            exports: 'timer'
        },
        swipebox: {
            deps: ['jquery', 'timer'],
            exports: 'swipebox'
        },
        bootstrap: {
            deps: ["jquery"],
            exports: "$.fn.popover"
        }
    }
});

require(
    [
        'domready',
        'jquery'
    ],
    function (domReady, $) {
        "use strict";

        domReady(function () {
            var mainModule = null
            if($('script[data-main][data-slug]').data('slug').length > 1) {
                mainModule = $('script[data-main][data-slug]').data('slug');
            }

            console.log('DOM ready', mainModule);

            if (typeof mainModule !== 'undefined' && mainModule !== null) {
                require(["/js/page/" + mainModule + ".js"], function (mainModule) {});
            }
        });
    }
);
