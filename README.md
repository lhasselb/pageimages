
# SilverStripe page image extension

Add images to a DataObject

Useful to add images to all pages or a specific DataObject like BlogPost
(see [Blog](https://github.com/silverstripe/silverstripe-blog.git/ "Blog module") ) using an additional tab in the backend.

Uses a has_one relation to assign a folder containing your images. Offers [UploadField] (https://docs.silverstripe.org/en/3.2/developer_guides/forms/field_types/uploadfield/) sorting using [sortablefile](https://github.com/bummzack/sortablefile).

![pageimages Backend](screenshots/pageimages.png "Backend")

## Requirements

    bummzack/sortablefile


## Configuration
By default the extension is enabled for DataObject Page.
You can add the extension to other modules like the Blog module by changing the configuration.
See file "extensions.yml" within "/SS_ROOT/pageimages/_config" folder.
```
# ---
Name: pageimages-settings
# ---
Page:
  extensions:
    - PageImages
```
Read more about [silverstripe configuration](http://doc.silverstripe.com/framework/en/topics/configuration).

### HowTo enable images for blog pages
Add the following to your config.yml within "/SS_ROOT/mysite/_config"
```
BlogPost:
  extensions:
    - PageImages
```
You'll also need to run `dev/build`.


## Configurable parameter(s)
There are parameters available which can be used in extensions.yml.
If a parameter is not set, default values will be used:
```
# Set a max amount of images .
# Default = 5.
#  image_count_limit: 10

# Set a specific folder name to upload to
# Default = Uploads
#  upload_folder_name: 'UploadFolderName'

# Set to false to avoid uploading images
# Default = true
#  can_upload: false

# Set allowed extensions for images
# Default limited to types known by file category image
#  allowed_extensions:
#    - jpg
#    - jpeg
#    - gif
#    - png

# Set allowed maximum filesize for images
# Default = 1048576 = 1MB (1* 1024 * 1024)
#  allowed_max_file_size: 1048576
```
