json-db
=======

提供轻量级的json结构的数据服务，但是字段能随意增减。这令mysql这种固定字段的数据结构难以适应。于是就就自己做个吧，json结构存在文件系统中，由nginx直接发送，这该够轻量级了吧。

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
