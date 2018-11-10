define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/course/index',
                    add_url: 'course/course/add',
                    edit_url: 'course/course/edit',
                    del_url: 'course/course/del',
                    multi_url: 'course/course/multi',
                    table: 'course',
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
                        {field: 'id', title: __('Id')},
                        {field: 'course_category_id', title: __('Course_category_id')},
                        {field: 'name', title: __('Name')},
                        {field: 'coverimage', title: __('Coverimage'), formatter: Table.api.formatter.image},
                        {field: 'flag', title: __('Flag'), searchList: {"recommend":__('Recommend')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label},
                        {field: 'desc', title: __('Desc')},
                        {field: 'coursetimes', title: __('Coursetimes')},
                        {field: 'auth', title: __('Auth')},
                        {field: 'readnum', title: __('Readnum')},
                        {field: 'zan', title: __('Zan')},
                        {field: 'comments', title: __('Comments')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'category.id', title: __('Category.id')},
                        {field: 'category.name', title: __('Category.name')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
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