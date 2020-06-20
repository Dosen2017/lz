var adsIdsList;
adsIdsList = $.parseJSON(Config.adsIdsList);

define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'deliverystat/page_logs/index' + location.search,
                    add_url: 'deliverystat/page_logs/add',
                    edit_url: 'deliverystat/page_logs/edit',
                    del_url: 'deliverystat/page_logs/del',
                    multi_url: 'deliverystat/page_logs/multi',
                    table: 'page_logs',
                }
            });

            var table = $("#table");
            table.on('post-common-search.bs.table', function(event, table) {
                var form = $("form", table.$commonsearch);
                $("select[name=ads_id]", form)
                    .attr("data-live-search", true)
                    .addClass("selectpicker");

            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'ads_id', title: __('Ads_id') , searchList: adsIdsList},
                        // {field: 'user_template_id', title: __('User_template_id')},
                        // {field: 'template_id', title: __('Template_id')},
                        {field: 'channel_id', title: __('Channel_id'), operate:false},
                        {field: 'ip', title: __('Ip'), operate:false},
                        // {field: 'user_agent', title: __('User_agent')},
                        // {field: 'device_id', title: __('Device_id')},
                        // {field: 'callback_url', title: __('Callback_url'), formatter: Table.api.formatter.url},
                        // {field: 'referer', title: __('Referer')},
                        // {field: 'reg_count', title: __('Reg_count')},
                        {field: 'add_time', title: __('Add_time'), operate:false, formatter: Table.api.formatter.datetime}
                        // {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'add_d', title: __('Add_d')},
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