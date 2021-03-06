/**
 * File: PageImages.js
 * ========================================
 * Add features to cms backend
 *
 * @package pageimage
 *
 * @author guggelimehl [at] gmail.com
 *
 */

(function($) {
    $.entwine('ss', function($) {

        var sortedList = function (list, attr, dir) {
            // Database ID matches to fileid within UploadField.ss template
            attr = (attr == 'id') ? 'fileid' : attr;
            //console.log('sorter=' + attr + ', dir=' + dir);
            list.sort(function(a,b) {
                //console.log('a=' + $(a).data(attr) + ', b=' + $(b).data(attr));
                // Compare integer
                if(attr == 'fileid') {
                    //console.log('a=' + $(a).data(attr) + ', b=' + $(b).data(attr) );
                    if(dir =='asc') {
                            return ( $(a).data(attr) - $(b).data(attr) );
                    } else {
                            return ( $(b).data(attr) - $(a).data(attr) );
                    }
                }
                // Cast String to integer (example: 480 KB will be casted to 480 )
                else if(attr =='imagesize') {
                    //console.log('a=' + $(a).data(attr) + ', b=' + $(b).data(attr) );
                    if(dir =='asc') {
                            return (parseInt( ($(a).data(attr)).slice(0,-3), 10 ) - parseInt( ($(b).data(attr)).slice(0,-3), 10 ));
                    } else {
                            return (parseInt( ($(b).data(attr)).slice(0,-3), 10 ) - parseInt( ($(a).data(attr)).slice(0,-3), 10 ));
                    }
                // Compare String
                } else {
                    if(dir =='asc') {
                            return (($(b).data(attr)) < ($(a).data(attr)) ? 1 : -1);
                    } else {
                            return (($(b).data(attr)) > ($(a).data(attr)) ? 1 : -1);
                    }
                }
            });
            return list;
        };

        /**
         * Class: #Form_EditForm_Images_Holder ul.ss-uploadfield-files.files
         *
         * Sort list by selected image attribute (title, name, fileID, size) and sort direction (asc, desc)
         */
        $('#Form_EditForm_Images_Holder ul.ss-uploadfield-files.files').entwine({
            onmatch: function() {
                var sorter = $('select.dropdown.sorter').val().toLowerCase();
                var sorterdir = $('select.dropdown.sorterdir').val().toLowerCase();
                var imagesList = $('#Form_EditForm_Images_Holder ul.ss-uploadfield-files.files li');
                this.html(sortedList(imagesList,sorter,sorterdir));
                // Enable drag n drop sorting
                if(sorter == 'sortorder') this._super();
            }
        });

        /**
         * Class: select.dropdown.sorter
         *
         * Sort list on "CHANGE" of selected attribute (title, name, fileID, size)
         */
        $('select.dropdown.sorter').entwine({
            onchange: function() {
                var sorter = this.val().toLowerCase();
                var sorterdir = $('select.dropdown.sorterdir').val().toLowerCase();
                var imagesList = $('#Form_EditForm_Images_Holder ul.ss-uploadfield-files.files li');
                if ( sorter!= 'sortorder') {
                    $('ul.ss-uploadfield-files.files').html(sortedList(imagesList,sorter,sorterdir));
                    $('div.field.dropdown.sorterdir').show();
                    // Disable drag n drop sorting
                    //$(".sortableupload.ss-uploadfield ul.ss-uploadfield-files").sortable("destroy");
                } else {
                    $('div.field.dropdown.sorterdir').hide();
                    // Enable drag n drop sorting
                    $(".sortableupload.ss-uploadfield ul.ss-uploadfield-files").sortable();
                }
            }
        });

        /**
         * Class: select.dropdown.sorterdir
         *
         * Sort list on "CHANGE" of selected sort direction (asc, desc)
         */
        $('select.dropdown.sorterdir').entwine({
            onchange: function() {
                var sorterdir = this.val().toLowerCase();
                var sorter = $('select.dropdown.sorter').val().toLowerCase();
                var imagesList = $('#Form_EditForm_Images_Holder ul.ss-uploadfield-files.files li');
                $('ul.ss-uploadfield-files.files').html(sortedList(imagesList,sorter,sorterdir));
                this._super();
            }
        });

        /**
         * Class: div.ss-upload .ss-uploadfield-files .ss-uploadfield-item
         *
         * Hide/Show sorter and direction dropdown for 0 or 1 image on adding or removing an image
         */
        $('div.ss-upload .ss-uploadfield-files li.ss-uploadfield-item').entwine({
            onadd: function() {
                if($('li.ss-uploadfield-item').length > 1) {
                    $('div.field.dropdown.sorter').show();
                    if($('select.dropdown.sorter').val().toLowerCase()!='sortorder') {
                        $('div.field.dropdown.sorterdir').show();
                    }
                }
                this._super();
            },
            onremove: function() {
                this._super();
                if($('li.ss-uploadfield-item').length-1 < 2 && $('li.ss-uploadfield-item').length-1 > 0) {
                    $('div.field.dropdown.sorter').hide();
                    $('div.field.dropdown.sorterdir').hide();
                }
            }
        });

        /**
         * Class: input#Form_EditForm_ShowImages
         *
         * Hide/Show pageimage settings
         */
        $('input#Form_EditForm_ShowImages').entwine({
            onchange: function() {
                if ($('input#Form_EditForm_ShowImages').is(':checked')) {
                    $('.settings').show();
                } else {
                    $('.settings').hide();
                }
                this._super();
            }
        });

    });
}(jQuery));
