/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (http://www.amasty.com)
 * @package Amasty_Xsearch
 */

var Xsearch = Class.create({
    timer: null,
    delay: 500,
    options: {},
    popupSelector: '#am_search_popup',
    popupElementSelector: '.am_element, .am-category',
    searchMiniFormSelector: '[id^="search_mini_form"]',
    maxVerticalPopupWidth: 600,
    currentItem: null,
    searchInput: null,

    initialize: function (options) {
        this.options = options;

        if (!this.prepareSearchElement()) {
            return false;
        }
        this.searchInput.stopObserving();

        if (this.options.delay !== undefined) {
            this.delay = this.options.delay;
        }
        var _caller = this;

        if (!this.searchInput.getAttribute("placeholder")) {
            this.searchInput.observe('click', function () {
                if (!_caller.searchInput.getAttribute('firstClick')) {
                    _caller.searchInput.value = '';
                    _caller.searchInput.setAttribute('firstClick', 1);
                }
            });
        }

        this.searchInput.observe('keyup', function (event) {
            if ([37, 38, 39, 40, 9, 13].indexOf(event.keyCode) !== -1) // arrow keys, tab, enter
                return;

            if (this.value.length >= _caller.options.minChars) {
                var q = this.value;

                if (_caller.timer !== null) {
                    clearTimeout(_caller.timer);
                }

                _caller.timer = setTimeout(function () {
                    _caller.search.call(_caller, q);
                }, _caller.delay);
            }

        });

        this.searchInput.observe('keydown', function (event) {
            var searchPopup = $$(this.popupSelector).first();
            if (searchPopup) {
                if ([38, 40, 9].indexOf(event.keyCode) !== -1) {
                    if (this.currentItem === null) {
                        this.currentItem = 0;
                    } else {
                        var totalItems = searchPopup.select(this.popupElementSelector).length;

                        switch (event.keyCode) {
                            case 38:
                                this.currentItem = (this.currentItem + totalItems - 1) % totalItems;
                                break;
                            case 40:
                            case 9:
                                this.currentItem = (this.currentItem + 1) % totalItems;
                                break;
                        }
                    }

                    searchPopup.select(this.popupElementSelector).each(function (element, i) {
                        if (i === this.currentItem) {
                            element.addClassName('active');
                        } else {
                            element.removeClassName('active');
                        }
                    }.bind(this));
                    event.stop();
                } else if (event.keyCode == 13) {
                    if (typeof this.currentItem == 'number') {
                        var itemLocation = searchPopup.down('a', this.currentItem).readAttribute('href');
                        window.location.href = itemLocation;

                        event.stop();
                    }
                }
            }
        }.bind(this));

        document.observe('click', function () {
            _caller.clear();
        });
    },

    prepareSearchElement: function () {
        var element = $$(this.options.selector);
        if (element.length === 0) {
            element = $$('input[id^="search"]');//most popular variant
        }

        if (element.length > 0) {
            this.searchInput = $(element.first());
            return true;
        }

        console.log('Please setup correct DOM-selector for Amasty Search Pro extension');
        return false;
    },

    clear: function () {
        if ($$('[id=am_search_container]').length > 0) {
            $$('[id=am_search_container]').each(function (el) {
                el.remove();
            });
        }
    },

    loading: function (flag) {
        if (flag) {
            this.searchInput.addClassName('loading');
        } else {
            this.searchInput.removeClassName('loading');
        }
    },

    check_json: function(str) {
        str = str.substr(0, str.lastIndexOf('}') + 1);

        var tmp = str;
        while (tmp.indexOf("{") > -1 && !str.isJSON()) {
            str = tmp.substr(tmp.indexOf("{"));
            tmp = str.substr(1);
        }
        if (!str.isJSON()) {
            throw new Error('Cannot convert response data to JSON');
        }
        return str;
    },

    search: function(q){
        var _caller = this;
        _caller.loading(true);

        new Ajax.Request(_caller.options.url, {
            method: 'get',
            requestHeaders: {Accept: 'application/json'},
            parameters: {q: q},
            onSuccess: function (response) {
                _caller.clear();

                var str = _caller.check_json(response.responseText);
                var json = str.evalJSON(true);

                _caller.loading(false);

                if (json.products.items.length > 0) {
                    _caller.showPopup(json, q);
                }
            }
        });

    },

    hightlight: function (data, text) {
        var words = text.trim().split(' ');

        var reg = new RegExp('(' + words.join('|') + ')', "ig");
        if (data) {
            data = data.replace(reg, "<span class='amhighlight'>$1</span>");
        }

        return data;
    },

    showPopup: function (data, text) {
        this.currentItem = null;
        var container = new Element('div', {
            'id': 'am_search_container'
        });

        var mainContainer = new Element('div', {
            'class': 'am_search_popup',
            'id': 'am_search_popup'
        });

        var innerContainer = new Element('div', {
            'class': 'am_search_popup_inner'
        });

        var popupWidth = this.options.popupWidth;

        if (data.categories && data.categories.items.length) {
            var categoriesContainer = new Element('div');
            categoriesContainer.addClassName('am-categories');

            if (popupWidth <= this.maxVerticalPopupWidth) {
                categoriesContainer.addClassName('am-fullwidth');
            }

            var categoriesTitle = new Element('h3');
            categoriesTitle.addClassName('am-container-title');
            categoriesTitle.innerHTML = data.categories.title;

            categoriesContainer.appendChild(categoriesTitle);
            data.categories.items.forEach(function (category) {
                var categoryContainer = new Element('div');
                categoryContainer.addClassName('am-category');

                var link = new Element('a');
                link.setAttribute('href', category.link);
                link.addClassName('category-name');
                link.innerHTML = this.hightlight(category.name, text);

                categoryContainer.appendChild(link);
                categoriesContainer.appendChild(categoryContainer);
            }.bind(this));

            innerContainer.appendChild(categoriesContainer);
        }

        if (data.products) {
            var productContainer = new Element('div'),
                productTitle = new Element('h3');

            productContainer.addClassName('am-products');
            productTitle.addClassName('am-container-title');
            productTitle.innerHTML = data.products.title;
            productContainer.appendChild(productTitle);

            if (!data.categories || !data.categories.items.length || popupWidth <= this.maxVerticalPopupWidth) {
                productContainer.addClassName('am-fullwidth');
            }

            for (var order in data.products.items) {
                var item = data.products.items[order];
                if (typeof(item) == 'object') {
                    var element = new Element('div');
                    element.addClassName('am_element');

                    var containerImage = new Element('div');
                    containerImage.addClassName('am_image');

                    var image = new Element('img');
                    image.setAttribute('src', item.image);

                    containerImage.appendChild(image);
                    element.appendChild(containerImage);

                    var containerText = new Element('div');
                    containerText.addClassName('am_right');

                    var rightColumn = [];
                    rightColumn.push('<div class="am_title">', this.hightlight(item.name, text), '</div>');

                    if (item.reviews != '') {
                        rightColumn.push('<div class="amreviews">', item.reviews, '</div>');
                    }
                    rightColumn.push(this.hightlight(item.description, text));
                    rightColumn.push('<div class="price_footer"><div class="price_cell">', item.price);
                    rightColumn.push('</div>', item.add_to_cart);


                    if (item.image_block_size && popupWidth > this.maxVerticalPopupWidth) {
                        containerImage.setStyle({
                            'maxWidth': item.image_block_size - 1 + '%'
                        });

                        containerText.setStyle({
                            'maxWidth': 100 - item.image_block_size - 1 + '%'
                        });
                    }

                    containerText.innerHTML += rightColumn.join('');
                    element.appendChild(containerText);

                    var link = new Element('a');
                    link.setAttribute('href', item.url);
                    link.addClassName('am-product-link');
                    link.appendChild(element);

                    if (popupWidth <= this.maxVerticalPopupWidth) {
                        link.addClassName('am-fullwidth');
                    }

                    productContainer.appendChild(link);
                }
            }
            innerContainer.appendChild(productContainer);
        }

        if (data.bottomHtml) {
            var bottomHtml = new Element('div');
            bottomHtml.addClassName('am-more-results');
            bottomHtml.innerHTML = data.bottomHtml;
            innerContainer.appendChild(bottomHtml);
        }

        mainContainer.appendChild(innerContainer);
        container.appendChild(mainContainer);

        var miniForm = this.searchInput.up(this.searchMiniFormSelector);
        if (miniForm) {
            miniForm.appendChild(container);
        } else {
            console.log('Search Mini From selector is incorrect.');
        }

        //this.initEvents(mainContainer);
        this.resizePopup();
    },

    resizePopup: function () {
        var max = 0;
        $$(this.searchMiniFormSelector + ' .price_cell').each(function (el) {
            max = el.getWidth() > max ? el.getWidth() : max;
        });

        $$(this.searchMiniFormSelector + ' .price_cell').each(function (el) {
            el.setStyle({'width': max + 'px'});
        })
    },

    initEvents: function (container) {
        var maxDelay = 500;
        var delay = 0;
        var iterations = [];

        function clear() {
            for (var ind in iterations) {
                var iteration = iterations[ind];
                if (typeof(iteration) == 'function') {
                    window.clearTimeout(iteration);
                }
            }
            iterations = [];
            delay = 0;
        }

        container.observe('mouseover', function (event) {
            clear();
        });

        container.observe('mouseout', function (event) {
            var target = Event.element(event);

            if (target == container) {
                function iteration() {
                    delay += 100;

                    if (delay >= maxDelay) {
                        clear();
                        container.remove();


                    } else {
                        var handler = window.setTimeout(function () {
                            iteration();
                        }, 100);

                        iterations.push(handler);
                    }
                }

                iteration();
            }
        });
    }
});
