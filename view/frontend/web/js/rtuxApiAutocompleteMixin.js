/**
 * Mixin to replace the Magento2 autocomplete request with a Boxalino API request
 * Uses the $.boxalino.rtuxApiHelper component which is loaded by adding the Boxalino_RealTimeUserExperience::js.phtml template to layout
 *
 * The following details have been changed from the origin Magento_Search/js/form-mini quickSearch script:
 * - new options parameters required for the API request
 * - the templates for elements of the API response (by default: suggestion, product; can be extended to filters, blogs, etc);
 *   in this sample, the Boxalino Layout Block for autocomplete-product-list has a "template" property that is the ID of the mage-template
 * - the $.getJSON request to Magento2 replaced with the $.ajax request to Boxalino API
 */
define([
    'jquery',
    'underscore',
    'mage/template',
], function ($, _, mageTemplate) {
    'use strict';

    return function (widget) {
        $.widget('mage.quickSearch', widget, {
            options: {
                suggestions: 3,
                hits: 5,
                groupBy:"products_group_id",
                sectionTitleTemplateId:"#rtux-title-template",
                sectionJSmarkupTemplateId:"#rtux-js-markup-template"
            },

            _onPropertyChange:function()
            {
                var searchField = this.element,
                    clonePosition = {
                        position: 'absolute',
                        width: searchField.outerWidth()
                    },
                    dropdown = $('<ul role="listbox"></ul>'),
                    value = this.element.val();

                /** adjust the minSearchLength in configurations if it is to display AC recos on any case **/
                if (value.length >= parseInt(this.options.minSearchLength, 10))
                {
                    let requestData = JSON.stringify(this._getApiRequestData(value));
                    let requestUrl = $.boxalino.rtuxApiHelper.getApiRequestUrl();
                    if (requestData && requestUrl) {
                        $.ajax({
                            type: "post",
                            url: requestUrl,
                            contentType: "application/json",
                            dataType: "json",
                            crossDomain: true,
                            cache: false,
                            data: requestData,
                            success: $.proxy(function (data) {
                                if (data.blocks.length) {
                                    var html = '';

                                    $.each(data.blocks, function (index, element) {
                                        var isProduct = element.content[0] === 'product',
                                            childCount = element.blocks.length,
                                            accessor = isProduct ? 'bx-hit' : 'bx-acQuery',
                                            templateId = element.template[0],
                                            sectionTitleTemplate = mageTemplate(this.options.sectionTitleTemplateId),
                                            sectionJSmarkup = mageTemplate(this.options.sectionJSmarkupTemplateId),
                                            template = mageTemplate(templateId),
                                            parentBxAttributesElement=this._getBxAttributeElement(element);

                                        $.each(element.blocks, function(childIndex, childBlock) {
                                            let item = childBlock[accessor];
                                            /** if array - it means it`s an empty textual suggestion **/
                                            if(item instanceof Array) {
                                                return;
                                            }
                                            item.index = childIndex;
                                            item.bxAttributes = this._getBxAttributeElement(childBlock);
                                            item.parentBxAttributes = parentBxAttributesElement;

                                            /** this can be a custom property class defined on the Layout Block **/
                                            item.class = childBlock.class[0];
                                            if(childIndex === 0) {
                                                /** render the title of the section - if configured **/
                                                html += sectionTitleTemplate({data:element});
                                                if(isProduct) {
                                                    html += sectionJSmarkup({data:parentBxAttributesElement});
                                                }
                                                /** if(isBlog){}.. **/
                                            }

                                            html += template({data:item});

                                            /** close the DIV from the product item template **/
                                            if(childIndex === childCount - 1){
                                                if(isProduct){
                                                    html += "</div>";
                                                }
                                            }
                                        }.bind(this));
                                    }.bind(this));

                                    dropdown.append(html);

                                    /** as seen on mage.quickSearch */
                                    this._resetResponseList(true);

                                    this.responseList.indexList = this.autoComplete.html(dropdown)
                                        .css(clonePosition)
                                        .show()
                                        .find(this.options.responseFieldElements + ':visible');

                                    this.element.removeAttr('aria-activedescendant');

                                    if (this.responseList.indexList.length) {
                                        this._updateAriaHasPopup(true);
                                    } else {
                                        this._updateAriaHasPopup(false);
                                    }

                                    this.responseList.indexList
                                        .on('click', function (e) {
                                            this.responseList.selected = $(e.currentTarget);
                                            this.searchForm.trigger('submit');
                                        }.bind(this))
                                        .on('mouseenter mouseleave', function (e) {
                                            this.responseList.indexList.removeClass(this.options.selectClass);
                                            $(e.target).addClass(this.options.selectClass);
                                            this.responseList.selected = $(e.target);
                                            this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                                        }.bind(this))
                                        .on('mouseout', function (e) {
                                            if (!this._getLastElement() &&
                                                this._getLastElement().hasClass(this.options.selectClass)) {
                                                $(e.target).removeClass(this.options.selectClass);
                                                this._resetResponseList(false);
                                            }
                                        }.bind(this));
                                } else {
                                    this._resetResponseList(true);
                                    this.autoComplete.hide();
                                    this._updateAriaHasPopup(false);
                                    this.element.removeAttr('aria-activedescendant');
                                }
                            }, this),
                            error: $.proxy(function (xhr, status, error) {
                                /** if the API request fails - execute default **/
                                return this._super();
                            }, this).bind(this)
                        }, this);
                    } else {
                        return this.super();
                    }
                } else {
                    this._resetResponseList(true);
                    this.autoComplete.hide();
                    this._updateAriaHasPopup(false);
                    this.element.removeAttr('aria-activedescendant');
                }
            },
            /**
             * additional parameters to be set: returnFields, filters, facets, sort
             * for more details, check the Narrative Api Technical Integration manual provided by Boxalino
             *
             * @param value
             * @private
             */
            _getApiRequestData(value) {
                var otherParameters = {
                    'acQueriesHitCount':this.options.suggestions,
                    'acHighlight': true,        // highlight matching sections
                    'acHighlightPre':"<em>",    //textual suggestion highlight start for match word
                    'acHighlightPost':"</em>",  //textual suggestion highlight end for match word
                    'query':value,
                    'filters': [
                        {"field": "visibility", "values": [1,3], "negative":true},
                        {"field": "status","values": [1]}
                    ],
                    'queries': [
                        {"name": "brand", "minHitCount": 4}
                    ]
                };
                return $.boxalino.rtuxApiHelper.getApiRequestData(
                    "autocomplete",
                    this.options.hits,
                    this.options.groupBy,
                    otherParameters
                );
            },

            /**
             * helper function to access the bx-attributes easier in the js markup
             * removes the - from the name and creates a camelcase key for bx-attributes name
             *
             * @param element
             * @returns {{}}
             * @private
             */
            _getBxAttributeElement(element) {
                let bxAttributesElement={};
                $.each(element["bx-attributes"], function(bxAttrElIndex, bxAttrElement){
                    let key = bxAttrElement.name.replace(/(-.)/g, function(x) {
                        return x[1].toUpperCase();
                    });
                    bxAttributesElement[key] = bxAttrElement.value;
                })

                return bxAttributesElement;
            }
        });

        return $.mage.quickSearch;
    };
});
