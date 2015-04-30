<?php

/*
 * class that enables multilingual support for all dataobjects that inherits from it
 * 
 * Extend from this DataObject to make your dataobjects multilingual
 */
if(class_exists("Widget")){
	class MultilingualWidget extends Widget {

		//if set it will override the global lang setting (Multilingual::current_lang())
		public $ForceLang;
		static $db = array(
		
		);
		static $multilingual_fields = array(
		
		);

		static function add_multilingual_fields($new_fields) {
			static::$multilingual_fields = array_merge(static::$multilingual_fields, $new_fields);
		}

		static $defaults = array(
		
		);
		protected static $current_class = "MultilingualDataObject";
		public static $default_sort = "\"Sort\"";
		static function set_current_class($current_class) {
			if (!isset(self::$current_class) || self::$current_class == "MultilingualDataObject") {
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
			$fields = new FieldList(
				$rootTab = new TabSet("Root", $tabMain = new Tab('Main')
				)
			);		
			return $fields;
		}

		function getRequirementsForPopup() {
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
			if (isset($this->record[$field]) && is_object($this->record[$field]))
				return $this->record[$field];

			// Do we have a field that needs to be lazy loaded?
			if (isset($this->record[$field . '_Lazy'])) {
				$tableClass = $this->record[$field . '_Lazy'];
				$this->loadLazyFields($tableClass);
			}

			// Otherwise, we need to determine if this is a complex field
			if (self::is_composite_field($this->class, $field)) {
				$helper = $this->castingHelper($field);
				$fieldObj = Object::create_from_string($helper, $field);

				$compositeFields = $fieldObj->compositeDatabaseFields();
				foreach ($compositeFields as $compositeName => $compositeType) {
					if (isset($this->record[$field . $compositeName . '_Lazy'])) {
						$tableClass = $this->record[$field . $compositeName . '_Lazy'];
						$this->loadLazyFields($tableClass);
					}
				}

				// write value only if either the field value exists,
				// or a valid record has been loaded from the database
				$value = (isset($this->record[$field])) ? $this->record[$field] : null;
				if ($value || $this->exists())
					$fieldObj->setValue($value, $this->record, false);

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

		/**
		 * OVERRIDE WIDGET Function
		 * @return FieldList
		 */
		public function CMSEditor() {
			$fields = $this->getCMSFields();
			$outputFields = new FieldList();
			foreach ($fields as $field) {
				$name = $field->getName();
				$value = $this->getField($name);
				if ($value) {
					$field->setValue($value);
				}
				$name = preg_replace("/([A-Za-z0-9\-_]+)/", "Widget[" . $this->ID . "][\\1]", $name);
				$field->setName($name);
				$outputFields->push($field);
			}

			$this->extend("onCMSEditor",$outputFields);
			return $outputFields;
		}

	}
}