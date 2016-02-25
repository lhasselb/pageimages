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
     * Inlcudes the CSS and Javascript files required by galleria.io
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
            Galleria.run('#galleria', {
                responsive: true,
                imageCrop: true,
                transition: 'fade',
                height:0.7,
                lightbox: true,
                swipe: true,
                show: 0,
                autoplay: 5000
            });
        ");
    }
}
