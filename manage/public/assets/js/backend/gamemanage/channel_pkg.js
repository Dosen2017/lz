define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'editable'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/channel_pkg/index' + location.search,
                    add_url: 'gamemanage/channel_pkg/add',
                    edit_url: 'gamemanage/channel_pkg/edit',
                    del_url: 'gamemanage/channel_pkg/del',
                    multi_url: 'gamemanage/channel_pkg/multi',
                    table: 'channel_pkg',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible:false, operate:false },
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'number', title: __('Number')},
                        {field: 'channel_pkg_name', title: __('Channel_pkg_name'), editable: true},
                        // {field: 'game_num', title: __('Game_num')},
                        {field: 'game_num', title: __('Game_num'), searchList: $.getJSON("gamemanage/game/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['game_name'] + "</span>";
                            }},
                        // {field: 'channel_num', title: __('Channel_num')},
                        {field: 'channel_num', title: __('Channel_num'), searchList: $.getJSON("gamemanage/channel/searchlist"), formatter: function(value,row,index){
                                return "<span class='label label-info'>" +  row['channel_name'] + "</span>";
                            }},
                        {field: 'os_type', title: __('Os_type'), searchList: {"1":__('Os_type 1'),"2":__('Os_type 2'),"3":__('Os_type 3'),"4":__('Os_type 4'),"5":__('Os_type 5')}, formatter: Table.api.formatter.normal},
                        {field: 'bundle', title: __('Bundle'), editable: true},
                        {field: 'notify_url', title: __('Notify_url'), formatter: Table.api.formatter.url, visible:false},
                        {field: 'api_url', title: __('Api_url'), formatter: Table.api.formatter.url, visible: false},
                        {field: 'name_suffix', title: __('Name_suffix')},
                        {field: 'channel_config', title: __('Channel_config'), operate:false},
                        // {field: 'payment_num', title: __('Payment_num'), operate:false},
                        {field: 'channel_pkg_num', title: __('支付策略'), formatter: Controller.api.formatter.channel_pkg_num, operate:false},
                        {field: 'login_lockswitch', title: __('Login_lockswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'register_lockswitch', title: __('Register_lockswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'pay_lockswitch', title: __('Pay_lockswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'login_smswitch', title: __('Login_smswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'pay_smswitch', title: __('Pay_smswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'quick_regitserswitch', title: __('Quick_regitserswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'fcmswitch', title: __('Fcmswitch'), formatter: Table.api.formatter.toggle, operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'gamemanage/channel_pkg/detail'
                            }],
                            formatter: Table.api.formatter.operate}
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
            },
            formatter: {
                channel_pkg_num: function (value, row, index) {
                    //这里手动构造URL
                    url = "gamemanage/channel_payments?" + this.field + "=" + value;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("%s(%d)", '策略设置', row['payment_num']) + '">' + __("%s(<font color='red'>%d</font>)", '策略设置', row['payment_num']) + '</a>';
                    // return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('Search %s', value) + '</a>';

                    //方式二,直接调用Table.api.formatter.addtabs
                    // return Table.api.formatter.addtabs(value, row, index, url);
                }
            }
        }
    };
    return Controller;
});