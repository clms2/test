CREATE TABLE `tb_treasure_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL COMMENT '对应user表id',
  `uname` varchar(100) NOT NULL,
  `follow_id` int(10) NOT NULL DEFAULT '0',
  `hash` char(13) NOT NULL DEFAULT '' COMMENT '对应user_set的hash',
  `total` int(10) NOT NULL DEFAULT '0' COMMENT '总保额',
  `baoe` int(6) NOT NULL DEFAULT '0' COMMENT '绑定之后领取的保额信息',
  `searchnum` int(7) NOT NULL DEFAULT '0' COMMENT '寻宝次数',
  `foundnum` int(7) NOT NULL DEFAULT '0' COMMENT '找到藏宝点数',
  `moqi` tinyint(3) NOT NULL DEFAULT '0' COMMENT '默契度',
  `addtime` int(10) unsigned NOT NULL,
  `gettime` int(10) NOT NULL DEFAULT '0' COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `follow_id` (`follow_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

CREATE TABLE `tb_treasure_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` char(28) NOT NULL DEFAULT '',
  `isbind` enum('0','1') NOT NULL DEFAULT '0',
  `uname` varchar(55) NOT NULL DEFAULT '' COMMENT '昵称',
  `startnum` int(6) NOT NULL DEFAULT '0' COMMENT '发起藏宝次数',
  `friendsnum` int(10) NOT NULL DEFAULT '0' COMMENT '好友数量',
  `loginip` varchar(15) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='用户信息表'

CREATE TABLE `tb_treasure_user_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `place` varchar(100) NOT NULL COMMENT '用户设置的藏宝点',
  `hash` char(13) NOT NULL COMMENT 'uniqid生成的唯一值',
  `setnumber` int(6) NOT NULL DEFAULT '1' COMMENT '该用户设置的第几个藏宝计划',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8

