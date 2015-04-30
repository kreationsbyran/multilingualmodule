/**
 * Extends File: CMSMain.EditForm.js and adds functionality of URLSegment fields in the cms admin
 */
(function($) {
	$.entwine('ss', function($){
		/**
		 * Class: .cms-edit-form :input[name=ClassName]
		 * Alert the user on change of page-type. This might have implications
		 * on the available form fields etc.
		 */
		$('.cms-edit-form :input[name=ClassName]').entwine({
			// Function: onchange
			onchange: function() {
				alert(ss.i18n._t('CMSMAIN.ALERTCLASSNAME'));
			}
		});

		
		$('.cms-edit-form input[name^=Title_]').entwine({
			// Constructor: onmatch
			onmatch : function() {
				var self = this;				
				self.data('OrigVal', self.val());
				
				var form = self.closest('form');
				var urlSegmentInput = $('input:text[name=URLSegment]', form);
				var liveLinkInput = $('input[name=LiveLink]', form);

				if (urlSegmentInput.length > 0) {
					self._addActions2();
					this.bind('change', function(e) {
						var origTitle = self.data('OrigVal');
						var title = self.val();
						self.data('OrigVal', title);
						console.log(title);
						// Criteria for defining a "new" page
						if ((urlSegmentInput.val().indexOf('new') == 0) && liveLinkInput.val() == '') {
							self.updateURLSegment2(title);
						} else {
							$('.update', self.parent()).show();
						}

						self.updateRelatedFields2(title, origTitle);
						self.updateBreadcrumbLabel(title);
					});
				}

				this._super();
			},
			onunmatch: function() {
				this._super();
			},
			
			/**
			 * Function: updateRelatedFields
			 * 
			 * Update the related fields if appropriate
			 * (String) title The new title
			 * (Stirng) origTitle The original title
			 */
			updateRelatedFields2: function(title, origTitle) {
				// Update these fields only if their value was originally the same as the title				
				var langsegment="";
				if(index=this.attr("name").indexOf("_")){
					var lang=this.attr("name").substring(index+1);
					langsegment="_"+lang;					
				}
				this.parents('form').find('input[name=MetaTitle'+langsegment+'], input[name=MenuTitle'+langsegment+']').each(function() {
					
					var $this = $(this);
					
					if($this.val() != title) {
						$this.val(title);
						// Onchange bubbling didn't work in IE8, so .trigger('change') couldn't be used
						if($this.updatedRelatedFields2) $this.updatedRelatedFields2();
					}
				});
			},
			
			/**
			 * Function: updateURLSegment
			 * 
			 * Update the URLSegment
			 * (String) title
			 */
			updateURLSegment2: function(title) {
				var langsegment="";
				if(index=this.attr("name").indexOf("_")){
					var lang=this.attr("name").substring(index+1);
					langsegment="_"+lang;					
				}
				
				var urlSegmentInput = $('input:text[name=URLSegment'+langsegment+']', this.closest('form'));
				var urlSegmentField = urlSegmentInput.closest('.field.urlsegment');
				var updateURLFromTitle = $('.update', this.parent());
				urlSegmentField.update(title);
				if (updateURLFromTitle.is(':visible')) {
					updateURLFromTitle.hide();
				}
				
			},
			
			/**
			 * Function: _addActions
			 *  
			 * Utility to add update from title action
			 * 
			 */
			_addActions2: function() {
				var self = this;
				var	updateURLFromTitle;
				
				// update button
				updateURLFromTitle = $('<button />', {
					'class': 'update ss-ui-button-small',
					'text': 'Update URL',
					'click': function(e) {
						e.preventDefault();
						self.updateURLSegment2(self.val());					
					}
				});				
				// insert elements
				updateURLFromTitle.insertAfter(self);
				updateURLFromTitle.hide();
			}
			
		});

		/**
		 * MenuTitle
		 */
		$('.cms-edit-form input[name^=MenuTitle_]').entwine({
			onchange: function() {
				this.updatedRelatedFields2();
			},

			

		});			
	});
}(jQuery));
