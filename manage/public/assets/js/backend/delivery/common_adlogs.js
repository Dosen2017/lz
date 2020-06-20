define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'delivery/common_adlogs/index' + location.search,
                    add_url: 'delivery/common_adlogs/add',
                    edit_url: 'delivery/common_adlogs/edit',
                    del_url: 'delivery/common_adlogs/del',
                    multi_url: 'delivery/common_adlogs/multi',
                    table: 'common_adlogs',
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
                        {field: 'ads_id', title: __('Ads_id')},
                        {field: 'channel_id', title: __('Channel_id'), operate:false},
                        // {field: 'device_id', title: __('Device_id')},
                        // {field: 'mac', title: __('Mac')},
                        // {field: 'ip', title: __('Ip')},
                        {field: 'callback_url', title: __('Callback_url'), formatter: Table.api.formatter.url, operate:false},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate:false},
                        // {field: 'callback_args', title: __('Callback_args')},
                        // {field: 'callback_time', title: __('Callback_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'callback_times', title: __('Callback_times')},
                        // {field: 'callback_result', title: __('Callback_result')},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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