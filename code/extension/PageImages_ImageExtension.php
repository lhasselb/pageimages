<?php
/**
 *
 * Extends SilverStripe image object to provide additional functionality.
 *
 * @package pageimages
 * @subpackage extension
 * @author      [SYBEHA] (http://sybeha.de)
 * @copyright   [SYBEHA]
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
class PageImages_ImageExtension extends DataExtension
{

    // Add 3 fields (columns to Image table)
    private static $db = array(
        // Store Image size
        'ImageSize' => 'int',
        // Store HTML Caption
        'Caption' => 'Text',
        // Store Exif date
        'ExifDate' => 'SS_Datetime'
    );

    private static $belongs_many_many = array(
        'Pages' => 'Page'
    );

    /**
     *
     * {@inheritdoc}
     *
     */
    function getCustomFields()
    {
        $fields = new FieldList();
        $fields->push(new TextField('Title', _t('PageImage.TITLE', 'Title')));
        $caption = new TextareaField('Caption', _t('PageImage.CAPTION', 'Caption'));
        $caption->setRightTitle(_t('PageImage.CAPTIONHINT', 'Plain text - no HTML tags.'));
        $fields->push($caption);
        return $fields;
    }

    /**
     * By default Image::canDelete and Image::canEdit
     * do not require admin privileges,
     * so make sure you override the methods in your Image extension class.
     */
    function canEdit($member)
    {
        // Add access for Owner
        if($member->ID == $this->owner->Owner()->ID) return true;
        // WARNING! This affects permissions on ALL images. Setting this incorrectly can restrict
        // access to authorised users or unintentionally give access to unauthorised users if set incorrectly.
        return Permission::check('CMS_ACCESS_AssetAdmin');
    }

    /**
     * By default Image::canDelete and Image::canEdit
     * do not require admin privileges,
     * so make sure you override the methods in your Image extension class.
     */
    function canDelete($member)
    {
        // Add access for Owner
        if($member->ID == $this->owner->Owner()->ID) return true;
        // WARNING! This affects permissions on ALL images. Setting this incorrectly can restrict
        // access to authorised users or unintentionally give access to unauthorised users if set incorrectly.
        return Permission::check('CMS_ACCESS_AssetAdmin');
    }

    /**
     * Creates a nice looking image title from an image filename.
     * Optional order numbers and file extensions are stripped off.
     * Example: "xxx-your-image-description.ext" ==> "Your image description"
     *
     * @return string Image Title created from filename
     */
    public function NiceTitle() {
        if (preg_match('#(\d*-)?(.+)\.(jpg|jpeg|gif|png|tif|tiff)#i', $this->owner->Title, $matches)) {
            return ucfirst(str_replace('-', ' ', $matches[2]));
        }
        return $this->owner->Title;
    }

    /**
     * Creates/updates the Size database column of all image objects.
     *
     * @param integer $parentId
     *            (if set only image objects assigned to this ID are updated)
     * @return void
     */
    public static function writeSize($images = null)
    {
        if (! $images->exists()) return false;
            // write/update Image.Size database columns
        foreach ($images as $image) {
            // get image size
            $size = $image->getAbsoluteSize();
            if ($size != $image->ImageSize) {
                $image->ImageSize = $size;
                $image->write();
            }
        }
    }

    /**
     * Returns EXIF info defined by $field from images (JPEG, TIFF) stored by the camera.
     *
     * @param string $field String with EXIF field to be returned
     * @return EXIF data or null
     */
    public function ExifData($field='DateTimeOriginal')
    {
        // only JPEG and TIFF files contain EXIF data
        $image_extension = strtolower($this->owner->Extension);
        if (! in_array($image_extension, array('jpg', 'jpeg', 'tif', 'tiff')))
        {
            return null;
        }

        // extract requested EXIF field
        $image_path = Director::getAbsFile($this->owner->Filename);
        $exif_data = @exif_read_data($image_path, 'EXIF', false, false);
        $exif_field = isset($exif_data[$field]) ? $exif_data[$field] : null;
        return $exif_field;
    }

    /**
     * Returns converted EXIF DateTimeOriginal.
     *
     * @param string $exifString String containing the EXIF DateTimeOriginal information
     * @return string date as string
     */
    public function ExifDateString($exifString)
    {
      $exifPieces = explode(":", $exifString);
      return $exifPieces[0] . "-" . $exifPieces[1] . "-" . $exifPieces[2] . ":" . $exifPieces[3] . ":" . $exifPieces[4];
    }

    /**
     * Creates/updates the ExifDate database column of all image objects.
     *
     * @param integer $parentId (if set only image objects assigned to this ID are updated)
     * @return void
     */
    public static function writeExifDates($images=null)
    {
        if (! $images->exists()) return false;
        // write/update Image.ExifDate database columns
        foreach ($images as $image) {
            // get exif original storage date if available
            $exif_date = $image->ExifData($field='DateTimeOriginal');
            $exif_date = is_null($exif_date) ? $image->Created : $exif_date;
            if($image->ExifDateString($exif_date) != $image->ExifDate)
            {
                // update database field
                $image->ExifDate = $exif_date;
                $image->write();
            }

        }
    }
}
// EOF
