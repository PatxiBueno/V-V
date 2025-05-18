CREATE TABLE IF NOT EXISTS token (
     id_usuario INT NOT NULL,
     token VARCHAR(255) NOT NULL,
     fecha_token TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     PRIMARY KEY (id_usuario, token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ttt (
    game_id INT NOT NULL,
    game_name VARCHAR(255),
    user_name VARCHAR(255) NOT NULL,
    total_videos INT,
    total_views BIGINT,
    most_viewed_title VARCHAR(255),
    most_viewed_views BIGINT,
    most_viewed_duration VARCHAR(100),
    most_viewed_created_at TIMESTAMP,
    PRIMARY KEY (game_id, user_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ttt_fecha (
    fecha_insercion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;