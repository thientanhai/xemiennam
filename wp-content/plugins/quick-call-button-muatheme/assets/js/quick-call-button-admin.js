jQuery(window).load(function() {	
	
	// add colour pickers
	jQuery('.colourme').wpColorPicker();
	
/* user clicks button on custom field, runs below code that opens new window */
jQuery('#lv_upload_image_logo_button').click(function() {

/*Thickbox function aimed to show the media window. This function accepts three parameters:
*
* Name of the window: "In our case Upload a Image"
* URL : Executes a WordPress library that handles and validates files.
* ImageGroup : As we are not going to work with groups of images but just with one that why we set it false.
*/
tb_show('Upload a Image', 'media-upload.php?referer=media_page&type=image&tab=library&TB_iframe=true&post_id=0', false);
return false;
});
// window.send_to_editor(html) is how WP would normally handle the received data. It will deliver image data in HTML format, so you can put them wherever you want.

window.send_to_editor = function(html) {
var image_url = jQuery('img', html).attr('src');
jQuery('#lv_upload_image').val(image_url);
tb_remove(); 

}
jQuery("#lv_checkbox_custom_image").change(function() {
    if(this.checked) {
       jQuery("#lv_row_upload_custom_icon").show();
    }
    else{
        
         jQuery("#lv_row_upload_custom_icon").hide();
    }
});	
});
