/*global $*/

(function() {
    'use strict';

    var $ = jQuery;

    // eztags tree version setup
    $.EzTags.Tree = $.EzTags.Default.extend({
        templates: {
            skeleton: [],
            suggestedItem: ['<li class="js-suggested-item" data-cid="<%= tag.cid %>" title="<%=tr.clickAddThisTag%>"><!--<img src="<%=tag.flagSrc %>"/>--><%=tag.name%></li>'],
            selectedItem: ['<li data-cid="<%= tag.cid %>"><!--<img src="<%=tag.flagSrc %>" />--><%=tag.name%><a href="#" class="js-tags-remove" title="<%=tr.removeTag%>">&times;</a></li>'],
            autocompleteItem: ['<div data-cid="<%= tag.cid %>" class="js-autocomplete-item resultItem <%= tag.main_tag_id !== 0 ? "itemSynonym" : "" %>"><a href="#"><!--<img src="<%=tag.flagSrc %>"/>--><%=tag.name%><span><%= tag.parent_name %></span></a></div>'],
        },

        initialize: function(){
            $.EzTags.Default.prototype.initialize.apply(this);


            this.fetch_all_tags();
        },
        setup_ui: function(){
            $.EzTags.Default.prototype.setup_ui.apply(this);
            this.$tag_selector_tree_element = $('#tags-selector-tree-'+this.group_id);
        },
        setup_events: function(){
            $.EzTags.Default.prototype.setup_events.apply(this);
            this.$tag_selector_tree_element.on('click', 'li[role="treeitem"] .jstree-anchor', $.proxy(this.handler_select_tag, this));
            var self = this,
                $jstree = $('.tags-tree', this.$tag_selector_tree_element);
            $jstree.on("load_node.jstree", function (event, data) {
                var node = data.node,
                    nodeIds = [node.id, ...node.children];
                for(var i in nodeIds){
                    if(self.tags.indexed[nodeIds[i]] !== undefined){
                        data.instance.check_node(nodeIds[i]);
                    }
                }
            });
            this.on('remove:after', function (e, data) {
                $jstree.jstree(true).uncheck_node(data.tag.id);
            }.bind(this));

            this.on('add:after', function (e, data) {
                $jstree.jstree(true).check_node(data.tag.id);
            }.bind(this));
        },

        handler_select_tag: function(e){
            e.preventDefault();
            var tagId = $(e.target).closest('li[role="treeitem"]').attr('id');
            var tag = this.autocomplete_tags.indexed[tagId];

            if (this.tag_is_selected(tag)) {
                this.remove(tag.id);
            }else{
                this.add(tag);
            }
        }
    });

})();
