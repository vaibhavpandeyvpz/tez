# vaibhavpandeyvpz/tez
Simple, framework-agnostic, http router implementation compatible with PHP 5.3+.

[![Build Status](https://img.shields.io/travis/vaibhavpandeyvpz/tez/master.svg?style=flat-square)](https://travis-ci.org/vaibhavpandeyvpz/tez)

Install
-------
```bash
composer require vaibhavpandeyvpz/tez
```

Usage
-----
Create files named ```.htaccess``` & ```index.php``` in your server's root directory. Put below contents in your ```.htaccess``` file:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L,QSA]
```

Then you can define your routes & dispatching mechanism in ```index.php``` similarly as shown below:

```php
<?php

// TODO
```

License
------
See [LICENSE.md](https://github.com/vaibhavpandeyvpz/tez/blob/master/LICENSE.md) file.
