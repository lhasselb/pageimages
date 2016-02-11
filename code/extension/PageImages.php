<?php

/**
 * Class PageImages
 *
 * Extension to enable images on a DataObject.
 * ========================================
 * Decorate DataObject with a booloan (is image tab enabled?)
 * and an enum for a sort and sort direction dropdown as i18Enum.
 * Enum has been replaced by i18nEnum to enable translation.
 * Use "ShowImages" => "Boolean(1)" for enabling images by default.
 *
 * @package silverstripe
 * @subpackage pageimages
 *
 * @author guggelimehl [at] gmail.com
 *
 */
class PageImages extends DataExtension
{

    // Add additional fields (columns to [OWNER] table)
    private static $db = array(
        // Store if images tab should be shown
        "ShowImages" => "Boolean(1)",
        // Store sort attribute used
        "Sorter" => "i18nEnum('SortOrder, Title, Name, ID, ImageSize')",
        // Store sort direction
        "SorterDir" => "i18nEnum('ASC, DESC')",
        // Store max number of images
        "MaxImages" => "Int(10)",
        // Store if user can upload "external" images
        "CanUpload" => "Boolean(1)"
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

    // Set field defaults
    private static $defaults = array(
        "ShowImages" => 1,
        "CanUpload" => 1,
        "Sorter" => "SortOrder",
        "CanEditType" => "ASC",
        "MaxImages" => "10"
    );
    /**
     * @config @var string upload folder name used to store/load images
     */
    private static $upload_folder_name = "Uploads";

    /**
     * @config @var array list of allowed extensions
     */
    private static $allowed_extensions = array("jpg", "jpeg", "gif", "png");
    // Empty because we"re defaulting to category image

    /**
     * @config @var int max file size for images
     */
    private static $allowed_max_file_size = 1048576;
    // 1 MB in bytes;

    /**
     * {@inheritdoc}
     */
    public function updateCMSFields(FieldList $fields)
    {

        // CSS reference has been moved to config.yml
        //Requirements::css(PAGEIMAGES_DIR . "/css/PageImages.css");
        Requirements::javascript(PAGEIMAGES_DIR . "/javascript/PageImages.js");

        if ($this->owner->ShowImages) {

            // Obtain selected folder ID - if nothing selected yet -> 0 !
            $selectedFolderPathNameId = $this->owner->Folder()->ID;

            // Obtain folder name
            $upload_folder_name = Config::inst()->get("PageImages", "upload_folder_name");

            // Obtain alowed image extensions
            $allowed_extensions = Config::inst()->get("PageImages", "allowed_extensions");

            // Obtain alowed max file size
            $allowed_max_file_size = Config::inst()->get("PageImages", "allowed_max_file_size");

            // Create a sortable uploadfield called imageField with an translateable name (default name "Images")
            $imageField = SortableUploadField::create("Images", _t("PageImages.IMAGES", "Images"));

            // Obtain user selected folder
            if ($selectedFolderPathNameId != 0) {
                // Trim leading "/assets/" for selected folder
                $selectedFolderPathName = ltrim($this->owner->Folder()->getRelativePath(), "/assets/");
                // Use selected folder to upload images to
                $imageField->setFolderName($selectedFolderPathName);
                // Use selected folder to select images from
                $imageField->setDisplayFolderName($selectedFolderPathName);

            } else
                // No folder selected yet, check configured folder, if no configuration was set use default (Uploads)
            {
                // Use selected folder to upload images to
                $imageField->setFolderName($upload_folder_name);
                // Use selected folder to select images from
                $imageField->setDisplayFolderName($upload_folder_name);
            }

            // Set configuration parameter "allowedMaxFileNumber"
            $imageField->setConfig("allowedMaxFileNumber", $this->owner->MaxImages);
            // Set can upload
            $imageField->setCanUpload((bool)$this->owner->CanUpload);
            // Set allowed file type(s), for category image use $imageField->setAllowedFileCategories("image");
            $imageField->getValidator()->allowedExtensions = $allowed_extensions;
            // Set allowed max filesize
            $imageField->getValidator()->setAllowedMaxFileSize($allowed_max_file_size);
            // Replace an existing file rather than renaming the new one.
            $imageField->getUpload()->setReplaceFile(true);
            // Warning before overwriting existing file (only relevant when Upload: replaceFile is true)
            $imageField->setOverwriteWarning(true);
            // Add a description to inform the user about limits
            $imageField->setDescription(_t("PageImages.IMAGESUPLOADLIMIT", "Up to {count} image(s) ({extensions}) with a max. size of {size} MB per file.", array(
                "count" => $this->owner->MaxImages,
                "extensions" => implode(",", $imageField->getAllowedExtensions()),
                "size" => $allowed_max_file_size / 1024 / 1024
            )));

            // Change the editable fields, see PageImage->getCustomFields()
            $imageField->setFileEditFields("getCustomFields");

            // User should be able to attach existing files when upload is diabled
            if(!(bool)$this->owner->CanUpload) {
                // allow access to SilverStripe assets library
                $imageField->setCanAttachExisting(true);
                // Don't show target filesystem folder on upload field
                $imageField->setCanPreviewFolder(false);
            }

            // Display preselected folder
            if($this->owner->Folder() && $this->owner->Folder()->ID !=0) {
                $imageField->setTitle(_t("PageImages.IMAGESFOLDER", "Preselected folder: ").$this->owner->Folder()->Name);
            } else $imageField->setTitle("");


            // Create a dropdown using Sorter
            $dropdownSorter = DropdownField::create("Sorter", _t("PageImages.IMAGESSORTER", "Sort imags by: "))->setSource($this->owner->dbObject("Sorter")->enumValues($this->class));
            // Add additional class for jquery/entwine selector
            $dropdownSorter->addExtraClass("sorter");
            // Add additional class to hide (dropdownSorter) div
            if ($this->owner->Images()->count() < 2) {
                $dropdownSorter->addExtraClass("hidden");
            }

            // Create a dropdown using SorterDir
            $dropdownSorterDir = DropdownField::create("SorterDir", _t("PageImages.IMAGESSORTERDIR", "Sort direction: "))->setSource($this->owner->dbObject("SorterDir")->enumValues($this->class));
            // Add additional class for jquery/entwine selector
            $dropdownSorterDir->addExtraClass("sorterdir");
            // Add additional class to hide (dropdownSorterDir) div
            if ($this->owner->Images()->count() < 2 || $this->owner->Sorter == "SortOrder") {
                $dropdownSorterDir->addExtraClass("hidden");
            }

            // Create a tab title
            $imageTabTitle = "Images";
            // Create a translatable tab header
            $imageTabHeader = _t("PageImages.IMAGETAB", "Page Images");
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
     * updateSettingsFields add a field to the CMS interface
     *
     * @param FieldList $fields
     * @return fields
     */
    public function updateSettingsFields(FieldList $fields)
    {

        // Get the current member
        $member = $this->getMember();
        /*
        SS_Log::log("Found member name = ".$member->Name.", ID= ".$member->ID,SS_Log::WARN);
        $groups = $member->Groups();
        if($groups) {
            foreach ($groups as $group) {
                SS_Log::log("Group = ".$group->Title." ,code=".$group->Code,SS_Log::WARN);
            }
        }*/
        //Limit user access to settings by permission for "Change site structure" (SITETREE_REORGANISE)
        if (Permission::checkMember($member, 'SITETREE_REORGANISE')) {

            // Create a nested fieldgroup for images
            $images_group = FieldGroup::create(
                FieldGroup::create(CheckboxField::create("ShowImages", _t("PageImages.SHOWIMAGES", "Enable pageimages?"))),
                $settings_group = FieldGroup::create(
                    FieldGroup::create(CheckboxField::create("CanUpload", _t("PageImages.CANUPLOAD", "Enable image upload?"))),
                    FieldGroup::create(NumericField::create("MaxImages", _t("PageImages.MAXIMAGES", "Number of images per page"))),
                    FieldGroup::create(TreeDropdownField::create("FolderID", _t("PageImages.CHOOSEIMAGEFOLDER", "Preselect folder:"), "Folder"))
                )
            )->setTitle(_t("PageImages.IMAGETAB", "Images"));

            if(!$this->owner->ShowImages) {
                $images_group->setRightTitle(_t("PageImages.IMAGETABHINT", "Show tab Images to manage additional page images."));
                $settings_group->addExtraClass("hidden");
            }

            // Add group to Root.Settings
            $fields->addFieldToTab("Root.Settings", $images_group);

        }

        return $fields;
    }

    public function validate(ValidationResult $validationResult) {
        //$field = $this->owner->
        SS_Log::log("validate(ValidationResult) called",SS_Log::WARN);
        SS_Log::log("result ".$this->owner->value,SS_Log::WARN);
        if($this->owner->value <= 1) { return false;}
        return true;

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
        if (!$member) {
            $member = Member::currentUser();
        }

        if (is_numeric($member)) {
            $member = Member::get()->byID($member);
        }

        return $member;
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

    /**
     * Updates the Page.Sorter & SorterDir database column of page objects before page is saved
     *
     * @return void
     */
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
     * Return wheter enabled or not
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
