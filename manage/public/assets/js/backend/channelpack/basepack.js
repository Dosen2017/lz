define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'channelpack/basepack/index' + location.search,
                    add_url: 'channelpack/basepack/add',
                    edit_url: 'channelpack/basepack/edit',
                    del_url: 'channelpack/basepack/del',
                    multi_url: 'channelpack/basepack/multi',
                    table: 'basepack',
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
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal, operate:false},
                        {field: 'remark', title: __('Remark'), operate:false},
                        {field: 'createtime', title: __('Createtime'), addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('母包下载'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'download',
                                    text: __('下载'),
                                    title: __('下载'),
                                    classname: 'btn ',
                                    icon: 'fa',
                                    url: function(row){
                                        return row.url;
                                    },
                                    extend: 'target="_blank"',
                                    hidden: function (row) {
                                        if (row.url === "") {
                                            return true;
                                        }
                                    }
                                }
                            ],
                            operate:false,
                            formatter: Table.api.formatter.buttons
                        }
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