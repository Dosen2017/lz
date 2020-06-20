define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamemanage/users/index' + location.search,
                    add_url: 'gamemanage/users/add',
                    edit_url: 'gamemanage/users/edit',
                    del_url: 'gamemanage/users/del',
                    multi_url: 'gamemanage/users/multi',
                    table: 'users',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'user_num',
                sortName: 'user_num',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'user_num', title: __('User_num')},
                        {field: 'user_name', title: __('User_name')},
                        // {field: 'password', title: __('Password')},
                        // {field: 'salt', title: __('Salt')},
                        // {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'nick_name', title: __('Nick_name')},
                        // {field: 'phone_code', title: __('Phone_code')},
                        {field: 'phone_number', title: __('Phone_number')},
                        // {field: 'email', title: __('Email')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'reg_ip', title: __('Reg_ip')},
                        {field: 'full_name', title: __('Full_name')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'country', title: __('Country'), operate:false},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'last_time', title: __('Last_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'last_ip', title: __('Last_ip')},
                        // {field: 'lock_flag', title: __('Lock_flag')},


                        {field: 'operate', title: __('Operate'), table: table,events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('修改密码'),
                                    title: __('弹出窗口打开'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    extend:'data-area=\'["30%", "30%"]\'',
                                    icon: 'fa fa-list',
                                    url: 'gamemanage/users/detail',
                                    callback: function (data) {
                                        Layer.alert(JSON.stringify(data), {title: "密码信息"});
                                    }
                                },

                                {
                                    name: 'lock',
                                    text: __('修改锁定标识'),
                                    title: __('弹出窗口打开'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    extend:'data-area=\'["30%", "30%"]\'',
                                    icon: 'fa fa-list',
                                    url: 'gamemanage/users/lock',
                                    callback: function (data) {
                                        Layer.alert(JSON.stringify(data), {title: "锁定信息"});
                                    }
                                },
                            ],
                            formatter: function (value, row, index) {
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                $(table).data("operate-edit", null);
                                $(table).data("operate-del", null);
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            }
                        }
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
        detail: function () {
            $(document).on('click', '.btn-callback', function () {
                passwd = $("input[name=callback]").val();
                id = $("input[name=ids]").val();
                //修改密码
                Fast.api.ajax({
                    url:'gamemanage/users/detail',
                    data:{ids:id, pwd:passwd},
                }, function(data, ret){
                    //成功的回调
                    Fast.api.close("修改成功，密码：" + passwd);
                    return false;
                }, function(data, ret){
                    //失败的回调
                    Fast.api.close("修改失败，密码：" + passwd);
                    return false;
                });

            });
        },
        lock: function () {
            $(document).on('click', '.btn-callback', function () {
                lock = $("input[name=callback]").val();
                id = $("input[name=ids]").val();
                //修改密码
                Fast.api.ajax({
                    url:'gamemanage/users/lock',
                    data:{ids:id, lock:lock},
                }, function(data, ret){
                    //成功的回调
                    Fast.api.close("修改成功，锁定标识：" + lock);
                    return false;
                }, function(data, ret){
                    //失败的回调
                    Fast.api.close("修改失败，锁定标识：" + lock);
                    return false;
                });

            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});