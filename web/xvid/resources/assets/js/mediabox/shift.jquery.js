(function($){
    $.fn.shifty = function(o){
        var o = $.extend({
            className:'shifty-select',
            select:function(){},
            unselect:function(){}
        }, o);
        elems = $(this);
        last = null;
        var className = o.className;
        return $(this).each(function(){
            var block = $(this);
            $(document).keydown(function(e){
                if (!e.ctrlKey && !e.shiftKey) return;
                this.onselectstart = function(){return false};
                block.unbind('click').css({'-moz-user-select':'none','-webkit-user-select':'none','user-select':'none'});
                if (e.ctrlKey) {
                    block.click(function(){
                        block.toggleClass(className);
                        last = elems.index(block);
                        o.unselect(elems);
                        o.select(elems.filter('.' + className));
                    });
                }
                if (e.shiftKey) {
                    block.click(function(){
                        first = elems.index(block);
                        if (first < last) {
                            elems.filter(':gt(' + (first - 1) + ')').addClass(className);
                            elems.filter(':lt(' + first + '),:gt(' + last + ')').removeClass(className);
                        } else {
                            elems.filter(':gt(' + last + ')').addClass(className);
                            elems.filter(':lt(' + last + '),:gt(' + first + ')').removeClass(className);
                        }
                        o.unselect(elems);
                        o.select(elems.filter('.' + className));
                    });
                }
            });
            $(document).keyup(function(e){
                this.onselectstart = function(){};
                block.unbind('click').click(blockClick).css({'-moz-user-select':'','-webkit-user-select':'','user-select':''});
            });
            block.click(blockClick);
        });
        function blockClick(){
            elems.removeClass(className);
            $(this).addClass(className);
            o.unselect(elems);
            o.select($(this));
            last = elems.index($(this));
        }
    };
})(jQuery);