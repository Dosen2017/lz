define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'delivery/user_templates/index' + location.search,
                    add_url: 'delivery/user_templates/add',
                    edit_url: 'delivery/user_templates/edit',
                    del_url: 'delivery/user_templates/del',
                    multi_url: 'delivery/user_templates/multi',
                    table: 'user_templates',
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
                        // {field: 'admin_id', title: __('Admin_id')},
                        {field: 'admin_id', title: __('Admin_id'), searchList: $.getJSON("auth/admin/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['nickname'] + "</span>";
                            }},
                        {field: 'page_name', title: __('Page_name')},
                        // {field: 'template_id', title: __('Template_id')},
                        {field: 'template_id', title: __('Template_id'), searchList: $.getJSON("delivery/page_templates/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['page_template_name'] + "</span>";
                            }},

                        // {field: 'title', title: __('Title')},
                        // {field: 'ios_url', title: __('Ios_url'), formatter: Table.api.formatter.url},
                        // {field: 'android_url', title: __('Android_url'), formatter: Table.api.formatter.url},
                        // {field: 'ios_channelid', title: __('Ios_channelid')},
                        // {field: 'android_channelid', title: __('Android_channelid')},
                        // {field: 'redirect_type', title: __('Redirect_type'), searchList: {"1":__('Redirect_type 1'),"2":__('Redirect_type 2'),"3":__('Redirect_type 3'),"4":__('Redirect_type 4')}, formatter: Table.api.formatter.normal},
                        // {field: 'query_device', title: __('Query_device')},
                        // {field: 'query_ip', title: __('Query_ip')},
                        // {field: 'query_callback', title: __('Query_callback')},
                        // {field: 'ext_config', title: __('Ext_config')},
                        // {field: 'ref_count', title: __('Ref_count')},

                        {field: 'preview', title: __('Preview'), formatter: Table.api.formatter.url, operate:false},

                        // {field: 'type_id', title: __('Type_id')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('新建发布'),
                                    title: __('弹出窗口打开'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    extend:'data-area=\'["60%", "60%"]\'',
                                    icon: 'fa fa-list',
                                    url: 'delivery/user_templates/user_pages_edit',
                                    // callback: function (data) {
                                    //     Layer.alert(JSON.stringify(data), {title: "新建发布"});
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
            }
        }
    };
    return Controller;
});