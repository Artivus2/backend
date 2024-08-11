-- --------------------------------------------------------

--
-- Структура таблицы `walletInv`
--

CREATE TABLE `walletInv` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `balance` decimal(27,8) NOT NULL DEFAULT 0.00000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Индексы таблицы `walletInv`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT для таблицы `walletInv`
--
ALTER TABLE `walletInv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Структура таблицы `walletTrade`
--

CREATE TABLE `walletTrade` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `balance` decimal(27,8) NOT NULL DEFAULT 0.00000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Индексы таблицы `walletTrade`
--
ALTER TABLE `walletTrade`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для таблицы `walletTrade`
--
ALTER TABLE `walletTrade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- для таблицы `user` 
--
ALTER TABLE `user` 
ADD COLUMN `created_at` INT NULL AFTER `status`;