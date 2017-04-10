
rpm包制作：
https://github.com/jordansissel/fpm

安装fpm依赖库： yum install ruby-devel gcc make rpm-build
安装fpm工具： gem install --no-ri --no-rdoc fpm
查看版本： fpm --version

制作命令：
    fpm -t 目标类型，即想要制作为什么包 -s 源类型 --prefix 设置包安装位置 -n 生成的包名 -v 生成的包版本  要打包的代码目录
    fpm -t rpm -s dir --prefix /usr/local/xxx -n cjs-php-flakeid -v 0.1.0  /tmp/source/xxx
    打包后的包名就是： cjs-php-flakeid-0.1.0-1.el6.x86_64.rpm
    查看包信息： rpm -qpi cjs-php-flakeid-0.1.0-1.el6.x86_64.rpm


