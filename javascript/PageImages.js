/**
 * File: PageImages.js
 */

(function($) {
    $.entwine('ss', function($) {

        var sortedList = function (list, attr, dir) {
            //console.log('sorter=' + attr + ', dir=' + dir);
            list.sort(function(a,b) {
                // Compare integer
                if(attr =='size') {
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
         * Class: select.dropdown.sorter
         *
         * Sort list by selected image attribute (title, name, fileID, size)
         */
        $('select.dropdown.sorter').entwine({
            onchange: function() {
                var sorter = this.val().toLowerCase();
                var sorterdir = $('select.dropdown.sorterdir').val().toLowerCase();
                if ( sorter!= 'sortorder') {
                    var imagesList = $('ul.ss-uploadfield-files.files li');
                    $('ul.ss-uploadfield-files.files').html(sortedList(imagesList,sorter,sorterdir));
                    $('div.field.dropdown.sorterdir').show();
                } else {
                    $('div.field.dropdown.sorterdir').hide();
                }
                //this._super();
            }
        });

        /**
         * Class: select.dropdown.sorterdir
         *
         * Sort list by selected sort direction (asc, desc)
         */
        $('select.dropdown.sorterdir').entwine({
            onchange: function() {
                var sorterdir = this.val().toLowerCase();
                var sorter = $('select.dropdown.sorter').val().toLowerCase();
                var imagesList = $('ul.ss-uploadfield-files.files li');
                $('ul.ss-uploadfield-files.files').html(sortedList(imagesList,sorter,sorterdir));
                //this._super();
            }
        });

        /**
         * Class: div.ss-upload .ss-uploadfield-files .ss-uploadfield-item
         *
         * Hide sorter field(s) for 0 or 1 image on adding or removing an image
         */
        $('div.ss-upload .ss-uploadfield-files li.ss-uploadfield-item').entwine({
            onadd: function() {
                //console.log('Add ' + $('li.ss-uploadfield-item').length);
                if($('li.ss-uploadfield-item').length > 1) {
                    $('div.field.dropdown.sorter').show();
                }
                this._super();
            },
            onremove: function() {
                this._super();
                //console.log('Remove' + ( $('li.ss-uploadfield-item').length-1 ));
                if($('li.ss-uploadfield-item').length -1 < 2 && $('li.ss-uploadfield-item').length -1 > 0) {
                    $('div.field.dropdown.sorter').hide();
                }
            }
        });

    });
}(jQuery));
