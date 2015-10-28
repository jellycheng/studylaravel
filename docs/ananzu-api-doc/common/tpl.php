<?php
$globalData = include 'config.php';

$globalData['header']=array(
					'nav'=>'主标题',
					'title'=>'章节名称',
					'right'=>'<a href="2_4.htm">上一页</a> <a href="3_2.htm">下一页</a>',
					);

require __DIR__ . '/header.php';
?>
<div class="title">标题样式 &lt;div class="title">标题样式 &lt;/div> </div>

<p>
	文章段落样式&lt;p>文章段落样式&lt;/p>
</p>


<ul rel="如果不加1234则加style='list-style:none;'样式即可">
  <li>1,2,3,4样式</li>
  <li>1,2,3,4样式 版本发布</li>
  <li><pre id="PHP" class="prettyprint">
		&lt;ul>
		  &lt;li>1,2,3,4样式&lt;/li>
		  &lt;li>1,2,3,4样式 版本发布&lt;/li>
		  &lt;li>1,2,3,4样式 版本发布&lt;/li>
		  
		&lt;/ul>	
		</pre>
  </li>
  
</ul>


<pre id="PHP" class="prettyprint">
	这里面方式代码，json格式<br>
	&lt;pre id="PHP" class="prettyprint">
		这里面方式代码，json格式
	&lt;/pre>
	
	{"jsonrpc":"2.0","error":{"code":-32600,"message":null}}
</pre>


<div class="title">
  <h3>表格样式</h3>
</div>
<table>
  <tr>
    <th style="width:20%;"> 参数 </th>
    <th style="width:20%;"> 类型 </th>
    <th style="width:10%;"> 是否必须 </th>
    <th style="width:50%;"> 描述 </th>
  </tr>
  <tr>
    <td>username</td>
    <td>String </td>
    <td>是</td>
    <td>KEY值-模板中的变量名称</td>
  </tr>
  <tr>
    <td>password</td>
    <td>String </td>
    <td>否</td>
    <td>模板变量值</td>
  </tr>
  <tr>
    <td>sex</td>
    <td>String </td>
    <td>否</td>
    <td>性别</td>
  </tr>
</table>



<?php
require __DIR__ . '/footer.php';
?>