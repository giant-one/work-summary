# ubuntu系统安装及开发环境配置

> 本文旨在记录在windows虚拟机中安装ubuntu系统和配置开发环境以及遇到的问题和解决方案。

### 一、虚拟机安装

### 二、安装ubuntu系统

### 三、 安装开发环境

#### PicGo + gitee 搭建个人图床

##### 安装picgo

1.以ubuntu系统为例，在[官网](https://github.com/Molunerfinn/PicGo/releases)下载AppImage文件，然后给与可执行权限

![](https://gitee.com/wlxc0912/images/raw/master/pic/2021-03-14%2019-40-29%20%E7%9A%84%E5%B1%8F%E5%B9%95%E6%88%AA%E5%9B%BE.png)

2.双击就可以打开了，此时还可以添加到快速启动栏

##### 配置gitee

创建一个gitee仓库，获取token

个人设置->私人令牌桶

![](https://gitee.com/wlxc0912/images/raw/master/pic/20210314201921.png)

配置picgo

![](https://gitee.com/wlxc0912/images/raw/master/pic/20210314201233.png)



### 四、遇到问题及解决方案

#### 如何将安装软件放到快速启动栏 

在/usr/share/applications下创建对应的配置文件即可,以picgo为例
```shell
vim picgo.desktop

[Desktop Entry]
Type=Application
Name=PicGo  #桌面快捷方式名称
GenericName=PicGo 
Comment=PicGo: a tool for quickly uploading pictures and getting pictures URL links #软件描述
Exec=/usr/local/picgo/picgo #可执行文件完整路径，用户主目录不可使用~代替
Icon=/usr/local/picgo/picgo.png #图标文件完整路径
Terminal=false #是否使用终端
Categories=Development;  #软件分类              

```

