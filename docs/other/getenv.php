<?php
//fastcgi_param  jtestabc 123abc_appname;   这个nginx中配置的则可以通过getenv()方法获取和$_SERVER['jtestabc']获取

//echo getenv('REMOTE_ADDR');
//echo getenv('SCRIPT_FILENAME');
echo getenv('jtestabc'); #123abc_appname
echo "<br>";
echo $_SERVER['jtestabc'];#123abc_appname
