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

class GalleryPage_Controller extends Page_Controller{}
