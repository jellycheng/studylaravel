


--
-- 表的结构 `t_system_info`
--

CREATE TABLE IF NOT EXISTS `t_system_info` (
  `iAutoID` int(11) NOT NULL AUTO_INCREMENT,
  `iUserID` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `sTitle` varchar(255) DEFAULT '' COMMENT '标题',
  `iDataOrigin` int(11) NOT NULL DEFAULT '0' COMMENT '信息对应的数据源id，如合同助手则是对应的合同id',
  `iType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '消息类型，1合同助手，2系统通知',
  `iTplCode` tinyint(4) NOT NULL DEFAULT '0' COMMENT '配合h5页面展现用的模板代号，因为消息不同展现的样式及格式不同',
  `iRead` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未读，1已读',
  `ext` text NOT NULL COMMENT '扩展信息，消息体，json字符串格式，配合业务扩展',
  `iStatus` tinyint(4) NOT NULL DEFAULT '1' COMMENT '数据状态(0:逻辑删除;1:正常)',
  `iCreateTime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `iUpdateTime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `iDeleteTime` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`iAutoID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='系统信息表_合同助手_系统通知' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `t_system_info`
--

INSERT INTO `t_system_info` (`iAutoID`, `iUserID`, `sTitle`, `iDataOrigin`, `iType`, `iTplCode`, `ext`, `iStatus`, `iCreateTime`, `iUpdateTime`, `iDeleteTime`) VALUES
(1, 1, '', 0, 2, 0, '', 1, 0, 0, 0);


