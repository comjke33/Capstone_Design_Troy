#!/bin/bash
DATE=`date +%Y%m%d%H%M`
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk  '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk  '{print $3}'`
WWW=`grep www /etc/passwd|awk -F: '{print $1}'`

if [ `whoami` = "root" ];then
	cd /home/judge/
        chsh judge -s /sbin/nologin
        mkdir new
        cd new
	wget -O hustoj.tar.gz http://dl.hustoj.com/hustoj.tar.gz
	tar xzf hustoj.tar.gz
	mv src/* ./
	cd ..
 	chmod +x new/install/*.sh
	if test -e /home/judge/src/web/include/db_info.inc.php ;then
		    echo 'db_info.inc.php exists !';
		    new/install/merge.sh src/web/include/db_info.inc.php new/web/include/db_info.inc.php
		else
		   echo 'db_info.inc.php not found';
		   sed -i "s/DB_USER[[:space:]]*=[[:space:]]*\"root\"/DB_USER=\"$USER\"/g" new/web/include/db_info.inc.php
		   sed -i "s/DB_PASS[[:space:]]*=[[:space:]]*\"root\"/DB_PASS=\"$PASSWORD\"/g" new/web/include/db_info.inc.php
	fi
	cp -a src/web/upload/* new/web/upload/
	mv src "old.$DATE"
 	echo "Your old files are moved to old.$DATE , find them if you need ."
	mv new src
        chmod +x src/install/*.sh
	sed -i "s/OJ_INTERNAL_CLIENT=1/OJ_INTERNAL_CLIENT=0/g" /home/judge/etc/judge.conf
        pkill -9 judged
	cd src/core
        bash make.sh
	judged
        cd /home/judge
	#不要合并，必须重新进入，否则执行的update.sql是老版本，没有更新
	cd src/install
	bash mysql.sh  <  update.sql
        cd ../..
	chown $WWW:$WWW -R src
	chmod 710 -R /home/judge/data 
	chmod 700 /home/judge/etc/judge.conf
else
	echo "usage: sudo $0"
fi
