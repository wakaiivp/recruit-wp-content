/**
 * admin js script
 * @package Weluka
 * @since Weluka Theme 00 1.0
 * 　
 */
(function ($, gobj) {
	'use strict';

	//private
	var _welukaThemeAdmin = {
		/**
		 * @since 1.0
		 */
		_init : function() {
			_welukaThemeAdmin._pageTemplate();
		},
		
		/**
		 * @since 1.0
		 */
		_pageTemplate : function() {
			var $ptemplate_select = $('select#page_template'),
			$ptemplate_box = $('#weluka_ptemp_meta');
		
			$ptemplate_select.on('change', function(){
				var this_value = jQuery(this).val();
				$ptemplate_box.find('.inside > div').css('display','none');
		
				switch ( this_value ) {
					case 'page-blog.php':
						$ptemplate_box.find('.weluka_pt_blog').css('display','block')
						break;
					default:
                		$ptemplate_box.find('.weluka_pt_info').css('display','block');
				}
			});	
			$ptemplate_select.trigger('change');
		}
   	};
    
    $(document).ready(function(){
    	_welukaThemeAdmin._init();
	});				
}(jQuery, this));
