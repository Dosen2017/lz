define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/game_users/index' + location.search,
                    // add_url: 'gamemanage/game_users/add',
                    // edit_url: 'gamemanage/game_users/edit',
                    // del_url: 'gamemanage/game_users/del',
                    // multi_url: 'gamemanage/game_users/multi',
                    table: 'game_users',
                }
            });



            var table = $("#table");

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatShowingRows'] = function (pageFrom, pageTo, totalRows) {
                return '显示第 ' + pageFrom + ' 到第 ' + pageTo + ' 条记录，总共 ' + totalRows + ' 条记录';
            };

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'game_user_num',
                sortName: 'game_user_num',
                columns: [
                    [
                        // {checkbox: true},

                        // {field: 'game_num', title: __('Game_num')},
                        {field: 'game_num', title: __('Game_num'), searchList: $.getJSON("gamemanage/game/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['game_name'] + "</span>";
                            }},
                        // {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num'), searchList: $.getJSON("gamemanage/channel_pkg/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['channel_pkg_name'] + "</span>";
                            }},
                        // {field: 'channel_num', title: __('Channel_num')},
                        {field: 'channel_num', title: __('Channel_num'), searchList: $.getJSON("gamemanage/channel/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['channel_name'] + "</span>";
                            }},

                        {field: 'game_user_num', title: __('Game_user_num'), operate:false},
                        {field: 'user_num', title: __('User_num')},
                        {field: 'user_name', title: __('User_name')},

                        {field: 'os_type', title: __('Os_type'), searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal},
                        {field: 'charge_amount', title: __('Charge_amount'), operate:false},
                        {field: 'charge_count', title: __('Charge_count'), operate:false},
                        {field: 'charge_amount_lz', title: __('Charge_amount_lz'), operate:false},
                        {field: 'charge_count_lz', title: __('Charge_count_lz'), operate:false},
                        {field: 'device_num', title: __('Device_num'), operate:false},
                        // {field: 'device_num_md5', title: __('Device_num_md5')},
                        {field: 'device_model', title: __('Device_model'), operate:false},
                        // {field: 'mac', title: __('Mac')},
                        {field: 'create_ip', title: __('Create_ip')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'create_d', title: __('Create_d')},
                        // {field: 'country', title: __('Country')},
                        {field: 'province', title: __('Province'), operate:false},
                        {field: 'city', title: __('City'), operate:false},
                        {field: 'last_time', title: __('Last_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'last_ip', title: __('Last_ip')},
                        // {field: 'reg_source', title: __('Reg_source')},
                        {field: 'pack_num', title: __('Pack_num')},
                        {field: 'b_id', title: __('B_id')},   //给渠道包打的标识，作用类似于广告ID
                        {field: 'ads_id', title: __('Ads_id')},
                        {field: 'last_pay', title: __('Last_pay'), operate:false},
                        {field: 'last_pay_lz', title: __('Last_pay_lz'), operate:false},
                        // {field: 'user_num', title: __('订单明细'), formatter: Controller.api.formatter.user_num, operate:false},

                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'pay_log',
                                    text: __('Pay_log'),
                                    // icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'gamemanage/game_users/pay_log'
                                },

                                {
                                    name: 'user_log',
                                    text: __('User_log'),
                                    // icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'gamemanage/game_users/user_log'
                                },

                            ],
                            formatter: Table.api.formatter.operate},

                    ]
                ],
                searchFormVisible: true,
                pageSize: 10,
                pageList: [10, 25, 50, 100, 1000, 10000, 'All'],
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
            },
            // formatter: {
            //     user_num: function (value, row, index) {
            //         //这里手动构造URL
            //         url = "gamemanage/game_orders?" + this.field + "=" + value;
            //
            //         //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
            //         return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("%s", '订单明细') + '">' + __("%s", '订单明细') + '</a>';
            //         // return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';
            //
            //         //方式二,直接调用Table.api.formatter.addtabs
            //         // return Table.api.formatter.addtabs(value, row, index, url);
            //     }
            // }
        }
    };
    return Controller;
});