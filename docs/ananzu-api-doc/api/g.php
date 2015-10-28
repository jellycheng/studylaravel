<?php
$globalData = include '../common/config.php';

$globalData['header']=array(
					'nav'=>'通用响应策略',
					'title'=>'通用',
					'right'=>'<a href="">上一页</a> <a href="">下一页</a>',
					);

require '../common/header.php';
?>
<div class="title">通用响应策略 </div>

<p>
	响应json格式
</p>


<pre id="PHP" class="prettyprint">
1.所有返回数据都会在下面json结构的data数据里
正确返回：
{
    "code": 0,
    "msg":"ok"
    "data": {...}
}
 

异常返回示例：
{
    "code": 200001,
    "msg":"手机号无效"
    "data": {}
}
 
列表格式返回示例：
{           
    "code":0,      
    "msg":"ok",
    "data":{
        "page":1,//当前第几页
        "perpage":10,//每页大小
        "total":200,//总条数
        "list":[
                业务数据列表......
        ]
    }
}
2.为了便于处理，对于客户端API方面服务器返回字段，在为空的情况下统一返回空字符串“”，不返回null
</pre>


<div class="title">
  <h3>公共request header(每次rest请求头都要填写)：</h3>
</div>
<table>
  <tr>
    <th style="width:40%;"> 字段 </th>
    <th style="width:60%;"> 描述 </th>
  </tr>
  <tr>
    <td>app-source</td>
    <td>1.Android app 用A表示
2. Ios app 用I表示
3. 触屏H5，浏览器中访问，用H表示
4. 微信内访问 用W表示 </td>
  </tr>
  <tr>
    <td>app-v</td>
    <td>当前应用版本号 </td>
  </tr>
  <tr>
    <td>app-deviceid</td>
    <td>设备唯一标识 </td>
  </tr>
  <tr>
    <td>app-channel</td>
    <td>渠道 </td>
  </tr>
  <tr>
    <td>app-devicemodel</td>
    <td>设备型号，比如 [KONKA W850] </td>
  </tr>
  <tr>
    <td>app-osVersion</td>
    <td>系统版本号</td>
  </tr>
  <tr>
    <td>app-type</td>
    <td>来源app，xx买家app，yy商家app，zz配送app</td>
  </tr>
</table>



<?php
require '../common/footer.php';
?>