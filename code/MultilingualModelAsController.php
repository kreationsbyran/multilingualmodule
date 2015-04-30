<?php
class MultilingualModelAsController extends ModelAsController {
	function init() {
		parent::init();

		if(isset($_SERVER['REQUEST_URI'])){
			$baseUrl = Director::baseUrl();
			$requestUri = $_SERVER['REQUEST_URI'];
			$lang = substr($requestUri, strlen($baseUrl), 2);
			if($lang==Multilingual::default_lang())$lang="";

			$localesmap= Multilingual::map_locale();
			if(!empty($localesmap[$lang])){
				Multilingual::set_current_lang($lang);
				i18n::set_locale($localesmap[$lang]); //Setting the locale				
			}else {
				i18n::set_locale($localesmap[Multilingual::default_lang()]); // default locale
			}
		}
		$this->setURLParams($this->request->allParams());		

		if($this->request->latestParam("URLSegment") == "" || $this->request->latestParam("URLSegment") == 'home' || $lang."/".$this->request->latestParam("URLSegment")==RootURLController::get_homepage_link()) {

            $langactivefield="LangActive";
            if(!Multilingual::is_default_lang()){
                $langactivefield="LangActive_".$lang;
            }
            if(class_exists('HomepageForDomainExtension')) {

                $host       = str_replace('www.', null, $_SERVER['HTTP_HOST']);
                $SQL_host   = Convert::raw2sql($host);

                $candidates = DataList::create("MultilingualPage")->where("\"HomepageForDomain\" LIKE '%$SQL_host%' AND ".$langactivefield."=1");
                if($candidates) foreach($candidates as $candidate) {
                    if(preg_match('/(,|^) *' . preg_quote($host) . ' *(,|$)/', $candidate->HomepageForDomain)) {
                        $homepage=$candidate;
                    }
                }
            }else{
                $homepage=DataList::create("MultilingualPage")->where("URLSegment='home' AND ".$langactivefield."=1")->First();
            }

			$_POST['this_is_a_hack_to_stop_the_home_redirect'] = true;
			if($homepage){
				$urlparams = array('URLSegment' => $homepage->URLSegment, 'Action' => ' ');
				$this->setURLParams($urlparams);
			}else{
				$homepage=DataList::create("SiteTree")->where("URLSegment='home'")->First();
				Multilingual::set_current_lang(Multilingual::default_lang());

				$this->response->redirect(
					Controller::join_links(
						$homepage->Link(
							Controller::join_links(
								$this->request->param('Action'),
								$this->request->param('ID'),
								$this->request->param('OtherID')
							)
						)
					),301);
			}
		}
	}
	
	
	/**
	 * @return ContentController
	 */
	public function getNestedController() {
		$request = $this->request;		
		$params=$this->getURLParams();
		
		if(!$URLSegment = $params['URLSegment']) {
			throw new Exception('ModelAsController->getNestedController(): was not passed a URLSegment value.');
		}
		/* modded from original */
		$lang=Multilingual::current_lang();
		if(Multilingual::$use_URLSegment && Multilingual::default_lang()!=$lang){
			$urlsegmentfield="URLSegment_".$lang;			
		}else{
			$urlsegmentfield="URLSegment";
			
		}		
		
		// Find page by link, regardless of current locale settings
		if(class_exists('Translatable')) Translatable::disable_locale_filter();
		$sitetree = DataObject::get_one(
			'SiteTree', 
			sprintf(
				'"'.$urlsegmentfield.'" = \'%s\' %s', 
				Convert::raw2sql(rawurlencode($URLSegment)),
				(SiteTree::config()->nested_urls ? 'AND "SiteTree"."ParentID" = 0' : null)
			)
		);		
		if(class_exists('Translatable')) Translatable::enable_locale_filter();
		
		if(!$sitetree) {
			// If a root page has been renamed, redirect to the new location.
			// See ContentController->handleRequest() for similiar logic.
			$redirect = self::find_old_page($URLSegment);
			if($redirect) {
				$params = $request->getVars();
				if(isset($params['url'])) unset($params['url']);
				$this->response = new SS_HTTPResponse();
				$this->response->redirect(
					Controller::join_links(
						$redirect->Link(
							Controller::join_links(
								$request->param('Action'), 
								$request->param('ID'), 
								$request->param('OtherID')
							)
						),
						// Needs to be in separate join links to avoid urlencoding
						($params) ? '?' . http_build_query($params) : null
					),
					301
				);
				
				return $this->response;
			}
			
			$response = ErrorPage::response_for(404);
			$this->httpError(404, $response ? $response : 'The requested page could not be found.');
		}
		
		// Enforce current locale setting to the loaded SiteTree object
		if(class_exists('Translatable') && $sitetree->Locale) Translatable::set_current_locale($sitetree->Locale);
		
		if(isset($_REQUEST['debug'])) {
			Debug::message("Using record #$sitetree->ID of type $sitetree->class with link {$sitetree->Link()}");
		}
		
		return self::controller_for($sitetree, $this->request->param('Action'));
	}
	
	
	
	/**
	 *  * Modded from original to make alternative URLSegments to work (from multilingual)
	 * 
	 * @param string $URLSegment A subset of the url. i.e in /home/contact/ home and contact are URLSegment.
	 * @param int $parentID The ID of the parent of the page the URLSegment belongs to. 
	 * @return SiteTree
	 */
	static function find_old_page($URLSegment,$parentID = 0, $ignoreNestedURLs = false) {
		/*modded */
		$lang=Multilingual::current_lang();		
		if(Multilingual::$use_URLSegment && Multilingual::default_lang()!=$lang){
			$urlsegmentfield="URLSegment_".$lang;			
		}else{
			$urlsegmentfield="URLSegment";			
		}
		$URLSegment = Convert::raw2sql($URLSegment);
		
		//$URLSegment = Convert::raw2sql(rawurlencode($URLSegment));
		
		
		$useParentIDFilter = SiteTree::nested_urls() && $parentID;
				
		// First look for a non-nested page that has a unique URLSegment and can be redirected to.
		if(SiteTree::nested_urls()) {
			$pages = DataObject::get(
				'SiteTree', 
				"\"".$urlsegmentfield."\" = '$URLSegment'" . ($useParentIDFilter ? ' AND "ParentID" = ' . (int)$parentID : '')
			);
			if($pages && $pages->Count() == 1) return $pages->First();
		}
		
		
		// Get an old version of a page that has been renamed.
		$query = new SQLQuery (
			'"RecordID"',
			'"SiteTree_versions"',
			"\"$urlsegmentfield\" = '$URLSegment' AND \"WasPublished\" = 1" . ($useParentIDFilter ? ' AND "ParentID" = ' . (int)$parentID : ''),
			'"LastEdited" DESC',
			null,
			null,
			1
		);
		/*end modded*/
		$record = $query->execute()->first();
		
		if($record && ($oldPage = DataObject::get_by_id('SiteTree', $record['RecordID']))) {
			// Run the page through an extra filter to ensure that all extensions are applied.
			if(SiteTree::get_by_link($oldPage->RelativeLink())) return $oldPage;
		}
	}
	
	

}