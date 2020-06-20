define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'channelpack/channel_pack/index' + location.search,
                    add_url: 'channelpack/channel_pack/add',
                    edit_url: 'channelpack/channel_pack/edit',
                    del_url: 'channelpack/channel_pack/del',
                    multi_url: 'channelpack/channel_pack/multi',
                    table: 'channel_pack',
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
                        {field: 'channel_name', title: __('Channel_num'), operate:false},
                        {field: 'channel_pack_name', title: __('Channel_pack_name'), operate:false},
                        {field: 'channel_pack_tag', title: __('Channel_pack_tag'), operate:false},
                        {field: 'keystore_name', title: __('Keystore'), operate:false},
                        {field: 'icon_name', title: __('Icon'), operate:false},
                        {field: 'status', title: __('Status'), operate:false},
                        // {field: 'basepack_name', title: __('Basepack_id')},
                        // {field: 'remark', title: __('Remark')},
                        // {field: 'packid', title: __('Packid')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('下载'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'download',
                                    text: __('下载APK'),
                                    title: __('下载APK'),
                                    classname: 'btn ',
                                    icon: 'fa',
                                    url: function(row){
                                        return row.pack_path;
                                    },
                                    extend: 'target="_blank"',
                                    hidden: function (row) {
                                        if (row.pack_path === "") {
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