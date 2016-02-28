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
        transition: 'fade',
        height:0.5625,
        lightbox: true,
        swipe: true,
        initialTransition: 'fadeslide',
        show: 0
        /*autoplay: 5000*/
    });
    Galleria.ready(function(){
        this.lazyLoadChunks(5,1000);
    });
