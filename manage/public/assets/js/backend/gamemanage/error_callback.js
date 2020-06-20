define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/error_callback/index' + location.search,
                    add_url: 'gamemanage/error_callback/add',
                    edit_url: 'gamemanage/error_callback/edit',
                    del_url: 'gamemanage/error_callback/del',
                    multi_url: 'gamemanage/error_callback/multi',
                    table: 'error_callback',
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
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'my_order_num', title: __('My_order_num')},
                        {field: 'callback_url', title: __('Callback_url'), formatter: Table.api.formatter.url},
                        {field: 'state', title: __('State'), searchList: {"1":__('State 1'),"2":__('State 2'),"3":__('State 3')}, formatter: Table.api.formatter.normal},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'last_time', title: __('Last_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('操作'),
                            table: table,
                            buttons: [
                                {
                                    name: 'ajax',
                                    text: __('回调CP'),
                                    title: __('回调CP'),
                                    classname: 'btn btn-xs btn-info btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'gamemanage/error_callback/callback_cp',
                                    confirm: '确认发起回调？',
                                    success: function (data, ret) {
                                        // dataJson =
                                        Layer.alert(ret.msg +  " 回调数据编号为:" + data.id + ", 订单号为:" + data.my_order_num);
                                        return false;
                                    },
                                    error: function () {
                                        Layer.alert("请求出错");
                                        return false;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                         }
                    ]
                ],
                searchFormVisible: true
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