/**
 * @package     Blueacorn\AnalyticsTagging
 * @version     1.0.0
 * @author      Blue Acorn <code@blueacorn.com>
 * @copyright   Copyright © 2015 Blue Acorn.
 */


(function($) {
    // Data from the CSV generated by Node script (array of objects)
    var data = [{"trackType":"pageViewOnLoad","el":null,"page":"/Category","type":null,"label":null,"eventType":"click","bodyClass":"catalog-category-view","row":2},{"trackType":"pageViewOnLoad","el":null,"page":"/Product","type":null,"label":null,"eventType":"click","bodyClass":"catalog-product-view","row":3},{"trackType":"pageViewOnLoad","el":null,"page":"/Search","type":null,"label":null,"eventType":"click","bodyClass":"catalogsearch-result-index","row":4},{"trackType":"pageViewOnLoad","el":null,"page":"/Cart","type":null,"label":null,"eventType":"click","bodyClass":"checkout-cart-index","row":5},{"trackType":"event","el":".catalog-category-view .toolbar .pages a.next:first","page":"Category Page","type":"Page Right - Upper","label":null,"eventType":"mousedown","bodyClass":null,"row":6},{"trackType":"event","el":".catalog-category-view .toolbar .pages a.previous:first","page":"Category Page","type":"Page Left - Upper","label":null,"eventType":"mousedown","bodyClass":null,"row":7},{"trackType":"event","el":".catalog-category-view .toolbar .pages a.previous:last","page":"Category Page","type":"Page Left - Lower","label":null,"eventType":"mousedown","bodyClass":null,"row":8},{"trackType":"event","el":".catalog-category-view .toolbar .pages a.next:last","page":"Category Page","type":"Page Right - Lower","label":null,"eventType":"mousedown","bodyClass":null,"row":9},{"trackType":"event","el":".page .page-header .page-header-container .logo","page":"Header","type":"Logo Home","label":null,"eventType":"mousedown","bodyClass":null,"row":10},{"trackType":"event","el":".page .page-header .page-header-container #header-search .search-button","page":"Header","type":"Search","label":null,"eventType":"mousedown","bodyClass":null,"row":11},{"trackType":"event","el":".page .page-header .page-header-container .account-cart-wrapper .skip-account","page":"Header","type":"My Account","label":null,"eventType":"mousedown","bodyClass":null,"row":12},{"trackType":"event","el":".page .page-header .page-header-container .account-cart-wrapper .header-minicart .skip-cart","page":"Header","type":"Mini Cart","label":null,"eventType":"mousedown","bodyClass":null,"row":13},{"trackType":"event","el":".page .page-header .page-header-container .account-cart-wrapper .header-minicart #header-cart .minicart-wrapper .minicart-actions .cart-link","page":"Mini Cart","type":"Cart","label":null,"eventType":"mousedown","bodyClass":null,"row":14},{"trackType":"event","el":".page .page-header .page-header-container .account-cart-wrapper .header-minicart #header-cart .minicart-wrapper .minicart-actions .checkout-types.minicart .checkout-button","page":"Mini Cart","type":"Checkout","label":null,"eventType":"mousedown","bodyClass":null,"row":15},{"trackType":"event","el":".checkout-cart-index .cart .title-buttons .checkout-types.top .btn-proceed-checkout","page":"Cart","type":"Top Proceed to Checkout","label":null,"eventType":"mousedown","bodyClass":null,"row":16},{"trackType":"event","el":".checkout-cart-index .cart .cart-totals-wrapper .cart-totals .checkout-types.bottom .btn-proceed-checkout","page":"Cart","type":"Lower Proceed to Checkout","label":null,"eventType":"mousedown","bodyClass":null,"row":17},{"trackType":"event","el":".checkout-cart-index .cart .cart-forms .discount .discount-form .field-wrapper .button-wrapper .button2","page":"Cart","type":"Promo Code Submit","label":null,"eventType":"mousedown","bodyClass":null,"row":18},{"trackType":"event","el":".checkout-cart-index .cart .cart-forms .giftcard #giftcard-form .field-wrapper .button-wrapper .button2","page":"Cart","type":"Gift Card Code Submit","label":null,"eventType":"mousedown","bodyClass":null,"row":19},{"trackType":"event","el":".checkout-cart-index .cart .cart-forms .shipping .shipping-form #shipping-zip-form .buttons-set .button2","page":"Cart","type":"Tax & Shipping Estimator Submit","label":null,"eventType":"mousedown","bodyClass":null,"row":20},{"trackType":"event","el":".checkout-cart-index .cart .cart-forms .shipping .shipping-form .form-list","page":"Cart","type":"Tax & Shipping Estimator","label":null,"eventType":"mousedown","bodyClass":null,"row":21},{"trackType":"event","el":".checkout-cart-index .cart .cart-table .cart-footer-actions .btn-continue","page":"Cart","type":"Cart Continue Shopping","label":null,"eventType":"mousedown","bodyClass":null,"row":22},{"trackType":"event","el":".checkout-cart-index .cart .cart-table .cart-footer-actions .btn-update","page":"Cart","type":"Update Cart","label":null,"eventType":"mousedown","bodyClass":null,"row":23},{"trackType":"pageView","el":".checkout-onepage-index .opc #opc-login #checkout-step-login .col-1 .form-list .control .radio-label:first","page":"/Checkout as Guest","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":24},{"trackType":"pageView","el":".checkout-onepage-index .opc #opc-login #checkout-step-login .col-1 .form-list .control .radio-label:last","page":"/Register & Checkout","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":25},{"trackType":"pageView","el":".checkout-onepage-index .opc #opc-login #checkout-step-login .col-2 .buttons-set .button","page":"/Checkout Log In","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":26},{"trackType":"pageView","el":".checkout-onepage-index .opc #opc-login #checkout-step-login .col-1 .buttons-set .button","page":"/Continue to Billing Info (From Checkout Method)","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":27},{"trackType":"pageView","el":".opc #opc-billing #checkout-step-billing #co-billing-form .fieldset #billing-buttons-container .continue","page":"/Continue to Shipping Info (from Billing)","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":28},{"trackType":"pageView","el":".opc #opc-shipping #checkout-step-shipping #co-shipping-form #shipping-buttons-container .continue","page":"/Continue to Shipping Method (from Shipping Information)","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":29},{"trackType":"pageView","el":".opc #opc-shipping_method #checkout-step-shipping_method #co-shipping-method-form #shipping-method-buttons-container .continue","page":"/Continue to Payment Information","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":30},{"trackType":"pageView","el":".opc #opc-payment #checkout-step-payment #payment-buttons-container .continue","page":"/Continue to Order Review","type":null,"label":null,"eventType":"mousedown","bodyClass":null,"row":31},{"trackType":"event","el":".page .footer-container .footer .connect-container .block-subscribe .block-content .actions .button","page":"Footer","type":"Email Subscription","label":null,"eventType":"mousedown","bodyClass":null,"row":32}];

    // Instantiate the constructor
    var BA_GAQ = new GaqTracker(data);

    BA_GAQ.setMode('universal');

    //Function to drop query string
    function getURL() {
        return window.location.href.split('?')[0];
    }

    //Function to append URL to the end of a string. Mostly used for labels.
    function appendUrl(label) {
        return label + ' || ' + getURL();
    }

    //Function to get page label based on body class
    function getPageName() {
        var $body = $('body'),
            page;

        if ($body.hasClass('catalog-category-view')) {
            page = 'Category Page';
        } else if ($body.hasClass('catalog-product-view')) {
            page = 'Product Page';
        } else if ($body.hasClass('cms-home')) {
            page = 'Homepage';
        } else if ($body.hasClass('catalogsearch-result-index')) {
            page = 'Search Results Page';
        } else if ($body.hasClass('customer-account')) {
            page = 'My Account Page';
        }

        return page;
    }

    // Run the tracking AFTER doc ready
    $(document).ready(function() {

        /* -------------------------- *\
         *     MAIN AUTOTRACKING      *
        \* -------------------------- */
        BA_GAQ.trackAll();

        /* ------------------------- *\
         *      MANUAL TRACKING      *
        \* ------------------------- */

        //Navigation: Mega Menu categories.
        $('#nav li.level0').each(function() {
            var $anchor = $($(this).find('a').first()),
                topCatName = $.trim($anchor.text());

            BA_GAQ.manualTracker('event', $anchor, 'Navigation', topCatName, topCatName, 'mousedown');

            $(this).find('a.level1').each(function() {
                var $this = $(this),
                    catName = $.trim($this.text());

                BA_GAQ.manualTracker('event', $this, 'Navigation', topCatName, catName, 'mousedown');
            });
        });

        //Navigation: Mega Menu CMS block
        $('.gaq-track-a').each(function() {
            var $this = $(this),
                page,
                pageEl,
                label;

            //Very little time to work on this, so quick and dirty way of tracking anchors in markup editable by user
            if($this.is('a') && $this.data('gaqPage') !== undefined && $this.data('gaqType') !== undefined) {
                page = $this.data('gaqPage');
                pageEl = $this.data('gaqType');
                label = $this.attr('href');
            } else {
                console.error("Error. Element with gaq-track-a class could not be tracked because it is either not an <a> or is missing data attributes.");
                return;
            }

            label = appendUrl(label);

            BA_GAQ.manualTracker('event', $this, page, pageEl, label, 'mousedown');
        });

        //Footer: links.
        $('div.footer a').each(function() {
            var $this = $(this),
                title = $this.closest('ul').prev('h5.block-title').text().trim(),
                elText = $this.text().trim(),
                regex = /http(s)?\:\/\//;

            if (regex.test(elText)) {
                elText = elText.replace(/http(s)?\:\/\//, "").replace(".com", "");
                elText = elText.charAt(0).toUpperCase() + elText.slice(1);

                if(elText.indexOf("plus") != -1) {
                    elText = elText.replace('plus', '+');
                }
            } else {
                elText = title + ' - ' + elText;
            }

            BA_GAQ.manualTracker('event', $this, 'Footer', elText, getURL(), 'mousedown');
        });

        //Mini Cart: cart items
        $('#cart-sidebar').on('mousedown', 'a.link-wishlist, a.remove, a.product-image, p.product-name a', function() {
                var $this = $(this),
                    elText,
                    label;

                if ($this.hasClass('product-image')) {
                    elText = 'Product Image View';
                    label = $this.closest('li.item').find('.product-name a').text().trim();
                } else if ($this.parent('p.product-name').length !== 0) {
                    elText = 'Product Description View';
                    label = $this.text().trim();
                } else {
                    elText = $this.text().trim();
                    label = appendUrl($this.closest('li.item').find('.product-name a').text().trim());
                }

                BA_GAQ.manualTracker('event', $this, 'Mini Cart', elText, label, null);
        });

        /* ------------------------- *\
         *         Homepage          *
        \* ------------------------- */

        //Featured products
        $('.feature-wrap div.feature-item').each(function(){
            var $this = $(this),
                label = appendUrl($this.find('a h3').text() + " || " + getURL() + $this.children('a').attr('href')),
                $els = $this.find('a img, a p, a h3, a.button');

            $els.each(function(){
                var el = $(this),
                    elText;

                if (el.is('a')){
                    elText = 'Featured Product Details';
                } else if (el.is('img')) {
                    elText = 'Featured Product Image';
                } else if (el.is('h3')) {
                    elText = 'Featured Product Title';
                } else if (el.is('p')) {
                    elText = 'Featured Product Text';
                } else {
                    return;
                }

                if ($this.hasClass('feature-promo')) {
                    elText += ' - Promo';
                }

                BA_GAQ.manualTracker('event', el, 'Homepage', elText, label, 'mousedown');
            });
        });

        //Hero button.
        $('div.hero-content > a.button').on('mousedown', function() {
            var label = appendUrl($(this).attr('href'));

            BA_GAQ.trackEvent('Homepage', 'Hero Button', label);
        });

        //Breadcrumbs
        $('div.breadcrumbs li a').each(function() {
            var $this = $(this),
                body = $('body'),
                page = getPageName(),
                label = appendUrl($this.attr('href'));

            BA_GAQ.manualTracker('event', $this, page, 'Breadcrumb Click', label, 'mousedown');

        });

        /* ------------------------- *\
         *      Category Page        *
        \* ------------------------- */

        //Category Page: sort tool
        $('.toolbar .sort-by select').change(function() {
            var $this = $(this),
                index = this.selectedIndex,
                label = appendUrl($.trim($this.find('option').eq(index).html()));

            BA_GAQ.trackEvent('Category Page', 'Sort', label);
        });

        //Category Page: show tool
        $('.toolbar .limiter select').change(function() {
            var $this = $(this),
                index = this.selectedIndex,
                label = appendUrl($.trim($this.find('option').eq(index).html()));

            BA_GAQ.trackEvent('Category Page', 'Show Number', label);
        });

        //Category Page: page selector upper
        $('.category-products > .toolbar .pages li > a:not(".next, .previous")').each(function() {
            var $this = $(this),
                label = appendUrl($this.text().trim());

            BA_GAQ.manualTracker('event', $this, 'Category Page', 'Page Selection - Upper', label, 'mousedown');
        });

        //Category Page: page selector lower
        $('.category-products > .toolbar-bottom .pages li > a:not(".next, .previous")').each(function() {
            var $this = $(this),
                label = appendUrl($this.text().trim());

            BA_GAQ.manualTracker('event', $this, 'Category Page', 'Page Selection - Lower', label, 'mousedown');
        });

        //Category Page: grid products
        $('ul.products-grid li.item').each(function() {
            var $this = $(this),
                label = appendUrl($this.find('h2.product-name').text().trim()),
                $els = $this.find('a, button');

            $els.each(function() {
                var el = $(this),
                    pageEl = 'Product - ';

                if (el.is('button')){
                    pageEl += 'Config / Add';
                } else if (el.hasClass('product-image')) {
                    pageEl += 'Image Select';
                } else if (el.hasClass('link-wishlist')) {
                    pageEl += 'Wishlist';
                } else if (el.hasClass('link-compare')) {
                    pageEl += 'Compare';
                } else {
                    pageEl += 'Text Select';
                }

                BA_GAQ.manualTracker('event', el, 'Category Page', pageEl, label, 'mousedown');
            });
        });

        //Category Page: layered nav filter nav selects
        $('#narrow-by-list ol li a').each(function() {
            var $this = $(this),
                page = "Category Page",
                filterName = "Filter - " + $this.closest('dd').prev('dt').text().trim(),
                spanText = $this.find('span.count').first().text(),
                label = appendUrl("Select || " + $this.text().replace(spanText, "").trim());

            if($('body').hasClass('catalogsearch-result-index')) {
                page = "Search Results";
            }

            BA_GAQ.manualTracker('event', $this, page, filterName, label, 'mousedown');
        });

        //Category Page: layered nav filter de-selects
        $('.block-layered-nav .currently > ol li a.btn-remove').each(function() {
            var $this = $(this),
                page = "Category Page",
                filterName = "Filter - " + $this.nextAll('span.label').text().replace(":", "").trim(),
                label = appendUrl("Deselect || " + $this.nextAll('span.value').text().trim());

            if($('body').hasClass('catalogsearch-result-index')) {
                page = "Search Results";
            }

            BA_GAQ.manualTracker('event', $this, page, filterName, label, 'mousedown');
        });

        //Category Page: layered nav filter expansion and collapse
        $('#narrow-by-list > dt').on('mousedown', function() {
            var $this = $(this),
                page = "Category Page",
                filterName = "Filter - " + $this.text().trim(),
                label;

            if($('body').hasClass('catalogsearch-result-index')) {
                page = "Search Results";
            }

            if($this.hasClass('current')) {
                label = "Close";
            } else {
                label = "Open";
            }

            label = appendUrl(label);

            BA_GAQ.trackEvent(page, filterName, label);
        });

        /* ------------------------- *\
         *        Product Page       *
        \* ------------------------- */

        //Product Page: Yopto Write a Review button, instead of native
        $('.col-main').on('mousedown', 'span.yotpo-display-wrapper .write-review-button', function() {
            var page = 'Product Page',
                label = appendUrl($('.top-product-details div.product-name').text().trim());

            BA_GAQ.trackEvent(page, 'Write Review', label);
        });

        //Product Page: product image thumbnails
        $('.product-img-box .thumb-link').each(function() {
            var $this = $(this),
                label = appendUrl($('.top-product-details div.product-name').text().trim());

            BA_GAQ.manualTracker('event', $this, 'Product Page', 'Thumbnail', label, 'mousedown');
        });

        //Product Page: configurable option select
        $('#product-options-wrapper select').change(function() {
            var $this = $(this),
                pageEl = $this.children('option:first-child').text().trim().camelCaseString(),
                index = this.selectedIndex,
                label = appendUrl($.trim($this.find('option').eq(index).html()));

            BA_GAQ.trackEvent('Product Page', pageEl, label);
        });

        //Product Page: add to cart wrapper button and links
        $('.add-to-cart-wrapper').on('mousedown', 'button, a', function() {
            var $this   = $(this),
                pageEl,
                label   = $('.top-product-details div.product-name').text().trim();

            if ($this.hasClass('btn-cart') || $this.hasClass('link-wishlist')) {
                pageEl  = $this.text().trim();
                label   += " || " + $('#qty').val();
            } else if ($this.is('#map_request')) {
                pageEl  = $this.text().trim();
            } else if ($this.hasClass('link-finance')) {
                pageEl = 'Finance Info';
            } else {
                return;
            }

            label = appendUrl(label);

            BA_GAQ.trackEvent('Product Page', pageEl, label);
        });

        /* ------------------------- *\
         *        Cart Page          *
        \* ------------------------- */

        //Cart Page: discount code apply
        $('#discount-coupon-form').on('mousedown', 'button', function() {
            var label = $('#coupon_code').val();

            BA_GAQ.trackEvent('Cart Page', 'Promo Code Submit', label);
        });

        //Cart Page: cart items
        $('#shopping-cart-table tbody tr').on('mousedown', 'a.btn-remove, h2.product-name a', function() {
            var $this = $(this),
                pageEl,
                label;

            if ($this.hasClass('btn-remove')) {
                pageEl = 'Remove From Cart';
                label = $this.closest('tr').find('h2.product-name').text().trim();
            } else {
                pageEl = 'Product Title';
                label = $this.text().trim();
            }

            BA_GAQ.trackEvent('Cart Page', pageEl, label);
        });

        //Cart Page: crosssells
        $('#crosssell-products-list li.item').each(function() {
            var $this = $(this),
                label = appendUrl($this.find('h3.product-name').text().trim()),
                els = $this.find('a, button');

            els.each(function() {
                var el = $(this),
                    pageEl = 'Cart Crosssell Product - ';

                if (el.is('button')){
                    pageEl += 'Config / Add';
                } else if (el.hasClass('product-image')) {
                    pageEl += 'Image Select';
                } else if (el.hasClass('link-wishlist')) {
                    pageEl = 'Wishlist';
                } else if (el.parent().hasClass('product-name')) {
                    pageEl += 'Text Select';
                } else {
                    pageEl += 'Compare';
                }

                BA_GAQ.manualTracker('event', el, 'Cart Page', pageEl, label, 'mousedown');
            });
        });

    }); // end doc ready

}(jQuery));
