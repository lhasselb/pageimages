<?php

class GalleryPage extends Page
{
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        //$fields->removeByName('Location');
        return $fields;
    }
}

class GalleryPage_Controller extends Page_Controller
{
    /**
     * Inlcudes the CSS and Javascript files required by the cwsoft-foldergallery module
     *
     * @return void
     */
    function init() {
        parent::init();
        Requirements::javascript("framework/thirdparty/jquery/jquery.min.js");
        Requirements::javascript("pageimages/javascript/galleria/galleria-1.4.2.min.js");
        Requirements::javascript("pageimages/javascript/galleria/themes/classic/galleria.classic.min.js");
        Requirements::css("pageimages/javascript/galleria/themes/classic/galleria.classic.css");
        Requirements::css("pageimages/css/Gallery.css");

        Requirements::customScript("
                // Initialize Galleria
                if($('.galleria').length != 0){
                    Galleria.run('.galleria',{
                        variation: 'light',
                        transition: 'flash', //fade,flash,pulse,slide,fadeslide
                        transitionSpeed: 400,
                        _locale: {
                            show_thumbnails: 'Zeige Miniaturbilder',
                            hide_thumbnails: 'Verberge Miniaturbilder',
                            play: 'Starte Diavorführung',
                            pause: 'Pause Diavorführung',
                            enter_fullscreen: 'Öffne Vollbildmodus',
                            exit_fullscreen: 'Schließe Vollbildmodus',
                            popout_image: 'Bild in eigenem Fenster öffnen',
                            showing_image: 'Bild %s von %s'
                        },
                    });
                    Galleria.ready(function() {
                        // Default to Thumbnail view
                        this.$('thumblink').click();
                        // Slideshow speed
                        this.bind('image', function(e) {
                            this.setPlaytime(4000); // 10000 = 10 seconds
                        });
                    });
                }
            ");
    }
}
