/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Codilar_CustomerAddressCity/js/form/element/districtUi'
], function (District) {
    'use strict';

    return District.extend({
        defaults: {
            districtScope: 'data.district'
        },

        /**
         * Set region to customer address form
         *
         * @param {String} value - region
         */
        setDifferedFromDefault: function (value) {
            this._super();

            if (parseFloat(value)) {
                this.source.set(this.districtScope, this.indexedOptions[value].label);
            }
        }
    });
});
