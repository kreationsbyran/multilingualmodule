<?php

class CMSFieldsExtension extends DataExtension {
	/*
	 * Replace all fields added in $multilingual array to multilingual versions
	 * Please use the "SiteTree::disableCMSFieldsExtensions()" and "SiteTree::enableCMSFieldsExtensions()"
	 * bewtween "parent::getCMSFields()" in your sub classes for full access to all multilingual fields.
	 */

	function updateCMSFields(FieldList $fields) {


		//echo get_class($originalfield);
		//$fields->addFieldToTab("Root.Main",new TextAreaField("test"));
		if ($this->owner instanceof MultilingualPage ||
				$this->owner instanceof MultilingualDataObject ||
				$this->owner instanceof MultilingualSiteConfig) {
			Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
			Requirements::javascript("multilingual/javascript/multilingual.js");
			Requirements::css("multilingual/css/multilingual.css");

			//Requirements::block("cms/javascript/CMSMain.EditForm.js");
			Requirements::javascript("multilingual/javascript/CMSMain.EditForm.Multilingual.js");
			//Requirements::block("cms/javascript/SiteTreeURLSegmentField.js");
			//Requirements::javascript("multilingual/javascript/SiteTreeURLSegmentField.js");			
			$class_multilingual_fields = Config::inst()->get($this->owner->ClassName, 'multilingual_fields') ? Config::inst()->get($this->owner->ClassName, 'multilingual_fields') : array();
			$baseclass = $this->owner == "SiteConfig" ? "SiteConfig" : ClassInfo::baseDataClass($this->owner);

			$multilingual_fields = $class_multilingual_fields;

			if (!$this->owner instanceof Widget) {
				foreach ($multilingual_fields as $fieldkey) {
					$newfields = null;
					$originalfield = $fields->dataFieldByName($fieldkey);
					if ($originalfield) {
						self::convert_field_to_multilingual($originalfield, $fields, $this->owner);
					}
				}
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

			//Fix original fields
			//$extraclass = Multilingual::admin_current_lang() != $langcode ? "hiddenfield" : "";

			$fieldclone = clone $originalfield;

			$fieldclone->setName($fieldid);

			$fieldclone->setTitle($originalfield->Title() . " 
							<span class='marked-label " . $langcode . "' title='" . $langnice . "'>
								<img src='multilingual/images/flag-" . $langcode . ".png' />
							<span class='language-nice'> </span></span>");

			$fieldclone->setValue($owner->$fieldname);

			if ($fieldname == "URLSegment") {
				/* $hiddenclass = Multilingual::admin_current_lang() != $langcode ? "urlsegmenthiddenfield" : "";
				  $fieldclone->addExtraClass("multilingual " . $hiddenclass);
				  $baseLink = Controller::join_links (
				  Director::absoluteBaseURL(),
				  (SiteTreeURLSegmentField::config()->nested_urls && $this->owner->ParentID ? $this->owner->Parent()->RelativeLink(true) : null)
				  );

				  $fieldclone->setURLPrefix($baseLink); */
			} else {
				$hiddenclass = Multilingual::admin_current_lang() != $langcode ? "hiddenfield" : "";
				$fieldclone->addExtraClass("multilingual " . $hiddenclass);
			}

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
		$class_multilingual_fields = Config::inst()->get($this->owner->ClassName, 'multilingual_fields', Config::INHERITED) ? Config::inst()->get($this->owner->ClassName, 'multilingual_fields', Config::INHERITED) : array();
		$multilingual_fields = $class_multilingual_fields;
		foreach ($multilingual_fields as $fieldkey) {

			$newfields = null;
			$searchname = preg_replace("/([A-Za-z0-9\-_]+)/", "Widget[" . $this->owner->ID . "][\\1]", $fieldkey);
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
						$fieldkey_ml = $fieldkey . "_" . $langcode;
						$fieldid = substr($fieldname, 0, -1) . "_" . $langcode . "]";
					}

					//Fix original fields
					//$extraclass = Multilingual::admin_current_lang() != $langcode ? "hiddenfield" : "";

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

}