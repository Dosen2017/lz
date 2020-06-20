define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'editable'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/game/index' + location.search,
                    add_url: 'gamemanage/game/add',
                    edit_url: 'gamemanage/game/edit',
                    del_url: 'gamemanage/game/del',
                    multi_url: 'gamemanage/game/multi',
                    table: 'game',
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
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'name', title: __('Name'), editable: true},
                        {field: 'app_num', title: __('App_num')},
                        {field: 'app_key', title: __('App_key'), operate:false, visible: false, },
                        {field: 'pay_key', title: __('Pay_key'), operate:false, visible: false, },
                        {field: 'iconimage', title: __('Iconimage'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate:false},
                        {field: 'notify_url', title: __('Notify_url'), formatter: Table.api.formatter.url,visible: false, },
                        {field: 'web_notify_url', title: __('Web_notify_url'), formatter: Table.api.formatter.url, visible: false, },
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'gamemanage/game/detail'
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
            }
        }
    };
    return Controller;
});