## centos install php

#
> update yum
```.linux
yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm

yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm

yum install yum-utils

yum-config-manager --enable remi-php72   [Install PHP 7.2]

yum install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo
```
#