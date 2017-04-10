<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户登录态表
 */
class LoginStatus extends Model {
    //连接配置名
    protected $connection = 'user_db'; #在/config/database.php文件中connections配置的key
    //配置主键,默认主键是id
    protected $primaryKey = "iAutoID";

    //表名
    protected $table = 't_user_login_status';

    public $timestamps = false; //关闭自动填充的2个字段 updated_at 和 created_at
    /**
    CREATE TABLE `t_user_login_status` (
    `iAutoID` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `iUserID` int(10) unsigned NOT NULL COMMENT '用户ID',
    `sToken` char(40) NOT NULL DEFAULT '' COMMENT '登录token',
    `iType` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '登录类型。1=移动端，2=web端,3=hft APP,4=H5登录,5=aaz APP登录,6=aaz H5登录,7=aaz web登录,8=移动营销平台APP',
    `iStatus` int(11) unsigned NOT NULL COMMENT '是否有效。1=有效，0=无效',
    `iCreateTime` int(10) unsigned NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`iAutoID`),
    KEY `iUserID` (`iUserID`),
    KEY `sToken` (`sToken`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 COMMENT='用户登录状态表';
     */

}
