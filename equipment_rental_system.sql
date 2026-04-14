SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `condition_status` enum('New','Good','Damaged','Needs Service') NOT NULL DEFAULT 'Good',
  `total_quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `equipment` (`equipment_id`, `name`, `category`, `serial_number`, `condition_status`, `total_quantity`, `available_quantity`, `created_at`) VALUES
(3, 'Epson Projector X1', 'Projector', 'PRO-001', 'Good', 100, 99, '2026-04-08 16:44:11'),
(4, 'Shure SM58', 'Microphone', 'MIC-001', 'New', 100, 100, '2026-04-08 16:44:11'),
(5, 'Epson EB-E24 Projector', 'Projector', 'PRO-002', 'New', 49, 49, '2026-04-09 16:49:52'),
(6, 'Eyeline Basic Tripod Screen', 'Screen', 'SC-001', 'New', 32, 32, '2026-04-09 16:50:39'),
(7, 'Celexon Economy Tripod Screen', 'Screen', 'sc-39', 'New', 111, 111, '2026-04-09 16:51:21'),
(8, 'HyperX QuadCast', 'Microphone', 'mc-332', 'New', 21, 21, '2026-04-09 16:51:54'),
(9, 'Neumann U 87 Ai Studio Set', 'Microphone', 'mset-03', 'New', 11, 11, '2026-04-09 16:52:57'),
(10, 'Neumann U 56 Studio Set', 'Microphone', 'MIC-0043', 'New', 53, 53, '2026-04-14 13:55:56');


CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `rental_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Rented','Returned','Overdue') NOT NULL DEFAULT 'Rented'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `rentals` (`rental_id`, `user_id`, `equipment_id`, `rental_date`, `due_date`, `return_date`, `status`) VALUES
(17, 6, 3, '2026-04-09', '2026-04-16', '2026-04-09', 'Returned'),
(18, 6, 4, '2026-04-09', '2026-04-16', '2026-04-09', 'Returned'),
(19, 6, 3, '2026-04-09', '2026-04-16', '2026-04-09', 'Returned'),
(20, 6, 3, '2026-04-09', '2026-04-16', '2026-04-09', 'Returned'),
(21, 6, 3, '2026-04-09', '2026-04-16', '2026-04-09', 'Returned'),
(22, 6, 7, '2026-04-14', '2026-04-21', '2026-04-14', 'Returned'),
(23, 6, 5, '2026-04-14', '2026-04-21', '2026-04-14', 'Returned'),
(24, 6, 4, '2026-04-14', '2026-04-21', '2026-04-14', 'Returned'),
(25, 6, 3, '2026-04-14', '2026-04-21', NULL, 'Rented');

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','User') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `full_name`, `email`, `username`, `password`, `role`, `status`, `created_at`) VALUES
(3, 'Robert Misiek', 'happy.bears.trade@gmail.com', 'admin', '$2y$10$WVcXhZ3erHbkPGZcnERe/.ZqXeB5U/gJw4kqfbDMnmUJ3SO3NvUem', 'Admin', 'Active', '2026-04-08 17:34:51'),
(6, 'Anna Misiek', 'Annamisiek2019@gmail.com', 'user', '$2y$10$reAi2dieyZ2Ri60EeMNQ7ONNZ1kwySF2ZSOB57O49SZrHjAu6BYAK', 'User', 'Active', '2026-04-09 16:46:58');


ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`);


ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `fk_rentals_user` (`user_id`),
  ADD KEY `fk_rentals_equipment` (`equipment_id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;


ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;


ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `rentals`
  ADD CONSTRAINT `fk_rentals_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rentals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;


