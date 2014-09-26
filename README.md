Важно: по всем изменениям планирую позже отписаться подробнее

---

mediabox-php
============
## Demo Site:
http://mediabox.8x86.ru/

Login/password: test/test

## Install:

1) git clone https://github.com/Zazza/mediabox-php-yii.git

2) cd mediabox-php-yii

3) curl -s https://getcomposer.org/installer | php

4) php composer.phar install

5) chown -R www-data:www-data *

6) chmod -R 770 app/runtime/ web/assets/

7) Install DB
```
cd sql/dump/
mongorestore mediabox/user.bson
```
8) Edit: web/config/storage.js

9) /etc/init.d/apache2 restart or /etc/init.d/nginx restart

10) Default login/password: admin:admin

