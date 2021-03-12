--
-- structure table `users_remember_tokens`
--

CREATE TABLE `users_remember_tokens` (
        `id` int NOT NULL,
        `user_id` int NOT NULL,
        `token` varchar(255) NOT NULL,
        `ip` varchar(18) NOT NULL,
        `user_agent` varchar(255) NOT NULL,
        `expired_at` timestamp NOT NULL,
        `created_at` timestamp NOT NULL,
        `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `users_remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users_remember_tokens`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

