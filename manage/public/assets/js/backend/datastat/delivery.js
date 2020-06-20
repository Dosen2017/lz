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
            Table.api.init();

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            first: function () {
                // 表格1
                var table1 = $("#table1");
                xrTable(table1);
                table1.bootstrapTable({
                    url: 'datastat/delivery/table1',
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    search: false,
                    escape:false,
                    columns: [
                        [
                            {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                            {field: 'os_type', title: __('Os_type'), operate:'FIND_IN_SET',searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal, visible:false},
                            {field: 'channel_num', title: __('Channel_num'),operate:'FIND_IN_SET', searchList: channelList, visible:false},
                            {field: 'channel_pkg_num', title: __('Channel_pkg_num'), operate:'FIND_IN_SET',searchList: channelPkgNumList,  visible:false},
                            {field: 'b_id', title: __('B_id'), operate:'FIND_IN_SET',searchList: bIdList,  visible:false},
                            {field: 'complete_time', title: __('Complete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                            {field: 'stat_date', title: __('Stat_date'), operate:false},
                            {field: 'register_num', title: __('Register_num'), operate:false},
                            {field: 'pay_money', title: __('Pay_money'), operate:false},
                            {field: 'pay_person_num', title: __('Pay_person_num'), operate:false},
                            {field: 's_active2', title: __('S_active2'), operate:false},
                            {field: 's_active2_per', title: __('S_active2_per'), operate:false},
                            {field: 'new_amount', title: __('New_amount'), operate:false},
                            {field: 'new_charge_arrpu', title: __('New_charge_arrpu'), operate:false},
                            {field: 'new_charge_person_count', title: __('New_charge_person_count'), operate:false},
                            {field: 'new_pay_rate', title: __('New_pay_rate'), operate:false},
                            {field: 'all_amount', title: __('All_amount'), operate:false},
                            {field: 'charge_person_count', title: __('Charge_person_count'), operate:false},
                            {field: 'charge_arrpu', title: __('Charge_arrpu'), operate:false},
                            {field: 'all_amount8', title: __('All_amount8'), operate:false},
                            {field: 'ltv', title: __('Ltv'), operate:false},
                            {field: 'old_player_num', title: __('Old_player_num'), operate:false},
                        ]
                    ],
                    searchFormVisible: true,
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            second: function () {
                // 表格2
                var table2 = $("#table2");
                xrTable(table2);
                table2.bootstrapTable({
                    url: 'datastat/delivery/table2',
                    // extend: {
                    //     index_url: '',
                    //     add_url: '',
                    //     edit_url: '',
                    //     del_url: '',
                    //     multi_url: '',
                    //     table: '',
                    // },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    search: false,
                    escape:false,
                    columns: [
                        [
                            {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                            {field: 'os_type', title: __('Os_type'), operate:'FIND_IN_SET',searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal, visible:false},
                            {field: 'channel_num', title: __('Channel_num'),operate:'FIND_IN_SET', searchList: channelList, visible:false},
                            {field: 'channel_pkg_num', title: __('Channel_pkg_num'), operate:'FIND_IN_SET',searchList: channelPkgNumList,  visible:false},
                            {field: 'b_id', title: __('B_id'), operate:'FIND_IN_SET',searchList: bIdList,  visible:false},
                            {field: 'complete_time', title: __('Complete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                            {field: 'b_id', title: __('B_id'), operate:false},
                            {field: 'register_num', title: __('Register_num'), operate:false},
                            {field: 'pay_money', title: __('Pay_money'), operate:false},
                            {field: 'pay_person_num', title: __('Pay_person_num'), operate:false},
                            {field: 's_active2', title: __('S_active2'), operate:false},
                            {field: 's_active2_per', title: __('S_active2_per'), operate:false},
                            {field: 'new_amount', title: __('New_amount'), operate:false},
                            {field: 'new_charge_arrpu', title: __('New_charge_arrpu'), operate:false},
                            {field: 'new_charge_person_count', title: __('New_charge_person_count'), operate:false},
                            {field: 'new_pay_rate', title: __('New_pay_rate'), operate:false},
                            {field: 'all_amount', title: __('All_amount'), operate:false},
                            {field: 'charge_person_count', title: __('Charge_person_count'), operate:false},
                            {field: 'charge_arrpu', title: __('Charge_arrpu'), operate:false},
                            {field: 'all_amount8', title: __('All_amount8'), operate:false},
                            {field: 'ltv', title: __('Ltv'), operate:false},
                            {field: 'old_player_num', title: __('Old_player_num'), operate:false},
                        ]
                    ],
                    searchFormVisible: true,
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            }
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
