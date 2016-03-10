<?php
/**
 *
 * Extends SilverStripe page object to provide additional images.
 *
 * @package pageimages
 * @subpackage extension
 * @author      [SYBEHA] (http://sybeha.de)
 * @copyright   [SYBEHA]
 * @license     MIT-style license http://opensource.org/licenses/MIT
 *
 */
class PageImages_PageExtension extends DataExtension
{

    // Add 5 fields (columns to [OWNER] table)
    private static $db = array(
        // Store if additional images tab should be enabled
        "ShowImages" => "Boolean(1)",
        // Store sort attribute used
        "Sorter" => "i18nEnum('SortOrder, Title, Name, ID, ExifDate, ImageSize')",
        // Store sort direction
        "SorterDir" => "i18nEnum('ASC, DESC')",
        // Store max number of images
        "MaxImages" => "Int(10)",
        // Store if user can upload "external" images
        "CanUpload" => "Boolean(1)",
        // Store if gallery
        "IsGallery" => "Boolean()"
    );

    // Add column FolderID to [OWNER] table
    private static $has_one = array(
        // Store selected folder
        "Folder" => "Folder"
    );

    // Create a relation table [OWNER]_images
    private static $many_many = array(
        // Multiple images in several places
        "Images" => "Image"
    );

    /**
     * Add the SortOrder field to the relation table for SortableUploadField.
     * DO NOT CHANGE the field name SortOrder (required by sortablefile)!
     * Please note that the key (in this case "Images") has to be the same key as in the $many_many definition!
     */
    private static $many_many_extraFields = array(
        // Store sorting
        "Images" => array(
            "SortOrder" => "Int"
        )
    );

    /**
     * @config @var string upload folder name used to store/load images
     */
    private static $upload_folder_name = "Uploads";

    /**
     * @config @var array list of allowed extensions
     */
    private static $allowed_extensions = array();
    // Empty because we"re defaulting to category image

    /**
     * @config @var int max file size for images
     */
    private static $allowed_max_file_size = 1048576;// 1 MB in bytes;

    /**
     * @config @var array list of allowed extensions
     */
    private static $include_galleria_io = true;

    /**
     *
     * {@inheritdoc}
     *
     */
    public function updateCMSFields(FieldList $fields)
    {
        // CSS reference has been moved to config.yml
        // Requirements::css(PAGEIMAGES_DIR . "/css/PageImages.css");
        Requirements::javascript(PAGEIMAGES_DIR . "/javascript/PageImages.js");
        // Only if enabled
        if ($this->owner->ShowImages) {

            // Obtain configured default folder name
            $upload_folder_name = Config::inst()->get("PageImages_PageExtension", "upload_folder_name");
            // Obtain user selected folder - if not selected yet folder ID = 0
            if ($this->owner->Folder()->ID != 0) {
                // Use complete path instead of folder name ($this->owner->Folder()->Name) and remove leading assets/ !
                $upload_folder_name = str_replace("assets/","",$this->owner->Folder()->getRelativePath());
            }
            // Obtain alowed image extensions
            $allowed_extensions = Config::inst()->get("PageImages_PageExtension", "allowed_extensions");
            // Set a fallback if not configured
            if(empty($allowed_extensions)) $allowed_extensions = array("jpg", "jpeg", "gif", "png");
            // Obtain alowed max file size
            $allowed_max_file_size = Config::inst()->get("PageImages_PageExtension", "allowed_max_file_size");

            // Create a sortable uploadfield called imageField with an translateable name (default name "Images")
            $imageField = SortableUploadField::create("Images", _t("PageImages_PageExtension.IMAGES", "Images"));
            // Use selected folder to upload images to
            $imageField->setFolderName($upload_folder_name);
            // Use selected folder to select images from
            $imageField->setDisplayFolderName($upload_folder_name);
            // Set configuration parameter "allowedMaxFileNumber"
            $imageField->setConfig("allowedMaxFileNumber", $this->owner->MaxImages);
            // Set can upload
            $imageField->setCanUpload((bool) $this->owner->CanUpload);
            // Set allowed file type(s), for category image use $imageField->setAllowedFileCategories("image");
            $imageField->getValidator()->allowedExtensions = $allowed_extensions;
            // Set allowed max filesize
            $imageField->getValidator()->setAllowedMaxFileSize($allowed_max_file_size);
            // Replace an existing file rather than renaming the new one.
            $imageField->getUpload()->setReplaceFile(true);
            // Warning before overwriting existing file (only relevant when Upload: replaceFile is true)
            $imageField->setOverwriteWarning(true);
            // Add a description to inform the user about limits
            $imageField->setDescription(_t("PageImages_PageExtension.IMAGESUPLOADLIMIT", "Up to {count} image(s) ({extensions}) with a max. size of {size} MB per file.", array(
                "count" => $this->owner->MaxImages,
                "extensions" => implode(",", $imageField->getAllowedExtensions()),
                "size" => $allowed_max_file_size / 1024 / 1024
            )));

            // Change the editable fields, see PageImage->getCustomFields()
            $imageField->setFileEditFields("getCustomFields");

            // User should be able to attach existing files when upload is diabled - allow access to SilverStripe assets library
            $imageField->setCanAttachExisting(true);
            if (! (bool) $this->owner->CanUpload) {
                // Don't show target filesystem folder on upload field
                $imageField->setCanPreviewFolder(false);
            }

            // Display preselected folder
            if ($this->owner->Folder() && $this->owner->Folder()->ID != 0) {
                $imageField->setTitle(_t("PageImages_PageExtension.IMAGESFOLDER", "Preselected folder: <span style='font-weight: bold;'>{folder}</span>", array(
                    "folder" => $this->owner->Folder()->Name
                ))); // $this->owner->Folder()->Name
                $imageField->addExtraClass('wide-title');
            } else
                $imageField->setTitle("");

            // Create a dropdown using Sorter
            $dropdownSorter = DropdownField::create("Sorter", _t("PageImages_PageExtension.IMAGESSORTER", "Sort imags by: "))->setSource($this->owner->dbObject("Sorter")
                ->enumValues($this->class));
            // Add additional class for jquery/entwine selector
            $dropdownSorter->addExtraClass("sorter");
            // Add additional class to hide (dropdownSorter) div
            if ($this->owner->Images()->count() < 2) {
                $dropdownSorter->addExtraClass("hidden");
            }

            // Create a dropdown using SorterDir
            $dropdownSorterDir = DropdownField::create("SorterDir", _t("PageImages_PageExtension.IMAGESSORTERDIR", "Sort direction: "))->setSource($this->owner->dbObject("SorterDir")
                ->enumValues($this->class));
            // Add additional class for jquery/entwine selector
            $dropdownSorterDir->addExtraClass("sorterdir");
            // Add additional class to hide (dropdownSorterDir) div
            if ($this->owner->Images()->count() < 2 || $this->owner->Sorter == "SortOrder") {
                $dropdownSorterDir->addExtraClass("hidden");
            }

            // Create a tab title
            $imageTabTitle = "Images";
            // Create a translatable tab header
            $imageTabHeader = _t("PageImages_PageExtension.IMAGETAB", "Page Images");
            // Create reference for fields added down below
            $imageTab = "Root." . $imageTabTitle . "";

            // Create a new tab and place it after Main tab
            $fields->insertAfter(new Tab($imageTabTitle, $imageTabHeader), "Main");
            // Add dropdownsorter to the tab
            $fields->addFieldToTab($imageTab, $dropdownSorter);
            // Add dropdownsorter direction to the tab if not SortOrder (manual sort)
            $fields->addFieldToTab($imageTab, $dropdownSorterDir);
            // Add the sortableuploadfield to the tab
            $fields->addFieldToTab($imageTab, $imageField);
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function updateSettingsFields(FieldList $fields)
    {

        // Get the current member
        $member = $this->getMember();

        // Limit user access to settings by permission for "Change site structure" (SITETREE_REORGANISE)
        if (Permission::checkMember($member, 'SITETREE_REORGANISE')) {

            // Create a nested fieldgroup for images
            $images_group = FieldGroup::create(
                CheckboxField::create("ShowImages", _t("PageImages_PageExtension.SHOWIMAGES", "Enable pageimages?")),
                $settings_group = FieldGroup::create(
                    CheckboxField::create("CanUpload", _t("PageImages_PageExtension.CANUPLOAD", "Enable image upload?")),
                    NumericFieldNotZero::create("MaxImages", _t("PageImages_PageExtension.MAXIMAGES", "Number of images per page")),
                    TreeDropdownField::create("FolderID", _t("PageImages_PageExtension.CHOOSEIMAGEFOLDER", "Preselect folder:"),"Folder"),
                    CheckboxField::create("IsGallery", _t("PageImages_PageExtension.ISGALLERY", "Add galleria.io JS"))
                )->addExtraClass("settings")
            )->setTitle(_t("PageImages_PageExtension.IMAGETAB", "Images"));

            if (! $this->owner->ShowImages) {
                $images_group->setRightTitle(_t("PageImages_PageExtension.IMAGETABHINT", "Show tab Images to manage additional page images."));
                $settings_group->addExtraClass("hidden");
            }

            // Add group to Root.Settings
            $fields->addFieldToTab("Root.Settings", $images_group);
        }

        return $fields;
    }

    /**
     * Obtain a Member
     *
     * @param null|int|Member $member
     *
     * @return null|Member
     */
    protected function getMember($member = null)
    {
        if (! $member) {
            $member = Member::currentUser();
        }

        if (is_numeric($member)) {
            $member = Member::get()->byID($member);
        }

        return $member;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    function onAfterWrite()
    {
        parent::onAfterWrite();

        // Update Image.Size database fields of all images assigned to actual page if image sort option is set to "ImageSize"
        if ($this->owner->Sorter == "ImageSize" && $this->owner->Images()->count() > 0) {
            PageImages_ImageExtension::write_size($this->owner->Images());
        }
        // Update Image.ExifDate database fields of all images assigned to actual page if image sort option is set to  "ExifDate"
        if ($this->owner->Sorter == "ExifDate" && $this->owner->Images()->count() > 0) {
            PageImages_ImageExtension::write_exif_dates($this->owner->Images());
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // Reset default Sorter if all images have been removed
        if ($this->owner->Images()->count() == 0) {
            $this->owner->Sorter = "SortOrder";
            $this->owner->SorterDir = "ASC";
        }

        // Extension has been disabled - Clean up!
        if (!$this->owner->ShowImages && $this->owner->Folder()->ID != 0) {
            $this->owner->Sorter = "SortOrder";
            $this->owner->SorterDir = "ASC";
            $this->owner->CanUpload = 1;
            $this->owner->MaxImages = 10;
            $images = $this->owner->Images();
            foreach ($images as $image) {
                $images->remove($image);
            }
            // Attention: Set has_one relation to 0 again
            $this->owner->FolderID = 0;
            // Remove frontend JS
            $this->owner->IsGallery = 0;
        }
    }

    /**
     * Return whether enabled or not
     *
     * @return Boolean
     */
    public function isEnabled()
    {
        return $this->owner->ShowImages;
    }

    /**
     * Returns sorted list of images
     *
     * @return ManyManyList
     */
    public function SortedImages()
    {
        if ($this->owner->Sorter == "SortOrder") {
            return $this->owner->Images()->Sort($this->owner->Sorter);
        } else {
            // Date should be ExifDate
            $sorter = $this->owner->Sorter;
            //$sorter = ($sorter == "Date") ? "ExifDate" : $sorter;
            return $this->owner->Images()->Sort($sorter, $this->owner->SorterDir);
        }
    }

    /**
     * Returns first image only
     *
     * @return PageImages
     */
    public function FirstImage()
    {
        return $this->owner->Images()
            ->Sort($this->owner->Sorter)
            ->limit(1)
            ->First();
    }

    /**
     * TODO: Add sorting for folder based images
     * Returns all images from folder set
     *
     * @return PageImages
     */
    public function AllImagesFromFolder()
    {
        $folder = $this->owner->Folder();
        // SS_Log::log("folder = ".$folder->ID, SS_Log::WARN);
        return $folder ? DataObject::get("Image", "ParentID = '{$folder->ID}'") : false;
    }

    /**
     * Utility to create a JSON string from image properties
     * @param  SSList $images a list of images
     * @param  Int $thumbWidth thumb width
     * @param  Int $thumbHeight thum height
     * @param  Int $imageWidth image width
     * @param  Int $imageHeight image height
     * @return String containing json format
     */
    public function GalleriaData($images,$thumbWidth=60,$thumbHeight=40,$imageWidth=800,$imageHeight=400)
    {
        // CHeck if jonom/silverstripe-focuspoint
        $focuspoint = (class_exists('FocusPointImage')) ? true : false;
        $data = array();
        foreach ($images as $image) {
            $data[] = array(
                'thumb' => $focuspoint ? $image->CroppedFocusedImage($thumbWidth,$thumbHeight)->URL : $image->CroppedImage($thumbWidth,$thumbHeight)->URL,
                'image' => $focuspoint ? $image->CroppedFocusedImage($imageWidth,$imageHeight)->URL : $image->CroppedImage($imageWidth,$imageHeight)->URL,
                'big' => $image->URL,
                'title' => $image->Title,
                'description' => $image->NiceTitle(),
                'layer' => $image->Caption ? $image->Caption : ""
            );

        }
        return Convert::array2json($data);
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function contentcontrollerInit()
    {
        $include_galleria_io = Config::inst()->get("PageImages_PageExtension", "include_galleria_io");
        if((bool)$include_galleria_io && (bool)$this->owner->IsGallery)
        {
            Requirements::javascript("framework/thirdparty/jquery/jquery.min.js");
            Requirements::javascript(PAGEIMAGES_DIR . "/javascript/galleria/galleria-1.4.2.min.js");
            Requirements::javascript(PAGEIMAGES_DIR . "/javascript/galleria/themes/classic/galleria.classic.min.js");
            Requirements::css(PAGEIMAGES_DIR . "/javascript/galleria/themes/classic/galleria.classic.css");
            Requirements::css(PAGEIMAGES_DIR . "/css/Gallery.css");
            // Prepare data for replacing JS variables
            //$vars = array("data" => $this->GalleriaData($this->SortedImages()));
            //Requirements::javascriptTemplate(PAGEIMAGES_DIR . "/javascript/PageImagesGalleria.js",$vars);
            $escapedJson = $this->GalleriaData($this->SortedImages(),60,40,960,300);

            Requirements::customScript(
<<<JS
    var data = $escapedJson;

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
JS

        );
    }

}
// EOF
