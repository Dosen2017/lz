define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {

            Table.config.dragsortfield = 'order_id';

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/channel_payments/index' + location.search,
                    add_url: 'gamemanage/channel_payments/add',
                    edit_url: 'gamemanage/channel_payments/edit',
                    del_url: 'gamemanage/channel_payments/del',
                    multi_url: 'gamemanage/channel_payments/multi',
                    dragsort_url: 'gamemanage/channel_payments/sort',
                    table: 'channel_payments',
                }
            });

            var table = $("#table");

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里我们手动设置底部的值
                $("#channel_pkg_num").text(data.extend.channel_pkg_num);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                // pk: 'id',
                sortName: 'order_id',
                escape: false,
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        // {field: 'channel_pkg_num', title: __('Channel_pkg_num'), searchList: $.getJSON("gamemanage/channel_pkg/searchlist"), formatter: function(value,row,index){
                        //         return "<span class='label label-info'>" +  row['channel_pkg_name'] + "</span>";
                        //     }},
                        {field: 'desc', title: __('Desc'), operate:false},
                        {field: 'strategy_class', title: __('Strategy_class'), operate:false},
                        // {field: 'strategy_json', title: __('Strategy_json'), operate:false},
                        // {field: 'collection_id', title: __('Collection_id'), operate:false},
                        // {field: 'order_id', title: __('Order_id')},
                        {field: 'use_switch', title: __('Use_switch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                searchFormVisible: true,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {

                $("#c-strategy_class").change(function () {

                    var regex1 = /\((.+?)\)/g;
                    selectVal = $(this).val().match(regex1);
                    if ("(ChargeAmountStrategy)" == selectVal) {
                        $(".strategy").css('display','none');
                        $("#ChargeAmountStrategy").css('display','block');
                    }

                    if ("(PackIdStrategy)" == selectVal) {
                        $(".strategy").css('display','none');
                        $("#PackIdStrategy").css('display','block');
                    }

                    if ("(UserStrategy)" == selectVal) {
                        $(".strategy").css('display','none');
                        $("#UserStrategy").css('display','block');
                    }

                });

                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});