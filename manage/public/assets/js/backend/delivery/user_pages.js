define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'delivery/user_pages/index' + location.search,
                    add_url: 'delivery/user_pages/add',
                    edit_url: 'delivery/user_pages/edit',
                    del_url: 'delivery/user_pages/del',
                    multi_url: 'delivery/user_pages/multi',
                    table: 'user_pages',
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
                        // {field: 'admin_id', title: __('Admin_id')},
                        {field: 'admin_id', title: __('Admin_id'), searchList: $.getJSON("auth/admin/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['nickname'] + "</span>";
                            }},
                        // {field: 'user_template_id', title: __('User_template_id')},
                        {field: 'user_template_id', title: __('User_template_id'), searchList: $.getJSON("delivery/user_templates/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['user_template_name'] + "</span>";
                            }},

                        {field: 'preview', title: __('Preview'), formatter: Table.api.formatter.url, operate:false},
                        // {field: 'title', title: __('Title')},
                        // {field: 'ios_url', title: __('Ios_url'), formatter: Table.api.formatter.url},
                        // {field: 'android_url', title: __('Android_url'), formatter: Table.api.formatter.url},
                        // {field: 'ios_channelid', title: __('Ios_channelid')},
                        // {field: 'android_channelid', title: __('Android_channelid')},
                        // {field: 'redirect_type', title: __('Redirect_type'), searchList: {"1":__('Redirect_type 1'),"2":__('Redirect_type 2'),"3":__('Redirect_type 3'),"4":__('Redirect_type 4')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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