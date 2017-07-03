<?php
include 'ada_pack.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dinos = json_decode(file_get_contents('dinos.json'), true);

function get_next_version($current_version){
	global $dinos;
	
	if ( $current_version == null )
		return '';

	$dino_key = array_search($current_version, $dinos, true);
	if ( !$dino_key ){
		return 'unknown species';
	}
	if ( isset($dinos[$dino_key + 1]))
		return $dinos[$dino_key + 1];
	return "this species has not been revealed yet";
}

$html = file_get_contents('versions.html');

$projects = isset($_COOKIE["saved_projects"]) ? json_decode($_COOKIE["saved_projects"], true) : array();

$project_name = (isset($_AR['pname']) && (strlen($_AR['pname'])) > 0) ? $_AR['pname'] : 'Select project';

if (!isset($_COOKIE["saved_projects"])) {
	$project_name = "Create your first project";
}

if ( isset($_AR['pname'] ) ) {
	if ( !array_key_exists($_AR['pname'], $projects) ) {
		$projects[$project_name] = $dinos[1];
	}	
}	

$last_version = isset($projects[$project_name]) ? $projects[$project_name] : null;

if ( $last_version != null ) {
	if ( isset($_AR['deploy']) ) {
		$last_version = get_next_version($last_version);
		$projects[$project_name] = $last_version;
	}
	$new_version = get_next_version($last_version);
	$html = str_replace('%PROJECT_INFO%', '<h2>Current version %CURRENT_VERSION%</h2><h2>Next version %NEW_VERSION%</h2><a href="%DEPLOY_URL%"><button class="deploy">DEPLOY</button></a>', $html);
} else {
	$new_version = $last_version = "will be displayed here";
	$html = str_replace('%PROJECT_INFO%', '', $html);
}

setcookie("saved_projects", json_encode($projects), time() + (10 * 365 * 24 * 60 * 60));

if ( isset($_AR['deploy']) ) {
	header("Location: " . strtok($_SERVER["REQUEST_URI"],'?') . '?pname=' . $project_name);
}

$html = str_replace('%PROJECT_NAME%', $project_name, $html);
$html = str_replace('%CURRENT_VERSION%', $last_version, $html);
$html = str_replace('%NEW_VERSION%', $new_version, $html);
$html = str_replace('%SCRIPT_PATH%', $_SERVER['REQUEST_URI'], $html);
$html = str_replace('%DEPLOY_URL%', strtok($_SERVER["REQUEST_URI"],'?') . '?deploy=t&pname=' . $project_name, $html);

$html_project_list = "";
foreach ($projects as $project => $project_version) {
	if ( $project == $project_name )
		continue;
    $html_project_list .= '<li><a href="' . strtok($_SERVER["REQUEST_URI"],'?') . '?pname=' . $project . '">' . $project . ' <i>(' . $project_version . ')</i></a></li>';
}
if (strlen($html_project_list) > 0 ){
	$html_project_list = "<h3>Your projects:</h3><ul>" . $html_project_list . "</ul>";
}

$html = str_replace('%PROJECTS_LIST%', $html_project_list, $html);

print($html);
?>