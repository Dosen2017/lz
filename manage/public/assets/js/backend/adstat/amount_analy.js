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
                    index_url: 'adstat/amount_analy/index' + location.search,
                    add_url: 'adstat/amount_analy/add',
                    edit_url: 'adstat/amount_analy/edit',
                    del_url: 'adstat/amount_analy/del',
                    multi_url: 'adstat/amount_analy/multi',
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
                        {field: 'ads_id', title: __('Ads_id'), operate:'FIND_IN_SET',searchList: adsIdsList,  visible:false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                        {field: 'stat_date', title: __('Stat_date'), operate:false},
                        {field: 'register_num', title: __('Register_num'), operate:false},
                        {field: 'charge_num', title: __('Charge_num'), operate:false},
                        {field: 'below10', title: __('Below10'), operate:false},
                        {field: 'over10', title: __('Over10'), operate:false},
                        {field: 'over100', title: __('Over100'), operate:false},
                        {field: 'over200', title: __('Over200'), operate:false},
                        {field: 'over300', title: __('Over300'), operate:false},
                        {field: 'over500', title: __('Over500'), operate:false},
                        {field: 'over1000', title: __('Over1000'), operate:false},
                        {field: 'over2000', title: __('Over2000'), operate:false},
                        {field: 'over3000', title: __('Over3000'), operate:false},
                        {field: 'over5000', title: __('Over5000'), operate:false},
                        {field: 'over10000', title: __('Over10000'), operate:false},
                        {field: 'over20000', title: __('Over20000'), operate:false},
                        {field: 'over30000', title: __('Over30000'), operate:false},
                        {field: 'over50000', title: __('Over50000'), operate:false},
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