/**
 * File: PageImages.js
 */

(function($) {
    $.entwine('ss', function($) {

        /**
         * Class: select.dropdown.sorter
         *
         * Sort list by selected image attribute
         */
        $('select.dropdown.sorter').entwine({
            onchange: function() {
                var sorter = this.find(":selected").val().toLowerCase();
                var lis = $('ul.ss-uploadfield-files.files li');
                //jQuery.each( lis, function( i, element ) {console.log(i + ' ' + $(element).data(sorter));});
                lis.sort(function(a,b) {
                    return ($(b).data(sorter)) < ($(a).data(sorter)) ? 1 : -1;
                });
                $('ul.ss-uploadfield-files.files').html(lis);
                //this._super();
            }
        });

        /**
         * Class: select.dropdown.sorterdir
         *
         * Sort list by selected sort direction
         */
        $('select.dropdown.sorterdir').entwine({
            onchange: function() {
                var sorter = $('select.dropdown.sorter').find(":selected").val().toLowerCase();
                var sorterdir = this.find(":selected").val().toLowerCase();
                var lis = $('ul.ss-uploadfield-files.files li');
                //jQuery.each( lis, function( i, element ) {console.log(i + ' ' + $(element).data(sorter));});
                lis.sort(function(a,b) {
                    if(sorterdir == 'asc') {
                        return ($(b).data(sorter)) < ($(a).data(sorter)) ? 1 : -1;
                    } else return ($(b).data(sorter)) > ($(a).data(sorter)) ? 1 : -1;
                });



                $('ul.ss-uploadfield-files.files').html(lis);
                //this._super();
            }
        });

        /**
         * Class: div.TreeDropdownField.treedropdown.single.searchable.searchable
         *
         * Update UploadField label item name
         */
        $('div.TreeDropdownField.treedropdown.single.searchable.searchable').entwine({
            onchange: function() {
                var selectedFolder = this.find('span.treedropdownfield-title').text();
                var pattern = /\/(.*?)\)$/;
                var labelFolder = $('label.ss-uploadfield-item-name small').html();
                var updatedLabelFolder = labelFolder.replace(pattern,'\/' + selectedFolder + '\)');
                //console.log('updated = ' + updated );
                this._super();

            }
        });

        /**
         * Class: #Form_EditForm_Sorter option:selected
         *
         * Hide sort direction on manual sort otption
         */
        $('#Form_EditForm_Sorter option:selected').entwine({
            onmatch: function() {
                //console.log('match ' + this.val());
                if(this.val() == 'SortOrder') {
                    $('#Form_EditForm_SorterDir_Holder').hide();
                } else {
                    $('#Form_EditForm_SorterDir_Holder').show();
                }
                this._super();
            }
        });

        /**
         * Class: div.ss-upload
         *
         * Hide sorter field(s) for 0 or 1 image on page load
         */
        $('div.ss-upload').entwine({
            onmatch: function() {
                this._super();
                if($('li.ss-uploadfield-item').length < 2) {
                    $('div.field.dropdown.sorter').hide();
                } else {
                    $('div.field.dropdown.sorter').show();
                }
            },
        });

        /**
         * Class: div.ss-upload .ss-uploadfield-files .ss-uploadfield-item
         *
         * Hide sorter field(s) for 0 or 1 image on adding or removing an image
         */
        $('div.ss-upload .ss-uploadfield-files .ss-uploadfield-item').entwine({
            onadd: function() {
                //console.log('Add ' + $('li.ss-uploadfield-item').length);
                if($('li.ss-uploadfield-item').length < 2) {
                    $('div.field.dropdown.sorter').hide();
                } else {
                    $('div.field.dropdown.sorter').show();
                }
                this._super();
            },
            onremove: function() {
                //console.log('Remove' + ( $('li.ss-uploadfield-item').length-1 ));
                if($('li.ss-uploadfield-item').length -1 < 2) {
                    $('div.field.dropdown.sorter').hide();
                } else {
                    $('div.field.dropdown.sorter').show();
                }
                this._super();
            }
        });

    });
}(jQuery));
