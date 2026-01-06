/**
 * @class Minder.AbstractPageController
 */
minderNamespace('Minder.AbstractPageController', function () {
    this.disabledRelations  = {};
    this.containerMap       = {};
    this.namespaceMap       = {};
    this.screenMap          = {};
    this.namespaceVariances = [];
    this.variances          = [];

    this.onLoad = function () {
    };

    this.initSearchForms = function(searchResults) {
        var self = this;
        $.each(searchResults, function(screenName, searchResult){
            self.initSearchForm(searchResult.namespace);
        });
    };

    this.initSearchForm = function(namespace) {
        var $searchFormContainer = $('.search_form_container').filter('[data-namespace="' + namespace + '"]');

        $searchFormContainer.find('.SEARCH_RESULTS_FORM_search_btn').click($.proxy(this.searchButtonOnClick, this));
        $searchFormContainer.bind('do-search', $.proxy(this.searchFormOnSearch, this));
    };

    this.searchButtonOnClick = function(event) {
        event.preventDefault();
        var searchEvent = $.Event('do-search'),
            $container = $(event.target).parents('.search_form_container'),
            $form = $container.find('form');
        searchEvent.searchParams =  $form.serializeArray();
        searchEvent.namespace = $container.attr('data-namespace');
        $form.trigger(searchEvent);
    };

    this.searchFormOnSearch = function(event) {
        var
            formData = event.searchParams,
            self = this,
            extraData,
            namespacesToReLoad = this.getAllSubNamespaces([event.namespace]);

        namespacesToReLoad.push(event.namespace);

        extraData = this.prepareLoadDataToSend(namespacesToReLoad);
        extraData['searchNamespace'] = event.namespace;

        $('#barcode').val('');
        $(document).queue(function() {
            self.onPreSearch(namespacesToReLoad);
            $.post(
                self.getSearchUrl(),
                [$.param(formData), $.param(extraData)].join('&'),
                $.proxy(self.onDataReady, self),
                'json'
            );
        }).queue(function() {
            self.onAfterSearch(namespacesToReLoad);
            $(this).dequeue();
        });

    };

    this.initVariances = function(variances, autoRegisterContainers) {
        var self = this;
        $.each(variances, function(varianceName, screenVarianceResults){
            self.initScreenVarianceSearchResults(screenVarianceResults, autoRegisterContainers);
            self.variances.push(varianceName);
        });
    };

    this.initScreenVarianceSearchResults = function(results, autoRegisterContainers) {
        var self = this, variances = [];
        $.each(results, function(screenName, searchResults){
            variances.push(searchResults.namespace);
            self.initSearchResult(searchResults.searchResults, autoRegisterContainers);
        });
        this.namespaceVariances.push(variances);
    };

    this.initSearchResult = function (searchResult, autoRegisterContainer) {
        var
            $container;

        if (autoRegisterContainer) {
            $container = $('.' + searchResult.namespace);
            this.registerContainer(searchResult.namespace, $container);
        } else {
            $container = this.getContainer(searchResult.namespace);
        }

        this.registerScreenName(searchResult.namespace, searchResult.sysScreenName);
        this.registerNamespace(searchResult.sysScreenName, searchResult.namespace);

        var tmpSettings = $.extend({autosorter: true, customSort: true, autotabs: false}, searchResult);

        $container.minderSearchResult(tmpSettings);
        $container.bind('select-row', $.proxy(this.onSelectRow, this));
        $container.bind('page-changed', $.proxy(this.onPageChanged, this));
        $container.bind('show-by-changed', $.proxy(this.onShowByChanged, this));
        $container.bind('pre-sort', $.proxy(this.onPreSort, this));
        $container.bind('sort', $.proxy(this.onSort, this));
        $container.bind('after-render', $.proxy(this.onAfterRender, this))
    };

    this.onAfterRender = function(evt) {
        var
            $screenContainer = this.getContainerByScreenName(evt.sysScreen.sysScreenName),
            $tabsContainer = $screenContainer.parents('.ui-tabs-container').find('ul.ui-tabs-nav'),
            tabsData = $tabsContainer.data('tabs');

        if (tabsData) {
            $tabsContainer.tabs(tabsData.options);
        } else {
            $tabsContainer.tabs();
        }
    };

    this.waitPrompt = function(message) {
        return $('<div class="mdr-wait-prompt"><center><h2>' + (message || 'Loading data. Please wait...') + '</h2></center></div>');
    };

    this.lockContainer = function(namespace, $prompt) {
        $prompt = $prompt || this.waitPrompt();
        this.getContainer(namespace).children('div').filter(':visible').block($prompt);
    };

    this.unlockContainer = function(namespace) {
        this.getContainer(namespace).children('div').filter(':visible').unblock();
    };

    this.beforeRowSelect = function(event) {
        this.getContainer(event.namespace).children('div').block(this.waitPrompt('Selecting row. Please wait...'));
    };

    this.afterRowSelect = function(event) {
        this.getContainer(event.namespace).children('div').unblock();
    };

    this.getRowSelectUrl = function() {
        return '';
    };

    this.getNamespaceVariances = function(namespace) {
        return this.namespaceVariances.filter(function(variances){
            return variances.indexOf(namespace) > -1;
        }).shift();
    };

    this.onSelectRow = function (evt) {
        var dataToSend = {
                'sysScreens': {}
            },
            self = this;

        dataToSend.sysScreens[evt.namespace] = {
            'paginator': this.getContainer(evt.namespace).minderSearchGetPaginatorState(),
            'rowId': evt.rowId,
            'state': evt.rowState
        };

        $(document).queue(function () {
            self.beforeRowSelect(evt);
            $.post(
                self.getRowSelectUrl(),
                dataToSend,
                function (response) {
                    if (response.errors && response.errors.length > 0)
                        showErrors(response.errors);

                    if (response.warnings && response.warnings.length > 0)
                        showWarnings(response.warnings);

                    if (response.messages && response.messages.length > 0)
                        showMessage(response.messages);

                    $.each(response.sysScreens, $.proxy(self.setSelection, self));

                    $(document).dequeue();
                },
                'json'
            );
        }).queue(function () {
                self.afterRowSelect(evt);
                $(this).dequeue();
            });

        this.loadData(this.getAllSubNamespaces([evt.namespace]));
    };

    this.onPageChanged = function (evt) {
        this.loadData([this.getNamespace(evt.screenName)]);
    };

    this.onShowByChanged = function (evt) {
        this.loadData([this.getNamespace(evt.screenName)]);
    };

    this.onPreSort = function (evt) {
    };

    this.onSort = function (evt) {
        this.beforeSort(evt);

        if (evt.isDefaultPrevented()) {
            return;
        }

        this.doSort(evt);

        if (evt.isDefaultPrevented()) {
            return;
        }

        this.afterSort(evt);
    };

    this.beforeSort = function(evt) {

    };

    this.afterSort = function(evt) {

    };

    this.doSort = function(evt) {
        var namespaces = [this.getNamespace(evt.screen.sysScreenName)];
        this.silentLoad(this.prepareLoadDataToSend(namespaces));
    };

    this.showAll = function() {
        var self = this;
        $.each(this.containerMap, function(namespace, $container) {
            $container.show();
        });
        $.each(this.variances, function(index, varianceName){
            self.getVarianceContainer(varianceName).show();
        });
    };

    this.hideAll = function() {
        var self = this;
        $.each(this.variances, function(index, varianceName){
            self.getVarianceContainer(varianceName).hide();
        });
        $.each(this.containerMap, function(namespace, $container) {
            $container.hide();
        });
    };

    this.hasContainer = function(namespace) {
        return !!this.containerMap[namespace];
    };

    this.registerContainer = function(namespace, $container) {
        this.containerMap[namespace] = $container;
    };

    this.getContainerByScreenName = function(screenName) {
        return this.getContainer(this.getNamespace(screenName));
    };

    this.getContainer = function (namespace) {
        if (this.containerMap[namespace]) {
            return this.containerMap[namespace];
        }

        throw 'Unknown namespace: ' + namespace;
    };

    this.getVarianceContainer = function(variance) {
        return $('.VARIANCE-' + variance);
    };

    this.registerNamespace = function(screenName, namespace) {
        this.namespaceMap[screenName] = namespace;
    };

    this.getNamespace = function (screenName) {
        if (this.namespaceMap[screenName]) {
            return this.namespaceMap[screenName];
        }

        throw 'Unknown screen: ' + screenName;
    };

    this.registerScreenName = function(namespace, screenName) {
        this.screenMap[namespace] = screenName;
    };

    this.getScreenName = function (namespace) {
        if (this.screenMap[namespace]) {
            return this.screenMap[namespace];
        }

        throw 'Unknown namespace: ' + namespace;
    };

    this.setSelection = function (namespace, sysScreen) {
        $('.selected-container.' + namespace).html(sysScreen.selectedRowsTotal);

        var $container = this.getContainer(namespace);
        var rowsOnPage = $container.find('.data-set:first tr').length;

        var totalRows = parseInt($('.total-container.' + namespace).html());
        totalRows = isNaN(totalRows) ? 0 : totalRows;

        if (rowsOnPage == sysScreen.selectedRowsOnPage) {
            $('.select_all_rows.' + namespace).attr('checked', true);
        } else {
            $('.select_all_rows.' + namespace).removeAttr('checked');
        }

        if (totalRows == sysScreen.selectedRowsTotal) {
            $('.select_complete.' + namespace).attr('checked', true);
        } else {
            $('.select_complete.' + namespace).removeAttr('checked');
        }

        $('.row_selector.' + namespace).each(function () {
            var $_this = $(this);
            var thisRowId = $_this.attr('row_id');

            if (sysScreen.selectedRows[thisRowId]) {
                $_this.attr('checked', true);
            } else {
                $_this.removeAttr('checked');
            }
        });
    };

    this.setDataReadyListener = function(namespace, callback) {
        var fullEventName = 'DATA-READY.' + namespace;
        $(this).unbind(fullEventName).bind(fullEventName, callback);
    };

    this.setDataRenderedListener = function(namespace, callback) {
        var fullEventName = 'DATA-RENDERED.' + namespace;
        $(this).unbind(fullEventName).bind(fullEventName, callback);
    };

    this.onDataReady = function (response) {
        var
            self = this,
            readyEvent = $.Event('DATA-READY'),
            renderedEvent = $.Event('DATA-RENDERED');

        readyEvent.response = _.clone(response);
        renderedEvent.response = _.clone(response);

        $(this).trigger(readyEvent);

        if (!readyEvent.isDefaultPrevented()) {
            if (response.errors && response.errors.length > 0)
                showErrors(response.errors);

            if (response.warnings && response.warnings.length > 0)
                showWarnings(response.warnings);

            if (response.messages && response.messages.length > 0)
                showMessage(response.messages);

            if (response.sysScreens) {
                $.each(response.sysScreens, function (namespace, searchResult) {
                    self.getContainer(namespace).minderSearchResult(searchResult);
                });
            }

            $(this).trigger(renderedEvent);
        }

        $(document).dequeue();
    };

    this.blockNamespaces = function(namespaces, message) {
        var self = this;

        namespaces.forEach(function(namespace) {
            self.getContainer(namespace).children('div').filter(':visible').block(self.waitPrompt(message));
        });
    };

    this.unblockNamespaces = function(namespaces) {
        var self = this;

        namespaces.forEach(function(namespace) {
            self.getContainer(namespace).children('div').filter(':visible').unblock();
        });
    };

    this.onPreSearch = function(namespaces) {
        $('#barcode').parent().block();
        this.blockNamespaces(namespaces, 'Searching....');
    };

    this.onAfterSearch = function(namespaces) {
        $('#barcode').parent().unblock();
        this.unblockNamespaces(namespaces);
    };

    this.onPreLoad = function(namespaces) {
        this.blockNamespaces(namespaces);
    };

    this.onAfterLoad = function(namespaces) {
        this.unblockNamespaces(namespaces);
    };

    this.getLoadUrl = function() {
        return '';
    };

    this.getSearchUrl = function() {
        return '';
    };

    this.loadData = function (namespaces) {
        var self = this;

        $(document).queue(function () {
            self.onPreLoad(namespaces);
            $(this).dequeue();
        });

        this.silentLoad(this.prepareLoadDataToSend(namespaces));

        $(document).queue(function () {
            self.onAfterLoad(namespaces);
            $(this).dequeue();
        });
    };

    this.prepareLoadDataToSend = function(namespaces) {
        var
            dataToSend = {
                'sysScreens': {},
                'disabledRelations': this.disabledRelations
            },
            self = this;

        namespaces.forEach(function (namespace) {
            dataToSend.sysScreens[namespace] = {
                'paginator': self.getContainer(namespace).minderSearchGetPaginatorState()
            }
        });

        return dataToSend;
    };

    this.silentLoad = function(dataToSend) {
        var self = this;

        $(document).queue(function () {
            $.post(
                self.getLoadUrl(),
                dataToSend,
                $.proxy(self.onDataReady, self),
                'json'
            );
        });

    };

    this.getAllSubNamespaces = function (masterNamespaces) {
        var
            self = this;

        return masterNamespaces.reduce(function (result, namespace) {
            var
                subNamespaces = self.getSubNamespaces([namespace]);

            return result.concat(subNamespaces).concat(self.getAllSubNamespaces(subNamespaces));
        }, []);
    };

    this.getSubNamespaces = function (masterNamespaces) {
        var
            fullChain = this.getMasterSlaveChain();

        return masterNamespaces
            .map($.proxy(this.getScreenName, this))
            .map(function (screenName) {
                return fullChain[screenName] ? Object.keys(fullChain[screenName]) : [];
            })
            .reduce(function (result, screenName) {
                return result.concat(screenName);
            }, [])
            .map($.proxy(this.getNamespace, this))
            .filter($.proxy(this.isMasterSlaveRelationEnabled, this));
    };

    this.getMasterSlaveChain = function () {
        //noinspection UnnecessaryReturnStatementJS
        return {};
    };

    this.isMasterSlaveRelationEnabled = function (namespace) {
        return !this.isMasterSlaveRelationDisabled([namespace]);
    };

    this.isMasterSlaveRelationDisabled = function (namespaces) {
        var
            self = this;
        return namespaces.reduce(function(previous, namespace){
            return previous && !!self.disabledRelations[namespace];
        }, true);
    };

    this.enableMasterSlaveRelation = function (namespaces) {
        var
            self = this;
        namespaces.forEach(function (namespace) {
            self.disabledRelations[namespace] = false;
        });

        this.loadData(namespaces);
    };

    this.disableMasterSlaveRelation = function (namespaces) {
        var
            self = this;
        namespaces.forEach(function (namespace) {
            self.disabledRelations[namespace] = true;
        });

        this.loadData(namespaces);
    };

    this.getRegisteredNamespaces = function() {
        var result = [];
        $.each(this.namespaceMap, function(index, value) {
            result.push(value);
        });

        return result;
    }
});
