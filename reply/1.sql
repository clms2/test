CREATE TABLE `qanum` (
  `day` char(8) NOT NULL COMMENT '日期:1104',
  `num` int(6) unsigned NOT NULL DEFAULT '1' COMMENT '问答数量(提问数+回答数)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='问答数记录表'


CREATE TABLE `question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT 'uid=0是后台添加的提问',
  `uname` varchar(55) NOT NULL DEFAULT '' COMMENT '账号密码登陆 手机号为空',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号登陆 账号为空',
  `tit` varchar(100) NOT NULL,
  `cont` varchar(255) NOT NULL DEFAULT '',
  `tag` varchar(255) NOT NULL DEFAULT '' COMMENT '问题所属的类别 多个',
  `recreply` int(10) NOT NULL DEFAULT '0' COMMENT '预留字段,推荐回复,对应reply表id,显示在/ask/页面',
  `view` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `solved` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否已解决',
  `replynum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题回复数',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1:显示,0:待审核,-1:不显示',
  `alias` varchar(200) NOT NULL DEFAULT '' COMMENT 'url alias',
  `keywords` varchar(200) NOT NULL DEFAULT '' COMMENT 'seo keywords',
  `description` varchar(400) NOT NULL DEFAULT '' COMMENT 'seo description',
  `addtime` int(10) NOT NULL,
  `uptime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `solved` (`solved`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 COMMENT='问题表'

CREATE TABLE `question_tag` (
  `qid` int(10) unsigned NOT NULL COMMENT '对应问题表id',
  `typeid` int(6) unsigned NOT NULL COMMENT '对应product的小类stype',
  KEY `qid` (`qid`),
  KEY `typeid` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='问题与类别对应表多对多'


CREATE TABLE `reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qid` int(10) NOT NULL COMMENT '对应question表id',
  `uname` varchar(100) NOT NULL,
  `cont` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `uptime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 CHECKSUM=1 COMMENT='回复表'


