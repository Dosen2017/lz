define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/orders/index' + location.search,
                    add_url: 'gamemanage/orders/add',
                    edit_url: 'gamemanage/orders/edit',
                    del_url: 'gamemanage/orders/del',
                    multi_url: 'gamemanage/orders/multi',
                    table: 'orders',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'order_id',
                sortName: 'order_id',
                columns: [
                    [
                        // {checkbox: true},
                        // {field: 'game_num', title: __('Game_num')},
                        {field: 'game_num', title: __('Game_num'), searchList: $.getJSON("gamemanage/game/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['game_name'] + "</span>";
                            }},
                        // {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num'), searchList: $.getJSON("gamemanage/channel_pkg/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['channel_pkg_name'] + "</span>";
                            }},
                        {field: 'order_id', title: __('Order_id'), operate:false},
                        {field: 'my_order_num', title: __('My_order_num'), formatter: Controller.api.formatter.my_order_num},
                        // {field: 'my_order_num', title: __('My_order_num')},
                        {field: 'pt_order_num', title: __('Pt_order_num')},
                        {field: 'real_amount', title: __('Real_amount'), operate:false},
                        {field: 'amount', title: __('Amount')},
                        {field: 'source', title: __('Source')},
                        {field: 'remark', title: __('Remark'), operate:false},
                        {field: 'user_num', title: __('User_num')},
                        {field: 'user_name', title: __('User_name')},
                        // {field: 'game_num', title: __('Game_num')},
                        // {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'os_type', title: __('Os_type'), searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal},
                        {field: 'bundle', title: __('Bundle'), operate:false},
                        {field: 'currency', title: __('Currency'), operate:false},
                        {field: 'product_num', title: __('Product_num'), operate:false},
                        {field: 'product_name', title: __('Product_name'), operate:false},
                        {field: 'product_desc', title: __('Product_desc'), operate:false},
                        {field: 'server_id', title: __('Server_id'), operate:false},
                        {field: 'server_name', title: __('Server_name'), operate:false},
                        {field: 'role_id', title: __('Role_id'), operate:false},
                        {field: 'role_name', title: __('Role_name'), operate:false},
                        {field: 'role_level', title: __('Role_level'), operate:false},
                        {field: 'extra', title: __('Extra'), operate:false},
                        {field: 'notify_url', title: __('Notify_url'), formatter: Table.api.formatter.url, operate:false},
                        {field: 'cp_order_num', title: __('Cp_order_num')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, sortable: true},
                        {field: 'complete_time', title: __('Complete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'state', title: __('State'), searchList: {"1":__('State 1'),"2":__('State 2'),"3":__('State 3'),"4":__('State 4'),"5":__('State 5'),"6":__('State 6'),"7":__('State 7'),"8":__('State 8')}, formatter: Table.api.formatter.normal},
                        {field: 'last_notice', title: __('Last_notice'), operate:false},
                        {field: 'notice_times', title: __('Notice_times'), operate:false},
                        {field: 'notice_result', title: __('Notice_result'), operate:false},
                        {field: 'gateway_num', title: __('Gateway_num'), operate:false},
                        {field: 'settle_amount', title: __('Settle_amount'), operate:false},
                        {field: 'settle_currency', title: __('Settle_currency'), operate:false},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                my_order_num: function (value, row, index) {
                    //这里手动构造URL
                    url = "gamemanage/error_orders?" + this.field + "=" + value;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("%s", value) + '">' + __('%s', value) + '</a>';
                    // return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

                    //方式二,直接调用Table.api.formatter.addtabs
                    // return Table.api.formatter.addtabs(value, row, index, url);
                }
            }
        }
    };
    return Controller;
});