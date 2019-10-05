### 单点登录
    
    顾名思义就是当存在多个系统时，在某一个系统登陆后，访问任何系统都是登录态。本文
    将介绍一种通过js跨域设置cookie来实现单点登录的目的。京东目前就是使用的这种
    技术。下面先看一张图。
    
![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso.png)

##### 下面分析一下整个流程

- 在京东首页点击登录，跳转到京东的登录中心 https://passport.jd.com/new/login.aspx?ReturnUrl=http%3A%2F%2Fwww.jd.com%2F
- 输入用户名，密码验证通过后跳转到京东的首页。设置*.jd.com cookie
- 首页通过Jquery.getJSON()发起一个ajax请求，获取需要跨域设置cookie的域名列表。

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-01.png)

- js遍历这个列表对每条数据发起跨域jsonp请求。


- 每个请求都会有一个重定向的过程。

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-02.png)

- ajax再起请求重定向的url，这是设置cookie的关键。

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-03.png)

- 到这一步京东的单点登录就完成了。

##### 核心点

    跨域设置cookie,当访问A网站输入用户名密码，验证成功后，返回设置cookie，此时A网站
    就可以说是登录了，有了登录态，但是如何把cookie值设置到B网站呢，这里就用到了跨域
    设置cookie。
    
    下面演示一下
    我这里采用一台服务器，不同的端口来模拟跨域
    
    http://10.211.55.8:8801/index.html 代码如下
    
```html
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="jsonp">
    <meta name="description" content="jsonp">
    <title>jsonp</title>
</head>
<body>
<script type="text/javascript" src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
  $.ajax({
      url:"http://10.211.55.8:8802/index.php",
      type:"GET",
      dataType:"jsonp",
      crossDomain:true,
      success:function(data){
      	   console.log('成功');
      }
});
</script>
</body>
</html>
```

http://10.211.55.8:8801/index.php

```
<?php
header('Location:http://10.211.55.8:8802/index.php');
```
http://10.211.55.8:8802/index.php
```
<?php
setcookie('age','12');
$callback = $_GET['callback'];
echo $callback.'(8802)';
```
截图如下

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-04.png)

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-05.png)

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-06.png)

![流程图](https://github.com/yigebanchengxuyuan/work-summary/blob/master/images/sso-07.png)


可以看到在ajax响应中有set-cookie，这样就可以跨域实现设置cookie



