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
