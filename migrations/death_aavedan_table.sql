




CREATE TABLE `death_aavedan` (
  `id` int(11) NOT NULL,
  `application_number` varchar(50) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `member_name` varchar(100) DEFAULT NULL,
  `member_dob` date DEFAULT NULL,
  `member_mobile` varchar(10) DEFAULT NULL,
  `member_address` text DEFAULT NULL,
  `applicant_name` varchar(100) NOT NULL,
  `applicant_dob` date NOT NULL,
  `applicant_relation` varchar(50) NOT NULL COMMENT 'Relationship with deceased: पिता (Father), पुत्री (Daughter), पत्नी (Wife)',
  `applicant_parent_name` varchar(100) NOT NULL COMMENT 'Father/Mother/Spouse name of applicant',
  `father_name` varchar(100) DEFAULT NULL COMMENT '[DEPRECATED - No longer used] Kept for backwards compatibility',
  `deceased_name` varchar(100) NOT NULL,
  `deceased_member_id` varchar(20) NOT NULL,
  `deceased_dob` date NOT NULL,
  `deceased_age` int(11) NOT NULL COMMENT 'Age at time of death (18-60)',
  `death_date` date NOT NULL,
  `deceased_relationship` varchar(50) DEFAULT NULL COMMENT 'Relationship to applicant',
  `cause_of_death` text DEFAULT NULL,
  `family_income` decimal(10,2) DEFAULT NULL,
  `family_members` int(11) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `account_holder_name` varchar(100) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('submitted','under_review','approved','rejected','on_hold') DEFAULT 'submitted',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deceased_aadhar` varchar(255) DEFAULT NULL,
  `death_certificate` varchar(255) DEFAULT NULL,
  `post_mortem_report` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Death benefit applications';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `death_aavedan`
--
ALTER TABLE `death_aavedan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_number` (`application_number`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `deceased_member_id` (`deceased_member_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `death_aavedan`
--
ALTER TABLE `death_aavedan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `death_aavedan`
  ADD CONSTRAINT `death_aavedan_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`),
  ADD CONSTRAINT `death_aavedan_ibfk_2` FOREIGN KEY (`deceased_member_id`) REFERENCES `members` (`member_id`);
COMMIT;

