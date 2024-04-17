# VNBB 1.0

### Usertable ###
DROP TABLE IF EXISTS `bbs_user`;
CREATE TABLE `bbs_user` (
  uid int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Tên người dùng',
  gid smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Số nhóm người dùng',
  email char(40) NOT NULL DEFAULT '' COMMENT 'Thư',
  username char(32) NOT NULL DEFAULT '' COMMENT 'Tên người dùng',
  realname char(16) NOT NULL DEFAULT '' COMMENT 'Tên người dùng',
  `password` char(32) NOT NULL DEFAULT '' COMMENT 'Mật khẩu',
  `password_sms` char(16) NOT NULL DEFAULT '' COMMENT 'Mật khẩu',
  salt char(16) NOT NULL DEFAULT '' COMMENT 'Mật khẩu hỗn hợp',
  mobile char(11) NOT NULL DEFAULT '' COMMENT 'Số điện thoại',
  qq char(15) NOT NULL DEFAULT '' COMMENT 'QQ',
  threads int(11) NOT NULL DEFAULT '0' COMMENT 'Số bài viết',
  posts int(11) NOT NULL DEFAULT '0' COMMENT 'Số câu trả lời',
  credits int(11) NOT NULL DEFAULT '0' COMMENT 'Điểm',
  golds int(11) NOT NULL DEFAULT '0' COMMENT 'Vàng',
  rmbs int(11) NOT NULL DEFAULT '0' COMMENT 'VNĐ',
  create_ip int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IP được tạo',
  create_date int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian tạo',
  login_ip int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IP đăng nhập',
  login_date int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian đăng nhập',
  logins int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Số lần đăng nhập',
  avatar int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Lần cuối cùng người dùng cập nhật hình ảnh',
  PRIMARY KEY (uid),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email),
  KEY gid (gid)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
INSERT INTO `bbs_user` SET uid=1, gid=1, email='admin@admin.com', username='admin',`password`='d98bb50e808918dd45a8d92feafc4fa3',salt='123456';

# Usergroup
DROP TABLE IF EXISTS `bbs_group`;
CREATE TABLE `bbs_group` (
  gid smallint(6) unsigned NOT NULL,
  name char(20) NOT NULL default '',
  creditsfrom int(11) NOT NULL default '0',
  creditsto int(11) NOT NULL default '0',
  allowread int(11) NOT NULL default '0',
  allowthread int(11) NOT NULL default '0',
  allowpost int(11) NOT NULL default '0',
  allowattach int(11) NOT NULL default '0',
  allowdown int(11) NOT NULL default '0',
  allowtop int(11) NOT NULL default '0',
  allowupdate int(11) NOT NULL default '0',
  allowdelete int(11) NOT NULL default '0',
  allowmove int(11) NOT NULL default '0',
  allowbanuser int(11) NOT NULL default '0',
  allowdeleteuser int(11) NOT NULL default '0',
  allowviewip int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (gid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `bbs_group` SET gid='0', name="Nhóm khách", creditsfrom='0', creditsto='0', allowread='1', allowthread='0', allowpost='1', allowattach='0', allowdown='1', allowtop='0', allowupdate='0', allowdelete='0', allowmove='0', allowbanuser='0', allowdeleteuser='0', allowviewip='0';

INSERT INTO `bbs_group` SET gid='1', name="Nhóm admin", creditsfrom='0', creditsto='0', allowread='1', allowthread='1', allowpost='1', allowattach='1', allowdown='1', allowtop='1', allowupdate='1', allowdelete='1', allowmove='1', allowbanuser='1', allowdeleteuser='1', allowviewip='1';
INSERT INTO `bbs_group` SET gid='2', name="Nhóm Super Moderator", creditsfrom='0', creditsto='0', allowread='1', allowthread='1', allowpost='1', allowattach='1', allowdown='1', allowtop='1', allowupdate='1', allowdelete='1', allowmove='1', allowbanuser='1', allowdeleteuser='1', allowviewip='1';
INSERT INTO `bbs_group` SET gid='4', name="Nhóm Moderator", creditsfrom='0', creditsto='0', allowread='1', allowthread='1', allowpost='1', allowattach='1', allowdown='1', allowtop='1', allowupdate='1', allowdelete='1', allowmove='1', allowbanuser='1', allowdeleteuser='0', allowviewip='1';
INSERT INTO `bbs_group` SET gid='5', name="Nhóm Internship Moderator", creditsfrom='0', creditsto='0', allowread='1', allowthread='1', allowpost='1', allowattach='1', allowdown='1', allowtop='1', allowupdate='1', allowdelete='0', allowmove='1', allowbanuser='0', allowdeleteuser='0', allowviewip='0';

INSERT INTO `bbs_group` SET gid='6', name="Nhóm UnVerified", creditsfrom='0', creditsto='0', allowread='1', allowthread='0', allowpost='1', allowattach='0', allowdown='1', allowtop='0', allowupdate='0', allowdelete='0', allowmove='0', allowbanuser='0', allowdeleteuser='0', allowviewip='0';
INSERT INTO `bbs_group` SET gid='7', name="Nhóm Forbidden", creditsfrom='0', creditsto='0', allowread='0', allowthread='0', allowpost='0', allowattach='0', allowdown='0', allowtop='0', allowupdate='0', allowdelete='0', allowmove='0', allowbanuser='0', allowdeleteuser='0', allowviewip='0';

INSERT INTO `bbs_group` SET gid='101', name="Thành viên", creditsfrom='0', creditsto='10000000', allowread='1', allowthread='1', allowpost='1', allowattach='1', allowdown='1', allowtop='0', allowupdate='0', allowdelete='0', allowmove='0', allowbanuser='0', allowdeleteuser='0', allowviewip='0';

# Sectiontable
DROP TABLE IF EXISTS bbs_forum;
CREATE TABLE bbs_forum (
  fid int(11) unsigned NOT NULL auto_increment,
 # fup int(11) unsigned NOT NULL auto_increment,
  name char(20) NOT NULL default '',
  `rank` tinyint(3) unsigned NOT NULL default '0',
  threads mediumint(8) unsigned NOT NULL default '0',
  todayposts mediumint(8) unsigned NOT NULL default '0',
  todaythreads mediumint(8) unsigned NOT NULL default '0',
  brief text NOT NULL,
  announcement text NOT NULL,
  accesson int(11) unsigned NOT NULL default '0',
  orderby tinyint(11) NOT NULL default '0',
  create_date int(11) unsigned NOT NULL default '0',
  icon int(11) unsigned NOT NULL default '0',
  moduids char(120) NOT NULL default '',
  seo_title char(64) NOT NULL default '',
  seo_keywords char(64) NOT NULL default '',
  PRIMARY KEY (fid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO bbs_forum SET fid='1', name='Mặc định', brief='Default Brief';
#  cache_date int(11) NOT NULL default '0',
  
# Section_access_rules
DROP TABLE IF EXISTS bbs_forum_access;
CREATE TABLE bbs_forum_access (
  fid int(11) unsigned NOT NULL default '0',
  gid int(11) unsigned NOT NULL default '0',
  allowread tinyint(1) unsigned NOT NULL default '0',
  allowthread tinyint(1) unsigned NOT NULL default '0',
  allowpost tinyint(1) unsigned NOT NULL default '0',
  allowattach tinyint(1) unsigned NOT NULL default '0',
  allowdown tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (fid, gid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Forumtopic
DROP TABLE IF EXISTS bbs_thread;
CREATE TABLE bbs_thread (
  fid smallint(6) NOT NULL default '0',
  tid int(11) unsigned NOT NULL auto_increment,
  top tinyint(1) NOT NULL default '0',
  uid int(11) unsigned NOT NULL default '0',
  userip int(11) unsigned NOT NULL default '0',
  subject char(128) NOT NULL default '',
  create_date int(11) unsigned NOT NULL default '0',
  last_date int(11) unsigned NOT NULL default '0',

  views int(11) unsigned NOT NULL default '0',
  posts int(11) unsigned NOT NULL default '0',
  images tinyint(6) NOT NULL default '0',
  files tinyint(6) NOT NULL default '0',
  mods tinyint(6) NOT NULL default '0',
  closed tinyint(1) unsigned NOT NULL default '0',
  firstpid int(11) unsigned NOT NULL default '0',
  lastuid int(11) unsigned NOT NULL default '0',
  lastpid int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (tid),
  KEY (lastpid),
  KEY (fid, tid),
  KEY (fid, lastpid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Toptopic
DROP TABLE IF EXISTS bbs_thread_top;
CREATE TABLE bbs_thread_top (
  fid smallint(6) NOT NULL default '0',
  tid int(11) unsigned NOT NULL default '0',
  top int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (tid),
  KEY (top, tid),
  KEY (fid, top)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Forum_post_data
DROP TABLE IF EXISTS bbs_post;
CREATE TABLE bbs_post (
  tid int(11) unsigned NOT NULL default '0',
  pid int(11) unsigned NOT NULL auto_increment,
  uid int(11) unsigned NOT NULL default '0',
  isfirst int(11) unsigned NOT NULL default '0',
  create_date int(11) unsigned NOT NULL default '0',
  userip int(11) unsigned NOT NULL default '0',
  images smallint(6) NOT NULL default '0',
  files smallint(6) NOT NULL default '0',
  doctype tinyint(3) NOT NULL default '0',
  quotepid int(11) NOT NULL default '0',

  message longtext NOT NULL,
  message_fmt longtext NOT NULL,
  PRIMARY KEY (pid),
  KEY (tid, pid),
  KEY (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
# Edit_history

#Forum_attachment_table
DROP TABLE IF EXISTS bbs_attach;
CREATE TABLE bbs_attach (
  aid int(11) unsigned NOT NULL auto_increment ,
  tid int(11) NOT NULL default '0',
  pid int(11) NOT NULL default '0',
  uid int(11) NOT NULL default '0',
  filesize int(8) unsigned NOT NULL default '0',
  width mediumint(8) unsigned NOT NULL default '0',
  height mediumint(8) unsigned NOT NULL default '0',
  filename char(120) NOT NULL default '',
  orgfilename char(120) NOT NULL default '',
  filetype char(7) NOT NULL default '',
  create_date int(11) unsigned NOT NULL default '0',
  comment char(100) NOT NULL default '',
  downloads int(11) NOT NULL default '0',
  credits int(11) NOT NULL default '0',
  golds int(11) NOT NULL default '0',
  rmbs int(11) NOT NULL default '0',
  isimage tinyint(1) NOT NULL default '0',
  PRIMARY KEY (aid),
  KEY pid (pid),
  KEY uid (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# my_main_topic
DROP TABLE IF EXISTS bbs_mythread;
CREATE TABLE bbs_mythread (
  uid int(11) unsigned NOT NULL default '0',
  tid int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (uid, tid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Myreply
DROP TABLE IF EXISTS bbs_mypost;
CREATE TABLE bbs_mypost (
  uid int(11) unsigned NOT NULL default '0',
  tid int(11) unsigned NOT NULL default '0',
  pid int(11) unsigned NOT NULL default '0',
  KEY (tid),
  PRIMARY KEY (uid, pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# session
DROP TABLE IF EXISTS bbs_session;
CREATE TABLE bbs_session (
  sid char(32) NOT NULL default '0',
  uid int(11) unsigned NOT NULL default '0',
  fid tinyint(3) unsigned NOT NULL default '0',
  url char(32) NOT NULL default '',
  ip int(11) unsigned NOT NULL default '0',
  useragent char(128) NOT NULL default '',
  data char(255) NOT NULL default '',
  bigdata tinyint(1) NOT NULL default '0',
  last_date int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (sid),
  KEY ip (ip),
  KEY fid (fid),
  KEY uid_last_date (uid, last_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS bbs_session_data;
CREATE TABLE bbs_session_data (
  sid char(32) NOT NULL default '0',
  last_date int(11) unsigned NOT NULL default '0',
  data text NOT NULL,
  PRIMARY KEY (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Moderator_operation_log
DROP TABLE IF EXISTS bbs_modlog;
CREATE TABLE bbs_modlog (
  logid int(11) unsigned NOT NULL auto_increment,
  uid int(11) unsigned NOT NULL default '0',
  tid int(11) unsigned NOT NULL default '0',
  pid int(11) unsigned NOT NULL default '0',
  subject char(32) NOT NULL default '',
  comment char(64) NOT NULL default '',
  rmbs int(11) NOT NULL default '0',
  create_date int(11) unsigned NOT NULL default '0',
  action char(16) NOT NULL default '',
  PRIMARY KEY (logid),
  KEY (uid, logid),
  KEY (tid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# ttserver, mysql
DROP TABLE IF EXISTS bbs_kv;
CREATE TABLE bbs_kv (
  k char(32) NOT NULL default '',
  v mediumtext NOT NULL,
  expiry int(11) unsigned NOT NULL default '0',
  PRIMARY KEY(k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Cache_table
DROP TABLE IF EXISTS bbs_cache;
CREATE TABLE bbs_cache (
  k char(32) NOT NULL default '',
  v mediumtext NOT NULL,
  expiry int(11) unsigned NOT NULL default '0',
  PRIMARY KEY(k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Temporary_queue
DROP TABLE IF EXISTS bbs_queue;
CREATE TABLE bbs_queue (
  queueid int(11) unsigned NOT NULL default '0',
  v int(11) NOT NULL default '0',
  expiry int(11) unsigned NOT NULL default '0',
  UNIQUE KEY(queueid, v),
  KEY(expiry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `bbs_table_day`;
CREATE TABLE `bbs_table_day` (
  `year` smallint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Năm',
  `month` tinyint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Tháng',
  `day` tinyint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Ngày',
  `create_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Dấu thời gian',
  `table` char(16) NOT NULL default '' COMMENT 'Tên bảng',
  `maxid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Maximum ID',
  `count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Tổng cộng',
  PRIMARY KEY (`year`, `month`, `day`, `table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
