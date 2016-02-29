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
 * @type {Galleria}
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
        /*autoplay: 5000*/

    });
    Galleria.ready(function(){
        this.lazyLoadChunks(10,1000);
        /*
        this.addElement('play');
        this.appendChild('stage','play');
        var btn = this.$('play').css('color', 'white').text('PAUSE').click(function() { gallery.playToggle(); });
        this.bind('play', function() {
           btn.text('PLAY');
           btn.addClass('playing');
        }).bind('pause', function() {
           btn.removeClass('playing');
        });
           this.addIdleState(this.get('play'), { opacity:0 });
        */
    });
