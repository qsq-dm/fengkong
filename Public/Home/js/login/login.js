var login_url = "/Home/Login/check_login";
var home_url = "/Home/Index/index";

var login = $('#login');
var username = $('#username');
var password = $('#password');
var verify = $('#verify');
//验证码图片
var picture = $('#picture');

//登录界面对话框
login.dialog({
   width:400,
   height:300,
   title:'华育国际',
   iconCls:'icon-lock',
   buttons:[
      {
         text:'登录',
         id:'loginBtn',
         iconCls:'icon-system',
         handler:function(){
            if(username.textbox('getValue') == ""){
               username.next('span').find('input').focus();
            }else if (password.textbox('getValue') == ""){
               password.next('span').find('input').focus();
            }else if(verify.textbox('getValue') == ""){
               verify.next('span').find('input').focus();
            }else {
               $.ajax({
                  url:login_url,
                  method:'POST',
                  data:{
                     username:username.val(),
                     password:password.val(),
                     verify:verify.val()
                  },
                  beforeSend:function(){
                     $.messager.progress({
                        text:'正在登录中...'
                     });
                  },
                  success:function(data){
                     $.messager.progress('close');
                     if (data == 0){
                        $.messager.alert('登录失败','验证码错误！','warning',function(){
                           verify.next('span').find('input').select();
                           picture.attr('src',picture.attr('src')+'?'+new Date().getTime());
                        });
                     }else if (data == 1){
                        location.href = home_url;
                     }else if (data == 2){
                        $.messager.alert('登录失败','用户已禁用！','warning',function(){
                           username.next('span').find('input').select();
                        });
                     }else{
                        $.messager.alert('登录失败','用户名或密码错误！','warning',function(){
                           username.next('span').find('input').select();
                           picture.attr('src',picture.attr('src')+'?'+new Date().getTime());
                           password.textbox('clear');
                           verify.textbox('clear');
                        });
                     }
                  }
               });
            }
         }
      }
   ],
   onClose:function(){
      username.textbox('clear');
      password.textbox('clear');
      verify.textbox('clear');
   }
});

//用户名文本框
username.textbox({
   width:200,
   height:38,
   prompt:'请输入用户名',
   iconCls:'icon-man',
   iconWidth:'38',
   type:'text'
});

//密码文本框
password.textbox({
   width:200,
   height:38,
   prompt:'请输入密码',
   iconCls:'icon-lock',
   iconWidth:'38',
   type:'password'
});

//验证码文本框
verify.textbox({
   width:80,
   height:38,
   prompt:'验证码',
   iconWidth:'38'
});

//验证码点击重置
picture.click(function(){
   //this.src = this.src+'?'+Math.random(); 两种方法
   this.src = this.src+'?'+new Date().getTime();
});

//加载时判断验证
if(username.textbox('getValue') == ""){
   username.next('span').find('input').focus();
}else if (password.textbox('getValue') == ""){
   password.next('span').find('input').focus();
}else if(verify.textbox('getValue') == ""){
   verify.next('span').find('input').focus();
}

//按回车键后登陆
verify.next('span').find('input').keydown(function(event){
   if(event.which == 13){
      $('#loginBtn').click();
   }
});
