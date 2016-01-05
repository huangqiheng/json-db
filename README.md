json-db
=======

提供轻量级的json结构的数据服务，但是字段能随意增减，每个数据单独存储成一个文件，可以由web直接访问。
在当前移动应用时代，服务器结构越来越扁平，业务逻辑越趋简单，而且要易于集群和缓存友好，易于灾备恢复。

TODO
=======
	. 周期自动备份
	. 管理端的导出和导入
	. 对key等特殊字段的加密。

nginx 配置
======
```
	server {
		listen 80;
		server_name db.youname.com;
		root /srv/http/json-db;
		index	index.html index.htm index.php;

		location ~ (\.htaccess|\.inc|\.tpl|\.sql|\.db)$ {
			deny all;
		}

		location / {
			if ($uri ~ ^/(admin|service)/) {
				break;
			}

			if ($uri ~ /databases/.+\.json$) {
				rewrite ^(/databases)/(.*)$ $1/$http_host/$2 break;
				break;
			}

			if ($uri = /) {
				rewrite ^(.*)$ /index.php last;
				break;
			}

			if ($uri ~ /databases/) {
				break;
			}

			rewrite ^/(.*)$ /databases/$http_host/__wwwroot__/$1 break;
		}

		location ~ \.php$ {
			set $is_public 1;
			if ($uri ~ ^/(admin|service)/) {
				set $is_public 0;
			}

                        if ($uri ~ ^/port.php) {
                                set $is_public 0;
                        }

			if ($is_public = 1) {
				rewrite ^/(.*)$ /databases/$http_host/__wwwroot__/$1 break;
			}
			include fastcgi_params;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			fastcgi_pass unix:/var/run/php5-fpm.sock;
		}
	}
```

配置目录权限
======
```
cd /srv/http/json-db
chown www-data:www-data ./databases -R
chown www-data:www-data ./cache -R
chown www-data:www-data ./admin/uploads -R
chown www-data:www-data ./service/queue/cache -R
```

配置定时器
======
这样就会每分钟调用本脚本一次，is_cron_calling函数是用来检查来自系统cron的调用
```
vim /etc/crontab，添加：
*  *    * * *	www-data /usr/bin/php -q /srv/http/json-db/admin/crontab.php > /dev/null 2>&2
```

如果是目录模式，需要ftp同步到网站
======
```
#!/bin/bash
HOST='112.124.123.89'
USER='xxxx'
PASS='xxxx'
TARGETFOLDER='/jdb'
SOURCEFOLDER=$PWD

echo $(date)

lftp -e "
open $HOST
user $USER $PASS
lcd $SOURCEFOLDER
mirror  --reverse \
	--delete \
	--exclude-glob *.sh \
	--exclude-glob .* \
	--exclude-glob debug.* \
	--exclude-glob .git/ \
	--exclude-glob databases/ \
	--exclude-glob cache/ \
	--exclude-glob admin/uploads/ \
	--verbose $SOURCEFOLDER $TARGETFOLDER
bye
"

echo $(date)
```
