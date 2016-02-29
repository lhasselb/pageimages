/**
 * File: PageImagesGalleria.js
 * ========================================
 * Add features to cms backend
 *
 * @package pageimage
 *
 * @author guggelimehl [at] gmail.com
 *
 */

/**
 * JSON data for Galleria images
 * @type {Array}
 */
    var data = [
        $data
    ];

/**
 * Run Galleria witth options
 * See all options on http://galleria.io/docs/options/#list-of-options
 * @type {[type]}
 */
    Galleria.run('#galleria', {
        dataSource: data,
        thumbnails: 'lazy',
        responsive: true,
        imageCrop: true,
        thumbCrop: "height",
        transition: 'none', /*fade*/
        easing: 'galleriaOut',
        // Setting a relative height (16/9 ratio)
        height:0.5625,
        lightbox: true,
        swipe: true,
        initialTransition: 'fadeslide',
        show: 0,
        showInfo: false,
        _hideDock: Galleria.TOUCH ? false : true,
        //maxScaleRatio: 1,
        /*autoplay: 5000*/
        extend: function(options) {
            var gallery = this;
            $('#play').click(function() {
                //console.log('play clicked');
                gallery.playToggle();
            });
        }
    });
    Galleria.ready(function(){
        this.lazyLoadChunks(5,1000);
    });
