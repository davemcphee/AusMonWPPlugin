jQuery(document).ready(function() {
    // Load future dates from http://austin.minutes.city/ajax_getdates.php
    // Set select box to closest future date
    loadDatesIntoListBox();

    // Add listener to #insertURLButton
    insertURLButtonListener();
});

function loadDatesIntoListBox(){
    jQuery.ajax({
        type: "GET",
        url: "http://austin.minutes.city/ajax_getdates.php",
        dataType: "json",
        cache: false,
        success: function(jsonData) {
            jQuery.each(jsonData.message[1].dates, function(k, v) {
                // Fill dates box with all known future dates
                jQuery('#meetingDateSelector')
                    .append(jQuery("<option></option>")
                        .attr("value", v.d_id)
                        .text(v.date));
            });

            // Once filled, let's auto-select the next date
            jQuery.ajax({
                type: "GET",
                url: "http://austin.minutes.city/ajax_getnextmeeting.php",
                dataType: "json",
                cache: false,
                success: function(jsonData){
                    jQuery('option[value="' + jsonData['id'] + '"]').prop('selected', true);
                    // On page load, once correct date is selected, we load agenda items manually.
                    // Later on, an event listener will do this automatically.
                    loadAgendaItemsIntoListBox();

                    // and attach an event listener to take care of manual dropbox changes
                    jQuery('#meetingDateSelector').change(function(){
                        loadAgendaItemsIntoListBox();
                    })
                }
            });
        }
    })
}

function loadAgendaItemsIntoListBox(){
    // Get the date ID from the date list box
    var dateID = jQuery('#meetingDateSelector').val();
    var myURI = 'http://austin.minutes.city/ajax_getitems.php?board_id=1&date_id=' + dateID;

    jQuery.ajax({
        type: "GET",
        url: myURI,
        dataType: "json",
        cache: false,
        success: function(jasonData){
            // empty the agenda items list
            jQuery('#agendaItemSelector').empty();

            // for each agenda item, insert it into select box, including 1st 120 chars of text
            jQuery.each(jasonData.message, function(k, v){
                jQuery("#agendaItemSelector")
                    .append(jQuery("<option></option>")
                        .attr({
                            'value': v.internal_id,
                            'marker_name': v.marker_name
                        })
                        .text(jQuery(v.marker_full_text).text().substring(0,120)));
            })
        }
    })
}

function insertURLButtonListener() {
    // builds a string using selected agendaitem details, turns it into an <a> link with a hidden
    // HTML comment containing parseable data
    jQuery('#insertURLButton').click(function() {
        var $mySelectOption = jQuery('#agendaItemSelector').find('option:selected');
        var $baseURL = 'http://austin.minutes.city/item/' + $mySelectOption.val();
        var $markerName = $mySelectOption.attr('marker_name');
        var $hiddenTag = '<!-- Minutes.City Link: ###' + $mySelectOption.val() + ' -->';
        var $myString = '<a href="' + $baseURL + '">' + $markerName + '</a>' + $hiddenTag;
        tinymce.activeEditor.execCommand('mceInsertContent', false, $myString);
    })
}