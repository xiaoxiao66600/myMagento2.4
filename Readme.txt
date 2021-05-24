安装步骤：
mysql8.x  apache2.4.x  php7.4.x  elasticsearch7.x  composer2.x
Enable php Extesion : intl , soap ,socket ,xls ,sodium

1. Download Install phpstudy and Composer

2. Install Elasticsearch & run and test
elasticsearch安装目录\bin\elasticsearch.bat
http://localhost:9200/​
do not turn off elasticsearch

3.Download Magento 2.4 from magento official site

note on windows: 
Find validateURLScheme function in vendor\magento\framework\Image\Adapter\Gd2.php file. at line 86. Replace function with this:
private function validateURLScheme(string $filename) : bool
  {
      $allowed_schemes = ['ftp', 'ftps', 'http', 'https'];
      $url = parse_url($filename);
      if ($url && isset($url['scheme']) && !in_array($url['scheme'], $allowed_schemes) && !file_exists($filename)) {
          return false;
      }
      return true;
  }
 
---------------------------------
Now, Install magento v2.4.1
Command line:
ps:需要更改base-url为本地magengto域名，db-name为数据库名，db-user ,db-password  admin相关信息自定义用于初始化登录后台用户。backend-frontname为后台登陆参数
php bin/magento setup:install --base-url="http://magento241.cn/" --db-host="localhost" --db-name="magento2.4" --db-user="root" --db-password="root" --admin-firstname="admin" --admin-lastname="admin" --admin-email="admin@admin.com" --admin-user="admin" --admin-password="admin123" --use-rewrites="1" --backend-frontname="admin"

do not turn off elasticsearch
please waiting install........., ok

4. Test and see the result

Change Symlink 
Find file Validator.php in ...vendor\Magento\Framework\View\Element\Template\File
At around line 138 replace code with this one

 $realPath = str_replace('\\', '/',$this->fileDriver->getRealPath($path));

php bin/magento indexer:reindex
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f


设置成开发者模式：php bin/magento deploy:mode:set developer
php bin/magento module:disable Magento_TwoFactorAuth
php bin/magento cache:clean
php bin/magento cache:flush

浏览器打开，http://magento241.cn/ 查看前台成功与否

配置路由重定向，在pub/.htaccess中添加：
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]

</IfModule>
打开后台， http://magento241.cn/admin (admin为安装时backend-frontname对应参数 可在app/etc/env.php中查看bakend['frontname']值，登录账号密码为安装时对应admin账号密码)