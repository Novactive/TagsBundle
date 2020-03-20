/*global $*/

(function() {
  'use strict';

  var $ = jQuery;

  var initTagsTranslations = function () {
    $.EzTags.Base.defaults.translations = {
      selectedTags: 'Selected tags',
      loading: 'Loading...',
      noSelectedTags: 'There are no selected tags',
      suggestedTags: 'Suggested tags',
      noSuggestedTags: 'There are no tags to suggest',
      addNew: 'Add new',
      clickAddThisTag: 'Click to add this tag',
      removeTag: 'Remove tag',
      translateTag: 'Translate tag',
      existingTranslations: 'Existing translations',
      noExistingTranslations: 'No existing translations',
      addTranslation: 'Add translation',
      cancel: 'Cancel',
      ok: 'OK',
      browse: 'Browse',
    };
  };

  initTagsTranslations();
  $('.tagssuggest').EzTags();
  $('.parent-selector-tree').find('.tags-modal-tree').tagsTree({'modal': true});

})();

(function (global) {
    const SELECTOR_FIELD = '.ez-field-edit--eztags';

    class EzTagsValidator extends global.eZ.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzStringValidator
         */
        validateInput(event) {
            const isRequired = event.target.closest(SELECTOR_FIELD).querySelector('.tagssuggest').dataset.required === "1";
            const isEmpty = !event.target.value;
            const isError = (isEmpty && isRequired);
            const label = event.target.closest(SELECTOR_FIELD).querySelector('.ez-field-edit__label').innerHTML;
            const result = {isError};

            if (isEmpty) {
                result.errorMessage = global.eZ.errors.emptyField.replace('{fieldName}', label);
            }
            return result;
        }
    };

    const validator = new EzTagsValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ez-field-edit--eztags input.tagids',
                eventName: 'change',
                callback: 'validateInput',
                errorNodeSelectors: ['.ez-field-edit__label-wrapper'],
                invalidStateSelectors: ['input.tagids']
            },
        ],
    });

    validator.init();

    global.eZ.fieldTypeValidators = global.eZ.fieldTypeValidators ?
        [...global.eZ.fieldTypeValidators, validator] :
        [validator];
})(window);
