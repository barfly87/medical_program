CREATE TABLE descriptor (
	auto_id SERIAL,
	uid VARCHAR(7),
	headingtext VARCHAR(200),
	treenumbers TEXT,
	synonyms TEXT,
	PRIMARY KEY (auto_id)
);
CREATE UNIQUE INDEX headingtext ON descriptor (headingtext);
CREATE INDEX treenumbers ON descriptor (treenumbers);
