Minder_PageController = $.inherit(
    {
        page: null,
        $_shortcutSwitcher: null,
        $_shortcutsContainer: null,
        $_pageContentContainer: null,
        $_leftPannelConatiner: null,
        $_modulesContainer: null,
        $_shortModulesContainer: null,
        limitSwitcher: null,
        $_modulesSwitcher: null,

        __constructor: function(page) {
            this.setPage(page);
        },

        setPage: function(page) {
            if (!(page instanceof Minder_Model_Page))
                throw 'page should be instance of MinderPage';

            if (this.page instanceof Minder_Model_Page) {
                $(this.page).unbind('.' + this.__self.eventNamespace);
            }

            this.page = page;

            $(this.page).bind(Minder_Model_Page.stateChangedEvent + '.' + this.__self.eventNamespace, this.onPageStateChanged);

            return this;
        },

        init: function(settings) {
            this.$_shortcutsContainer = settings.$_shortcutsContainer;
            this.$_pageContentContainer = settings.$_pageContentContainer;
            this.$_leftPannelConatiner = settings.$_leftPannelConatiner;
            this.$_modulesContainer    = settings.$_modulesContainer;
            this.$_shortModulesContainer = settings.$_shortModulesContainer;

            this._initShortcutsSwitcher(settings.$_shortcutsSwitcher);
            this._initModulesSwitcher(settings.$_modulesSwitcher);

            $('#limit_company').bind('change', this.onCompanyLimitCahnge);
            $('#limit_warehouse').bind('change', this.onWarehouseLimitChange);
            $('#limit_printer').bind('change', this.onPrinterLimitChange);
        },

        onCompanyLimitCahnge: function(event) {
            this.page.setCompanyLimit(this, $(event.target).val());
        },

        onWarehouseLimitChange: function(event) {
            this.page.setWarehouseLimit(this, $(event.target).val());
        },

        onPrinterLimitChange: function(event) {
            this.page.setCurrentPrinter(this, $(event.target).val());
        },

        _initShortcutsSwitcher: function($_switcherElement) {
            if (this.$_shortcutSwitcher instanceof jQuery) {
                this.$_shortcutSwitcher.unbind('.' + this.__self.eventNamespace);
            }

            this.$_shortcutSwitcher = $_switcherElement;
            this.$_shortcutSwitcher.bind('click.' + this.__self.eventNamespace, this.onShortcutSwitcherClick);
        },

        _initLimitSwitcher: function(switcherElement) {

        },

        _initModulesSwitcher: function($_switcherElement) {
            if (this.$_modulesSwitcher instanceof jQuery) {
                this.$_modulesSwitcher.unbind('.' + this.__self.eventNamespace);
            }

            this.$_modulesSwitcher = $_switcherElement;
            this.$_modulesSwitcher.bind('click.' + this.__self.eventNamespace, this.onModulesSwitcherClick);
        },

        onPageStateChanged: function(evt) {
            if (evt.sender == this)
                return;

            this.showShortcuts(this.page.isShortcutsVisible());
            this.showLeftPannel(this.page.isLeftPannelVisible());
            this.showModules(this.page.isModulesVisible());
        },

        onShortcutSwitcherClick: function(evt) {
            this.page.showLeftPannel(this, !this.page.isLeftPannelVisible());
            this.showShortcuts(this.page.isShortcutsVisible());
            this.showLeftPannel(this.page.isLeftPannelVisible());
        },

        onModulesSwitcherClick: function(evt) {

            if(typeof console !== 'undefined' && typeof console.debug == 'function') console.debug(evt.target);
            if(typeof console !== 'undefined' && typeof console.debug == 'function') console.debug(this);

            this.page.showModules(this, !this.page.isModulesVisible());
            this.showModules(this.page.isModulesVisible());
        },

        showModules: function(show) {
            if (show) {
                this.$_modulesContainer.removeClass('hidden');
                this.$_shortModulesContainer.addClass('hidden');
            } else {
                this.$_modulesContainer.addClass('hidden');
                this.$_shortModulesContainer.removeClass('hidden');
            }
        },

        showShortcuts: function(show) {
            if (show) {
                this.$_shortcutsContainer.removeClass('hidden');
            } else {
                this.$_shortcutsContainer.addClass('hidden');
            }

            return this;
        },

        hideShortcuts: function() {
            this.showShortcuts(false);
            return this;
        },

        showLeftPannel: function(show) {
            if (show) {
                this.$_pageContentContainer.css('margin-left', 190);
                this.$_leftPannelConatiner.css('display', 'block');
            } else {
                this.$_pageContentContainer.css('margin-left', 10);
                this.$_leftPannelConatiner.css('display', 'none');
            }

            return this;
        },

        hideLeftPannel: function() {
            this.showLeftPannel(false);
            return this;
        }
    },
    {
        //static members

        eventNamespace: 'minder-page-controller'
    }
);