# SilverStripe page image extension

Add images to a DataObject

Useful to add images to all pages or a specific DataObject like BlogPost
(see [Blog](https://github.com/silverstripe/silverstripe-blog.git/ "Blog module") ) using an additional tab in the backend.

Uses a has_one relation to assign a folder containing your images. Offers [UploadField] (https://docs.silverstripe.org/en/3.2/developer_guides/forms/field_types/uploadfield/) sorting using [sortablefile](https://github.com/bummzack/sortablefile) and enum translation from [i18nEnum](https://github.com/unisolutions/silverstripe-i18nenum).

![pageimages Backend](screenshots/pageimages.png "Backend")

## Requirements

    bummzack/sortablefile
    unisolutions/silverstripe-i18nenum


## Configure
This extension relies on [silverstripe configuration](http://doc.silverstripe.com/framework/en/topics/configuration).

You are able to configure the extension globally by using the file /SS_ROOT/pageimages/_config/extensions.yml
OR by adding the some lines to one (or more) specific module(s), e.g. Blog module.
The same applies to the configurable parameters section.

## Examples
By default the extension is enabled for all pages after the installation.
See file "extensions.yml" within "/SS_ROOT/pageimages/_config" folder.

	# ---
	Name: pageimages-settings
	# ---
	.....
	..
	Page:
  	  extensions:
    	- PageImages

### HowTo enable images for blog pages only
To enable the extension for blog pages only please open the file "extensions.yml" within
"/SS_ROOT/pageimages/_config" folder and comment out the mentioned lines above and uncomment the lines below.

  	BlogPost:
  	  extensions:
    	- PageImages

As module specific configuration comment out all within extensions.yml and copy the latter to _config/config.yml within the Blog module.

## Configurable parameter(s)
There are 2 parameters available which can be used in extensions.yml.
If a parameter is not set, default values will be used:

	* ImageCountLimit
	# Set a max amount of images per data object.
	# Default = 5.
	ImageCountLimit: 5

	# Set to 1 to avoid uploading images on instance level
	# thus limit to select existing images only
	# Defaul = nothing set -> upload is allowed
	AvoidImageUpload: 1

