# 用途

会有伤心的人需要用到的，是自我感动的小丑行为

# 用法

编辑 `love.php` 文件

修改顶部几行为自己的信息

```
$qq = "";// ta的QQ号
$sendMail = "";// 发件QQ邮箱
$sendMailPwd = "";// 邮箱密码；QQ邮箱用的是授权码
$sendName = "";// 发件人名，随便写，反正是发给自己
```

然后自己随便找个方法定时调用这个 php 文件就行了，比如用 crontab

```
crontab -e
```

每个整点调用一次

```
0 * * * * php love.php >> love.log
```