<?php
/**
 *
 * Extends SilverStripe image object to provide additional functionality.
 *
 * @package pageimages
 * @subpackage extension
 * @author      [SYBEHA] (http://sybeha.de)
 * @copyright   [SYBEHA]
 * @license     MIT-style license http://opensource.org/licenses/MIT
 *
 */
class PageImages_ImageExtension extends DataExtension
{

    // Add 3 fields (columns to Image table)
    private static $db = array(
        // Store Image size
        'ImageSize' => 'int',
        // Store HTML Caption
        'Caption' => 'HTMLText',
        // Store Exif date
        'ExifDate' => 'SS_Datetime'
    );

    private static $belongs_many_many = array(
        'Pages' => 'Page'
    );

    /**
     * @config @var int max width for uploaded image
     */
    public static $max_width = 1600;

    /**
     * @config @var int max height for uploaded image
     */
    public static $max_height = 1200;

    /**
     * @config @var boolean respect exif orienttaion
     */
    public static $exif_rotation = true;

    /**
     *
     * {@inheritdoc}
     *
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        $this->addSize();
        $this->addExifDate();
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function onAfterWrite() {
        parent::onAfterWrite();
        $this->ScaleUpload();
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function getCustomFields()
    {
        $fields = new FieldList();
        $fields->push(new TextField('Title', _t('PageImage.TITLE', 'Title')));
        $caption = new TextareaField('Caption', _t('PageImage.CAPTION', 'Caption'));
        $caption->setRightTitle(_t('PageImage.CAPTIONHINT', 'Plain text - no HTML tags.'));
        $fields->push($caption);
        return $fields;
    }

    /**
     * By default Image::canEdit does not require admin privileges.
     * Limit access to owner and CMS_ACCESS_AssetAdmin.
     *
     * {@inheritdoc}
     */
    public function canEdit($member)
    {
        // Add access for Owner
        if($member->ID == $this->owner->Owner()->ID) return true;
        // WARNING! This affects permissions on ALL images. Setting this incorrectly can restrict
        // access to authorised users or unintentionally give access to unauthorised users if set incorrectly.
        return Permission::check('CMS_ACCESS_AssetAdmin');
    }

    /**
     * By default Image::canDelete does not require admin privileges.
     * Limit access to owner and CMS_ACCESS_AssetAdmin.
     *
     * {@inheritdoc}
     */
    public function canDelete($member)
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
     * @param array $images update only given images
     *
     * @return void
     */
    public static function write_size($images = null)
    {
        if (! $images->exists()) return false;
            // write/update Image.Size database columns
        foreach ($images as $image) {
            // get image size
            $size = $image->getAbsoluteSize();
            if ($size != $image->ImageSize) {
                $image->ImageSize = $size;
                $image->write();
                //SS_Log::log("Done write_size for ".$image->Name,SS_Log::WARN);
            }
        }
    }

    protected function addSize()
    {
        $size = $this->owner->getAbsoluteSize();
        if ($size != $this->owner->ImageSize) {
            $this->owner->ImageSize = $size;
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
        //$image_path = $this->owner->getFullPath();
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
        if (strpos($exifString, "-") === false) {
            $exifPieces = explode(":", $exifString);
            return $exifPieces[0] . "-" . $exifPieces[1] . "-" . $exifPieces[2] . ":" . $exifPieces[3] . ":" . $exifPieces[4];
        } else return $exifString;
    }

    /**
     * Creates/updates the ExifDate database column of all image objects.
     *
     * @param array $images update only given images
     * @return void
     */
    public static function write_exif_dates($images=null)
    {
        if (! $images->exists()) return false;
        // write/update Image.ExifDate database columns
        foreach ($images as $image) {
            // get exif original storage date if available
            $exif_date = $image->ExifData($field='DateTimeOriginal');
            $exif_date = is_null($exif_date) ? $image->Created : $image->ExifDateString($exif_date);
            if($exif_date != $image->ExifDate)
            {
                // update database field
                $image->ExifDate = $exif_date;
                $image->write();
            }
        }
    }

    /**
     * [addExifDate description]
     */
    protected function addExifDate()
    {
        $date = SS_Datetime::now();
        $exif_date = $this->owner->ExifData($field='DateTimeOriginal');
        $exif_date = is_null($exif_date) ? $date : $this->owner->ExifDateString($exif_date);
        if($this->owner->ExifDateString($exif_date) != $this->owner->ExifDate)
        {
            $this->owner->ExifDate = $exif_date;
        }
    }

    /**
     * [getMaxWidth description]
     * @return [type] [description]
     */
    public function getMaxWidth() {
        $w = Config::inst()->get('ScaledUploads', 'max-width');
        return ($w) ? $w : self::$max_width;
    }

    /**
     * [getMaxHeight description]
     * @return [type] [description]
     */
    public function getMaxHeight() {
        $h = Config::inst()->get('ScaledUploads', 'max-height');
        return ($h) ? $h : self::$max_height;
    }

    /**
     * [getAutoRotate description]
     * @return [type] [description]
     */
    public function getAutoRotate() {
        $r = Config::inst()->get('ScaledUploads', 'auto-rotate');
        if ($r === 0 || $r == 'false') return false;
        return self::$exif_rotation;
    }

    /**
     * [ScaleUpload description]
     */
    public function ScaleUpload() {
        $extension = strtolower($this->owner->getExtension());
        if($this->owner->getHeight() > $this->getMaxHeight() || $this->owner->getWidth() > $this->getMaxWidth()) {
            $original = $this->owner->getFullPath();
            $resampled = $original . '.tmp.' . $extension;
            $gd = new GD($original);
            /* Backwards compatibility with SilverStripe 3.0 */
            $image_loaded = (method_exists('GD', 'hasImageResource')) ? $gd->hasImageResource() : $gd->hasGD();
            if ($image_loaded) {
                /* Clone original */
                $transformed = $gd;
                /* If rotation allowed & JPG, test to see if orientation needs switching */
                if ($this->getAutoRotate() && preg_match('/jpe?g/i', $extension)) {
                    $switchorientation = $this->exifRotation($original);
                    if ($switchorientation) {
                        $transformed = $transformed->rotate($switchorientation);
                    }
                }
                /* Resize to max values */
                if ($transformed) {
                    $transformed = $transformed->resizeRatio($this->getMaxWidth(), $this->getMaxHeight());
                }
                /* Overwrite original upload with resampled */
                if ($transformed) {
                    $transformed->writeTo($resampled);
                    unlink($original);
                    rename($resampled, $original);
                }
            }
        }
    }

    /*
     * exifRotation - return the exif rotation
     * @param string $FileName
     * @return int false|angle
     */
    public function exifRotation($file) {
        $exif = @exif_read_data($file);
        if (!$exif) return false;
        $ort = @$exif['IFD0']['Orientation'];
        if (!$ort) $ort = @$exif['Orientation'];
        switch($ort) {
            case 3: // image upside down
                return '180';
            break;
            case 6: // 90 rotate right & switch max sizes
                return '-90';
            break;
            case 8: // 90 rotate left & switch max sizes
                return '90';
            break;
            default:
                return false;
        }
    }

}
// EOF
