$(function(){
    //退出系统url
    var logout_url = "/Home/Login/logout";
    //左侧导航url
    var menu_url = "/Home/Index/menu";
    //修改密码
    var changePass = "/Home/Index/changePass";

    var logout = $('#logout');
    var tab = $('#tabs');
    var menu = $('#menu');
    var formPass = $('#formPass');
    var oldPass = $('#oldPass');
    var newPass = $('#newPass');
    var repeatPass = $('#repeatPass');

    //退出系统
    logout.click(function(){
        $.messager.confirm({
            title:'系统提示',
            msg:'您确定要退出本次登录吗?',
            fn:function(r){
                if (r){
                    location.href = logout_url;
                }
            }
        });
    });

    //左侧导航添加
    menu.accordion({
        //如果设置为true，分类容器大小将自适应父容器
        //fit:true,
        //定义是否显示边框
        border:false,
        //设置初始化时默认选中的面板索引号,0,1,2...
        //selected:true
        selected:2,
        multiple:true
    });

    //添加导航菜单
    addMenu();
    function addMenu(){
        $.ajax({
            url:menu_url,
            method: "POST",
            success:function(data){
                $.each(data,function(index,value){
                    var menulist = "<ul id='tt"+index+"'></ul>";
                    menu.accordion({
                        onAdd:function(title,index){
                            $('#tt' + index).tree({
                                data:data[index]['children'],
                                onClick:function(node){
                                    if (node.url){
                                        if (tab.tabs('exists', node.text)) {
                                            tab.tabs('select', node.text);
                                        }else {
                                            tab.tabs('add',{
                                                title:node.text,
                                                closable : true,
                                                iconCls:"icon-" + node.icon,
                                                href:"/" + node.url
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    });
                    menu.accordion('add', {
                        title: value.text,
                        content: menulist,
                        iconCls:"icon-" + value.icon
                        //selected: false
                    });
                });
            }
        });
    }


    //起始页
    tab.tabs({
        border:false,
        fit:true
    });

    //修改密码start
    $('#changePass').click(function(){
        formPass.dialog('open');
        oldPass.focus();
    });
    formPass.dialog({
        title:'密码修改',
        width:450,
        height:250,
        closed:true,
        //href:editPass,
        method:'post',
        onClose:function(){
            //清空表单数据
            formPass.form('reset');
        },
        buttons:[
            {
                id:'formConfirm',
                iconCls:'icon-ok',
                text:'确定',
                handler:function(){
                    if (formPass.form('validate')){
                        formPass.form('submit',{
                            url:changePass,
                            success:function(data){
                                if (data == 1){
                                    formPass.form('reset');
                                    formPass.dialog('close');
                                    $.messager.show({
                                        title:'提示',
                                        msg:'密码修改成功'
                                    });
                                }else if (data == 2){
                                    $.messager.alert('输入错误！', '原始密码输入错误！', 'warning', function () {
                                        oldPass.select();
                                    });
                                }else if (data == 3){
                                    $.messager.alert('输入错误！', '新密码和旧密码一样！', 'warning', function () {
                                        oldPass.select();
                                    });
                                }else if (data == 4){
                                    $.messager.alert('输入错误！', '未知错误！', 'warning', function () {
                                        oldPass.select();
                                    });
                                }
                            }
                        });
                    }else if (!oldPass.validatebox('isValid')){
                        oldPass.focus();
                    }else if (!newPass.validatebox('isValid')){
                        newPass.focus();
                    }else if (!repeatPass.validatebox('isValid')){
                        repeatPass.focus();
                    }
                }
            },
            {
                iconCls:'icon-undo',
                text:'重置',
                handler:function(){
                    formPass.form('reset');
                }
            }
        ]
    });

    oldPass.validatebox({
        required: true,
        validType: 'length[6,20]',
        missingMessage:'请输入旧密码',
        invalidMessage:'密码在6-20位'
    });

    newPass.validatebox({
        required: true,
        validType: 'length[6,20]',
        missingMessage:'请输入新密码',
        invalidMessage:'密码在6-20位'
    });

    repeatPass.validatebox({
        required: true,
        validType: 'equals["#newPass"]'
    });

/*    //验证原始密码是否输入正确
    oldPass.blur(function(){
        var oldpassval = oldPass.val();
        if (oldpassval){
            $.ajax({
                url:getPass,
                type:'post',
                data:{
                    oldPass:oldPass.val()
                },
                success:function(data){
                    if(data != 1){
                        $.messager.alert('输入错误！', '原始密码输入错误！', 'warning', function () {
                            oldPass.select();
                        });
                    }
                }
            });
        }
    });*/

    //按确定键后修改密码
    repeatPass.keydown(function(event){
        if(event.which == 13){
            $('#formConfirm').click();
        }
    });

    //检查密码和确认密码是否相同
    $.extend($.fn.validatebox.defaults.rules, {
        equals: {
            validator: function(value,param){
                return value == $(param[0]).val();
            },
            message: '两次输入密码不一致'
        }
    });
    //修改密码end



    $('#photo').hover(
        function(){
            $('.alterPhoto').show();
        },
        function(){
            $('.alterPhoto').hide();
        }
    );

    //修改头像
    $('.alterPhoto a').click(function(){
        window.open('/index.php/Home/Public/photo','','width=890,height=610');
    });

});



