(function() {
    'use strict';

    var $ = jQuery;

    /* options for tagsTree plugin
        'modal' - boolean - is the tagsTree opened in modal window (default false)
        'treeClassName' - string - class name for div on which jstree is initialized (default 'tags-tree')
        'modalClassName' - string - class name for modal div in which tagsTree are opened (default 'ng-modal')
    */
    var TagsTree = function(el, options){
        this.settings = $.extend({
            'treeClassName': 'tags-tree',
        }, options);


        this.$el = $(el);
        this.$tree = this.$el.find('.' + this.settings.treeClassName);
        this.rootNodeAdded = false;
        this.hideRoot = this.$tree.data('hide-root-tag');
        this.rootTagId = this.$tree.data('root-tag-id');
        this.path = this.$tree.data('path');

        this.getSetupData();
    };

    TagsTree.prototype.getSetupData = function(){

        var route = this.path
            .replace("_tagId_", this.rootTagId + "/true");

        $.getJSON(route + '?ContentType=json', function(data) {
            this.rootNode = data[0];
            this.treeInit();
        }.bind(this));
    };

    TagsTree.prototype.getTreeData = function(node, cb){
        if(this.rootNodeAdded || this.hideRoot){
            var tagId = node.id == '#' ? this.rootNode.id : node.id;

            var route = this.path
                .replace('_tagId_', tagId)
                .replace('#', this.rootTagId + '/true');


            $.getJSON(route + '?ContentType=json', function(data) {
                var children = data;
                for(var i=0; i<children.length; i++){
                    (children[i].parent == this.rootNode.id && this.hideRoot) && (children[i].parent = '#');
                }
                cb(children);
            }.bind(this));
        } else {
            cb([this.rootNode]);
            this.rootNodeAdded = true;
        }
    };

    TagsTree.prototype.treeInit = function(){
        var self = this;

        this.$tree.jstree({
            'plugins': ['checkbox', 'types'],
            'checkbox' : {
                'tie_selection': false,
                'cascade': 'up',
                'three_state' : false
            },
            'types': {
                'default': {
                    'icon': 'jstree-default-responsive jstree-file'
                }
            },
            'core': {
                'data': this.getTreeData.bind(this)
            },

        }).on("load_node.jstree", function (event, data) {
            var selectedTags = self.tags.tags.items;

            if(selectedTags.length){
                var Ids = selectedTags.map(tag => tag.id);
                data.instance.check_node(Ids);
            }
        });
    };


    $.EzTags.Tree = $.EzTags.Base.extend({
        templates: {
            skeleton: [
                '<div class="tagssuggest-ui">',
                '<div class="tags-output">',
                '<label><%=tr.selectedTags%>:</label>'
                ,                  '<div class="tags-list tags-listed no-results">',
                '<p class="loading"><%=tr.loading%></p>',
                '<p class="no-results"><%=tr.noSelectedTags%>.</p>',
                '<ul class="float-break clearfix js-tags-selected"></ul>',
                '</div>',
                '</div>',
                '</div>'
            ],
            suggestedItem: [],
            selectedItem: ['<li data-cid="<%= tag.cid %>"><!--<img src="<%=tag.flagSrc %>" />--><%=tag.name%><a href="#" class="js-tags-remove" title="<%=tr.removeTag%>">&times;</a></li>'],
            autocompleteItem: [],

        },

        /**
         * Initializes Select EzTag. Calls fetch tags function with callback which appends
         * fetched tags to select dropdowns. Also, registers 'onChange' listener on
         * all select dropdowns.
         */
        initialize: function(){
            var self = this;
            this.$tree_element = this.$el.parent().find('.ez-tags-tree-selector');

            $('.ez-tags-tree-selector').on('click', '.jstree-anchor', function(e){
                e.preventDefault();
                var selectedNode = $(e.target).jstree(true).get_node($(e.target));

                if(self.max_tags_limit_reached() && selectedNode.state.checked === true){
                    $(e.target).jstree(true).uncheck_node(selectedNode.id);
                    return;
                }

                var attributes = {
                    id: selectedNode.id,
                    name: selectedNode.text,
                    locale: self.opts.locale,
                    parent: selectedNode.parent,
                };

                if(selectedNode.state.checked === false)
                {
                    self.remove(attributes.id);
                }
                else if(selectedNode.state.checked === true){
                    self.add(attributes, {});
                }

            }.bind(this));

            this.addTree();
        },
        setup_events: function(){
            $.EzTags.Default.prototype.setup_events.apply(this, arguments);
            this.$el.on('click', '.js-tags-remove', $.proxy(this.handler_unchecked_tree, this));
        },
        handler_unchecked_tree: function(event){
            $('.ez-tags-tree-selector').children().jstree(true).uncheck_node(event.result.id);
        },
        addTree: function(){
            var tree = new TagsTree(this.$tree_element);
            tree.tags = this;
        }
    });

})();

