CREATE TABLE IF NOT EXISTS links (
	ind INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	steamid64 BIGINT UNSIGNED,
	panel_url VARCHAR(255),
	client_ip BIGINT UNSIGNED,
	server_ip BIGINT UNSIGNED,
	server_port INT UNSIGNED,
	panel_title VARCHAR(64),
	panel_hidden TINYINT(1),
	panel_width INT UNSIGNED,
	panel_height INT UNSIGNED,
	created_at BIGINT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS servers (
	ind INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(64),
	ip BIGINT UNSIGNED,
	port INT UNSIGNED,
	token VARCHAR(64),
	is_blocked TINYINT(1),
	created_at BIGINT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
