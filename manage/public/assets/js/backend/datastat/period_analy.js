var gameList;
var channelList;
var channelPkgNumList;
var bIdList;

gameList = $.parseJSON(Config.gameList);
channelList = $.parseJSON(Config.channelList);
channelPkgNumList = $.parseJSON(Config.channelPkgNumList);
bIdList = $.parseJSON(Config.bIdList);

function xrTable(tableObj) {



    tableObj.on('search.bs.table', function () {
        location.reload();
    })


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


define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, ArtTemp) {

    function getColumns(datelist)
    {
        var colums = [
            [
                {field: 'period', title: __('Stat_date'),rowspan:2, operate:false},
                {title: __('Reg'),colspan:datelist.length},
                {title: __('Charge_num'),colspan:datelist.length},
                {title: __('Charge_amount'),colspan:datelist.length+6},
            ],
            [

                {field: 'game_num', title: __('Game_num'),  operate:'FIND_IN_SET',searchList: gameList, visible:false},
                {field: 'os_type', title: __('Os_type'), operate:'FIND_IN_SET',searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal, visible:false},
                {field: 'channel_num', title: __('Channel_num'),operate:'FIND_IN_SET', searchList: channelList, visible:false},
                {field: 'channel_pkg_num', title: __('Channel_pkg_num'), operate:'FIND_IN_SET',searchList: channelPkgNumList,  visible:false},
                {field: 'b_id', title: __('B_id'), operate:'FIND_IN_SET',searchList: bIdList,  visible:false},
                {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.date, visible:false},
            ]
        ];

        datelist.forEach(function (item, index) {
            colums[1].push({field: 'reg_'+item, title: item, operate:false,
                cellStyle:function () {
                    return {
                        css:{
                            background: 'khaki'
                        }
                    }
                },
                formatter:function (value, row) {
                    if (row['reg_change_'+item] > 0) {
                        return value + ' <span class="description-percentage text-red"><i class="fa fa-long-arrow-up"></i> '+row['reg_change_'+item]+'</span>';
                    }else if(row['reg_change_'+item] < 0) {
                        return value + ' <span class="description-percentage text-green"><i class="fa fa-long-arrow-down"></i> '+row['reg_change_'+item]+'</span>';
                    }else{
                        return value;
                    }
                }
            });
        });
        datelist.forEach(function (item, index) {
            colums[1].push({field: 'charge_count_'+item, title: item, operate:false,
                cellStyle:function () {
                    return {
                        css:{
                            background: 'lightskyblue'
                        }
                    }
                }
            });
        });
        datelist.forEach(function (item, index) {
            colums[1].push({field: 'charge_amount_'+item, title: item, operate:false,
                cellStyle:function () {
                    return {
                        css:{
                            background: 'lightpink'
                        }
                    }
                }});
        });
        return colums;
    }
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/period_analy/index' + location.search,
                    add_url: 'datastat/period_analy/add',
                    edit_url: 'datastat/period_analy/edit',
                    del_url: 'datastat/period_analy/del',
                    multi_url: 'datastat/period_analy/multi',
                    table: 'game_users',
                }
            });

            var table = $("#table");
            xrTable(table);

            function createTable() {
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'game_user_num',
                    sortName: 'game_user_num',
                    columns: getColumns(Config.dateList),
                    search: false,
                    commonSearch: true,
                    searchFormVisible: true,
                    ajax: function (request) {
                        $.ajax({
                            type: request['type'],
                            url: $.fn.bootstrapTable.defaults.extend.index_url,
                            contentType: request['contentType'],
                            dataType: request['dataType'],
                            data: request['data'],

                            success: function (ret) {
                                //失败的回调
                                var head = ret.datelist;
                                var heads = '<tr>\n' +
                                    '    <th style="text-align: center; vertical-align: middle; " rowspan="2" data-field="period">\n' +
                                    '        <div class="th-inner ">时刻</div>\n' +
                                    '        <div class="fht-cell"></div>\n' +
                                    '    </th>\n' +
                                    '    <th style="text-align: center; vertical-align: middle; " colspan="'+head.length+'">\n' +
                                    '        <div class="th-inner ">注册</div>\n' +
                                    '        <div class="fht-cell"></div>\n' +
                                    '    </th>\n' +
                                    '    <th style="text-align: center; vertical-align: middle; " colspan="'+head.length+'">\n' +
                                    '        <div class="th-inner ">充值笔数</div>\n' +
                                    '        <div class="fht-cell"></div>\n' +
                                    '    </th>\n' +
                                    '    <th style="text-align: center; vertical-align: middle; " colspan="'+(head.length+6)+'">\n' +
                                    '        <div class="th-inner ">充值金额</div>\n' +
                                    '        <div class="fht-cell"></div>\n' +
                                    '    </th>\n' +
                                    '</tr>\n';

                                heads += '<tr>\n';
                                $.each(head, function (index, item) {
                                    heads += '<th style="text-align: center; vertical-align: middle; " data-field="reg_'+item+'">\n' +
                                        '        <div class="th-inner ">'+item+'</div>\n' +
                                        '        <div class="fht-cell"></div>\n' +
                                        '    </th>';
                                });
                                $.each(head, function (index, item) {
                                    heads += '<th style="text-align: center; vertical-align: middle; " data-field="charge_count_'+item+'">\n' +
                                        '        <div class="th-inner ">'+item+'</div>\n' +
                                        '        <div class="fht-cell"></div>\n' +
                                        '    </th>';
                                });
                                $.each(head, function (index, item) {
                                    heads += '<th style="text-align: center; vertical-align: middle; " data-field="charge_amount_'+item+'">\n' +
                                        '        <div class="th-inner ">'+item+'</div>\n' +
                                        '        <div class="fht-cell"></div>\n' +
                                        '    </th>';
                                });

                                heads += '</tr>\n';

                                table.children('thead').html(heads);

                                var list = ret.rows;

                                var datalist = '';
                                var lastKey = list.length-1;
                                $.each(list, function (index, item) {
                                    datalist += '<tr data-index="'+index+'"><td style="text-align: center; vertical-align: middle; ">'+item['period']+'</td>\n';

                                    $.each(head, function (k, v) {
                                        var color = 'khaki';

                                        var regV = item['reg_'+v];
                                        if (index === lastKey) {
                                            regV = '<b>'+regV+'</b>';
                                        }

                                        if (item['reg_change_'+v]) {
                                            if (item['reg_change_'+v] < 0) {
                                                regV += ' <span class="description-percentage text-green"><i class="fa fa-long-arrow-down"></i> '+item['reg_change_'+v]+'</span>';
                                            }else if(item['reg_change_'+v] > 0) {
                                                regV += ' <span class="description-percentage text-red"><i class="fa fa-long-arrow-up"></i> '+item['reg_change_'+v]+'</span>';
                                            }
                                        }
                                        datalist += '<td style="background: '+color+'; text-align: center; vertical-align: middle; ">'+regV+'</td>\n';
                                    });

                                    $.each(head, function (k, v) {
                                        var color = 'lightskyblue';
                                        var chargeCountV = item['charge_count_'+v];
                                        if (index === lastKey) {
                                            chargeCountV = '<b>'+chargeCountV+'</b>';
                                        }
                                        if (item['charge_count_change_'+v]) {
                                            if (item['charge_count_change_'+v] < 0) {
                                                chargeCountV += ' <span class="description-percentage text-green"><i class="fa fa-long-arrow-down"></i> '+item['charge_count_change_'+v]+'</span>';
                                            }else if(item['charge_count_change_'+v] > 0) {
                                                chargeCountV += ' <span class="description-percentage text-red"><i class="fa fa-long-arrow-up"></i> '+item['charge_count_change_'+v]+'</span>';
                                            }
                                        }
                                        datalist += '<td style="background: '+color+'; text-align: center; vertical-align: middle; ">'+chargeCountV+'</td>\n';
                                    });

                                    $.each(head, function (k, v) {
                                        var color = 'lightpink';
                                        var chargeAmountV = item['charge_amount_'+v];
                                        if (index === lastKey) {
                                            chargeAmountV = '<b>'+chargeAmountV+'</b>';
                                        }
                                        if (item['charge_amount_change_'+v]) {
                                            if (item['charge_amount_change_'+v] < 0) {
                                                chargeAmountV += ' <span class="description-percentage text-green"><i class="fa fa-long-arrow-down"></i> '+item['charge_amount_change_'+v]+'</span>';
                                            }else if(item['charge_amount_change_'+v] > 0) {
                                                chargeAmountV += ' <span class="description-percentage text-red"><i class="fa fa-long-arrow-up"></i> '+item['charge_amount_change_'+v]+'</span>';
                                            }
                                        }

                                        datalist += '<td style="background: '+color+'; text-align: center; vertical-align: middle; ">'+chargeAmountV+'</td>\n';
                                    });
                                    datalist += '</tr>';
                                });

                                request.success({
                                    total: ret.total,
                                    rows: ret.rows
                                });
                                table.children('tbody').html(datalist);
                            },

                            error: function () {
                                location.reload();
                            }
                        });
                    },
                });
                Table.api.bindevent(table);
            }

            createTable();

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"),function () {
                    alert(1);
                }, function () {

                }, function () {
                    alert('s');
                });
            }
        }
    };
    return Controller;
});