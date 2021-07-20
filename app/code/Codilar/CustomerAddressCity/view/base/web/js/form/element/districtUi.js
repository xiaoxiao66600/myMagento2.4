/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver'
], function (_, registry, Select, defaultPostCodeResolver) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.city_id:value'
            }
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            var city = registry.get(this.parentName + '.' + 'city_id'),
                options = city.indexedOptions,
                isDistrictRequired,
                option;

            if (!value) {
                return;
            }
            option = options[value];

            if (typeof option === 'undefined') {
                return;
            }

            if (this.skipValidation) {
                this.validation['required-entry'] = false;
                this.required(false);
            } else {
                if (option && !option['is_district_required']) {
                    this.error(false);
                    this.validation = _.omit(this.validation, 'required-entry');
                    registry.get(this.customName, function (input) {
                        input.validation['required-entry'] = false;
                        input.required(false);
                    });
                } else {
                    this.validation['required-entry'] = true;
                }

                if (option && !this.options().length) {
                    registry.get(this.customName, function (input) {
                        isDistrictRequired = !!option['is_district_required'];
                        input.validation['required-entry'] = isDistrictRequired;
                        input.validation['validate-not-number-first'] = true;
                        input.required(isDistrictRequired);
                    });
                }

                this.required(!!option['is_district_required']);
            }
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {
            var superFn = this._super;

            registry.get(this.parentName + '.' + 'city_id', function (city) {
                var option = city.indexedOptions[value];

                superFn.call(this, value, field);

                if (option && option['is_district_visible'] === false) {
                    // hide select and corresponding text input field if region must not be shown for selected country
                    this.setVisible(false);

                    if (this.customEntry) {// eslint-disable-line max-depth
                        this.toggleInput(false);
                    }
                }
            }.bind(this));
        }
    });
});

