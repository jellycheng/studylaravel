<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
	//连接配置名
	//protected $connection = 'mysql'; #在/config/database.php文件中connections配置的key
	//默认主键是id
	//配置主键
	protected $primaryKey = "userid";

	//表名
	protected $table = 't_user_reg';

	public $timestamps = false; //关闭自动填充的2个字段 updated_at 和 created_at


}
