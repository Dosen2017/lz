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
                    index_url: 'datastat/retention_pay/index' + location.search,
                    add_url: 'datastat/retention_pay/add',
                    edit_url: 'datastat/retention_pay/edit',
                    del_url: 'datastat/retention_pay/del',
                    multi_url: 'datastat/retention_pay/multi',
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
                        {field: 's_pay_num', title: __('S_pay_num'), operate:false},
                        {field: 's_active2', title: __('S_active2'), operate:false},
                        {field: 's_active2', title: __('S_active2'), operate:false},
                        {field: 's_active3', title: __('S_active3'), operate:false},
                        {field: 's_active4', title: __('S_active4'), operate:false},
                        {field: 's_active5', title: __('S_active5'), operate:false},
                        {field: 's_active6', title: __('S_active6'), operate:false},
                        {field: 's_active7', title: __('S_active7'), operate:false},
                        {field: 's_active8', title: __('S_active8'), operate:false},
                        {field: 's_active9', title: __('S_active9'), operate:false},
                        {field: 's_active10', title: __('S_active10'), operate:false},
                        {field: 's_active11', title: __('S_active11'), operate:false},
                        {field: 's_active12', title: __('S_active12'), operate:false},
                        {field: 's_active13', title: __('S_active13'), operate:false},
                        {field: 's_active14', title: __('S_active14'), operate:false},
                        {field: 's_active30', title: __('S_active30'), operate:false},
                        {field: 's_active60', title: __('S_active60'), operate:false}
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