var gameList;
var channelList;
var channelPkgNumList;

gameList = $.parseJSON(Config.gameList);
channelList = $.parseJSON(Config.channelList);
channelPkgNumList = $.parseJSON(Config.channelPkgNumList);
bIdList = $.parseJSON(Config.bIdList);

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
        $("select[name=b_id]", form)
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
                    index_url: 'datastat/pay_trend/index' + location.search,
                    add_url: 'datastat/pay_trend/add',
                    edit_url: 'datastat/pay_trend/edit',
                    del_url: 'datastat/pay_trend/del',
                    multi_url: 'datastat/pay_trend/multi',
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
                escape:false,
                columns: [
                    [
                        {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                        {field: 'os_type', title: __('Os_type'), operate:'FIND_IN_SET',searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal, visible:false},
                        {field: 'channel_num', title: __('Channel_num'),operate:'FIND_IN_SET', searchList: channelList, visible:false},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num'), operate:'FIND_IN_SET',searchList: channelPkgNumList,  visible:false},
                        {field: 'b_id', title: __('B_id'), operate:'FIND_IN_SET',searchList: bIdList,  visible:false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                        {field: 'stat_date', title: __('Stat_date'), operate:false},
                        {field: 'register_num', title: __('Register_num'), operate:false},

                        {field: 'a_charge1_amount', title: __('A_charge1_amount'), operate:false},
                        {field: 'a_charge2_amount', title: __('A_charge2_amount'), operate:false},
                        {field: 'a_charge3_amount', title: __('A_charge3_amount'), operate:false},
                        {field: 'a_charge4_amount', title: __('A_charge4_amount'), operate:false},
                        {field: 'a_charge5_amount', title: __('A_charge5_amount'), operate:false},
                        {field: 'a_charge6_amount', title: __('A_charge6_amount'), operate:false},
                        {field: 'a_charge7_amount', title: __('A_charge7_amount'), operate:false},
                        {field: 'a_charge8_amount', title: __('A_charge8_amount'), operate:false},
                        {field: 'a_charge9_amount', title: __('A_charge9_amount'), operate:false},
                        {field: 'a_charge10_amount', title: __('A_charge10_amount'), operate:false},
                        {field: 'a_charge11_amount', title: __('A_charge11_amount'), operate:false},
                        {field: 'a_charge12_amount', title: __('A_charge12_amount'), operate:false},
                        {field: 'a_charge13_amount', title: __('A_charge13_amount'), operate:false},
                        {field: 'a_charge14_amount', title: __('A_charge14_amount'), operate:false},
                        {field: 'a_charge15_amount', title: __('A_charge15_amount'), operate:false},
                        {field: 'a_charge30_amount', title: __('A_charge30_amount'), operate:false},
                        {field: 'a_charge60_amount', title: __('A_charge60_amount'), operate:false},

                        {field: 'a_charge_amount', title: __('A_charge_amount'), operate:false},
                        {field: 'a_charge_amount_lz', title: __('A_charge_amount_lz'), operate:false},

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