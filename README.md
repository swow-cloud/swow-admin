# swow-admin

**基于hyperf-swow开发的后台管理系统.**

# 生成ssl证书

```shell
 php bin/hyperf.php mkcert:command -d 127.0.0.1 -c ./ssl/localhost.pem -k ./ssl/localhost-key.pem

```

# Swow-RedLock

**通过阅读文章redis分布式锁进阶篇实现的基于swow实现的RedLock**

[redis 分布式锁进阶篇](https://mp.weixin.qq.com/s/3zuATaua6avMuGPjYEDUdQ)

# Swow-RedisLock

**基于swow实现的简单redis分布式锁**

# Casbin

# 使用box打包为二进制注意事项

1. 开启ssl证书的时候需要配置绝对路径不能通过`BASE_PATH`或者其他常量配置

2. Phar打包后是个包,不是源代码目录的形式，需要注意日志，或者其他文件写入的权限

# Github
[![GitHub Streak](https://streak-stats.demolab.com?user=AuroraYolo&theme=transparent)](https://git.io/streak-stats)
