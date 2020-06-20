define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'channelpack/icons/index' + location.search,
                    add_url: 'channelpack/icons/add',
                    edit_url: 'channelpack/icons/edit',
                    del_url: 'channelpack/icons/del',
                    multi_url: 'channelpack/icons/multi',
                    table: 'icons',
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
                        {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: $.parseJSON(Config.gameList), visible:false},

                        {field: 'game_name', title: __('Game_num'), operate:false},
                        {field: 'remark', title: __('Remark'), operate:false},
                        {field: 'icon_file', title: __('Icon_file'), formatter: Table.api.formatter.image, operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});