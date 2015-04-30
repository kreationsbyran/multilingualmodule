<?php
/*
 * Necessary to make SiteConfig multilingual
 * An alternative is to simply copy-paste this function in to siteconfig directly
 */
class MultilingualSiteConfig extends DataObject{		
	function getRequirementsForPopup(){
		$this->extend("onRequirementsForPopup");
	}
	public function __construct($record = null, $isSingleton = false, $model = null) {
		$originalfields=Config::inst()->get(get_class($this),"db");		
		$multilingual_fields=Multilingual::get_class_multilingual_fields(get_class($this),$extensions=true);
		if(sizeof($multilingual_fields)>0){
			$db=array();
			Multilingual::create_db_fields($originalfields,$multilingual_fields,$db);	
			$full=array_merge($originalfields, $db);
			Config::Inst()->update(get_class($this),"db",$db);	
			
			
		}
		parent::__construct($record, $isSingleton, $model);
	}
	
	//fix for multilanguage, override getField from dataobject.php	
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
		if (Multilingual::current_lang() && !Multilingual::is_default_lang()) {			
			$lang = Multilingual::current_lang();
			$langField = $field . '_' . $lang;
			
			if (isset($this->record[$langField])) {
				return $this->record[$langField];
			}
		}	

		return isset($this->record[$field]) ? $this->record[$field] : null;
	}


}