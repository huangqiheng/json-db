json-db
=======

提供轻量级的json结构的数据服务，但是字段能随意增减，每个数据单独存储成一个文件，可以由web直接访问。
在当前移动应用时代，服务器结构越来越扁平，业务逻辑越趋简单，而且要易于集群和缓存友好，易于灾备恢复。

TODO
=======
（1）生成id的互斥锁（get_random_id函数）
（2）管理端的导出和导入
（3）对key等特殊字段的加密。

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

			rewrite ^/(.*)$ /databases/$http_host/__wwwroot__/$1 break;
		}

		location ~ \.php$ {
			set $is_public 1;
			if ($uri ~ ^/(admin|service)/) {
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
