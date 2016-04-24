<?php

function code62($x) {  
    $show = '';  
    while($x > 0) {  
        $s = $x % 62;  
        if ($s > 35) {  
            $s = chr($s+61);  
        } elseif ($s > 9 && $s <=35) {  
            $s = chr($s + 55);  
        }  
        $show .= $s;  
        $x = floor($x/62);  
    }  
    return $show;  
}  
    
function shorturl($url) {  
    $url = crc32($url);  
    $result = sprintf("%u", $url);   
    return code62($result);  
}  
  
echo  shorturl("http://pai.game.weibo.com/love/") ."<br>";  
echo shorturl("http://www.oschina.net/code/snippet_878945_22499") ."<br>";

