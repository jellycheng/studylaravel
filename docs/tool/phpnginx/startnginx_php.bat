

echo Starting PHP FastCGI...  
RunHiddenConsole  D:/php-5.4.43/php-cgi.exe -b 127.0.0.1:9000 -c D:/php-5.4.43/php.ini  

cd /d D:\nginx-1.8.0
nginx.exe
