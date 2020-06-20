define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'delivery/page_templates/index' + location.search,
                    add_url: 'delivery/page_templates/add',
                    edit_url: 'delivery/page_templates/edit',
                    del_url: 'delivery/page_templates/del',
                    multi_url: 'delivery/page_templates/multi',
                    table: 'page_templates',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'template_id',
                sortName: 'template_id',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'template_id', title: __('Template_id'), operate:false},
                        {field: 'name', title: __('Name')},
                        // {field: 'template_path', title: __('Template_path')},
                        {field: 'desc', title: __('Desc'), operate:false},
                        // {field: 'picture', title: __('Picture')},
                        // {field: 'title', title: __('Title')},
                        // {field: 'ios_url', title: __('Ios_url'), formatter: Table.api.formatter.url},
                        // {field: 'android_url', title: __('Android_url'), formatter: Table.api.formatter.url},
                        // {field: 'ios_channelid', title: __('Ios_channelid')},
                        // {field: 'android_channelid', title: __('Android_channelid')},
                        // {field: 'args_config', title: __('Args_config')},
                        {field: 'ref_count', title: __('Ref_count'), operate:false},

                        {field: 'preview', title: __('Preview'), formatter: Table.api.formatter.url, operate:false},

                        // {
                        //     field: 'buttons',
                        //     width: "120px",
                        //     title: __('按钮组'),
                        //     table: table,
                        //     events: , ret,
                        //     buttons: [
                        //         {
                        //             name: 'detail',
                        //             text: __('修改密码'),
                        //             title: __('弹出窗口打开'),
                        //             classname: 'btn btn-xs btn-primary btn-dialog',
                        //             extend:'data-area=\'["30%", "30%"]\'',
                        //             icon: 'fa fa-list',
                        //             url: 'gamemanage/users/detail',
                        //             callback: function (data) {
                        //                 Layer.alert(JSON.stringify(data), {title: "密码信息"});
                        //             }
                        //         },
                        //     ],
                        //     formatter: Table.api.formatter.buttons
                        // },


                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('使用模板'),
                                    title: __('弹出窗口打开'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    extend:'data-area=\'["60%", "60%"]\'',
                                    icon: 'fa fa-list',
                                    url: 'delivery/page_templates/user_edit',
                                    // callback: function (data) {
                                    //     Layer.alert(JSON.stringify(data), {title: "使用模板"});
                                    // }
                                },
                            ],
                            formatter: Table.api.formatter.operate}
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
            },
        }
    };
    return Controller;
});