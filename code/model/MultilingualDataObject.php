<?php
/*
 * class that enables multilingual support for all dataobjects that inherits from it
 * 
 * Extend from this DataObject to make your dataobjects multilingual
 */
class MultilingualDataObject extends DataObject{		
	//if set it will override the global lang setting (Multilingual::current_lang())
	public $ForceLang;

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

	private static $db = array(
		"LangActive"=>"Boolean",		
	);
	
	static $multilingual_fields=array(
		"LangActive"
	);
	static function add_multilingual_fields($new_fields){
		static::$multilingual_fields=array_merge(static::$multilingual_fields, $new_fields);		
	}
	static $defaults=array(
		"LangActive"=>true
	);
	protected static $current_class="MultilingualDataObject";
	static function set_current_class($current_class){		
		if(!isset(self::$current_class) || self::$current_class=="MultilingualDataObject"){			
			self::$current_class=$current_class;
		}
	}
	function doExtend($hook,$args,$currentclass){				
		if($currentclass==self::$current_class){						
			$this->extend($hook,$args);
		}
	}	
	function getCMSFields(){
		self::set_current_class($this->class);	

		$tabbedFields = $this->scaffoldFormFields(array(
			// Don't allow has_many/many_many relationship editing before the record is first saved
			'includeRelations' => ($this->ID > 0),
			'tabbed' => true,
			'ajaxSafe' => true,
			'restrictFields'=>Multilingual::get_all_multilingual_fields($this->class)// array_keys(Multilingual::get_originalfields_from_class($this->class,'db',true))
		));
		//debug::dump((Multilingual::get_all_multilingual_fields($this->class)));
		
		if(!Multilingual::get_extend_in_child()) {
			$this->extend('updateCMSFields', $tabbedFields);
		}
		
		return $tabbedFields;
	}
	
	function getRequirementsForPopup(){
		$this->extend("onRequirementsForPopup");
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


	public function scaffoldSearchFields($_params = null) {
		$params = array_merge(
			array(
				'fieldClasses' => false,
				'restrictFields' => false
			),
			(array)$_params
		);
		$fields = new FieldList();
		foreach($this->searchableFields() as $fieldName => $spec) {
			if($params['restrictFields'] && !in_array($fieldName, $params['restrictFields'])) continue;
			
			// If a custom fieldclass is provided as a string, use it
			if($params['fieldClasses'] && isset($params['fieldClasses'][$fieldName])) {
				$fieldClass = $params['fieldClasses'][$fieldName];
				$field = new $fieldClass($fieldName);
			// If we explicitly set a field, then construct that
			} else if(isset($spec['field'])) {
				// If it's a string, use it as a class name and construct
				if(is_string($spec['field'])) {
					$fieldClass = $spec['field'];
					$field = new $fieldClass($fieldName);
					
				// If it's a FormField object, then just use that object directly.
				} else if($spec['field'] instanceof FormField) {
					$field = $spec['field'];
					
				// Otherwise we have a bug
				} else {
					user_error("Bad value for searchable_fields, 'field' value: "
						. var_export($spec['field'], true), E_USER_WARNING);
				}
				
			// Otherwise, use the database field's scaffolder
			} else if($this->relObject($fieldName)){
				$field = $this->relObject($fieldName)->scaffoldSearchField();
			}

			if (strstr($fieldName, '.')) {
				$field->setName(str_replace('.', '__', $fieldName));
			}
			$field->setTitle($spec['title']);

			$fields->push($field);
		}
		return $fields;
	}
	
	

}
