-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Мар 06 2018 г., 13:49
-- Версия сервера: 10.1.25-MariaDB
-- Версия PHP: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `local`
--

-- --------------------------------------------------------

--
-- Структура таблицы `buyend`
--

CREATE TABLE `buyend` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `date` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `buyend`
--

INSERT INTO `buyend` (`id`, `uid`, `obj_id`, `date`) VALUES
(1, 1, 3, 1520329969),
(2, 1, 4, 1520329969),
(3, 1, 2, 1515232368);

-- --------------------------------------------------------

--
-- Структура таблицы `objects`
--

CREATE TABLE `objects` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `objects`
--

INSERT INTO `objects` (`id`, `name`) VALUES
(1, 'лампа'),
(2, 'стул'),
(3, 'стол'),
(4, 'ручка'),
(5, 'ведро');

-- --------------------------------------------------------

--
-- Структура таблицы `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `obj_id` int(30) NOT NULL,
  `rating` int(11) NOT NULL,
  `uip` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `rating`
--

INSERT INTO `rating` (`id`, `obj_id`, `rating`, `uip`) VALUES
(1, 2, 3, 2130706430),
(14, 2, 4, 2130706431),
(15, 2, 1, 2130706433);

-- --------------------------------------------------------

--
-- Структура таблицы `recyclebin`
--

CREATE TABLE `recyclebin` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `date` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `recyclebin`
--

INSERT INTO `recyclebin` (`id`, `uid`, `obj_id`, `date`) VALUES
(1, 1, 2, 1520329969),
(2, 1, 4, 1520329969),
(3, 1, 1, 1525232368),
(4, 2, 3, 1520337918),
(5, 2, 1, 1488801917);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fio` varchar(60) NOT NULL,
  `email` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `fio`, `email`) VALUES
(1, 'иванов', 'ivanov@mail.ru'),
(2, 'petrov', 'petrov@mail.ru');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `buyend`
--
ALTER TABLE `buyend`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_uid` (`uid`),
  ADD KEY `fk_objid` (`obj_id`);

--
-- Индексы таблицы `objects`
--
ALTER TABLE `objects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `recyclebin`
--
ALTER TABLE `recyclebin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_uid` (`uid`),
  ADD KEY `fk_objid` (`obj_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `buyend`
--
ALTER TABLE `buyend`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT для таблицы `objects`
--
ALTER TABLE `objects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT для таблицы `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT для таблицы `recyclebin`
--
ALTER TABLE `recyclebin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `recyclebin`
--
ALTER TABLE `recyclebin`
  ADD CONSTRAINT `fk_objid` FOREIGN KEY (`obj_id`) REFERENCES `objects` (`id`),
  ADD CONSTRAINT `fk_uid` FOREIGN KEY (`uid`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
