define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'channelpack/keystore/index' + location.search,
                    add_url: 'channelpack/keystore/add',
                    edit_url: 'channelpack/keystore/edit',
                    del_url: 'channelpack/keystore/del',
                    multi_url: 'channelpack/keystore/multi',
                    table: 'keystore',
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
                        {field: 'remark', title: __('Remark')},
                        // {field: 'key_file', title: __('Key_file'), mimetype:['key', 'keystore']},
                        // {field: 'key_pwd', title: __('Key_pwd')},
                        // {field: 'aliaskey', title: __('Aliaskey')},
                        // {field: 'aliaspwd', title: __('Aliaspwd')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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