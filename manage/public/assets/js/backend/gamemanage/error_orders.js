define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/error_orders/index' + location.search,
                    add_url: 'gamemanage/error_orders/add',
                    edit_url: 'gamemanage/error_orders/edit',
                    del_url: 'gamemanage/error_orders/del',
                    multi_url: 'gamemanage/error_orders/multi',
                    table: 'error_orders',
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
                        // {checkbox: true},
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'channel_pkg_num', title: __('Channel_pkg_num')},
                        {field: 'my_order_num', title: __('My_order_num')},
                        {field: 'error_id', title: __('Error_id'), searchList: {"1":'参数缺失1',"2":'身份验证失败2',"3":'订单未找到3',"4":'重复请求4',"5":'APP_ID对应的产品ID没有配置5',
                                "6":'产品ID不在该游戏产品ID中6',"7":'下单时产品ID和请求验证receipt中的产品ID不一致7',"8":'产品ID对应的金额不在后台配置中8',"9":'下单时的充值金额和验证后返回的金额不一致9',"10":'CP端回调失败10',
                                "11":'微信预下单CURL请求失败11',"12":'微信预下单后，打开MWEB_URL失败12',"13":'微信预下单失败13',"14":'同一个订单请求重复生成订单14',"15":'获取订单号的请求已经超时15',
                                "16":'获取订单号时，游戏ID和渠道包ID不匹配16', "20001":'支付宝回调的订单状态不对20001', "20002":'卖家用户号错误20002', "20003":'应用ID错误20003',
                                "401":'支付验证返回为空401',"402":'获取订单号时，receipt重复请402'}, formatter: Table.api.formatter.normal},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                searchFormVisible: true
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