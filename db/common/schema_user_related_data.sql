create table student_evaluate(
  auto_id serial,
  uid varchar,
  type varchar(80),
  type_id int,
  epoch int,
  comment text,
  primary key(auto_id)
);
CREATE INDEX student_evaluate_typeid ON student_evaluate(type_id);
CREATE INDEX student_evaluate_type ON student_evaluate(type);
CREATE INDEX student_evaluate_typeid_type ON student_evaluate(type_id,type);


CREATE TABLE student_evaluate_data(
  auto_id serial,
  student_evaluate_id int,
  key varchar,
  val varchar,
  primary key(auto_id)
);
create index student_evaluate_data_id on student_evaluate_data(student_evaluate_id); 


CREATE TABLE evaluate_lecture (
  auto_id serial,
  ta_id int,
  domain_id int,
  student_id varchar(256),
  student_attendance int,
  student_attendance_comment text,
  lecture_delivery int,
  content_matched int,
  information_covered int,
  scientific_level int,
  overlap varchar(8),
  overlap_explanation text,
  overall_rating int,
  suggestions text,
  datetime timestamp,
  role varchar(255) DEFAULT 'student',
  primary key (auto_id)
);
CREATE INDEX evaluate_lecture_taid ON evaluate_lecture(ta_id);
CREATE INDEX evaluate_lecture_studentid ON evaluate_lecture(student_id);
CREATE INDEX evaluate_lecture_taid_studentid ON evaluate_lecture(ta_id, student_id);
CREATE INDEX evaluate_lecture_datetime ON evaluate_lecture(datetime);
CREATE INDEX evaluate_lecture_domaindid ON evaluate_lecture(domain_id);


CREATE TABLE search_configure (
  auto_id SERIAL,
  user_id VARCHAR(120),
  column_ids VARCHAR(255),
  search_type VARCHAR(80),
  PRIMARY KEY (auto_id)   
);


CREATE TABLE podcasturl (
  auto_id SERIAL,
  uid VARCHAR(256),
  epoch INT,
  url text,
  flag INT default 0,
  primary key (auto_id)
);
CREATE INDEX podcasturl_autoid_epoch on podcasturl(auto_id, epoch);
CREATE INDEX podcasturl_uid_url on podcasturl(uid, url);


CREATE TABLE user_disc (
  auto_id SERIAL,
  uid VARCHAR(80) NOT NULL,
  type VARCHAR(80) NOT NULL,
  disc_id INT,
  PRIMARY KEY (auto_id)
);
CREATE INDEX userdisc_uid_type_discid ON user_disc(uid,type,disc_id);


create table studentresourcelink (
  auto_id serial primary key,
  loid int,
  mid varchar(255),
  mimetype varchar(255),
  cn varchar(255),
  givenname varchar(255),
  sn varchar(255),
  datecreated numeric,
  cohort varchar(16),
  uid varchar(32),
  description text,
  category varchar(32),
  copyright char(1),
  copyrightother varchar(255),
  collaborative varchar(8),
  private varchar(8),
  dateadded timestamp default now(),
  archived numeric default 0,
  previous_version_id int,
  downloadcount numeric default 0
);
create index studentresourcelink_loid on studentresourcelink(loid);
create index studentresourcelink_mid on studentresourcelink(mid);

CREATE TABLE ratings (
  auto_id SERIAL,
  uid varchar(64),
  resource_id int,
  rating int,
  dateadded timestamp default now(),
  PRIMARY KEY (auto_id)
);
create index ratings_uid_resourceid on ratings(uid, resource_id);


CREATE TABLE ratingscores (
  auto_id SERIAL,
  uid varchar(64),
  resource_id int,
  rating int,
  dateadded timestamp default now(),
  comment text,
  PRIMARY KEY (auto_id)
);
create index ratingscores_uid_resourceid on ratingscores(uid, resource_id);

-- add QuestionScores table
create table questionscores (
  auto_id serial,
  uid varchar,
  choice int,
  correct int,
  mid varchar(128),
  rtime numeric,
  primary key(auto_id)
);
create index questionscores_uid on questionscores(uid);

CREATE TABLE studentinfo (
  auto_id SERIAL,
  uid varchar(64),
  education text,
  interests text,
  mobile_phone char(15),
  mobile_publicity INT DEFAULT 0,
  PRIMARY KEY (auto_id)
);

CREATE TABLE staff (
  auto_id SERIAL,
  stafftype INT DEFAULT 1,
  staffpage INT default 1,
  domain_id INT DEFAULT 1,
  uid VARCHAR(32),
  description text,
  seq_no INT DEFAULT 10000,
  PRIMARY KEY (auto_id)
);


CREATE TABLE studyconsent (
  auto_id SERIAL,
  uid VARCHAR(32),
  consentdate timestamp default now(),
  consent int,
  PRIMARY KEY (auto_id)
);

