<?php

/**
 * Extension to Image object.
 * ========================================
 * Extends SilverStripe image object to provide additional functionality.
 *
 * @author guggelimehl [at] gmail.com
 * @package pageimages
 */
class PageImage extends DataExtension  {

    private static $db = array(
        // Store Image size
        'ImageSize' => 'int',
        // Store HTML Caption
        'Caption' => 'Text'
    );

    private static $belongs_many_many = array(
        'Pages' => 'Page'
    );

    /**
     *
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
