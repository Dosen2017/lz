define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/game_orders/index' + location.search,
                    // add_url: 'gamemanage/game_orders/add',
                    // edit_url: 'gamemanage/game_orders/edit',
                    // del_url: 'gamemanage/game_orders/del',
                    // multi_url: 'gamemanage/game_orders/multi',
                    table: 'game_orders',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        // {checkbox: true},
                        // {field: 'id', title: __('Id'), operate:false},

                        // {field: 'game_num', title: __('Game_num')},
                        {field: 'game_num', title: __('Game_num'), searchList: $.getJSON("gamemanage/game/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['game_name'] + "</span>";
                            }},
                        // {field: 'channel_num', title: __('Channel_num')},
                        {field: 'channel_num', title: __('Channel_num'), searchList: $.getJSON("gamemanage/channel/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['channel_name'] + "</span>";
                            }},
                        // {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num'), searchList: $.getJSON("gamemanage/channel_pkg/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['channel_pkg_name'] + "</span>";
                            }},

                        {field: 'my_order_num', title: __('My_order_num')},
                        {field: 'pt_order_num', title: __('Pt_order_num')},
                        // {field: 'real_amount', title: __('Real_amount'), operate:false},
                        {field: 'amount', title: __('Amount'), operate:false},
                        {field: 'source', title: __('Source'), operate:false},
                        // {field: 'remark', title: __('Remark'), operate:false},
                        {field: 'user_num', title: __('User_num')},
                        {field: 'user_name', title: __('User_name')},
                        {field: 'os_type', title: __('Os_type'), searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal},
                        // {field: 'bundle', title: __('Bundle'), operate:false},
                        // {field: 'currency', title: __('Currency'), operate:false},
                        // {field: 'product_num', title: __('Product_num'), operate:false},
                        // {field: 'product_name', title: __('Product_name'), operate:false},
                        {field: 'server_id', title: __('Server_id'), operate:false},
                        {field: 'server_name', title: __('Server_name'), operate:false},
                        {field: 'role_id', title: __('Role_id')},
                        {field: 'role_name', title: __('Role_name')},
                        // {field: 'role_level', title: __('Role_level'), operate:false},
                        {field: 'cp_order_num', title: __('Cp_order_num')},
                        // {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate:false},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate:false},
                        {field: 'complete_time', title: __('Complete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'order_d', title: __('Order_d'), operate:false},
                        // {field: 'gateway_num', title: __('Gateway_num'), operate:false},
                        {field: 'notice_result', title: __('Notice_result')},
                        // {field: 'settle_amount', title: __('Settle_amount'), operate:false},
                        // {field: 'settle_currency', title: __('Settle_currency'), operate:false},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                pageSize: 25,
                pageList: [10, 25, 50, 100, 1000, 10000, 'All'],
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
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});