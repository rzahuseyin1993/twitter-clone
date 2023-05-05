<?php 
# @*************************************************************************@
# @ Software author: Mansur Altamirov (Mansur_TL)							@
# @ Author_url 1: https://www.instagram.com/mansur_tl                       @
# @ Author_url 2: http://codecanyon.net/user/mansur_tl                      @
# @ Author E-mail: mansurtl.contact@gmail.com                               @
# @*************************************************************************@
# @ ColibriSM - The Ultimate Modern Social Media Sharing Platform           @
# @ Copyright (c) 2020 - 2021 ColibriSM. All rights reserved.               @
# @*************************************************************************@

require_once("../core/settings.php");

$sql_db_host   = (isset($sql_db_host) ? $sql_db_host : "");
$sql_db_user   = (isset($sql_db_user) ? $sql_db_user : "");
$sql_db_pass   = (isset($sql_db_pass) ? $sql_db_pass : "");
$sql_db_name   = (isset($sql_db_name) ? $sql_db_name : "");
$site_url      = (isset($site_url)    ? $site_url    : "");
$mysqli        = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);
$server_errors = array();

if (mysqli_connect_errno()) {
    $server_errors[] = "Error: Failed to connect to MySQL Server: " . mysqli_connect_error();
}

if (empty($server_errors) != true) {
    foreach ($server_errors as $serv_error) {
        echo "<h3>{$serv_error}</h3>";
    }

    die();
}

require_once("../core/libs/DB/vendor/autoload.php");

$query        = $mysqli->query("SET NAMES utf8");
$set_charset  = $mysqli->set_charset('utf8mb4');
$set_charset  = $mysqli->query("SET collation_connection = utf8mb4_unicode_ci");
$db           = new MysqliDb($mysqli);
$curr_version = $db->where('name', 'version')->getOne('cl_configs');
$curr_theme   = $db->where('name', 'theme')->getOne('cl_configs');
$curr_version = (empty($curr_version['value'])) ? 0 : $curr_version['value'];
$curr_theme   = (empty($curr_theme['value'])) ? 'default' : $curr_theme['value'];
$new_version  = '1.3.5';
$update       = (version_compare($curr_version, $new_version) == -1);
$errors       = array();
$update_stat  = false;
$new_sql      = array(
	"INSERT INTO `cl_configs` (`id`, `title`, `name`, `value`, `regex`) VALUES (NULL, 'Non-binary gender', 'non_binary_gender', 'on', '/^(on|off)$/');",
	"INSERT INTO `cl_configs` (`id`, `title`, `name`, `value`, `regex`) VALUES (NULL, 'Post audio download system', 'post_audio_download_system', 'on', '/^(on|off)$/');"
);

if (isset($_POST['update'])) {
	try {
		sleep(3);

		foreach ($new_sql as $query) {
			mysqli_query($mysqli, $query);
		}

		$db = $db->where('name','version');
        $up = $db->update('cl_configs',array(
            'value' => $new_version
        ));

	 	$update_stat = true;

	 	$users = $db->where("followers", 0, "<")->where("following", 0, "<", "OR")->get("cl_users");

		if (empty($users) != true) {
			foreach ($users as $user_data) {
				$db->where("id", $user_data["id"])->update("cl_users", array(
					"followers" => $db->where("following_id", $user_data["id"])->getValue("cl_connections", "COUNT(*)"),
					"following" => $db->where("follower_id", $user_data["id"])->getValue("cl_connections", "COUNT(*)")
				));
			}
		}
	} 

	catch (Exception $e) {
		$errors[] = $e->getMessage();
	} 
}

$update_changelog = array(
	"Added the ability to collapse/expand long text",
	"Fixed a bug with the translation of the text on the authorization page",
	"Fixed a bug with the premium settings option link on the account settings page",
	"Fixed the algorithm for displaying posts so that too old posts are not shown",
	"Added ability to download audio files",
	"Added ability to disable non-binary genders like: Other or They",
	"Fixed a bug with login through social networks",
	"Added the ability to show the number of online users in the admin panel",
	"Fixed issue with duplicate posts on homepage"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Update ColibriSM Script</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<script src="<?php echo $site_url; ?>/update/assets/js/jquery-3.5.1.min.js"></script>
	<script src="<?php echo $site_url; ?>/update/assets/js/master.script.js"></script>

	<link rel="stylesheet" href="<?php echo $site_url; ?>/update/assets/css/master.style.css">
	<link rel="icon" href="<?php echo $site_url; ?>/update/assets/img/favicon.png" type="image/png">
	<link rel="icon" href="<?php echo $site_url; ?>/update/assets/img/favicon.png" type="image/x-icon">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
	<div class="container" data-section-block="script-update">	
		<div class="container__header">
			<div class="page-offset">
				<div class="page-offset__inner">
					<div class="logo">
						<img src="<?php echo $site_url; ?>/update/assets/img/csm-logo.png" alt="Logo">
					</div>

					<h1>Update Your ColibriSM Script to <span>v<?php echo($new_version); ?></span></h1>
					<p>
						Welcome to the new ColibriSM script version. The new version of ColibriSM will make your work more convenient, safe, high-quality and fast.
					</p>
					<div>
						<a href="https://codecanyon.net/item/colibrism-the-ultimate-php-modern-social-media-sharing-platform/26612898" target="_blank">
							Download latest version here
						</a>
					</div>
				</div>
			</div>
		</div>

		<?php if ($update == true && $update_stat == false): ?>
			<div class="container__body">
				<div class="page-offset">
					<div class="page-offset__inner">
						<h5 class="updates-title">
							Changelogs of v[<?php echo($new_version); ?>] ColibriSM script:
						</h5>
						
						<div class="updates-list">

							<?php foreach ($update_changelog as $ind => $log): ?>
								<p>
									<strong>[<?php if($ind <= 8) {echo "0" . ($ind += 1);} else {echo ($ind += 1);} ?>]</strong> <?php echo $log; ?>
								</p>
							<?php endforeach; ?>
						</div>

						<div class="updates-alert">
							<div class="updates-alert__icon">
								<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 5.99L19.53 19H4.47L12 5.99M12 2L1 21h22L12 2zm1 14h-2v2h2v-2zm0-6h-2v4h2v-4z"/></svg>
							</div>
							<div class="updates-alert__body">
								<b>Attention:</b>
								Make sure that you read and follow all the steps listed in the documentation for updating scripts, otherwise the process will result in an error and new updates will not be installed correctly.
							</div>
		                </div>
						<div class="updates-ctrls">
							<form method="post" data-section-node="submit-form" action="<?php echo($site_url); ?>/update/index.php">
								<button class="updates-primary-btn" type="submit" data-section-node="update" data-status="none">
									Install updates of [v<?php echo($new_version); ?>] version
								</button>
								<input type="hidden" name="update" value="1">
							</form>
						</div>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="container__body">
				<div class="page-offset">
					<div class="page-offset__inner">
						<?php if (empty($errors) != true): ?>
							<div class="updates-message">
								<h5 class="col-danger">
									Update installation failed :(
								</h5>
								<h6>
									Please check the following error messages:
								</h6>
								<ol>
									<?php foreach ($errors as $err): ?>
										<li>
											<?php echo($err); ?>
										</li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php else: ?>
							<?php if ($update_stat == true): ?>
								<div class="updates-message">
									<h5 class="col-success">
										Updates installed successfully :)
									</h5>
									<p>
										Your site has been successfully updated [v<?php echo($new_version); ?>]. <br><br> Now you can go to the control panel or to your account and start using the new features of your web application.
										You can view the entire list and changelogs of updates at any time in the <strong>Changelogs</strong> page of your website admin panel
									</p>

									<a class="updates-primary-btn-wrapper" href="<?php echo $site_url; ?>">
										<div class="updates-primary-btn btn-success" type="submit" data-section-node="update" data-status="none">
											Click here to check new updates
										</div>
									</a>	
								</div>
							<?php else: ?>
								<div class="updates-message">
									<h5>
										You are up to date!
									</h5>
									<p>
										ColibriSM Script v<?php echo($new_version); ?>  is currently the newest version available. <br><br>

										If you have any questions about this or you do not understand something, then feel free to contact our support team at this email address: <a href="mailto:mansurtl.contact@gmail.com">mansurtl.contact@gmail.com</a>
									</p>

									<a class="updates-primary-btn-wrapper" href="<?php echo $site_url; ?>">
										<div class="updates-primary-btn" type="submit" data-section-node="update" data-status="none">
											Ok, Launch the application
										</div>
									</a>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>

