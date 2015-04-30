

jQuery().ready(function($){
	$(".multilingual.urlsegmenthiddenfield:not(.multilingual.urlsegmenthiddenfield .multilingual.urlsegmenthiddenfield)").livequery(function(){
		var self=$(this);
		setTimeout(function(){			
			self.hide();		
		},0);		
	});

	/*
	 * For links
	 */
	$("#TopLangSelector a").livequery("click",function(){		

		if($(this).hasClass("disabled"))return false;
		
		lang=$(this).attr("rel");		
		if(lang.length>0 && $(this).closest("form").find("div.multilingualfield >.field[id*=_"+lang+"]").length){			
			$(this).closest("form").find("div.multilingualfield > .field").hide();
			$(this).closest("form").find("div.multilingualfield >.field[id*=_"+lang+"]").show();
			
		}else{
			//If lang is default lang
			$(this).closest("form").find("div.multilingualfield >.field").show();
			$(this).closest("form").find("div.multilingualfield >.field[id*=_]").hide();
			
		}		
		$(this).closest("div.langflags").find("a").removeClass("selected");
		$(this).addClass("selected");
		
		
		//only set cookie if in page mode, not in dataobject popups
		if($(this).closest("form").attr("id")=="Form_EditForm" ||
			$(this).closest("form").attr("id")=="Form_ItemEditForm"){
			setCookie("CurrentLanguageAdmin",lang, 7);
		}

		if($(this).hasClass("selected") && $("#Form_EditForm[action^='admin/pages']").length){

			//$(".cms-preview iframe").attr("src", $("base").attr("href").substring(0,-1)+$(this).attr("href"));
			//jQuery(".cms-preview").entwine("ss.preview")._loadUrl("http://dn.se");
			//jQuery(".cms-preview").entwine("ss.preview").setPendingURL("http://dn.se");
			var pageURL=$(this).attr("href");
			jQuery(".cms-preview").entwine("ss.preview")._loadUrl(pageURL);
		}
		
		return false;
	});
	setTimeout(function(){			
		if($("#TopLangSelector a.selected:not(.disabled)").length<1){
			$("#TopLangSelector a:first").addClass("selected");
		}
	},500);
	//jQuery(".cms-preview").entwine("ss.preview").setPendingURL("http://dn.se");
	var pageURL=$("#TopLangSelector a.selected:not(.disabled)").attr("href");
	if($(".cms-preview iframe").attr("src")!=pageURL){
		jQuery(".cms-preview").entwine("ss.preview")._loadUrl(pageURL);
	}
});

function setCookie(c_name,value,exdays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());	
	document.cookie=c_name + "=" + c_value+"; javahere=yes;path=/admin";
}

