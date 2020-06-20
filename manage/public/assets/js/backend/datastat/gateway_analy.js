var gameList;

gameList = $.parseJSON(Config.gameList);

//渲染查询条件中多选的项
function xrTable(tableObj) {
    tableObj.on('post-common-search.bs.table', function(event, table) {
        var form = $("form", table.$commonsearch);
        $("select[name=game_num]", form)
            .attr("multiple", true)
            .attr("data-live-search", true)
            .attr("data-actions-box", true)
            .attr("data-deselect-all-text", "取消")
            .attr("data-select-all-text", "全选")
            .addClass("selectpicker");
    });
}

define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/gateway_analy/index' + location.search,
                    add_url: 'datastat/gateway_analy/add',
                    edit_url: 'datastat/gateway_analy/edit',
                    del_url: 'datastat/gateway_analy/del',
                    multi_url: 'datastat/gateway_analy/multi',
                    table: 'game_users',
                }
            });

            var table = $("#table");
            xrTable(table);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'game_user_num',
                sortName: 'game_user_num',
                columns: [
                    [
                        {field: 'order_d', title: __('Stat_date'),rowspan:2, operate:false},
                        {field: 'game_name', title: __('Game_name'),rowspan:2, operate:false},
                        {title: __('Charge_title'),colspan:2},
                        {title: __('Lz_Alipay_title'),colspan:2},
                        {title: __('Lz_Wechat_H5_title'),colspan:2},
                        {title: __('Lz_Sdk_title'),colspan:4},
                    ],
                    [

                        {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},


                        {field: 'charge_count', title: __('Charge_count'), operate:false},
                        {field: 'charge_amount', title: __('Charge_amount'), operate:false},

                        {field: 'lz_alipay_charge_count', title: __('Lz_Alipay_Charge_count'), operate:false},
                        {field: 'lz_alipay_charge_amount', title: __('Lz_Alipay_Charge_amount'), operate:false},

                        {field: 'lz_wechat_h5_charge_count', title: __('Lz_Wechat_H5_Charge_count'), operate:false},
                        {field: 'lz_wechat_h5_charge_amount', title: __('Lz_Wechat_H5_Charge_amount'), operate:false},

                        {field: 'lz_sdk_charge_count', title: __('Lz_Sdk_Charge_count'), operate:false},
                        {field: 'lz_sdk_charge_amount', title: __('Lz_Sdk_Charge_amount'), operate:false},

                    ]
                ],
                searchFormVisible: true,
                pagination: false,
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