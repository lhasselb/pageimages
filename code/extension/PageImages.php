<?php

/**
 * Extension to enable images on a DataObject.
 * ========================================
 * This class adds image support to DataObjects, allowing you to tick "Show tab images on this page"
 * under the settings pane.
 * Decorate DataObject with a booloan (is image tab enabled?)
 * and an enum for a sort and sort direction dropdown as i18Enum.
 * Enum has been replaced by i18nEnum to enable translation.
 * Use 'ShowImages' => 'Boolean(1)' for enabling images by default.
 *
 * @author guggelimehl [at] gmail.com
 * @package pageimages
 */
class PageImages extends DataExtension
{

    // Add 3 columns to table page (ShowImages, Sorter)
    private static $db = array(
        // Store if images tab should be shown
        'ShowImages' => 'Boolean(1)',
        // Store sort attribute used
        'Sorter' => 'i18nEnum("SortOrder, Title, Name, ID, ImageSize")',
        // Store sort direction
        'SorterDir' => 'i18nEnum("ASC, DESC")'
    );

    // Add a column to table page (FolderID)
    private static $has_one = array(
        // Store selected folder
        'Folder' => 'Folder'
    );

    // Create a relation table page_images
    private static $many_many = array(
        // Multiple images in several places
        'Images' => 'Image'
    );

    /**
     * Add the SortOrder field to the relation table for SortableUploadField.
     * DO NOT CHANGE the field name SortOrder (required by sortablefile)!
     * Please note that the key (in this case 'Images')
     * has to be the same key as in the $many_many definition!
     */
    private static $many_many_extraFields = array(
        // Store sorting
        'Images' => array(
            'SortOrder' => 'Int'
        )
    );

    /**
     * @config @var string upload folder name used to store/load images
     */
    private static $upload_folder_name = "Uploads";

    /**
     * @config @var int max number of images allowed
     */
    private static $image_count_limit = 15;

    /**
     * @config @var bool is image upload possible?
     */
    private static $can_upload = true;

    /**
     * @config @var array list of allowed extensions
     */
    private static $allowed_extensions = [];
    // Empty because we're defaulting to category image

    /**
     * @config @var int max file size for images
     */
    private static $allowed_max_file_size = 1048576;
    // 1 MB in bytes;

    /**
     * Add an additional tab in the CMS interface
     *
     * @param
     *            FieldSet
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->ShowImages) {

            Requirements::css(PAGEIMAGES_DIR . '/css/pageimages.css');
            Requirements::javascript(PAGEIMAGES_DIR . '/javascript/PageImages.js');

            // Obtain selected folder ID - if nothing selected yet -> 0 !
            $selectedFolderPathNameId = $this->owner->Folder()->ID;

            // Obtain folder name
            $upload_folder_name = Config::inst()->get('PageImages', 'upload_folder_name');

            // Obtain configured limit from configuration or fallback to 5
            $image_count_limit = Config::inst()->get('PageImages', 'image_count_limit');

            // Obtain if images can be uploaded (if not they can be selected only)
            $can_upload = Config::inst()->get('PageImages', 'can_upload');

            // Obtain alowed image extensions
            $allowed_extensions = Config::inst()->get('PageImages', 'allowed_extensions');

            // Obtain max alowed file size for image uploads
            $allowed_max_file_size = Config::inst()->get('PageImages', 'allowed_max_file_size');

            // Use SortableUploadField instead of UploadField (if available)!
            $uploadClass = (class_exists("SortableUploadField") && $this->owner->Sorter == "SortOrder") ? "SortableUploadField" : "UploadField";

            // Create a sortable uploadfield called imageField with an translateable name (default name "Images")
            $imageField = $uploadClass::create('Images', _t("PageImages.IMAGESUPLOADLABEL", "Images"));

            // Obtain user selected folder
            if ($selectedFolderPathNameId != 0) {
                // Trim leading "/assets/" for selected folder
                $selectedFolderPathName = ltrim($this->owner->Folder()->getRelativePath(), '/assets/');
                // Not 0, use selected folder
                $imageField->setFolderName($selectedFolderPathName);
                $imageField->setDisplayFolderName($selectedFolderPathName);
                // No folder selected yet, check configured folder
            } else {
                // No configuration set? default to Uploads
                $imageField->setFolderName($upload_folder_name);
                $imageField->setDisplayFolderName($upload_folder_name);
            }

            // Set configuration parameter "allowedMaxFileNumber" to $image_count_limit
            $imageField->setConfig('allowedMaxFileNumber', $image_count_limit);

            // Set can upload
            if ($can_upload == '0' || $can_upload == false)
                $imageField->setCanUpload(false);

                // Set allowed file type(s) to category image
            $imageField->setAllowedFileCategories('image');
            // Further limiting if set
            if (! empty($allowed_extensions))
                $imageField->getValidator()->allowedExtensions = $allowed_extensions;

            // Set allowed max filesize
            $imageField->getValidator()->setAllowedMaxFileSize($allowed_max_file_size);
            // Replace an existing file rather than renaming the new one.
            $imageField->getUpload()->setReplaceFile(true);
            // Warning before overwriting existing file (only relevant when Upload: replaceFile is true)
            $imageField->setOverwriteWarning(true);
            // Add a description to be displayed
            $imageField->setDescription(_t("PageImages.IMAGESUPLOADLIMIT", "Up to {count} images ({extensions}) with a max. size of {size} MB per file.", array(
                'count' => $image_count_limit,
                'extensions' => implode(",", $imageField->getAllowedExtensions()),
                'size' => $allowed_max_file_size / 1024 / 1024
            )));
            // Alter the editable fields
            $imageField->setFileEditFields('getCustomFields');

            // Create a dropdown using Sorter
            $dropdownSorter = DropdownField::create('Sorter', _t("PageImages.IMAGESSORTER", "Sort imags by: "))->setSource($this->owner->dbObject('Sorter')->enumValues($this->class));
            // Add additional class for jquery selector
            $dropdownSorter->addExtraClass('sorter');
            // Add additional class to hide (dropdownSorter) div
            if ($this->owner->Images()->count() < 2) {
                $dropdownSorter->addExtraClass('hidden');
            }

            // Create a dropdown using SorterDir
            $dropdownSorterDir = DropdownField::create('SorterDir', _t("PageImages.IMAGESSORTERDIR", "Sort direction: "))->setSource($this->owner->dbObject('SorterDir')->enumValues($this->class));
            // Add additional class for jquery selector
            $dropdownSorterDir->addExtraClass('sorterdir');
            // Add additional class to hide (dropdownSorterDir) div
            if ($this->owner->Images()->count() < 2 || $this->owner->Sorter == "SortOrder") {
                $dropdownSorterDir->addExtraClass('hidden');
            }

            // Show a notice about SortabeUploadField
            if ($this->owner->Sorter == "SortOrder") {
                $imageNotice = (class_exists("SortableUploadField")) ? ""/*_t("PageImages.IMAGESNOTICE", "<span style='color: green'>Sort images by draging thumbnail</span>")*/ :
                _t("PageImages.IMAGESNOTICEWRONG", "<span style='color: red'>Sorting images by draging thumbnails (SortOrder) not allowed. Missing module SortabeUploadField.</span>");
            } else {
                $imageNotice = ""; // _t("PageImages.IMAGESSORTERNOTICE", "Correct image sorting is visible on frontend only (if Sort by = Title, ID, Name)");
            }

            // Important: Use propertyID as reference name to store the selected value
            // Info: A click on the selected folder within the interface will reset or better unset!
            $selectFolderTreedropdown = new TreeDropdownField('FolderID', _t("PageImages.CHOOSEIMAGEFOLDER", "Choose Image Folder"), 'Folder');

            // Create a translatable tab title
            $imageTabTitle = 'Images';
            // Create a translatable tab header
            $imageTabHeader = _t("PageImages.IMAGETAB", "Images");
            // Create reference for fields added down below
            $imageTab = "Root." . $imageTabTitle . "";

            // Create a new tab and place it after Main tab
            $fields->insertAfter(new Tab($imageTabTitle, $imageTabHeader), 'Main');

            // Add treedropdown to the tab
            $fields->addFieldToTab($imageTab, $selectFolderTreedropdown);
            // Add a image notice to the tab
            $fields->addFieldToTab($imageTab, HeaderField::create('ImagesNotice', $imageNotice)->setHeadingLevel(4));
            // Add dropdownsorter to the tab
            $fields->addFieldToTab($imageTab, $dropdownSorter);
            // Add dropdownsorter direction to the tab if not SortOrder (manual sort)
            $fields->addFieldToTab($imageTab, $dropdownSorterDir);

            // Add the sortableuploadfield to the tab
            $fields->addFieldToTab($imageTab, $imageField);
        }
    }

    /**
     * Updates the Image.Size database column of image objects when page is saved
     *
     * @return void
     */
    function onAfterWrite() {
        parent::onAfterWrite();
        // Update Image.Size database fields of all images assigned to actual page if image sort option is set "Size"
        if ($this->owner->Sorter == "ImageSize")
        {
            PageImage::writeSize($this->owner->Folder()->ID);
        }
    }

    function onBeforeWrite() {
        parent::onBeforeWrite();
        // Set default Sorter if all images have been removed
        if ($this->owner->Images()->count() == 0)
        {
            $this->owner->Sorter = "SortOrder";
            $this->owner->SorterDir = "ASC";
        }
    }

    /**
     * updateSettingsFields add a field to the CMS interface
     *
     * @param FieldList $fields
     * @return fields
     */
    public function updateSettingsFields(FieldList $fields)
    {
        $images_group = FieldGroup::create(CheckboxField::create("ShowImages", _t("PageImages.SHOWIMAGES", "Show tab images on this page?")))->setTitle(_t("PageImages.IMAGETAB", "Images"));

        $fields->addFieldToTab("Root.Settings", $images_group);

        return $fields;
    }

    /**
     * Returns sorted list of images
     *
     * @return ManyManyList
     */
    public function SortedImages()
    {
        /*
        SS_Log::log("sorter = ".$this->owner->Sorter, SS_Log::WARN);
        SS_Log::log("sorterdir = ".$this->owner->SorterDir, SS_Log::WARN);
        SS_Log::log("Images = ".$this->owner->Images()->count(), SS_Log::WARN);
        $images = $this->owner->Images()->Sort($this->owner->Sorter,$this->owner->SorterDir);
        foreach($images as $image)
        {
            SS_Log::log("Image Title= ".$image->getTitle().' Name='.$image->Name.' ID='.$image->ID.' Size='.$image->ImageSize.' Size='.$image->getSize(), SS_Log::WARN);
        }
        */
        if($this->owner->Sorter == "SortOrder")
        {
            return $this->owner->Images()->Sort($this->owner->Sorter);
        }
        else
        {
            return $this->owner->Images()->Sort($this->owner->Sorter,$this->owner->SorterDir);
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
}

// EOF
