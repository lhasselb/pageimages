<?php

class GalleryPage extends Page
{
    private static $singular_name = 'Gallery';
    private static $description = 'Gallery page using Galleria.io';
    private static $icon = 'pageimages/images/gallery.png';
    private static $db = array();
    private static $has_one = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }
}

class GalleryPage_Controller extends Page_Controller{}
