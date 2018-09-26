USE TESTECOMMERCIAL;

CREATE TABLE Email(
	email_id INT IDENTITY(1,1) PRIMARY KEY,
	user_id int NULL,
	parent_id varchar(10) NULL,
	email_origin varchar(100) NOT NULL,
	email_subject varchar(100) NOT NULL,
	email_status varchar(1) NOT NULL,
	email_trash varchar(1) NOT NULL,
	email_update datetime DEFAULT NULL,
	email_date datetime NOT NULL
);

CREATE TABLE EmailAccount(
  email_account_id INT IDENTITY(1,1) PRIMARY KEY,
  user_id int NULL,
  email_account_host varchar(100) NOT NULL,
  email_account_port int NOT NULL,
  email_account_smtp varchar(1) NOT NULL,
  email_account_user varchar(100) NOT NULL,
  email_account_pass varchar(100) NOT NULL,
  email_account_update datetime NULL,
  email_account_date datetime NOT NULL
);

CREATE TABLE EmailLog (
  email_log_id INT IDENTITY(1,1) PRIMARY KEY,
  email_id int NOT NULL,
  user_id int NULL,
  email_log_message varchar(200) NOT NULL,
  email_log_date datetime NOT NULL
);