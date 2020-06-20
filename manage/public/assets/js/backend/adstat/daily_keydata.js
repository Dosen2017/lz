var gameList;
var channelList;
var channelPkgNumList;
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
                    index_url: 'adstat/daily_keydata/index' + location.search,
                    add_url: 'adstat/daily_keydata/add',
                    edit_url: 'adstat/daily_keydata/edit',
                    del_url: 'adstat/daily_keydata/del',
                    multi_url: 'adstat/daily_keydata/multi',
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
                        {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                        {field: 'os_type', title: __('Os_type'), operate:'FIND_IN_SET',searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal, visible:false},
                        {field: 'channel_num', title: __('Channel_num'),operate:'FIND_IN_SET', searchList: channelList, visible:false},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num'), operate:'FIND_IN_SET',searchList: channelPkgNumList,  visible:false},
                        {field: 'ads_id', title: __('Ads_id'),  operate:'FIND_IN_SET',searchList: adsIdsList, visible:false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                        {field: 'stat_date', title: __('Stat_date'), operate:false},
                        {field: 'new_player_num', title: __('New_player_num'), operate:false},
                        {field: 'dau', title: __('Dau'), operate:false},
                        {field: 'old_player_num', title: __('Old_player_num'), operate:false},
                        {field: 'all_amount', title: __('All_amount'), operate:false},
                        {field: 'new_amount', title: __('New_amount'), operate:false},
                        {field: 'old_amount', title: __('Old_amount'), operate:false},
                        {field: 'new_all_amount', title: __('New_all_amount'), operate:false},
                        {field: 'pay_person_num', title: __('Pay_person_num'), operate:false},
                        {field: 'new_pay_person_count', title: __('New_pay_person_count'), operate:false},
                        {field: 'old_pay_person_count', title: __('Old_pay_person_count'), operate:false},
                        {field: 'all_pay_rate', title: __('All_pay_rate'), operate:false},
                        {field: 'new_pay_rate', title: __('New_pay_rate'), operate:false},
                        {field: 'old_pay_rate', title: __('Old_pay_rate'), operate:false},
                        {field: 'all_arpu', title: __('All_arpu'), operate:false},
                        {field: 'new_arpu', title: __('New_arpu'), operate:false},
                        {field: 'old_arpu', title: __('Old_arpu'), operate:false},
                        {field: 'dau_arpu', title: __('Dau_arpu'), operate:false},
                        {field: 'new_ltv', title: __('New_ltv'), operate:false},
                        {field: 'time_ltv', title: __('Time_ltv'), operate:false},
                        {field: 'all_pay_num', title: __('All_pay_num'), operate:false},
                        {field: 'new_charge_count', title: __('New_charge_count'), operate:false},
                        {field: 'old_charge_count', title: __('Old_charge_count'), operate:false},
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