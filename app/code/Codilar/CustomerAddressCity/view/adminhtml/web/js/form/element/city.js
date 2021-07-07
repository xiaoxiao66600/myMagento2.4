/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Codilar_CustomerAddressCity/js/form/element/cityUi'
], function (City) {
    'use strict';

    return City.extend({
        defaults: {
            cityScope: 'data.city'
        },

        /**
         * Set region to customer address form
         *
         * @param {String} value - region
         */
        setDifferedFromDefault: function (value) {
            this._super();

            if (parseFloat(value)) {
                this.source.set(this.cityScope, this.indexedOptions[value].label);
            }
        }
    });
});
