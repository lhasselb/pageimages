<?php

/**
 * This class adds image support to the CMS, allowing you to tick "Show tab images on this page"
 * under the settings pane. This then adds the Images tab.
 * @author guggelimehl [at] gmail.com
 * @package pageimages
 */
class PageImagesExtension extends DataExtension{

    /**
     * Using i18n enum:
     * Please consider the convention for language.yml which depends
     * on the choosen DataObject.
     * Example using german language for DataObject Page
     * de:
     *   Page:
     *     db_Sorter_SortOrder: 'Displayed order'
     *     db_Sorter_Title: 'Title'
     *     db_Sorter_Name: 'Name'
     *     db_Sorter_FileID: 'ID'
     */
    // Add 2 columns to table page (ShowImages, Sorter)
    private static $db = array(
        // Store if images tab should be shown
        'ShowImages' => 'Boolean(1)',
        // Enum replaced by i18nEnum to enable translation
        'Sorter' => 'i18nEnum("SortOrder, Title, Name, FileID, Size")'
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

    /** Add the SortOrder field to the relation table for SortableUploadField.
     * DO NOT CHANGE the field name SortOrder (required by sortablefile)!
     * Please note that the key (in this case 'Images')
     * has to be the same key as in the $many_many definition!
     */
    private static $many_many_extraFields = array(
        // Store sorting
        'Images' => array('SortOrder' => 'Int')
    );


    /**
     * Add an additional tab in the CMS interface
     *
     * @param FieldSet
     */
    public function updateCMSFields(FieldList $fields) {

	    if ($this->owner->ShowImages) {

            Requirements::javascript(PAGEIMAGES_DIR . '/javascript/PageImages.js');

            // Obtain selected folder ID - if nothing selected yet -> 0 !
            $selectedFolderPathNameId = $this->owner->Folder()->ID;

            // Obtain configured limit from config.yml or extensions.yml or fallback to 5
            $limit =  Config::inst()->get('pageimages-settings', 'ImageCountLimit');
            // Set a default
            if(empty($limit)) $limit = 5;

            // Use SortableUploadField instead of UploadField (if available)!
            $uploadClass = (class_exists("SortableUploadField") && $this->owner->Sorter == "SortOrder") ? "SortableUploadField" : "UploadField";

            // Create a sortable uploadfield called imageField with an translateable name (default name "Images")
            $imageField = $uploadClass::create('Images', _t("PageImages.IMAGESUPLOADLABEL", "Images"));

            // Set allowed file type(s) to category image
            $imageField->setAllowedFileCategories('image');

            // Further limiting to jpg, gif and png
            $imageField->getValidator()->allowedExtensions = array('jpeg','jpg', 'gif', 'png');

            // Get allowed types
            $types = implode(",", $imageField->getAllowedExtensions());

            // Add a description to be displayed
            $imageField->setDescription(_t("PageImages.IMAGESUPLOADLIMIT","Max. {limit} images of either following type: {types}",array('limit' => $limit,'types' => $types)));

            // Set configuration parameter "allowedMaxFileNumber" to $limit
            $imageField->setConfig('allowedMaxFileNumber', $limit);

            // Replace an existing file rather than renaming the new one.
            $imageField->getUpload()->setReplaceFile(true);

            // Warning before overwriting existing file (only relevant when Upload: replaceFile is true)
            $imageField->setOverwriteWarning(true);

            // Obtain configured folder name from config.yml or extensions.yml
            $uploadFolderPathName = Config::inst()->get('pageimages-settings', 'UploadFolderName');

            // Obtain user selected folder
            if($selectedFolderPathNameId != 0) {
                // Trim leading "/assets/" for selected folder
                $selectedFolderPathName = ltrim($this->owner->Folder()->getRelativePath(),'/assets/');
                // Not 0, use selected folder
                $imageField->setFolderName($selectedFolderPathName);
                $imageField->setDisplayFolderName($selectedFolderPathName);
            // No folder selected yet, check configured folder
            } else {
                // No configuration set? default to Uploads
                if(empty($uploadFolderPathName) || $uploadFolderPathName == null) {
                    // Default to /PATH_TO_SS_ROOT/assets/Uploads/CLASSNAME/ID
                    // $uploadFolderPathName = 'Uploads/'.$this->owner->ClassName.'/'.$this->owner->ID;
                    $uploadFolderPathName = 'Uploads/';
                }
                // Set configured or default folder to SortableUploadField
                $imageField->setFolderName($uploadFolderPathName);
                $imageField->setDisplayFolderName($uploadFolderPathName);
            }

            // Obtain if image upload is disabled
            $avoidImageUpload = Config::inst()->get('pageimages-settings', 'AvoidImageUpload');
            // Default 0
            if($avoidImageUpload == '1') {
                $imageField->setCanUpload(false);
            }

            // Create a dropdown using Sorter
            $dropdownSorter = DropdownField::create('Sorter', _t("PageImages.IMAGESSORTER", "Sort imags by: "))->setSource($this->owner->dbObject('Sorter')->enumValues());
            // Add additional class for jquery selector
            $dropdownSorter->addExtraClass('sorter');

            // Show a notice about sorting
            if ($this->owner->Sorter == "SortOrder")  {
                $imageNotice=(class_exists("SortableUploadField")) ?
                ""/*_t("PageImages.IMAGESNOTICE", "<span style='color: green'>Sort images by draging thumbnail</span>")*/ :
                _t("PageImages.IMAGESNOTICEWRONG", "<span style='color: red'>Sorting images by draging thumbnails (SortOrder) not allowed. Missing module SortabeUploadField.</span>");
            } else {
                $imageNotice =  "";//_t("PageImages.IMAGESSORTERNOTICE", "Correct image sorting is visible on frontend only (if Sort by = Title, ID, Name)");
            }

            // Show a notice about the user selected folder
            if($selectedFolderPathNameId == 0) {
                $folderNotice = "";//_t("PageImages.FOLDERNOTICE", "Folder assigned to this page.");
            } else{
                $folderNotice = "";//_t("PageImages.FOLDERNOTICESET", "<span style='color: green'>Choosen Folder ({folder})</span>",array('folder' => $selectedFolderPathName));
            }


            // Important: Use propertyID as reference name to store the selected value
            // Info: A click on the selected folder within the interface will reset or better unset!
            $selectFolderTreedropdown = new TreeDropdownField('FolderID', _t("PageImages.CHOOSEIMAGEFOLDER","Choose Image Folder"),'Folder');

            // Create a translatable tab title
            $imageTabTitle = 'Images';
            // Create a translatable tab header
            $imageTabHeader = _t("PageImages.IMAGETAB","Images");
            // Create reference for fields added down below
            $imageTab = "Root.". $imageTabTitle ."";

            // Create a new tab and place it after Main tab
            $fields->insertAfter(new Tab($imageTabTitle, $imageTabHeader), 'Main');

            // Add a folder notice to the tab
            $fields->addFieldToTab($imageTab, HeaderField::create('FolderNotice', $folderNotice)->setHeadingLevel(4));
            // Add treedropdown to the tab
            $fields->addFieldToTab($imageTab, $selectFolderTreedropdown);
            // Add a image notice to the tab
            $fields->addFieldToTab($imageTab, HeaderField::create('ImagesNotice', $imageNotice)->setHeadingLevel(4));
            // Add dropdownsorter to the tab
            $fields->addFieldToTab($imageTab, $dropdownSorter);
            // Add the sortableuploadfield to the tab
            $fields->addFieldToTab($imageTab, $imageField);
        }
    }

    /**
     * updateSettingsFields add a field to the CMS interface
     * @param  FieldList $fields
     * @return fields
     */
    public function updateSettingsFields(FieldList $fields) {
        $images_group = FieldGroup::create(
                CheckboxField::create("ShowImages", _t("PageImages.SHOWIMAGES", "Show tab images on this page?"))
        )->setTitle(_t("PageImages.IMAGETAB", "Images"));

        $fields->addFieldToTab("Root.Settings", $images_group);

        return $fields;
    }

    /**
     * Returns sorted list of images
     *
     * @return ManyManyList
     */
    public function SortedImages() {
        return $this->owner->Images()->Sort($this->owner->Sorter);
    }

    /**
     * Returns first image only
     *
     * @return PageImages
     */
    public function MainImage() {
        return $this->owner->Images()->Sort($this->owner->Sorter)->limit(1)->First();
    }

    /**
     * Returns all images from folder set
     *
     * @return PageImages
     */
    public function AllImagesFromFolder() {
        $folder = $this->owner->Folder();
        //SS_Log::log("folder = ".$folder->ID, SS_Log::WARN);
        return $folder ? DataObject::get("Image", "ParentID = '{$folder->ID}'") : false;
    }
}

// EOF
