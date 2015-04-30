<?php

class Multilingual extends DataExtension {
	/*	 * ****************************************************************************************************
	  /* lang statics, getter, setters
	  /***************************************************************************************************** */

	static $disable_filters_in_controller_context = array(
		"AdminRootController", "DevelopmentAdmin"
	);
	static $use_URLSegment = false;

	static function use_multilingual_urlsegments($active) {
		self::$use_URLSegment = $active;
	}
	
	static $originalfields_cache = array();

	static $decorated_class; // used in the child classes to get what we are decorating
	private static $enabled = false;

	/**
	 * Track if we should run doExtend in child classes or we do it right in the extension
	 * @var bool
	 */
	private static $extend_in_child = true;

	function is_enabled() {
		return self::$enabled;
	}

	static function get_extend_in_child() {
		return self::$extend_in_child;
	}
	
	static function set_extend_in_child($v = true) {
		self::$extend_in_child = $v;
	}

	static function get_decorated_class() {
		return static::$decorated_class;
	}

	private static $lang;
	private static $default_lang = "sv";

	/*
	 * All the languages available for the site, as an associative array
	 */
	static $langs = array(
		"Swedish" => array("sv" => "sv_SE"),
		"English" => array("en" => "en_US"),
	);
	static $default_activated=array(
		"en"
	);
	static function set_langs($array) {
		static::$langs = $array;
	}

	/*
	 * All multilingual fields on the object, will
	 * translate to selected language if found on the decorated object.
	 * ex: "Title" will be "Title_de" and so on for each language set.
	 * these are set in the subclasses
	 */

	static $multilingual_fields = array();

	static function multilingual_fields() {
		return static::$multilingual_fields;
	}

	static function set_multilingal_fields($array) {
		static::$multilingual_fields = $array;
	}

	/*
	 * Getter / setter for current site lang
	 */

	static function current_lang() {

		if (empty(Multilingual::$lang)) {
			return Multilingual::default_lang();
		} else {
			return Multilingual::$lang;
		}
	}

	static function set_current_lang($lang) {
		return Multilingual::$lang = $lang;
	}

	/*
	 * Getter / setter for default site lang
	 */

	static function default_lang() {
		return Multilingual::$default_lang;
	}

	static function is_default_lang($lang = null) {
		if ($lang) {
			return $lang == Multilingual::default_lang();
		} else {
			return Multilingual::current_lang() == Multilingual::default_lang();
		}
	}

	static function default_locale() {
		$map = Multilingual::map_locale();
		return $map[self::default_lang()];
	}
	
	static function current_locale() {
		$map = Multilingual::map_locale();
		return $map[self::current_lang()];
	}

	static function set_default_lang($lang) {
		Multilingual::$default_lang = $lang;
	}

	/*
	 * Returns the map of locales of lang, ex: sv=>sv_SE	
	 */

	static function map_locale() {
		$map = array();
		foreach (Multilingual::$langs as $keys => $langs) {
			$key = array_keys($langs);
			$value = array_values($langs);
			$map[$key[0]] = $value[0];
		}
		return $map;
	}
	
	/**
	 * Check if a lang (two characters) is available
	 * 
	 * @param string $lang
	 * @return boolean
	 */
	static function is_allowed_lang($lang) {
		foreach (Multilingual::$langs as $nicename => $array) {
			if(isset($array[0]) && $lang == $array[0]) {
				return true;
			}
		}
		return false;
	}

	/*
	 * Returns an associative array for lang dropdowns, ex: sv=>Swedish
	 */

	static function map_to_dropdown($withdefaultlangkey=false) {
		$map = array();
		foreach (Multilingual::$langs as $nicename => $array) {
			$langcode = array_keys($array);
			$langcode = $langcode[0];
			if ($langcode == Multilingual::default_lang() && !$withdefaultlangkey) {
				$langcode = "";
			}
			$map[$langcode] = $nicename;
		}
		return $map;
	}

	/*
	 * Returns an associative array for locales, ex: sv_SE=>Swedish
	 */

	static function map_allowed_locales() {
		$map = array();
		foreach (Multilingual::$langs as $nicename => $array) {
			$langcode = array_values($array);
			$langcode = $langcode[0];
			$map[$langcode] = $langcode;
		}
		return $map;
	}

	/*
	 * Returns all langs except the default lang as an array
	 */

	static function multilingual_extra_langs() {
		$langsarray = array_keys(Multilingual::map_locale());
		$arr = array_diff($langsarray, array(Multilingual::default_lang()));
		return ($arr);
	}

	/*
	 * Returns all locales except the default locale as an array
	 */

	static function multilingual_extra_locales() {
		$langsarray = array_keys(Multilingual::map_allowed_locales());
		$arr = array_diff($langsarray, array(Multilingual::default_locale()));
		return ($arr);
	}

	private static $filter_enabled = true;

	public function filter_enabled() {
		return self::$filter_enabled;
	}

	public function set_filter_enabled() {
		self::$filter_enabled = true;
	}

	public function set_filter_disabled() {
		self::$filter_enabled = false;
	}

	/* Add global multilingual fields */

	static $sitetree_multilingual_fields = array();

	static function set_sitetree_global_multilingual_fields(array $fields) {
		self::$sitetree_multilingual_fields = $fields;
	}

	static $siteconfig_multilingual_fields = array();

	static function set_siteconfig_global_multilingual_fields(array $fields) {
		self::$siteconfig_multilingual_fields = $fields;
	}

	static $dataobject_multilingual_fields = array();

	static function set_dataobject_global_multilingual_fields(array $fields) {
		self::$dataobject_multilingual_fields = $fields;
	}

	static function get_global_multilingual_fields($class) {
		switch ($class) {
			case "SiteTree":
				//$decorated_class = "SiteTree";
				$global_multilingual_fields = static::$sitetree_multilingual_fields;

				break;
			case "SiteConfig":
				//$decorated_class = $decoratedclass;
				$global_multilingual_fields = static::$siteconfig_multilingual_fields;
				break;
			case "MultilingualDataObject":
				//$decorated_class = $decoratedclass;
				$global_multilingual_fields = static::$dataobject_multilingual_fields;
				break;
			default:
				//$decorated_class = $decoratedclass;
				$global_multilingual_fields = array();
		}
		return $global_multilingual_fields;
	}
	static function get_class_global_multilingual_fields($class){
		$global_multilingual_fields=array();
		if(ClassInfo::baseDataClass($class)=="SiteTree"){
			$global_multilingual_fields = self::get_global_multilingual_fields("SiteTree");			
		}else if(class_exists($class) && in_array("MultilingualDataObject",ClassInfo::ancestry($class))){
			$global_multilingual_fields = self::get_global_multilingual_fields("MultilingualDataObject");			
		}else if($class=="SiteConfig" || (class_exists($class) && in_array("MultilingualSiteConfig",ClassInfo::ancestry($class)))){			
			$global_multilingual_fields = self::get_global_multilingual_fields("SiteConfig");			
		}
		return $global_multilingual_fields;		
	}
	static function get_class_multilingual_fields($class,$extensions=false,$inherit=false){
		$filter=$extensions?CONFIG::UNINHERITED :CONFIG::UNINHERITED | CONFIG::EXCLUDE_EXTRA_SOURCES;
		if($inherit){
			$filter=$extensions?CONFIG::INHERITED :CONFIG::INHERITED | CONFIG::EXCLUDE_EXTRA_SOURCES;
		}
		return Config::inst()->get($class,"multilingual_fields",$filter)?Config::inst()->get($class,"multilingual_fields",$filter):array();
	}
	static function get_all_multilingual_fields($class,$extensions=false){		
		return array_merge(static::get_class_global_multilingual_fields($class,$extensions), static::get_class_multilingual_fields($class,$extensions));
	}
	static function get_originalfields_from_class($class, $fields = "db", $use_cache = false, $use_extensions=false) {
		if($use_cache && isset(self::$originalfields_cache[$class . '_' . $fields])) {
			return self::$originalfields_cache[$class . '_' . $fields];
		}
		$filter=$use_extensions?Config::UNINHERITED:Config::UNINHERITED | CONFIG::EXCLUDE_EXTRA_SOURCES;
		$origfields = Config::inst()->get($class, $fields, $filter); //Object::get_static($class, "db");
		if ($extensions = Config::inst()->get($class, 'extensions', Config::UNINHERITED | Config::EXCLUDE_EXTRA_SOURCES)) {
			foreach ($extensions as $ext) {
				if (strpos($ext, "Versioned") === false &&
					strpos($ext, "Multilingual") === false &&
					strpos($ext, "Hierarchy") === false &&
					strpos($ext, "FulltextSearchable") === false) {
					if (strpos($ext, "(") !== false) {
						$extension = substr($ext, 0, strpos($ext, "("));
					} else {
						$extension = $ext;
					}
					if (isset($extension::$db)) {
						$origfields = array_merge($origfields, Config::inst()->get($extension, "db", $filter));
					}
				}
			}
		}
		self::$originalfields_cache[$class . '_' . $fields] = $origfields;

		
		return $origfields;
	}

	static function move_extension_to_last($class,$extensionClass){
			$extensions = Config::Inst()->get($class, 'extensions');		
			$updatedExtensionsArray=array();
			$absoluteLastExtensions=array();
			if($extensions) {
				unset($extensions[0]);//we unset the latest addition - multilingual_siteconfig			
				
				foreach($extensions as $ext){					

					if(!(strpos($ext,"ersioned(")>0 || $ext=="Hierarchy" || $ext=="SiteTreeLinkTracking")){						
						array_push($updatedExtensionsArray,$ext);
					}else{
						array_push($absoluteLastExtensions,$ext);
					}					
				}				
				array_push($updatedExtensionsArray,$extensionClass); // we add it in the last position - will be handled last by ss
				foreach($absoluteLastExtensions as $lastExt){
					array_push($updatedExtensionsArray,$lastExt);
				}				
				//debug::dump($updatedExtensionsArray);

			}else{
				array_push($updatedExtensionsArray,$extensionClass);
			}
			//print_r($updatedExtensionsArray);
			Config::Inst()->remove($class, 'extensions');		
			Config::Inst()->update($class, 'extensions',$updatedExtensionsArray);		
			Config::inst()->extraConfigSourcesChanged($class);
			Injector::inst()->unregisterNamedObject($class);

			
	}

	/*	 * ****************************************************************************************************
	  /* Decoration functions
	  /***************************************************************************************************** */

	/*
	 * Enable function, which simplifies the activation of multilingual module.
	 * It extends SiteTree, SiteConfig and the custom dataobject "TranslatableObject".
	 * It also creates URL-rules for all the languages.
	 */

	static function enable() {
		self::$enabled = true;

		/* Basic checks */
		$SiteConfig = singleton("SiteConfig");
		if (!($SiteConfig instanceof MultilingualSiteConfig)) {
			user_error('You need to extend SiteConfig from MultilingualSiteConfig. SiteConfig is located in cms/code/model/SiteConfig.php', E_USER_ERROR);
		}

		//translate URLSegment ?
		if (static::$use_URLSegment) {
			self::$sitetree_multilingual_fields[] = "URLSegment";
		}

		$multilingual_classes_to_affect=array("SiteTree","MultilingualDataObject","SiteConfig","MultilingualWidget");

		foreach ($multilingual_classes_to_affect as $class) {
			if(class_exists($class)){
				$ancestry=ClassInfo::ancestry($class);
				if($class=="SiteTree" || $class=="SiteConfig" 
					|| in_array("MultilingualPage",$ancestry)
					|| in_array("MultilingualDataObject",$ancestry)					
					|| in_array("MultilingualWidget",$ancestry)){					
					Object::add_extension($class,"Multilingual");			
					static::move_extension_to_last($class,"Multilingual");							
					$extensions = Config::Inst()->get($class, 'extensions');		
					//debug::dump($extensions);
				}
			}
		}		

		
		

		if (class_exists("GoogleSitemap")) {
			Object::add_extension("GoogleSitemap", "MultilingualGoogleSitemapExtension");
		}

		
		
		
		//default lang active
		$defaults=array();		
		if(in_array(self::default_lang(),self::$default_activated)){
			$defaults["LangActive"]=true;
		}
		
		//fix routing of lang prefixes in URL
		foreach (Multilingual::map_locale() as $lang => $locale) {
			Director::addRules(100, array(
				$lang . '/$Controller//$Action/$ID/$OtherID' => '*',
				$lang . '/$URLSegment//$Action/$ID/$OtherID' => 'MultilingualModelAsController'
			));
			if(in_array($lang, self::$default_activated)){
				$defaults["LangActive_".$lang]=true;
			}			
		}		
		Config::inst()->update("MultilingualPage","defaults",$defaults);
		Config::inst()->update("MultilingualDataObject","defaults",$defaults);





		//get all db fields (including the extensions) for all classes that are children of the decorated pages above		

		

		//die();
		
		

	}		
	static function create_db_fields($originalFields=array(), $fieldsToTranslate=array(), &$db){
		foreach (Multilingual::multilingual_extra_langs() as $lang) {
			foreach ($fieldsToTranslate as $fieldtotranslate) {
				/*echo "GLOBAL: <br/>";
				echo $class.":<br/>";
				echo $fieldtotranslate . "_" . $lang.": ";
				debug::dump($origkeys);*/
				// make sure we find a field with correct name that hasnt been translated before

				if (is_array($originalFields) && array_key_exists($fieldtotranslate, $originalFields) && !array_key_exists($fieldtotranslate . "_" . $lang, $originalFields)) {
					$db[$fieldtotranslate . "_" . $lang] = $originalFields[$fieldtotranslate];
				}
			}
		}
	}
	function extraDBFields($class, $extensionClass){		
		
		$global_multilingual_fields=static::get_global_multilingual_fields($class);		
		$MultilingualChildClasses=ClassInfo::subclassesFor($class);
		foreach($MultilingualChildClasses as $class){
			$db = array();	
			
			
			//$global_multilingual_fields = self::get_global_multilingual_fields("SiteTree");			
			$original_fields=self::get_originalfields_from_class($class, "db");
			$db=$original_fields;
			//echo $class;			
			if (is_array($original_fields)) {
				
				$origfields=$original_fields;
				$origkeys = array_keys($original_fields);				
				//debug::dump($origkeys);
				/*echo $class.": ";
				debug::dump($class_multilingual_fields);
				debug::dump($origfields);*/
				

				
					if(is_array($global_multilingual_fields)){
						//look after the global fields set in _config.php
						static::create_db_fields($original_fields,$global_multilingual_fields, $db);
					}				
					//look after class multilingual fields set on class
					$class_multilingual_fields=static::get_class_multilingual_fields($class);
					if(is_array($class_multilingual_fields)){
						static::create_db_fields($original_fields,$class_multilingual_fields, $db);		
					}
				

				if (sizeof($db) > 0) {
					/*echo $class."DB:<br/>";
					debug::dump($db);*/
					Config::inst()->update($class,"db", $db);
					
				}


				
			}
		}

	}
	/*
	static function __get_extra_config($decoratedclass, $extensionClass, $args = null) {

		
	}		
	static function __add_to_class($decoratedclass, $extensionClass, $args = null) {
		$extension=$extensionClass;
		$class=$decoratedclass;
		if(method_exists($extension, 'extraDBFields')) {
			$extraStaticsMethod = 'extraDBFields';
		} else {
			$extraStaticsMethod = 'extraStatics';
		}

		$statics = Injector::inst()->get($extension, true, $args)->$extraStaticsMethod($class, $extension);

		if ($statics) {
			Deprecation::notice('3.1.0',
				"$extraStaticsMethod deprecated. Just define statics on your extension, or use get_extra_config",
				Deprecation::SCOPE_GLOBAL);
			return $statics;
		}
		
		//echo $decoratedclass."<br/>";
		//debug::dump(Config::Inst()->get($decoratedclass,"extensions",CONFIG::EXCLUDE_EXTRA_SOURCES));
		
		if($extensionClass=="multilingual") {
			
		}

	}*/
	

	/*	 * ****************************************************************************************************
	  /* ADMIN / CMS functions
	  /***************************************************************************************************** */

	/*
	 * Admin lang when changing languages in admin.
	 * In admin we use a cookie to set and remember current language.
	 * The cookie is set from js-file javascript/multilingual.js
	 * @returns String language
	 */

	static function admin_current_lang($showfull = true) {
		$currentlang = Cookie::get("CurrentLanguageAdmin");
		if (!$currentlang) {
			if ($showfull) {
				return Multilingual::default_lang();
			} else {
				return "";
			}
		} else {
			return $currentlang;
		}
	}
	static function set_admin_current_lang($lang) {		
		Cookie::set("CurrentLanguageAdmin", $lang,null,"/admin");		
	}

	function FieldInLang($field, $lang) {
		if (empty($lang)) {
			$lang = Multilingual::default_lang();
		}
		$currlang = Multilingual::current_lang();
		Multilingual::set_current_lang($lang);
		$field = $this->owner->$field;
		Multilingual::set_current_lang($currlang);
		return $field;
	}

	
	/*
	 * Replace all fields added in $multilingual array to multilingual versions
	 * Please remember to use $this->doExtend("updateCMSFields",$fields,get_class()) last in 
	 * getCMSFields() in your sub classes for full access to all multilingual fields.
	 */

	function updateCMSFields(FieldList $fields) {		
		if ($this->owner instanceof MultilingualPage ||
			$this->owner instanceof MultilingualDataObject ||
			$this->owner instanceof MultilingualWidget ||
			$this->owner instanceof SiteConfig) {									
			Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
			Requirements::javascript("multilingual/javascript/multilingual.js");
			Requirements::css("multilingual/css/multilingual.css");			


			if (self::$use_URLSegment) {
				//Requirements::block("cms/javascript/CMSMain.EditForm.js");
				Requirements::javascript("multilingual/javascript/CMSMain.EditForm.Multilingual.js");
				
				//Requirements::block("cms/javascript/SiteTreeURLSegmentField.js");
				//Requirements::javascript("multilingual/javascript/SiteTreeURLSegmentField.js");
				
				
			}
			$class=$this->owner->class;			
			

			$multilingual_fields = static::get_all_multilingual_fields($class);			
			//debug::dump($this->owner->class);
			//print_r($multilingual_fields);
			//debug::dump($multilingual_fields);
			 /*echo get_class($this->owner);
			  print_r($multilingual_fields);
			echo $baseclass." - ";
			  print_r(Config::Inst()->get($baseclass,"multilingual_fields")); 
			  print_r($mlm_fields);*/
			if (!$this->owner instanceof Widget) {
				foreach ($multilingual_fields as $fieldkey) {					
					$newfields = null;
					
					$originalfield = $fields->dataFieldByName($fieldkey);					

					if ($originalfield) {						
						$fieldname = $originalfield->getName();						
						foreach (Multilingual::$langs as $langnice => $langarray) {

							$key = array_keys($langarray);
							$langcode = $key[0];
							if ($langcode == Multilingual::default_lang()) {
								$fieldid = $fieldname;
							} else {
								$fieldid = $fieldname . "_" . $langcode;
							}							


							//Fix original fields						
							$fieldclone = clone $originalfield;

							$fieldclone->setName($fieldid);

							$fieldclone->setTitle($originalfield->Title() . " 
							<span class='marked-label " . $langcode . "' title='" . $langnice . "'>
								<img src='multilingual/images/flag-" . $langcode . ".png' />
							<span class='language-nice'> </span></span>");
							$fieldclone->setValue($this->owner->$fieldname);

							if ($fieldname == "URLSegment") {
								$hiddenclass = Multilingual::admin_current_lang() != $langcode ? "urlsegmenthiddenfield" : "";
								$fieldclone->addExtraClass("multilingual " . $hiddenclass);
																
								$lang=$langcode==Multilingual::default_lang()?"":$langcode;
								$this->owner->Parent()->ForceLang=$lang;
								//$oldlang=Multilingual::current_lang();
								//Multilingual::set_current_lang($lang);

								$baseLink =
									Director::absoluteURL(
										((SiteTree::config()->nested_urls && $this->owner->ParentID) ? $this->owner->Parent()->RelativeLink() : $lang."/")
									);

								if(SiteTree::has_extension("SiteTreeSubsites")){
									$subsite = $this->owner->Subsite();
									$nested_urls_enabled = Config::inst()->get('SiteTree', 'nested_urls');
									if($subsite && $subsite->ID) {
										$baseUrl = 'http://' . $subsite->domain() . '/';
										$baseLink = Controller::join_links (
											$baseUrl,
											($nested_urls_enabled && $this->owner->ParentID ? $this->owner->Parent()->RelativeLink(true) : $lang."/")
										);

									}
								}


								//$baseLink=$this->owner->Parent()->AbsoluteLink();

								$fieldclone->setURLPrefix($baseLink);
								//Multilingual::set_current_lang($oldlang);								
								$this->owner->Parent()->ForceLang=null;
							} else {
								$hiddenclass = Multilingual::admin_current_lang() != $langcode ? "hiddenfield" : "";
								$fieldclone->addExtraClass("multilingual " . $hiddenclass);
							}

							$newfields[] = $fieldclone;
							$fieldclone = null;
						}						
						$fields->replaceField($fieldname, $compositefield = new CompositeField($newfields));
						$compositefield->setID("Multilingual_" . $fieldname);
						$compositefield->addExtraClass("multilingualfield field-" . $fieldname);
						/*echo $fieldname."<br/>";
						debug::dump($compositefield);*/
					}
				}
			}

			if ($this->owner instanceof MultilingualDataObject ) {
				$this->updateSettingsFields($fields);
			}

			/*
			 * Flag links in cms
			 */
			if (!$this->owner instanceof Widget) {
				$langselector = $this->CreateLangSelectorForAdmin();
				$fields->unshift(new LiteralField("MultilingualSelector", $langselector, null, true));
			}
		}

	}
	

	/**
	 * You can also call this method outside of the extension (eg : in other extensions)
	 * to add the translated field
	 * 
	 * @param FormField $originalfield
	 * @param FiedList $fields
	 * @param DataObject $owner
	 */
	public static function convert_field_to_multilingual($originalfield, $fields, $owner) {
		$fieldname = $originalfield->getName();
		foreach (Multilingual::$langs as $langnice => $langarray) {

			$key = array_keys($langarray);
			$langcode = $key[0];
			if ($langcode == Multilingual::default_lang()) {
				$fieldid = $fieldname;
			} else {
				$fieldid = $fieldname . "_" . $langcode;
			}

			$fieldclone = clone $originalfield;

			$fieldclone->setName($fieldid);

			$fieldclone->setTitle($originalfield->Title() . " 
							<span class='marked-label " . $langcode . "' title='" . $langnice . "'>
								<img src='multilingual/images/flag-" . $langcode . ".png' />
							<span class='language-nice'> </span></span>");

			$fieldclone->setValue($owner->$fieldname);

			
			$hiddenclass = Multilingual::admin_current_lang() != $langcode ? "hiddenfield" : "";
			$fieldclone->addExtraClass("multilingual " . $hiddenclass);
			

			$newfields[] = $fieldclone;
			$fieldclone = null;
		}
		if (!$fields->dataFieldByName($fieldid)) {
			$fields->replaceField($fieldname, $compositefield = new CompositeField($newfields));
			$compositefield->setID("Multilingual_" . $fieldname);
			$compositefield->addExtraClass("multilingualfield field-" . $fieldname);
		}
	}

	
	//for widgets
	function onCMSEditor($fields) {
		$baseclass = $this->owner == "SiteConfig" ? "SiteConfig" : ClassInfo::baseDataClass($this->owner);
		$multilingual_fields = Multilingual::get_global_multilingual_fields($baseclass);
		foreach ($multilingual_fields as $fieldkey) {
			$newfields = null;
			$searchname=preg_replace("/([A-Za-z0-9\-_]+)/", "Widget[" . $this->owner->ID . "][\\1]", $fieldkey);			
			
			$originalfield = $fields->fieldByName($searchname);
			
			if ($originalfield) {
				$fieldname = $originalfield->getName();
				foreach (Multilingual::$langs as $langnice => $langarray) {

					$key = array_keys($langarray);
					$langcode = $key[0];
					if ($langcode == Multilingual::default_lang()) {
						$fieldid = $fieldname;
						$fieldkey_ml = $fieldkey;
					} else {
						$fieldkey_ml = $fieldkey."_".$langcode;
						$fieldid = substr($fieldname,0,-1) . "_" . $langcode."]";
					}

					//Fix original fields

					$fieldclone = clone $originalfield;

					$fieldclone->setName($fieldid);

					$fieldclone->setTitle($originalfield->Title() . " 
							<span class='marked-label " . $langcode . "' title='" . $langnice . "'>
								<img src='multilingual/images/flag-" . $langcode . ".png' />
							<span class='language-nice'> </span></span>");
					
					$fieldclone->setValue($this->owner->$fieldkey_ml);	
					
					$hiddenclass = Multilingual::admin_current_lang() != $langcode ? "hiddenfield" : "";
					$fieldclone->addExtraClass("multilingual " . $hiddenclass);
					
					$newfields[] = $fieldclone;
					$fieldclone = null;
				}
				if (!$fields->dataFieldByName($fieldid)) {

					$fields->replaceField($fieldname, $compositefield = new CompositeField($newfields));
					$compositefield->setID("Multilingual_" . $fieldname);
					$compositefield->addExtraClass("multilingualfield field-" . $fieldname);
				}
			}
		}
	}

	function updateSettingsFields(&$f) {
		//IF Widget		- NOT USED YET
		if (strpos($this->owner->ClassName, "Widget")) {

			
			foreach (Multilingual::$langs as $langnice => $langarray) {
				$key = array_keys($langarray);
				$langcode = $key[0];
				if ($langcode != Multilingual::default_lang()) {
					$langactive = new CheckboxField("LangActive_" . $langcode, "
						<span class='marked-label " . $langcode . "'>
							<img src='multilingual/images/flag-" . $langcode . ".png' />
						<span class='language-nice'>" . $langnice . "</span></span>");
					$f->unshift($langactive);
				}
				/*else{
					$langactive = new CheckboxField("LangActive", "
						<span class='marked-label " . $langcode . "'>
							<img src='multilingual/images/flag-" . $langcode . ".png' />
						<span class='language-nice'>" . $langnice . "</span></span>");
					$f->push($langactive);
				}*/
			}
			$f->unshift(new LabelField("LangHeading", _t("Multilingual.VISIBLEINLANGS", "Visible in languages")));
		} else {//if page or dataobject in popup
			$checkboxes=array();
			//add LangActive field, not in SiteConfig			
			if ($this->owner->ClassName != "SiteConfig") {
				
				foreach (Multilingual::$langs as $langnice => $langarray) {
					$key = array_keys($langarray);
					$langcode = $key[0];
					$langfield = "LangActive_" . $langcode;
					if ($langcode == Multilingual::default_lang()) {
						$langcode = Multilingual::default_lang();
						$langfield = "LangActive";
					}

					$langactive = new CheckboxField($langfield, "
						<span class='marked-label " . $langcode . "'>
							<img src='multilingual/images/flag-" . $langcode . ".png' />
						<span class='language-nice'> </span></span>", $this->owner->getField($langfield));
					
					
					$checkboxes[]=$langactive;
					
				}								
				if ($this->owner instanceof SiteTree) {
					$f->addFieldToTab("Root.Settings", FieldGroup::create(_t("Multilingual.VISIBLEINLANGS", "Visible in languages"), $checkboxes), "ClassName");
				} else {
					$f->addFieldToTab("Root.Settings", FieldGroup::create(_t("Multilingual.VISIBLEINLANGS", "Visible in languages"), $checkboxes));
				}
			}
		}
	}
	function IsFieldTranslatedInLang($dbField, $lang){
		$suffix="";
		if(!static::is_default_lang($lang)){
			$suffix="_".$lang;
		}
		$field=$dbField.$suffix;
		if($this->owner->getField($field)){
			return true;
		}
	}
	/*
	 * Build up necessary html for a simple flag selector for admin
	 */

	function CreateLangSelectorForAdmin() {
		$langselectors = '<div class="langflags field" id="TopLangSelector">
			<label class="left">' . _t("Multilingual.LANGUAGELABEL", "Choose language") . ': </label>
			<div class="lang-holder">';
		$origlang = Multilingual::current_lang();
		$langactive = Multilingual::admin_current_lang();
		foreach (Multilingual::map_to_dropdown(true) as $langcode => $langnice) {
			Multilingual::set_current_lang($langcode);
			
			$selected = (Multilingual::admin_current_lang() == $langcode) ? "selected" : "";
			$classlangcode = !empty($langcode) ? $langcode : Multilingual::default_lang();
			if(!($this->owner instanceof SiteConfig) && Multilingual::default_lang()!=$langcode && !empty($langcode)){					
				$langactive=$this->owner->getField("LangActive_".$langcode)?"":"disabled";
			}else{
				$langactive="";
			}
			if ($this->owner instanceof SiteTree) {
				$livelink=$this->owner->getAbsoluteLiveLink()?$this->owner->getAbsoluteLiveLink():Controller::join_links($this->owner->AbsoluteLink(),"?stage=Stage");
				
				$langselectors.='<div><a href="' . $livelink . '" rel="' . ($langcode==Multilingual::default_lang()?"":$langcode) . '" lang-title="' . $this->owner->Title . '" class="'.$langactive.' '. $classlangcode . ' ' . $selected . '" title="' . $langnice . '"><img src="multilingual/images/flag-' . $classlangcode . '.png" /><span>'.$langnice.'</span></a></div>';
			} else {
				$langselectors.='<div><a href="' . Director::absoluteBaseURL() . $langcode . '" rel="' . $langcode . '" lang-title="' . $this->owner->Title . '" class="'.$langactive.' '. $classlangcode . ' ' . $selected . '" title="' . $langnice . '"><img src="multilingual/images/flag-' . $classlangcode . '.png" /><span>'.$langnice.'</span></a></div>';
			}
			
		}
		Multilingual::set_current_lang($origlang);
		$langselectors.="</div></div>";
		return $langselectors;
	}

	/*	 * ***************************************************************************************************
	  /* Template functions
	  /***************************************************************************************************** */

	function IsActiveInLang($lang) {
		$lookupfield = Multilingual::is_default_lang($lang) ? "LangActive" : "LangActive_" . $lang;
		if ($this->owner->$lookupfield) {
			return true;
		}
	}

	function InLang($lang) {
		return Multilingual::current_lang() == $lang;
	}

	function LangSelector($displayCurrentLang = false, $CheckExistsfield = false) {//checkexistfield couldbe ex "Title", it will then check if Title_XX exists
		$list = array();
		$origlang = Multilingual::current_lang();
		foreach (Multilingual::$langs as $langnice => $langarray) {
			$arr = array_keys($langarray);
			$langcode = $arr[0];
			$isDefaultLang = $langcode == Multilingual::default_lang();
			Multilingual::set_current_lang($langcode);

			$do = new DataObject();
			$do->Link = $this->owner->Link();
			$do->LangCode = $langcode;
			$do->ImgURL = Director::absoluteBaseURL() . "multilingual/images/flag-" . $langcode . ".png";
			$do->Selected = $langcode == $origlang ? "selected" : "";
			$do->LangNice = $langnice;
			$langactive = $isDefaultLang ? "LangActive" : "LangActive_" . $langcode;
			
			//debug::dump(Config::inst()->get($this->owner->class,"db"));
			if (($displayCurrentLang || !$isDefaultLang) && $this->owner->getField($langactive)) {

				$active = false;
				if (Multilingual::$use_URLSegment) {
					$langactive = "LangActive_" . $langcode;
					if ($this->owner->getField($langactive) && Multilingual::default_lang() != $langcode) {
						$active = true;
					}
				}
				if ($CheckExistsfield || $active) {
					$checkfield = $CheckExistsfield . "_" . $langcode;
					if (($this->owner->$checkfield || $isDefaultLang) || $active) {
						$list[] = ($do);
					}
				} else {
					$list[] = ($do);
				}
			}
		}
		Multilingual::set_current_lang($origlang);
		return new ArrayList($list);
	}

	//needed for export purposes
	public function updateSummaryFields(&$fields) {
		$multilingual_fields = Config::inst()->get($this->owner->class, 'multilingual_fields', Config::INHERITED) ? Config::inst()->get($this->owner->class, 'multilingual_fields', Config::INHERITED) : array();
		if (sizeof($multilingual_fields) > 0) {			
			foreach ($multilingual_fields as $fieldname) {
				if ($fieldname) {
					if (in_array($fieldname, array_keys($fields))) {
						foreach (Multilingual::$langs as $langnice => $langarray) {
							$key = array_keys($langarray);
							$langcode = $key[0];
							if ($langcode != Multilingual::default_lang()) {
								$fieldid = $fieldname . "_" . $langcode;
								$fields[$fieldid] = $fieldid;
							}
						}
					}
				}
			}
		}		
	}

	public function onBeforeWrite() {
		if (Multilingual::$use_URLSegment && $this->owner instanceof MultilingualPage) {
			$originalTitle = $this->owner->Title;
			$origlang = Multilingual::admin_current_lang();
			$origlang2 = Multilingual::current_lang();


			foreach (Multilingual::multilingual_extra_langs() as $lang) {
				Multilingual::set_current_lang($lang);
				$URLSegmentfield = "URLSegment_" . $lang;
				$Titlefield = "Title_" . $lang;
				if (empty($this->owner->$Titlefield)) {
					$this->owner->$Titlefield = $originalTitle . "-" . $lang;
				}
				if ((!$this->owner->$URLSegmentfield || $this->owner->$URLSegmentfield == 'new-page') && $this->owner->$Titlefield) {
					$this->owner->$URLSegmentfield = $this->owner->generateURLSegment($this->owner->$Titlefield);
				} else if ($this->owner->isChanged($URLSegmentfield)) {
					// Make sure the URLSegment is valid for use in a URL										
					//$segment = preg_match('/[^A-Za-z0-9]+/i', '-', $this->owner->getField($URLSegmentfield));
					//$segment = preg_match('/-+/', '-', $segment);

					if ($this->owner->$URLSegmentfield == "") {
						$segment = $this->owner->URLSegment . "_" . $lang;
					} else {
						$segment = $this->owner->generateURLSegment($this->owner->$URLSegmentfield);
					}
					// If after sanitising there is no URLSegment, give it a reasonable default
					if (!$segment) {
						$segment = "page-$this->owner->ID";
					}
					$this->owner->$URLSegmentfield = $segment;
				}
			}
			Multilingual::set_current_lang($origlang2);
		}
	}

	function augmentSQL(SQLQuery &$query) {
		if (($this->owner instanceof MultilingualDataObject || $this->owner instanceof MultilingualPage) &&
			!in_array(Controller::curr()->request->param("Controller"), self::$disable_filters_in_controller_context)) {//|| $this->owner instanceof MultilingualDataObject) {
			if (false && $this->owner instanceof MultilingualPage && Versioned::current_stage() == "Live") {
				$suffix = "_Live";
			} else {
				$suffix = "";
			}

			$lang = Multilingual::current_lang();
			if ($lang != Multilingual::default_lang()) {
				$field = "LangActive_" . $lang;
			} else {//if default language
				$field = "LangActive";
			}

			if ($this->owner instanceof MultilingualPage) {
				$baseTable = "MultilingualPage";
			} else if ($this->owner instanceof MultilingualDataObject) {
				$baseTable = "MultilingualDataObject";
			} else {
				return false;
			}


			$oldwhere = implode(" AND ", $query->getWhere());
			if (
				$lang
				// unless the filter has been temporarily disabled
				&& Multilingual::filter_enabled()
				// DataObject::get_by_id() should work independently of language
				//&& !$dataQuery->filtersOnID()
				// or we're already filtering by Lang (either from an earlier augmentSQL() call or through custom SQL filters)
				&& !preg_match('/("|\'|`)' . $field . '("|\'|`)/', $oldwhere)
			//&& !$query->filtersOnFK()
			) {
				if ($this->owner instanceof MultilingualPage) {
					$query->addLeftJoin($baseTable . $suffix, "\"" . $baseTable . $suffix . "\".\"ID\" = \"SiteTree" . "\".\"ID\"");
				}

				$qry = sprintf('"%s"."' . $field . '" = \'%s\'', $baseTable . $suffix, 1);

				$where = $oldwhere . " AND " . $qry;
				$query->setWhere($where);
				/* echo $query->sql();
				  echo "<br/>";
				  echo "<br/>"; */
			}
		}
	}

	public function augmentStageChildren(&$staged, $showAll = false) {
		if (!in_array(Controller::curr()->request->param("Controller"), self::$disable_filters_in_controller_context)) {
			if ($this->owner->db('ShowInMenus')) {
				$extraFilter = ($showAll) ? '' : " AND \"ShowInMenus\"=1";
			} else {
				$extraFilter = '';
			}

			$lang = Multilingual::current_lang();
			if ($lang != Multilingual::default_lang()) {
				$extraFilter.=" AND \"LangActive_" . $lang . "\"=1";
			} else {
				$extraFilter.=" AND \"LangActive\"=1";
			}

			$baseClass = ClassInfo::baseDataClass($this->owner->class);


			if (Versioned::current_stage() == "Live") {
				$suffix = "_Live";
			} else {
				$suffix = "";
			}
			/* $result = DataObject::get($baseClass, "\"{$baseClass}\".\"ParentID\" = " 
			  . (int)$this->owner->ID . " AND \"{$baseClass}\".\"ID\" != " . (int)$this->owner->ID
			  . $extraFilter, "")->leftJoin("MultilingualPage".$suffix, "\"MultilingualPage".$suffix."\".ID=\"SiteTree".$suffix."\".ID"); */

			$result = DataList::create($baseClass)->where("\"{$baseClass}\".\"ParentID\" = "
					. (int) $this->owner->ID . " AND \"{$baseClass}\".\"ID\" != " . (int) $this->owner->ID
					. $extraFilter)->sort("")->leftJoin("MultilingualPage" . $suffix, "\"MultilingualPage" . $suffix . "\".ID=\"SiteTree" . $suffix . "\".ID");

			$staged = $result;
		}
	}

}
