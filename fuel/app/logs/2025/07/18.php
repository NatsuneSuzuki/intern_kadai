<?php defined('COREPATH') or exit('No direct script access allowed'); ?>

WARNING - 2025-07-18 23:49:17 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
WARNING - 2025-07-18 23:49:17 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
WARNING - 2025-07-18 23:49:18 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
WARNING - 2025-07-18 23:49:25 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:49:25 --> Warning - The use statement with non-compound name 'Model_Game' has no effect in /var/www/html/my_fuel_project/fuel/app/classes/controller/gamepaylog.php on line 3
WARNING - 2025-07-18 23:50:53 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:50:53 --> Error - Class 'Monthlypayment' not found in /var/www/html/my_fuel_project/fuel/app/classes/controller/gamepaylog.php on line 165
WARNING - 2025-07-18 23:53:15 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:53:15 --> Error - Class 'Orm\Model' not found in /var/www/html/my_fuel_project/fuel/app/classes/model/monthlypayment.php on line 5
WARNING - 2025-07-18 23:54:38 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:54:38 --> Error - Class 'Orm\Model' not found in /var/www/html/my_fuel_project/fuel/app/classes/model/monthlypayment.php on line 5
WARNING - 2025-07-18 23:54:39 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:54:39 --> Error - Class 'Orm\Model' not found in /var/www/html/my_fuel_project/fuel/app/classes/model/monthlypayment.php on line 5
WARNING - 2025-07-18 23:55:49 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:55:49 --> 1146 - Table 'ns_db.monthlypayments' doesn't exist [ SELECT `t0`.`id` AS `t0_c0`, `t0`.`goals_id` AS `t0_c1`, `t0`.`year` AS `t0_c2`, `t0`.`month` AS `t0_c3`, `t0`.`total_amount` AS `t0_c4`, `t1`.`id` AS `t1_c0`, `t1`.`goal_amount` AS `t1_c1` FROM (SELECT `t0`.`id`, `t0`.`goals_id`, `t0`.`year`, `t0`.`month`, `t0`.`total_amount` FROM `monthlypayments` AS `t0` WHERE `t0`.`year` = 2025 AND `t0`.`month` = 7 ORDER BY `t0`.`id` ASC LIMIT 1) AS `t0` LEFT JOIN `monthlygoals` AS `t1` ON (`t0`.`goals_id` = `t1`.`id`) ORDER BY `t0`.`id` ASC ] in /var/www/html/my_fuel_project/fuel/core/classes/database/mysqli/connection.php on line 292
WARNING - 2025-07-18 23:57:49 --> Fuel\Core\Fuel::init - The configured locale en_US is not installed on your system.
ERROR - 2025-07-18 23:57:49 --> 1146 - Table 'ns_db.monthlypayment' doesn't exist [ SELECT `t0`.`id` AS `t0_c0`, `t0`.`goals_id` AS `t0_c1`, `t0`.`year` AS `t0_c2`, `t0`.`month` AS `t0_c3`, `t0`.`total_amount` AS `t0_c4`, `t1`.`id` AS `t1_c0`, `t1`.`goal_amount` AS `t1_c1` FROM (SELECT `t0`.`id`, `t0`.`goals_id`, `t0`.`year`, `t0`.`month`, `t0`.`total_amount` FROM `monthlypayment` AS `t0` WHERE `t0`.`year` = 2025 AND `t0`.`month` = 7 ORDER BY `t0`.`id` ASC LIMIT 1) AS `t0` LEFT JOIN `monthlygoals` AS `t1` ON (`t0`.`goals_id` = `t1`.`id`) ORDER BY `t0`.`id` ASC ] in /var/www/html/my_fuel_project/fuel/core/classes/database/mysqli/connection.php on line 292
