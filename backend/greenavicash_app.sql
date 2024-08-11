-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 18 2023 г., 08:23
-- Версия сервера: 10.3.38-MariaDB-0+deb10u1
-- Версия PHP: 7.3.31-1~deb10u3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `greenavicash_app`
--

-- --------------------------------------------------------

--
-- Структура таблицы `affiliate`
--

CREATE TABLE `affiliate` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `affiliate_history`
--

CREATE TABLE `affiliate_history` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_code`
--

CREATE TABLE `auth_code` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` int(11) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `b2b_ads`
--

CREATE TABLE `b2b_ads` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `min_price` decimal(27,8) NOT NULL,
  `price` decimal(27,8) NOT NULL,
  `course` decimal(27,8) NOT NULL,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `b2b_history`
--

CREATE TABLE `b2b_history` (
  `id` int(11) NOT NULL,
  `b2b_ads_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL DEFAULT 0,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `price` decimal(27,8) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `betting`
--

CREATE TABLE `betting` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `amount` decimal(27,8) NOT NULL,
  `start_price` decimal(27,8) NOT NULL,
  `end_price` decimal(27,8) NOT NULL,
  `minutes` int(11) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `chain`
--

CREATE TABLE `chain` (
  `id` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `chart`
--

CREATE TABLE `chart` (
  `id` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `symbol` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `yield` int(11) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `chart_chain`
--

CREATE TABLE `chart_chain` (
  `id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `chain_id` int(11) NOT NULL,
  `symbol` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `chart_favourite`
--

CREATE TABLE `chart_favourite` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `commission`
--

CREATE TABLE `commission` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `value` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `inn` varchar(10) NOT NULL,
  `ogrn` varchar(13) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `currency`
--

CREATE TABLE `currency` (
  `id` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `symbol` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `futures`
--

CREATE TABLE `futures` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `order_type` int(11) NOT NULL,
  `margin_type` int(11) NOT NULL,
  `multiplier` int(11) NOT NULL,
  `price` decimal(27,8) NOT NULL,
  `limit_price` decimal(27,8) NOT NULL DEFAULT 0.00000000,
  `amount` decimal(27,8) NOT NULL,
  `final_amount` decimal(27,8) NOT NULL DEFAULT 0.00000000,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `start_price` decimal(27,8) NOT NULL,
  `end_price` decimal(27,8) NOT NULL,
  `start_chart_id` int(11) NOT NULL,
  `end_chart_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `image`
--

CREATE TABLE `image` (
  `id` int(11) NOT NULL,
  `filePath` varchar(400) NOT NULL,
  `itemId` int(11) DEFAULT NULL,
  `isMain` tinyint(1) DEFAULT NULL,
  `modelName` varchar(150) NOT NULL,
  `urlAlias` varchar(400) NOT NULL,
  `name` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `p2p_ads`
--

CREATE TABLE `p2p_ads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `min_price` decimal(27,8) NOT NULL,
  `price` decimal(27,8) NOT NULL,
  `course` decimal(27,8) NOT NULL,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `p2p_history`
--

CREATE TABLE `p2p_history` (
  `id` int(11) NOT NULL,
  `p2p_ads_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL DEFAULT 0,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `price` decimal(27,8) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `p2p_payment`
--

CREATE TABLE `p2p_payment` (
  `id` int(11) NOT NULL,
  `p2p_ads_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `payment_type`
--

CREATE TABLE `payment_type` (
  `id` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `currency_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `payment_user`
--

CREATE TABLE `payment_user` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `spot`
--

CREATE TABLE `spot` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `order_type` int(11) NOT NULL,
  `price` decimal(27,8) NOT NULL,
  `amount` decimal(27,8) NOT NULL,
  `final_amount` decimal(27,8) NOT NULL DEFAULT 0.00000000,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` bigint(11) DEFAULT NULL,
  `verify_status` int(11) NOT NULL DEFAULT 0,
  `chart_id` int(11) NOT NULL DEFAULT 258,
  `currency_id` int(11) NOT NULL DEFAULT 1,
  `telegram` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблицы `user_two_factor`
--

CREATE TABLE `user_two_factor` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_verify`
--

CREATE TABLE `user_verify` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_words`
--

CREATE TABLE `user_words` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `words` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `wallet`
--

CREATE TABLE `wallet` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `balance` decimal(27,8) NOT NULL DEFAULT 0.00000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `wallet_address`
--

CREATE TABLE `wallet_address` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chain_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `affiliate`
--
ALTER TABLE `affiliate`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `affiliate_history`
--
ALTER TABLE `affiliate_history`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD PRIMARY KEY (`item_name`,`user_id`),
  ADD KEY `idx-auth_assignment-user_id` (`user_id`);

--
-- Индексы таблицы `auth_code`
--
ALTER TABLE `auth_code`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `auth_item`
--
ALTER TABLE `auth_item`
  ADD PRIMARY KEY (`name`),
  ADD KEY `rule_name` (`rule_name`),
  ADD KEY `idx-auth_item-type` (`type`);

--
-- Индексы таблицы `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD PRIMARY KEY (`parent`,`child`),
  ADD KEY `child` (`child`);

--
-- Индексы таблицы `auth_rule`
--
ALTER TABLE `auth_rule`
  ADD PRIMARY KEY (`name`);

--
-- Индексы таблицы `b2b_ads`
--
ALTER TABLE `b2b_ads`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `b2b_history`
--
ALTER TABLE `b2b_history`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `betting`
--
ALTER TABLE `betting`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chain`
--
ALTER TABLE `chain`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chart`
--
ALTER TABLE `chart`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chart_chain`
--
ALTER TABLE `chart_chain`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chart_favourite`
--
ALTER TABLE `chart_favourite`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `commission`
--
ALTER TABLE `commission`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `futures`
--
ALTER TABLE `futures`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `p2p_ads`
--
ALTER TABLE `p2p_ads`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `p2p_history`
--
ALTER TABLE `p2p_history`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `p2p_payment`
--
ALTER TABLE `p2p_payment`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `payment_type`
--
ALTER TABLE `payment_type`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `payment_user`
--
ALTER TABLE `payment_user`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `spot`
--
ALTER TABLE `spot`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user_two_factor`
--
ALTER TABLE `user_two_factor`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user_verify`
--
ALTER TABLE `user_verify`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user_words`
--
ALTER TABLE `user_words`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `wallet_address`
--
ALTER TABLE `wallet_address`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `affiliate`
--
ALTER TABLE `affiliate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `affiliate_history`
--
ALTER TABLE `affiliate_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `auth_code`
--
ALTER TABLE `auth_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `b2b_ads`
--
ALTER TABLE `b2b_ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `b2b_history`
--
ALTER TABLE `b2b_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `betting`
--
ALTER TABLE `betting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chain`
--
ALTER TABLE `chain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chart`
--
ALTER TABLE `chart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chart_chain`
--
ALTER TABLE `chart_chain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chart_favourite`
--
ALTER TABLE `chart_favourite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `commission`
--
ALTER TABLE `commission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `currency`
--
ALTER TABLE `currency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `futures`
--
ALTER TABLE `futures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `image`
--
ALTER TABLE `image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `p2p_ads`
--
ALTER TABLE `p2p_ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `p2p_history`
--
ALTER TABLE `p2p_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `p2p_payment`
--
ALTER TABLE `p2p_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `payment_type`
--
ALTER TABLE `payment_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `payment_user`
--
ALTER TABLE `payment_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `spot`
--
ALTER TABLE `spot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_two_factor`
--
ALTER TABLE `user_two_factor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_verify`
--
ALTER TABLE `user_verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_words`
--
ALTER TABLE `user_words`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `wallet`
--
ALTER TABLE `wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `wallet_address`
--
ALTER TABLE `wallet_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `auth_item`
--
ALTER TABLE `auth_item`
  ADD CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
