--
-- structure table `users`
--

CREATE TABLE `users` (
    `id` int NOT NULL,
    `username` varchar(50) NOT NULL,
    `name` varchar(255) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` timestamp NULL,
    `updated_at` timestamp NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;
