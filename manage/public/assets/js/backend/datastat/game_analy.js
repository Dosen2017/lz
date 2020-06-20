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
                    index_url: 'datastat/game_analy/index' + location.search,
                    add_url: 'datastat/game_analy/add',
                    edit_url: 'datastat/game_analy/edit',
                    del_url: 'datastat/game_analy/del',
                    multi_url: 'datastat/game_analy/multi',
                    table: 'game',
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
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},

                        {field: 'game_name', title: __('Game_name'), operate:false},
                        {field: 'register_num', title: __('Register_num'), operate:false},
                        {field: 'charge_amount', title: __('Charge_amount'), operate:false},
                        {field: 'charge_amount_lz', title: __('Charge_amount_lz'), operate:false},
                        {field: 'charge_amount_pt', title: __('Charge_amount_pt'), operate:false},
                        {field: 'charge_count', title: __('Charge_count'), operate:false},
                        {field: 'charge_count_lz', title: __('Charge_count_lz'), operate:false},
                        {field: 'charge_num', title: __('Charge_num'), operate:false},
                        {field: 'charge_num_lz', title: __('Charge_num_lz'), operate:false},
                        {field: 'arrpu', title: __('Arrpu'), operate:false},
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