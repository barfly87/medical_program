CREATE TABLE lk_stage (
  auto_id SERIAL,
  stage varchar(32),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_block (
  auto_id SERIAL,
  name  VARCHAR(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE stage_block_seq (
  auto_id SERIAL,
  stage_id INT,
  seq_no INT,
  block_id INT,
  year_id INT,
  PRIMARY KEY (auto_id)
);
CREATE INDEX sbs_stage ON stage_block_seq (stage_id);
CREATE INDEX sbs_seq_no ON stage_block_seq (seq_no);
CREATE INDEX sbs_block_id ON stage_block_seq (block_id);


CREATE TABLE lk_pbl (
  auto_id SERIAL,
  name  VARCHAR(128),
  description  VARCHAR(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_blockweek (
  auto_id SERIAL,
  weeknum INT,
  PRIMARY KEY (auto_id)
);


CREATE TABLE block_pbl_seq (
  auto_id SERIAL,
  block_seq_id INT,
  week_id INT,
  pbl_id INT,
  PRIMARY KEY (auto_id)
);
CREATE INDEX bps_block_seq_id ON block_pbl_seq (block_seq_id);
CREATE INDEX bps_week_id ON block_pbl_seq (week_id);
CREATE INDEX bps_pbl_id ON block_pbl_seq (pbl_id);
