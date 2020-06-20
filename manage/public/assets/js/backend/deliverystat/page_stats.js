var adsIdsList;
adsIdsList = $.parseJSON(Config.adsIdsKVList);

define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'deliverystat/page_stats/index' + location.search,
                    add_url: 'deliverystat/page_stats/add',
                    edit_url: 'deliverystat/page_stats/edit',
                    del_url: 'deliverystat/page_stats/del',
                    multi_url: 'deliverystat/page_stats/multi',
                    table: 'page_stats',
                }
            });

            var table = $("#table");

            table.on('post-common-search.bs.table', function(event, table) {
                var form = $("form", table.$commonsearch);
                $("select[name=id]", form)
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
                        {field: 'opt_d', title: __('Opt_d'), operate:'RANGE', addclass:'datetimerange', visible:false},
                        {field: 'bid', title: __('Bid'), operate:false},
                        // {field: 'source_id', title: __('Source_id')},
                        {field: 'id', title: __('id') , searchList: adsIdsList, formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['source_name'] + "</span>";
                            }},
                        {field: 'vc_sum', title: __('Vc_sum'), operate:false},
                        {field: 'hc_sum', title: __('Hc_sum'), operate:false},
                        {field: 'hit_per', title: __('Hit_per'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'stat_record',
                                    text: __('Stat_record'),
                                    // icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'deliverystat/page_stats/record'
                                },
                                {
                                    name: 'record_log',
                                    text: __('Record_log'),
                                    // icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'deliverystat/page_stats/record_log'
                                },

                            ],
                            formatter: Table.api.formatter.operate},
                        {field: 'pre_view', title: __('Pre_view'), formatter: function(value,row,index){
                                return '<a style="text-decoration: underline" target="_blank" href="' + row['pre_view'] + '">预览</a>';
                            }, operate:false},
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
