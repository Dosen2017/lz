define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/user_logs/index' + location.search,
                    add_url: 'gamemanage/user_logs/add',
                    edit_url: 'gamemanage/user_logs/edit',
                    del_url: 'gamemanage/user_logs/del',
                    multi_url: 'gamemanage/user_logs/multi',
                    table: 'user_logs',
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
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_num', title: __('User_num')},
                        {field: 'user_name', title: __('User_name')},
                        {field: 'game_num', title: __('Game_num')},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'channel_num', title: __('Channel_num')},
                        {field: 'pack_num', title: __('Pack_num')},
                        {field: 'b_id', title: __('B_id')},
                        {field: 'ads_id', title: __('Ads_id')},
                        {field: 'ip', title: __('Ip')},
                        {field: 'device_num', title: __('Device_num')},
                        {field: 'device_num_md5', title: __('Device_num_md5')},
                        {field: 'device_model', title: __('Device_model')},
                        {field: 'mac', title: __('Mac')},
                        {field: 'os_type', title: __('Os_type'), searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal},
                        {field: 'os_version', title: __('Os_version')},
                        {field: 'net_type', title: __('Net_type')},
                        {field: 'country', title: __('Country')},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'opt_time', title: __('Opt_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'opt_d', title: __('Opt_d')},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
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