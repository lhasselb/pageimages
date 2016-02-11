<?php

/**
 * Class PageImage
 *
 * Extension to Image object.
 * ========================================
 * Extends SilverStripe image object to provide additional functionality.
 *
 * @package silverstripe
 * @subpackage pageimage
 *
 * @author guggelimehl [at] gmail.com
 *
 */
class PageImage extends DataExtension  {

    // Add 2 columns to Image table
    private static $db = array(
        // Store Image size
        'ImageSize' => 'int',
        // Store HTML Caption
        'Caption' => 'Text'
    );

    /**
     * {@inheritdoc}
     */
    function getCustomFields() {
        $fields = new FieldList();
        $fields->push(new TextField('Title', _t('PageImage.TITLE','Title') ) );
        $caption = new TextareaField('Caption', _t('PageImage.CAPTION','Caption'));
        $caption->setRightTitle(_t('PageImage.CAPTIONHINT','Plain text - no HTML tags.'));
        $fields->push($caption);
        return $fields;
    }

    /**
     * By default Image::canDelete and Image::canEdit
     * do not require admin privileges,
     * so make sure you override the methods in your Image extension class.
     */
    function canEdit($member) {
        // WARNING! This affects permissions on ALL images. Setting this incorrectly can restrict
        // access to authorised users or unintentionally give access to unauthorised users if set incorrectly.
        return Permission::check('CMS_ACCESS_AssetAdmin');
    }

    function canDelete($member) {
        // WARNING! This affects permissions on ALL images. Setting this incorrectly can restrict
        // access to authorised users or unintentionally give access to unauthorised users if set incorrectly.
        return Permission::check('CMS_ACCESS_AssetAdmin');
    }


    /**
     * Creates/updates the Size database column of all image objects.
     *
     * @param integer $parentId (if set only image objects assigned to this ID are updated)
     * @return void
     */
    public static function writeSize($parentId=null) {
        // fetch all requested image objects
        if (is_numeric($parentId)) {
            $images = Image::get()->filter('ParentID', $parentId);
        } else {
            $images = Image::get();
        }

        if (! $images->exists()) return false;

        // write/update Image.Size database columns
        foreach ($images as $image) {
            // get image size
            $size = $image->getAbsoluteSize();
            //SS_Log::log("Image getSize = ".$image->getAbsoluteSize(), SS_Log::WARN);
            //SS_Log::log("Image ImageSize = ".$image->ImageSize, SS_Log::WARN);
            // update database field only when value differs from dtored
            if ($size != $image->ImageSize) {
                $image->ImageSize = $size;
                $image->write();
            }

        }
    }

}

// EOF
