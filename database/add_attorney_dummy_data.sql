-- =========================================================
-- ADD ATTORNEY DUMMY DATA TO MEDLAW DATABASE
-- This script adds missing attorney1 and attorney2 users with proper roles
-- to the MedLaw database schema.
-- =========================================================

-- Ensure we're using the correct database
USE `medlaw`;

-- =========================================================
-- PART 1: ADD MISSING ATTORNEY USERS
-- =========================================================

INSERT INTO `users` (`email`, `password_hash`, `name`, `phone`, `role`, `is_active`, `email_verified`, `profile_completed`, `login_count`, `last_login`) VALUES
('attorney1@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Attorney One', '+27 11 200 3001', 'attorney', 1, 1, 1, 25, NOW())
ON DUPLICATE KEY UPDATE 
    `id` = LAST_INSERT_ID(`id`),
    `name` = VALUES(`name`),
    `phone` = VALUES(`phone`),
    `role` = VALUES(`role`),
    `is_active` = VALUES(`is_active`);
SET @attorney1_id = LAST_INSERT_ID();

INSERT INTO `users` (`email`, `password_hash`, `name`, `phone`, `role`, `is_active`, `email_verified`, `profile_completed`, `login_count`, `last_login`) VALUES
('attorney2@medlaw.co.za', '$2y$10$06vdIPaZptBr70mXGBmG7.Z3ZRpfstF2oF1W5MSynBarN4L.rpwuK', 'Attorney Two', '+27 11 200 3002', 'attorney', 1, 1, 1, 18, NOW())
ON DUPLICATE KEY UPDATE 
    `id` = LAST_INSERT_ID(`id`),
    `name` = VALUES(`name`),
    `phone` = VALUES(`phone`),
    `role` = VALUES(`role`),
    `is_active` = VALUES(`is_active`);
SET @attorney2_id = LAST_INSERT_ID();

-- =========================================================
-- PART 2: ADD ATTORNEY PROFILES
-- =========================================================

INSERT INTO `attorney_profiles` (`user_id`, `name`, `title`, `bio`, `practice_areas`, `years_experience`, `bar_admission_year`) VALUES
(@attorney1_id, 'Attorney One', 'Senior Attorney', 'Experienced attorney specializing in medical negligence and personal injury cases. Committed to providing excellent legal representation for clients.', 'Medical Negligence, Personal Injury, Product Liability', 12, 2012)
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`),
    `title` = VALUES(`title`),
    `bio` = VALUES(`bio`),
    `practice_areas` = VALUES(`practice_areas`),
    `years_experience` = VALUES(`years_experience`),
    `bar_admission_year` = VALUES(`bar_admission_year`);

INSERT INTO `attorney_profiles` (`user_id`, `name`, `title`, `bio`, `practice_areas`, `years_experience`, `bar_admission_year`) VALUES
(@attorney2_id, 'Attorney Two', 'Associate Attorney', 'Dedicated attorney with expertise in motor vehicle accidents and premises liability. Focused on achieving the best outcomes for clients.', 'Motor Vehicle Accidents, Premises Liability, General Injury', 8, 2016)
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`),
    `title` = VALUES(`title`),
    `bio` = VALUES(`bio`),
    `practice_areas` = VALUES(`practice_areas`),
    `years_experience` = VALUES(`years_experience`),
    `bar_admission_year` = VALUES(`bar_admission_year`);

-- =========================================================
-- PART 3: ASSIGN CASES TO NEW ATTORNEYS
-- =========================================================

UPDATE `cases` 
SET `assigned_to` = @attorney1_id 
WHERE `id` IN (24001, 24004, 24009) 
AND `assigned_to` IS NULL;

UPDATE `cases` 
SET `assigned_to` = @attorney2_id 
WHERE `id` IN (24002, 24005, 24010) 
AND `assigned_to` IS NULL;

-- =========================================================
-- PART 4: ADD SAMPLE TASKS FOR NEW ATTORNEYS
-- =========================================================

INSERT INTO `tasks` (`case_id`, `assigned_to`, `title`, `description`, `due_date`, `priority`, `task_type`, `status`, `created_by`) VALUES
(24001, @attorney1_id, 'Review medical records for MVA case', 'Client suffered whiplash - need to review all medical documentation', DATE_ADD(NOW(), INTERVAL 3 DAY), 'high', 'case_review', 'pending', 20001),
(24004, @attorney1_id, 'Prepare settlement proposal', 'Product liability case - prepare initial settlement proposal', DATE_ADD(NOW(), INTERVAL 5 DAY), 'medium', 'admin_task', 'pending', 20001),
(24009, @attorney1_id, 'Conduct client interview', 'Product liability case - conduct detailed client interview', DATE_ADD(NOW(), INTERVAL 2 DAY), 'high', 'case_review', 'pending', 20001);

INSERT INTO `tasks` (`case_id`, `assigned_to`, `title`, `description`, `due_date`, `priority`, `task_type`, `status`, `created_by`) VALUES
(24002, @attorney2_id, 'Review surgical records', 'Medical negligence case - review all surgical documentation', DATE_ADD(NOW(), INTERVAL 4 DAY), 'urgent', 'document_review', 'pending', 20001),
(24005, @attorney2_id, 'Site inspection for dog bite case', 'General injury case - conduct site inspection', DATE_ADD(NOW(), INTERVAL 1 DAY), 'medium', 'case_review', 'pending', 20001),
(24010, @attorney2_id, 'Prepare expert witness list', 'General injury case - prepare list of expert witnesses', DATE_ADD(NOW(), INTERVAL 7 DAY), 'medium', 'admin_task', 'pending', 20001);

-- =========================================================
-- END OF SCRIPT
-- =========================================================

-- Verify the data was added successfully
SELECT 'Attorney dummy data added successfully' as status;
SELECT COUNT(*) as attorney_count FROM users WHERE role = 'attorney';
SELECT COUNT(*) as profile_count FROM attorney_profiles;
SELECT COUNT(*) as assigned_cases FROM cases WHERE assigned_to IN (@attorney1_id, @attorney2_id);
SELECT COUNT(*) as attorney_tasks FROM tasks WHERE assigned_to IN (@attorney1_id, @attorney2_id);
