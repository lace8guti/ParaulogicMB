-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 02-05-2023 a las 23:08:36
-- Versión del servidor: 8.0.32-0ubuntu0.22.04.2
-- Versión de PHP: 8.1.2-1ubuntu2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ParaulogicMBFinal`
--
CREATE DATABASE IF NOT EXISTS `ParaulogicMBFinal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `ParaulogicMBFinal`;

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `create_user` (IN `in_username` INT(255), IN `in_password` INT(255))  BEGIN
    DECLARE user_count INT DEFAULT 0;
    
    
    SELECT COUNT(*) INTO user_count FROM users WHERE username = in_username;
    
    IF user_count = 0 THEN
        
        INSERT INTO users (username, password, total_score)
        VALUES (in_username, in_password, 0);
        SELECT CONCAT('Usuario creado con éxito. ID: ', LAST_INSERT_ID()) AS message;
    ELSE
        SELECT 'El nombre de usuario ya está en uso.' AS message;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_user` (IN `in_username` INT, IN `in_password` INT)  BEGIN
    DECLARE user_count INT DEFAULT 0;

    
    SELECT COUNT(*) INTO user_count FROM users WHERE username = in_username AND password = in_password;

    
    IF user_count = 1 THEN
        DELETE FROM users WHERE username = in_username;
        SELECT 'Usuario eliminado correctamente.' AS message;
    ELSE
        SELECT 'Nombre de usuario o contraseña incorrectos.' AS message;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `challenges`
--

CREATE TABLE IF NOT EXISTS `challenges` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `pattern_id` int DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `score` int DEFAULT NULL,
  `word_guessed` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `challenges`
--
DELIMITER $$
CREATE TRIGGER `update_total_score` AFTER INSERT ON `challenges` FOR EACH ROW BEGIN
    UPDATE users
    SET total_score = total_score + NEW.score
    WHERE id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `daily_scores`
--

CREATE TABLE IF NOT EXISTS `daily_scores` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `daily_score` int DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `paraules`
-- (Véase abajo para la vista actual)
--
CREATE TABLE IF NOT EXISTS `paraules` (
`date` datetime
,`id` int
,`pattern` varchar(255)
,`word` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `paraurep`
-- (Véase abajo para la vista actual)
--
CREATE TABLE IF NOT EXISTS `paraurep` (
`date` datetime
,`id` int
,`pattern` varchar(255)
,`word` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `patterns`
--

CREATE TABLE IF NOT EXISTS `patterns` (
  `id` int NOT NULL,
  `pattern` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `puntuacions`
-- (Véase abajo para la vista actual)
--
CREATE TABLE IF NOT EXISTS `puntuacions` (
`daily_score` int
,`date` datetime
,`id` int
,`pattern` varchar(255)
,`user_id` int
,`username` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `ranquing`
-- (Véase abajo para la vista actual)
--
CREATE TABLE IF NOT EXISTS `ranquing` (
`id` int
,`total_score` int
,`username` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `total_score` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valid_words`
--

CREATE TABLE IF NOT EXISTS `valid_words` (
  `id` int NOT NULL,
  `pattern_id` int DEFAULT NULL,
  `word` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `valid_words`
--
DELIMITER $$
CREATE TRIGGER `check_word_length` BEFORE INSERT ON `valid_words` FOR EACH ROW BEGIN
    IF CHAR_LENGTH(NEW.word) < 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Palabra demasiado corta';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura para la vista `paraules`
--
DROP TABLE IF EXISTS `paraules`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `paraules`  AS SELECT `ParaulogicMB3`.`patterns`.`id` AS `id`, `ParaulogicMB3`.`patterns`.`pattern` AS `pattern`, `ParaulogicMB3`.`challenges`.`date` AS `date`, `ParaulogicMB3`.`valid_words`.`word` AS `word` FROM ((`ParaulogicMB3`.`patterns` join `ParaulogicMB3`.`challenges` on((`ParaulogicMB3`.`patterns`.`id` = `ParaulogicMB3`.`challenges`.`pattern_id`))) join `ParaulogicMB3`.`valid_words` on((`ParaulogicMB3`.`challenges`.`pattern_id` = `ParaulogicMB3`.`valid_words`.`pattern_id`))) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `paraurep`
--
DROP TABLE IF EXISTS `paraurep`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `paraurep`  AS SELECT `ParaulogicMB3`.`patterns`.`id` AS `id`, `ParaulogicMB3`.`patterns`.`pattern` AS `pattern`, `ParaulogicMB3`.`challenges`.`date` AS `date`, `ParaulogicMB3`.`valid_words`.`word` AS `word` FROM ((`ParaulogicMB3`.`patterns` join `ParaulogicMB3`.`challenges` on((`ParaulogicMB3`.`patterns`.`id` = `ParaulogicMB3`.`challenges`.`pattern_id`))) join `ParaulogicMB3`.`valid_words` on((`ParaulogicMB3`.`challenges`.`pattern_id` = `ParaulogicMB3`.`valid_words`.`pattern_id`))) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `puntuacions`
--
DROP TABLE IF EXISTS `puntuacions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `puntuacions`  AS SELECT `ParaulogicMB3`.`patterns`.`id` AS `id`, `ParaulogicMB3`.`patterns`.`pattern` AS `pattern`, `ParaulogicMB3`.`challenges`.`date` AS `date`, `ParaulogicMB3`.`users`.`username` AS `username`, `ParaulogicMB3`.`daily_scores`.`user_id` AS `user_id`, `ParaulogicMB3`.`daily_scores`.`daily_score` AS `daily_score` FROM (((`ParaulogicMB3`.`patterns` join `ParaulogicMB3`.`challenges` on((`ParaulogicMB3`.`patterns`.`id` = `ParaulogicMB3`.`challenges`.`pattern_id`))) join `ParaulogicMB3`.`users` on((`ParaulogicMB3`.`challenges`.`user_id` = `ParaulogicMB3`.`users`.`id`))) join `ParaulogicMB3`.`daily_scores` on((`ParaulogicMB3`.`users`.`id` = `ParaulogicMB3`.`daily_scores`.`user_id`))) WHERE (`ParaulogicMB3`.`daily_scores`.`daily_score` = (select max(`ParaulogicMB3`.`daily_scores`.`daily_score`) from `ParaulogicMB3`.`daily_scores`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `ranquing`
--
DROP TABLE IF EXISTS `ranquing`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ranquing`  AS SELECT `ParaulogicMB3`.`users`.`id` AS `id`, `ParaulogicMB3`.`users`.`username` AS `username`, `ParaulogicMB3`.`users`.`total_score` AS `total_score` FROM `ParaulogicMB3`.`users` ORDER BY `ParaulogicMB3`.`users`.`total_score` DESC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pattern_id` (`pattern_id`);

--
-- Indices de la tabla `daily_scores`
--
ALTER TABLE `daily_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `patterns`
--
ALTER TABLE `patterns`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `valid_words`
--
ALTER TABLE `valid_words`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pattern_id` (`pattern_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `daily_scores`
--
ALTER TABLE `daily_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `patterns`
--
ALTER TABLE `patterns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `valid_words`
--
ALTER TABLE `valid_words`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `daily_scores`
--
ALTER TABLE `daily_scores`
  ADD CONSTRAINT `daily_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `valid_words`
--
ALTER TABLE `valid_words`
  ADD CONSTRAINT `valid_words_ibfk_1` FOREIGN KEY (`pattern_id`) REFERENCES `patterns` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
