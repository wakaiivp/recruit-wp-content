/*---------------------------------------------
 * jQuery Fix Height Simple 2.0 - 2015-06-09
---------------------------------------------*/

jQuery.fn.fixHeightSimple = function(options){
	
	//オプション
	options = jQuery.extend({
		column : 0,
		responsive : false,
		responsiveParent : "body",
		boxSizingBorderBox : false
	}, options);
	
	var elm = this;
	options.responsiveParent = jQuery(options.responsiveParent);
	
	if(jQuery(elm).size() > 0){
		jQuery(window).on("load resize", function(){
			if(options.boxSizingBorderBox){
				var responsiveParentWidth = options.responsiveParent.outerWidth(),
					columnWidth = elm.eq(0).outerWidth();
			}else{
				var responsiveParentWidth = options.responsiveParent.width(),
					columnWidth = elm.eq(0).width();
			}
			if(options.responsive){
				options.column = Math.floor(responsiveParentWidth / columnWidth);
			}
			if(options.column != 1){
				var tgHeight = new Array(120); //Array([アーカイブの最大表示件数])
				var cnt = 0;
				var maxHeight = 0;
				elm.css("height","auto");
				elm.each(function(){
					if(options.boxSizingBorderBox){
						tgHeight[cnt] = jQuery(this).outerHeight();
					}else{
						tgHeight[cnt] = jQuery(this).height();
					}
					if(tgHeight[cnt] > maxHeight){
						maxHeight = tgHeight[cnt];
					}
					if(options.column){
						if(cnt !=0 && ((cnt+1) % options.column) == 0){
							for(var i = cnt - options.column; i < cnt; i++){
								elm.eq(i + 1).css("height",maxHeight + "px");
							}
							maxHeight = 0;
						}
					}
					cnt++;
				});
				if(!(options.column)) elm.css("height", maxHeight + "px");
			}else{
				elm.css("height","auto");
			}
		});
	}
	
}