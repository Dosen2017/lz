var gameList;
var channelList;
var channelPkgNumList;
var adsIdsList;
gameList = $.parseJSON(Config.gameList);
channelList = $.parseJSON(Config.channelList);
channelPkgNumList = $.parseJSON(Config.channelPkgNumList);

adsIdsList = $.parseJSON(Config.adsIdsList);

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
        $("select[name=os_type]", form)
            .attr("multiple", true)
            .attr("data-live-search", true)
            .attr("data-actions-box", true)
            .attr("data-deselect-all-text", "取消")
            .attr("data-select-all-text", "全选")
            .addClass("selectpicker");
        $("select[name=channel_num]", form)
            .attr("multiple", true)
            .attr("data-live-search", true)
            .attr("data-actions-box", true)
            .attr("data-deselect-all-text", "取消")
            .attr("data-select-all-text", "全选")
            .addClass("selectpicker");
        $("select[name=channel_pkg_num]", form)
            .attr("multiple", true)
            .attr("data-live-search", true)
            .attr("data-actions-box", true)
            .attr("data-deselect-all-text", "取消")
            .attr("data-select-all-text", "全选")
            .addClass("selectpicker");
        $("select[name=ads_id]", form)
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
                    index_url: 'adstat/game_orders/index' + location.search,
                    add_url: 'adstat/game_orders/add',
                    edit_url: 'adstat/game_orders/edit',
                    del_url: 'adstat/game_orders/del',
                    multi_url: 'adstat/game_orders/multi',
                    table: 'game_orders',
                }
            });

            var table = $("#table");
            xrTable(table);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                        {field: 'os_type', title: __('Os_type'), operate:'FIND_IN_SET',searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal, visible:false},
                        {field: 'channel_num', title: __('Channel_num'),operate:'FIND_IN_SET', searchList: channelList, visible:false},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num'), operate:'FIND_IN_SET',searchList: channelPkgNumList,  visible:false},
                        {field: 'ads_id', title: __('Ads_id'),  operate:'FIND_IN_SET',searchList: adsIdsList, visible:false},
                        {field: 'complete_time', title: __('Complete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                        {field: 'stat_date', title: __('Stat_date'), operate:false},
                        {field: 'register_num', title: __('Register_num'), operate:false},
                        {field: 'pay_money', title: __('Pay_money'), operate:false},
                        {field: 'pay_lz_money', title: __('Pay_lz_money'), operate:false},
                        {field: 'pay_pt_money', title: __('Pay_pt_money'), operate:false},
                        {field: 'pay_num', title: __('Pay_num'), operate:false},
                        {field: 'pay_lz_num', title: __('Pay_lz_num'), operate:false},
                        {field: 'pay_person_num', title: __('Pay_person_num'), operate:false},
                        {field: 'pay_lz_person_num', title: __('Pay_lz_person_num'), operate:false},
                        {field: 'arrpu', title: __('Arrpu'), operate:false},
                        {field: 'dau', title: __('Dau'), operate:false},
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