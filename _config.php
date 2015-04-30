<?php

/******************************************************************************************************
 * Multilingual module with i18n support -  copy settings to your _config.php
/******************************************************************************************************/
/*
Requirements::css("multilingual/css/langselector.css");

i18n::set_default_locale("en_US");
Multilingual::set_default_lang("en");
 
Multilingual::set_langs(array(	
	"English"=>array(
		"en"=>"en_US"
	),
	"Swedish"=>array(
		"sv"=>"sv_SE"
	),
	"Spanish"=>array(
		"es"=>"es_ES"
	)
));
Multilingual::$default_activated=array(
	"en","sv"
);

// Multilingual PAGES---------------------------------------------------------
// Global multilingual fields. If found in any child class it will translate 
// them (new fields will be created on the object).
// Ex: the field "Title" will create a new db field with name "Title_XX", 
// where XX is the lang-id, ex "es" for spanish 

// Multilingual fields for SiteTree class
Multilingual::set_sitetree_global_multilingual_fields(
array(
	"Title",
	"MenuTitle",
	"Content",
	"MetaTitle",
	"MetaDescription",
	"MetaKeywords",
	"ExtraMeta",
));

//SITECONFIG------------------------------------------------------------------------------
// For SiteConfig to work properly, you sadly have to hack the SiteConfig.php class. 
// The upside is that its a small hack. Change the following:
// 
// "class SiteConfig extends DataObject" To:
// 
// "class SiteConfig extends MultilingualSiteConfig"


// Multilingual fields for siteconfig
//because we cant add a static to the class (we dont want to do more hacking of the core)
//we add all multilingual fields for the SiteConfig here
Multilingual::set_siteconfig_global_multilingual_fields(
array(			
	"Title",
	"Tagline",
));



// GENERAL DATAOBJECTS------------------------------------------------------------------------------
// Global multilingual fields for general DataObjects and its descendants is
// best to add on the dataobject theself. Just add a new static for it:
// Ex: static $multilingual_fields=array("MyTitle","MyContent") etc.
Multilingual::set_dataobject_global_multilingual_fields(
	//array("Title")
	array()
);

// Multilingual URLSegment------------------------------------------------------------------------------
// Do you want to be able to translate the URLSegment of pages?
Multilingual::use_multilingual_urlsegments(true);




//ENABLE  last!
//You need to enable this in mysite/_config.php otherwise you can't configure the properties
Multilingual::enable();
*/

/*-----------------------------------------------------------------------------
* MULTILINGUAL END
------------------------------------------------------------------------------*/
