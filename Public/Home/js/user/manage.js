//得到信息
var getUserInfo = "/index.php/Home/User/getUserInfo";
//编辑
var editUserInfo = "/index.php/Home/User/edit";
//保存编辑
var doeditUserInfo = "/index.php/Home/User/doedit";
//删除
var deleteUserInfo = "/index.php/Home/User/delete";
//添加
var addUserInfo = "/index.php/Home/User/add";
//保存添加
var doaddUserInfo = "/index.php/Home/User/doadd";
//得到详细信息
var detailedUserInfo = "/index.php/Home/User/detailed";
//导出
var UserExport = "/index.php/Home/User/UserExport";

var userInfo= $('#userInfo');
//编辑
var edit = $('#edit');
//添加
var userAdd = $('#userAdd');
//详细信息
var detailed = $('#detailed');


userInfo.datagrid({
    //一个URL从远程站点请求数据
    url:getUserInfo,
    //当设置为true的时候面板大小将自适应父容器
    //fit:true,
    //真正的自动展开/收缩列的大小，以适应网格的宽度，防止水平滚动。
    fitColumns:true,
    //是否显示斑马线效果
    striped:true,
    //如果为true，则显示一个行号列
    rownumbers:true,
    //如果为true，则只允许选择一行
    singleSelect:true,
    //如果为true，则在同一行中显示数据
    nowrap:false,
    //边框是否显示
    border:false,
    //在从远程站点加载数据的时候显示提示消息
    //loadMsg:'数据加载中。。。',
    //如果为true，则在DataGrid控件底部显示分页工具栏
    pagination:true,
    //在设置分页属性的时候初始化页码
    pageNumber:1,
    //在设置分页属性的时候初始化页面大小
    pageSize:10,
    //在设置分页属性的时候 初始化页面大小选择列表
    pageList:[10,20,30,40,50],
    //顶部工具栏的DataGrid面板
    toolbar:[
        {
            text:'添加',
            iconCls:'icon-add',
            handler:function(){
                userHandels.add();
            }
        }
    ],
    columns:[[
        {field:'username',title:'用户名',width:100,align:'center',sortable:true},
        {field:'name',title:'真实姓名',width:100,align:'center',sortable:true},
        {field:'lastlogintime',title:'上次登录时间',width:100,align:'center',sortable:true},
        {field:'state',title:'用户状态',width:100,align:'center',sortable:true},
        {field:'handles',title:'操作',width:100,align:'center',sortable:true,
            formatter:function(value,row,index){
                var e = "<a href='javascript:;' onclick=userHandels.editrow("+index+","+row.id+");>编辑  </a>";
                var d = "<a href='javascript:;' onclick=userHandels.deleterow("+index+","+row.id+");>删除</a>";
                return e+d;
            }
        }
    ]],
    //定义从服务器对数据进行排序,设为false在当前页排序
    //改为true要更改php查询语句代码
    remoteSort:false,
    //定义是否允许多列排序
    multiSort:true
});

//操作对象
userHandels = {
    //修改
    editrow:function(index,id){
        edit.dialog({
            title:'编辑',
            width:500,
            height:700,
            href:editUserInfo,
            method:'post',
            queryParams:{
                id:id
            },
            buttons:[
                {
                    text:'保存',
                    handler:function(){
                        edit.form('submit',{
                            url:doeditUserInfo,
                            success:function(data){
                                if (data){
                                    UserInfo.datagrid('reload');
                                    edit.dialog('close').form('reset');
                                }
                            }

                        });
                    }
                },
                {
                    text:'关闭',
                    handler:function(){
                        edit.dialog('close').form('reset');
                    }

                }
            ]
        });

    },

    //删除
    deleterow:function(index,id){
        $.messager.confirm('系统提示', '您确定要删除吗？', function(r){
            if (r){
                $.ajax({
                    url:deleteUserInfo,
                    type:'POST',
                    data:{
                        id:id
                    },
                    success:function(data){
                        if (data){
                            UserInfo.datagrid('loaded');
                            UserInfo.datagrid('load');
                        }
                    }
                });
            }
        });
    },

    //添加
    add:function(){
        $.ajax({
            url:addUserInfo,
            method:'post',
            success:function(data){
                userAdd.dialog({
                    title:'添加',
                    modal : true,
                    width:500,
                    height:400,
                    content:data,
                    buttons:[
                        {
                            text:'添加',
                            handler:function(){
                                userAdd.form('submit',{
                                    url:doaddUserInfo,
                                    success:function(data){
                                        //console.log(data);
                                        if (data>0){
                                            userInfo.datagrid('reload');
                                            userAdd.dialog('close').form('reset');
                                        }else {
                                            $.messager.show({
                                                title:'系统提示',
                                                msg:'数据添加失败'
                                            });
                                        }
                                    }
                                });
                            }
                        },
                        {
                            text:'关闭',
                            handler:function(){
                                userAdd.dialog('close').form('reset');
                            }

                        }
                    ]
                });
            }
        });


    },

    //详细信息
    detailed:function(index,id){
        detailed.dialog({
            title:'详细信息',
            width:500,
            height:700,
            href:detailedUserInfo,
            method:'post',
            queryParams:{
                id:id
            },
            buttons:[
                {
                    text:'关闭',
                    handler:function(){
                        detailed.dialog('close');
                    }
                }
            ]
        });
    },
    //查询
    search:function(){
        UserInfo.datagrid('load',{
            name: $.trim($('input[name="name"]').val()),
            Userid: $.trim($('input[name="Userid"]').val())
        });
    },

    //导出
    export:function(){
        location.href = UserExport;
    }
};



