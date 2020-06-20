define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'assisttools/offline_pay/index' + location.search,
                    add_url: 'assisttools/offline_pay/add',
                    edit_url: 'assisttools/offline_pay/edit',
                    del_url: 'assisttools/offline_pay/del',
                    multi_url: 'assisttools/offline_pay/multi',
                    table: 'offline_pay',
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
                        // {field: 'id', title: __('Id')},
                        {field: 'order_num', title: __('Order_num')},
                        {field: 'p_type', title: __('P_type'), searchList: {"1":__('P_type 1'),"2":__('P_type 2'),"3":__('P_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'money', title: __('Money')},
                        {field: 'code_url', title: __('Code_url'), formatter: Table.api.formatter.url},
                        // {field: 'codeimage', title: __('Codeimage'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate:false},
                        {field: 'c_time', title: __('C_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},
                        {field: 'cb_time', title: __('Cb_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'bkinfo', title: __('Bkinfo')},
                        // {field: 'admin_id', title: __('Admin_id'), searchList: $.getJSON("auth/admin/searchlist"), formatter: function(value,row,index){
                        //         return "<span class='label label-info'>" +  row['nickname'] + "</span>";
                        //     }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'qr_code',
                                    text: __('Qr_code'),
                                    // icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'assisttools/offline_pay/qrcode_gen'
                                },
                            ],
                            formatter: Table.api.formatter.operate}

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
            }
        }
    };
    return Controller;
});