define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'clipboard.min'], function ($, undefined, Backend, Table, Form, ClipboardJS) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'delivery/common_apis/index' + location.search,
                    add_url: 'delivery/common_apis/add',
                    edit_url: 'delivery/common_apis/edit',
                    del_url: 'delivery/common_apis/del',
                    multi_url: 'delivery/common_apis/multi',
                    table: 'common_apis',
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
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'ads_id', title: __('Ads_id')},
                        // {field: 'admin_id', title: __('Admin_id')},
                        {field: 'admin_id', title: __('Admin_id'), searchList: $.getJSON("auth/admin/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['nickname'] + "</span>";
                            }},
                        {field: 'api_description', title: __('Api_description'), operate:false},
                        {field: 'channel_id', title: __('Channel_id'), operate:false},

                        {field: 'click_url', title: __('Click_url'), events:Controller.api.events.copy,  formatter: Controller.api.formatter.copy, operate:false},

                        // {field: 'copy', title: __('Copy'), formatter: function(value,row,index){
                        //         return '<a style="text-decoration: underline" target="_blank" href="' + row['copy'] + '">复制</a>';
                        //     }, operate:false},
                        // {field: 'query_deviceid', title: __('Query_deviceid')},
                        // {field: 'query_ip', title: __('Query_ip')},
                        // {field: 'query_mac', title: __('Query_mac')},
                        // {field: 'query_callback', title: __('Query_callback')},
                        // {field: 'return_content', title: __('Return_content')},
                        // {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                searchFormVisible:true
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
            formatter: {
                copy:function(value, row, index) {
                    return '<button class="btn btn-copy" data-clipboard-text="' + row['click_url'] + '">点击复制</button>';
                },
            },
            events:{
                copy:{
                    'click .btn-copy':function (e, value, row, index) {
                        var clipboard = new ClipboardJS('.btn');
                        Toastr.info("复制成功!");
                    }
                }
            }
        }
    };
    return Controller;
});