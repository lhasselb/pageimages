/**
 * File: PageImages.js
 */

(function($) {
    $.entwine(function($) {
            /**
             * Class: select.dropdown.sorter
             *
             * Sort list by selected image attribute
             */
            $('select.dropdown.sorter').entwine({
                onchange: function() {
                    var sorter = this.find(":selected").val().toLowerCase();
                    //console.log('On change sorter = ' + sorter);

                    var lis = $('ul.ss-uploadfield-files.files li');
                    //jQuery.each( lis, function( i, element ) {console.log(i + ' ' + $(element).data(sorter));});

                    lis.sort(function(a,b) {
                        return ($(b).data(sorter)) < ($(a).data(sorter)) ? 1 : -1;
                    });

                    $('ul.ss-uploadfield-files.files').html(lis);

                    this._super();
                }
            });
            /**
             * Class: div.TreeDropdownField.treedropdown.single.searchable.searchable
             *
             * Update UploadField label item name
             */
            /*
            $('div.TreeDropdownField.treedropdown.single.searchable.searchable').entwine({
                onchange: function() {
                    var title = this.find('span.treedropdownfield-title').text();

                    $('div.ss-uploadfield-item-info label.ss-uploadfield-item-name small').each(function(){
                        var pattern = /^\(saves into \/(.*?)(\/?)\)$/ig;
                        $(this).text($(this).text().replace(pattern,'\(saves into \/' + title + '$2\)' ));
                    });

                    this._super();
                }
            });
            */
    });
}(jQuery));
