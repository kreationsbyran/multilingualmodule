<?php

/*
 * Extend from this page to make your site tree multilingual
 * Ex: extend MultilingualPage from Page
 * 
 * Tip: add static $hide_ancestor = 'MultilingualPage' to your Page 
 * that extends this page to hide this page from being created.
 * 
 * 
 */

class MultilingualPage extends SiteTree {

	//if set it will override the global lang setting (Multilingual::current_lang())
	public $ForceLang;

	public function __construct($record = null, $isSingleton = false, $model = null) {
		$originalfields=Config::inst()->get(get_class($this),"db");
		//debug::dump($originalfields);
		$multilingual_fields=Multilingual::get_class_multilingual_fields(get_class($this),$extensions=true);
		//debug::dump($multilingual_fields);
		if(sizeof($multilingual_fields)>0){
			$db=array();
			Multilingual::create_db_fields($originalfields,$multilingual_fields,$db);	
			//debug::dump($db);
			$full=array_merge($originalfields, $db);
			Config::Inst()->update(get_class($this),"db",$db);	
			
			
		}
		parent::__construct($record, $isSingleton, $model);
		//debug::dump($this->record);

		
		
		


		//debug::dump(Config::inst()->get($this->class,"db"));
		//return;
		
		
		/*$originalfields=Multilingual::get_originalfields_from_class($this->class,"db",false,$extensions=true);
		if(!is_array($originalfields)) $originalfields=array();
		$db=$originalfields;
		$multilingual_fields=Multilingual::get_class_multilingual_fields($this->class,$extensions=true);
		Multilingual::create_db_fields($originalfields,$multilingual_fields,$db);
		*/
		/*echo $this->class;
		debug::dump($db);*/
		/*echo $this->class;
		debug::dump($originalfields);
		debug::dump(Multilingual::get_class_multilingual_fields($this->class));*/
		//if($this->class=="MultilingualPage"){
			//Multilingual::create_db_fields(array("LangActive"=>"Boolean"),array("LangActive"),$db);
		//}
		/*if (sizeof($db) > 0) {
			//echo $this->class."DB:<br/>";
			debug::dump($db);
			//Config::inst()->update($this->class,"db",$db);			
		}		*/
	}
	private static $db = array(
		"LangActive" => "Boolean"
	);
	//all pages that descend from MultilingualPage can have this static variable
	//it translates those specific fields on the page
	static $multilingual_fields = array(
		"LangActive"
	);
	private static $defaults = array(
		"LangActive" => true
	);
	protected static $current_class = "MultilingualPage";

	static function set_current_class($current_class) {
		if (!isset(self::$current_class) || self::$current_class == "MultilingualPage") {
			self::$current_class = $current_class;
		}
	}

	function doExtend($hook, $args, $currentclass) {
		if ($currentclass == self::$current_class) {
			$this->extend($hook, $args);
		}
	}

	function getCMSFields() {
		self::set_current_class($this->class);
		SiteTree::disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();
		SiteTree::enableCMSFieldsExtensions();

		return $fields;
	}

	//fix for multilanguage, override getField from dataobject.php
	static $skip_fields=array(
		"ID","ParentID","Version"
	);
	/**
	 * Gets the value of a field.
	 * Called by {@link __get()} and any getFieldName() methods you might create.
	 *
	 * @param string $field The name of the field
	 *
	 * @return mixed The field value
	 */
	public function getField($field) {		
		
		// If we already have an object in $this->record, then we should just return that
		if(isset($this->record[$field]) && is_object($this->record[$field]))  return $this->record[$field];
		
		// Do we have a field that needs to be lazy loaded?
		if(isset($this->record[$field.'_Lazy'])) {
			$tableClass = $this->record[$field.'_Lazy'];
			$this->loadLazyFields($tableClass);
		}

		// Otherwise, we need to determine if this is a complex field
		if(self::is_composite_field($this->class, $field)) {
			$helper = $this->castingHelper($field);
			$fieldObj = Object::create_from_string($helper, $field);

			$compositeFields = $fieldObj->compositeDatabaseFields();
			foreach ($compositeFields as $compositeName => $compositeType) {
				if(isset($this->record[$field.$compositeName.'_Lazy'])) {
					$tableClass = $this->record[$field.$compositeName.'_Lazy'];
					$this->loadLazyFields($tableClass);
				}
			}

			// write value only if either the field value exists,
			// or a valid record has been loaded from the database
			$value = (isset($this->record[$field])) ? $this->record[$field] : null;
			if($value || $this->exists()) $fieldObj->setValue($value, $this->record, false);
			
			$this->record[$field] = $fieldObj;

			return $this->record[$field];
		}

		if (!in_array($field,static::$skip_fields) && Multilingual::current_lang() && !Multilingual::is_default_lang()) {

			$lang = Multilingual::current_lang();

			$langField = $field . '_' . $lang;		
			if (isset($this->record[$langField])) {
				return $this->record[$langField];
			}
		}
		return isset($this->record[$field]) ? $this->record[$field] : null;
	}

	/* public function Link($action = null) {
	  if ($action == "index") {
	  $action = "";
	  }
	  $langSegment = "";
	  if (Multilingual::default_lang() != Multilingual::current_lang() ||
	  isset($this->ForceLang)) {
	  $lang = isset($this->ForceLang)? $this->ForceLang : Multilingual::current_lang();
	  $langSegment = ($lang != "") ? "$lang/" : "";
	  }

	  return Controller::join_links(Director::baseURL(), $langSegment, $this->RelativeLink($action));
	  } */

	/**
	 * Return the link for this {@link SiteTree} object relative to the SilverStripe root.
	 *
	 * By default, it this page is the current home page, and there is no action specified then this will return a link
	 * to the root of the site. However, if you set the $action parameter to TRUE then the link will not be rewritten
	 * and returned in its full form.
	 *
	 * @uses RootURLController::get_homepage_link()
	 * 
	 * @param string $action See {@link Link()}
	 * @return string
	 */
	public function RelativeLink($action = null) {
		$langSegment = "";
		if ((Multilingual::default_lang() != Multilingual::current_lang()) || isset($this->ForceLang)) {

			$lang = isset($this->ForceLang) ? $this->ForceLang : Multilingual::current_lang();
			$langSegment = ($lang != "") ? "$lang/" : "";

			if (isset($this->ForceLang)) {
				$oldlang = Multilingual::current_lang();
				Multilingual::set_current_lang($lang);

			}

		}
		$baselang = $langSegment;
		if($this->ParentID && self::config()->nested_urls) {
			$base = $this->Parent()->RelativeLink($this->URLSegment);

			//$baselang = Multilingual::default_lang();

		} else {
			$base = $this->URLSegment;

			//echo $langSegment." ".$base."<br/>";
		}

		// Unset base for homepage URLSegments in their default language.
		// Homepages with action parameters or in different languages
		// need to retain their URLSegment. We can only do this if the homepage
		// is on the root level.
		if (!$action && $base == RootURLController::get_homepage_link() && !$this->ParentID) {
			$base = null;
			if (class_exists('Translatable') && $this->hasExtension('Translatable') && $this->Locale != Translatable::default_locale()) {
				$base = $this->URLSegment;
			}
		}

		$this->extend('updateRelativeLink', $base, $action);
		// Legacy support
		if ($action === true)
			$action = null;

		$langarr = Multilingual::multilingual_extra_langs();
		if (!Multilingual::is_default_lang($baselang) && !empty($baselang) && !in_array(substr($base, 0, 2), array_values($langarr))) {
			$return = Controller::join_links($baselang, $base, '/', $action);
		} else {
			$return = Controller::join_links($base, '/', $action);
		}
		//echo $return."<br/>";
		if (isset($this->ForceLang)) {
			Multilingual::set_current_lang($oldlang);
		}

		return $return;
	}

	/**
	 * Base link used for previewing. Defaults to absolute URL,
	 * in order to account for domain changes, e.g. on multi site setups.
	 * Does not contain hints about the stage, see {@link SilverStripeNavigator} for details.
	 * 
	 * @param string $action See {@link Link()}
	 * @return string
	 */
	public function PreviewLink($action = null, $stage = "stage") {
		if ($this->hasMethod('alternatePreviewLink')) {
			return $this->alternatePreviewLink($action);
		} else {
			
			if ($stage == "live") {
				$oldStage = Versioned::current_stage();
				Versioned::reading_stage('Live');
				$page = Versioned::get_one_by_stage('SiteTree', 'Live', '"SiteTree"."ID" = ' . $this->ID);
				if ($page) {
					$link = $page->AbsoluteLink();
				} else {
					$link = null;
				}
			} else {
				$page = $this;
				$link = $page->AbsoluteLink();
			}
			if (Multilingual::default_lang() != Multilingual::admin_current_lang()) {
				
				$lang = Multilingual::admin_current_lang();
				$oldlang=  Multilingual::current_lang();
				Multilingual::set_current_lang($lang);
				if(!$page->ActiveLang){
					Multilingual::set_current_lang(Multilingual::default_lang());										
					Multilingual::set_admin_current_lang(Multilingual::default_lang());
				}
				$return = $page->AbsoluteLink($action);
				
				Multilingual::set_current_lang($oldlang);
				if ($stage == "live") {
					Versioned::reading_stage($oldStage);
				}
				return $return;
			}
			if ($stage == "live") {
				Versioned::reading_stage($oldStage);
			}

			return $link;
		}
	}
	function getMultilingualBaseURL(){
		$homepage=Director::absoluteBaseURL();
		$lang=Multilingual::current_lang();
		return Controller::join_links($homepage,$lang);
	}


}

class MultilingualPage_Controller extends ContentController {


	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	private static $allowed_actions = array (	
	);
	/**
	 * This acts the same as {@link Controller::handleRequest()}, but if an action cannot be found this will attempt to
	 * fall over to a child controller in order to provide functionality for nested URLs.
	 *
	 * @return SS_HTTPResponse
	 */
	public function handleRequest(SS_HTTPRequest $request, DataModel $model = null) {

		if (Multilingual::is_default_lang() && !$this->dataRecord->LangActive) {
			return ErrorPage::response_for(404);
		}
		$child = null;
		$action = $request->param('Action');
		$this->setDataModel($model);

		// If nested URLs are enabled, and there is no action handler for the current request then attempt to pass
		// control to a child controller. This allows for the creation of chains of controllers which correspond to a
		// nested URL.
		if ($action && SiteTree::nested_urls() && !$this->hasAction($action)) {
			// See ModelAdController->getNestedController() for similar logic
			if (class_exists('Translatable'))
				Translatable::disable_locale_filter();
			/* START changed from orginal */
			$lang = Multilingual::current_lang();
			if (Multilingual::$use_URLSegment && Multilingual::default_lang() != $lang) {
				$urlsegmentfield = "URLSegment_" . $lang;
			} else {
				$urlsegmentfield = "URLSegment";
			}
			// look for a page with this URLSegment
			$child = $this->model->SiteTree->where(sprintf(
						"\"ParentID\" = %s AND \"" . $urlsegmentfield . "\" = '%s'", $this->ID, Convert::raw2sql(rawurlencode($action))
				))->First();


			if ($child && !$child->canView()) {

				$child = null;
			}
			/* END changed from orginal */

			// look for a page with this URLSegment
			/* $child = $this->model->SiteTree->where(sprintf (
			  "\"ParentID\" = %s AND \"URLSegment\" = '%s'", $this->ID, Convert::raw2sql(rawurlencode($action))
			  ))->First(); */
			if (class_exists('Translatable'))
				Translatable::enable_locale_filter();

			// if we can't find a page with this URLSegment try to find one that used to have 
			// that URLSegment but changed. See ModelAsController->getNestedController() for similiar logic.
			if (!$child) {
				$child = ModelAsController::find_old_page($action, $this->ID);
				if ($child) {
					$response = new SS_HTTPResponse();
					$params = $request->getVars();
					if (isset($params['url']))
						unset($params['url']);
					$response->redirect(
						Controller::join_links(
							$child->Link(
								Controller::join_links(
									$request->param('ID'), // 'ID' is the new 'URLSegment', everything shifts up one position
									$request->param('OtherID')
								)
							),
							// Needs to be in separate join links to avoid urlencoding
							($params) ? '?' . http_build_query($params) : null
						), 301
					);
					return $response;
				}
			}
		}

		// we found a page with this URLSegment.
		if ($child) {
			$request->shiftAllParams();
			$request->shift();

			$response = ModelAsController::controller_for($child)->handleRequest($request, $model);
		} else {
			// If a specific locale is requested, and it doesn't match the page found by URLSegment,
			// look for a translation and redirect (see #5001). Only happens on the last child in
			// a potentially nested URL chain.
			if (class_exists('Translatable')) {
				if ($request->getVar('locale') && $this->dataRecord && $this->dataRecord->Locale != $request->getVar('locale')) {
					$translation = $this->dataRecord->getTranslation($request->getVar('locale'));
					if ($translation) {
						$response = new SS_HTTPResponse();
						$response->redirect($translation->Link(), 301);
						throw new SS_HTTPResponse_Exception($response);
					}
				}
			}

			Director::set_current_page($this->data());
			$response = parent::handleRequest($request, $model);
			Director::set_current_page(null);
		}

		return $response;
	}

	public function Menu($level) {
		return $this->getMenu($level);
	}

	/**
	 * Overload Menu from ContentController
	 */
	public function getMenu($level = 1) {
		$arraylist = parent::getMenu($level);
		$newlist = array();

		if (!Multilingual::is_default_lang()) {
			$currentLang = "LangActive_" . Multilingual::current_lang() . "";
		} else {
			$currentLang = "LangActive";
		}

		foreach ($arraylist->toArray() as $pos => $page) {			
			if ($page->LangActive) {
				$newlist[$pos] = $page;
			}
		}
		//debug::dump($newlist);
		return new ArrayList($newlist);
	}

}
