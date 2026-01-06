Minder_View_Tab = $.inherit(
    Minder_View_Container,
    {
        _pages: null,
        _pagesSorted: false,
        _nextOrder: 0,

        __constructor: function(name, model, $template, settings){
            this.__base(name, model, $template, settings);
            this._pages = [];
        },

        setModel: function(model) {
            this.__base(model);
            $(this._model)
                .bind(Minder_Model_DataSet.dataChangedEvent, this.onDataChanged)
                .bind(Minder_Model_SysScreen.dataUpdateStartedEvent, this.onDataUpdateStarted);
            return this;
        },

        addPage: function(page) {
            if (! (page instanceof Minder_View_TabPage))
                throw 'page shoud be instanceof Minder_View_TabPage';

            if (page.getOrder() == null) {
                page.setOrder(this._nextOrder);
            }

            if (page.getOrder() >= this._nextOrder) {
                this._nextOrder = page.getOrder() + 1;
            }

            this._pages.push(page);
            this._pagesSorted = false;
        },

        _sortPages: function() {
            if (this._pagesSorted) return;

            this._pages.sort(function(a, b){
                return a.getOrder() - b.getOrder();
            });
            this._pagesSorted = true;
        },

        getPages: function() {
            this._sortPages();
            return this._pages;
        },

        render: function($placement) {
            var $renderedContent = this._$template.tmpl(this).insertBefore($placement);
            $placement.remove();
            $renderedContent.data('minderElement', this);

            $.each(this.getPages(), function(index, page) {
                var $pagePlacement = $renderedContent.find('.minder-tab-page.' + page.getName());
                if ($pagePlacement.length > 0)
                    page.render($pagePlacement);
            });

            $.each(this._subViews, function(name, subView){
                var $subViewPlacement = $renderedContent.find('.minder-element.' + name);
                if ($subViewPlacement.length > 0)
                    subView.render($subViewPlacement);
            });

            if (this.getPages().length > 1)
                $renderedContent.find('ul').tabs();

            this._$renderedContent = $renderedContent;
        },

        onDataChanged: function(evt) {
            if (evt.sender == this) return;

            if (this._$renderedContent === null) return;

            this._$renderedContent.unblock();
        },

        onDataUpdateStarted: function(evt) {
            if (evt.sender == this) return;

            if (this._$renderedContent === null) return;

            this._$renderedContent.block('Loading Data...');
        }
    },
    {
        //static members
    }

);