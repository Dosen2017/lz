

//-------------------------------------------------------------------------//
(function ($) {
    // 鍒涘缓鏋勯€犲嚱鏁�
    function Slide(ele, options) {
        this.$ele = $(ele)//this. 鏋勯€犲嚱鏁扮殑瀹炰緥瀵硅薄
        this.options = $.extend({
            speed: 800
        }, options)//鎷撳睍
        this.states = [
            { '&zIndex': 1, width: 1.7 + "rem", height: 1.5 + "rem", top: (0.17)+"rem", left: 37.5+"%"},
            { '&zIndex': 2, width: 2.2 + "rem", height: 1.9 + "rem", top: 0.8+"rem", left: 5+"%"},
            { '&zIndex': 3, width: 2.7+"rem", height: 2.4+"rem", top: 1+"rem", left: 31+"%"},
            { '&zIndex': 2, width: 2.2 + "rem", height: 1.9+"rem", top: 0.8+"rem", left: 64+"%"}
        ]
        this.lis = this.$ele.find('a')
        this.interval
        // 鐐瑰嚮鍒囨崲鍒颁笅涓€寮�


        $('.leftBtn').on('click', function () {
            // this.stop()
            this.next()
            // this.play()
        }.bind(this));
        $('.prof_bd .prof_list').on('swipeLeft', function () {
            this.next()
        }.bind(this));
        // 鐐瑰嚮鍒囨崲鍒颁笂涓€寮�
        $('.rightBtn').on('click', function () {
            // this.stop()
            this.prev()
            // this.play()
        }.bind(this));
        $('.prof_bd .prof_list').on('swipeRight', function () {
            this.prev()
        }.bind(this));
        this.move()
        // 璁╄疆鎾浘寮€濮嬭嚜鍔ㄦ挱鏀�
        // this.play()
    }




    Slide.prototype = {
        move: function () {

            this.lis.each(function (i, el) {
                $(el)
                .css('z-index', this.states[i]['&zIndex'])
                .animate(this.states[i], this.options.speed);
            }.bind(this))
        },
        // 璁╄疆鎾浘鍒囨崲鍒颁笅涓€寮�
        next: function () {

            this.states.unshift(this.states.pop())
            this.move()
        },
        // 璁╄疆鎾浘婊氬姩鍒颁笂涓€寮�
        prev: function () {

            this.states.push(this.states.shift())
            this.move()
        }


    }
    $.fn.zySlide = function (options) {
        this.each(function (i, ele) {
            new Slide(ele, options)
        })
        return this
    }
})(Zepto)

Zepto('.zy-Slide').zySlide({ speed: 500 })