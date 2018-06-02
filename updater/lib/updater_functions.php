<?php

$updater_general_setting_checkboxes = array("updater_dm"=>0, "updater_status"=>0);
$updater_general_setting_input_fields = array("updater_notification_preference", "updater_frequency", "updater_dm", "updater_status");
/**
* @param $settingName, @param $settingValue
* @return bool 
**/
function setUpdaterSetting($settingName, $settingValue) {
$sql = "SELECT name FROM updater_settings WHERE name='$settingName'";
$query = sqlQ($sql);
$rows = sqlNumRows($query);
if ($rows == 0) {
//INSERT THE UPDATER SETTING
$bindArray = array($settingName, $settingValue);	
	if (sqlStatement("INSERT INTO `updater_settings`(`name`, `value`) VALUES (?,?)", $bindArray)) {
		return 1;
	}
	else {
		return 0;
	}

}
else {
//UPDATE THE UPDATER SETTING
	if (sqlStatement("UPDATE `updater_settings` SET `value`='$settingValue' WHERE name='$settingName'")) {
		return 1;
	}
	else {
		return 0;
	}
}

}


function getUpdaterSetting($settingName){
	$sql = "SELECT * FROM updater_settings WHERE name='$settingName'";
	$query = sqlQ($sql);
	$rows = sqlNumRows($query);
	if ($rows == 0) {
		return "empty_setting";
	}
	else {
		$row = sqlFetchArray($query);
		return $row['value']; 
	}

}


function internet_bool()
{
    $connected = @fsockopen("www.github.com", 80); 
                                        
    if ($connected){
        $is_conn = true; 
        fclose($connected);
    }else{
        $is_conn = false; 
    }
    return $is_conn;

}

function curl_bool() {
    if  (in_array  ('curl', get_loaded_extensions())) {
        return true;
    }
    else {
        return false;
    }
}

function file_permissions_bool($directory){
	if (is_writable($directory)) {
		return true;
	}
	else {
		return false;
	}

}
/**
* delete all data from the download table before making a download for another update
*/
function deleteDownloadFileDbEntry() {
	sqlStatement("DELETE FROM `updater_user_mode_download_entry` WHERE 1");
}
/**
*delete all data from the backup table before making a backup
*
*/
function deleteBackupFileDbEntry() {
	sqlStatement("DELETE FROM `updater_user_mode_backup_entry` WHERE 1");
}

/**
*@param $filename - it is combination of file sha, time stamp and the extension
*@param $status - it has four values added,modified,removed,renamed
*@param $original_name - it is location at which the downloaded file must be replaced
*@param $old_name - in case if some file was renamed, it will have the old_name, else it holds the value of empty, for renamed files we need to backup, delete the old file name and copy the new file name to the location
*/
function downloadFileDbEntry($filename, $status, $original_name, $old_name) {
	//Determine the status of the file and make Necessary action according to it
	// also save the deleted file values, which will be used for replacement purposes
	$bindArray = array($filename, $status, $original_name, $old_name);
	sqlStatement("INSERT INTO `updater_user_mode_download_entry`(`filename`, `status`, `original_name`, `old_name`) VALUES (?,?,?,?)", $bindArray);

}


/**
*@param $filename - the filename which consists of sha, time and extension 
*@param $old_name - in case if the status says as renamed we need to copy the old file to the backup
*@param $status - it has four values - added, modified, removed, renamed.
*if status is added then no backup is not added
*@param $original_name is the file at which the changes occured
*depending upon the status the backup is determined
*/
function backupFile($foldername, $filename, $original_name, $status, $old_name) {
	if ($status == "renamed") {
		// since it is renamed there is no file exists at the original file name location
		copy($old_name, $foldername."/".$file_name);
	}
	if ($status == "added") {
		//do nothing since the file does not exists
	}
	if ($status == "modified" || $status == "removed") {
		copy("../".$original_name, $foldername."/".$filename);
	}

}


function backupFileDbEntry($filename, $status, $original_name, $old_name) { 
	//Determine the status of the file and make Necessary action according to it
	// also save the deleted file values, which will be used for replacement purposes
	//everything must be recorded here
	$bindArray = array($filename, $status, $original_name, $old_name);
	sqlStatement("INSERT INTO `updater_user_mode_backup_entry`(`filename`, `status`, `original_name`, `old_name`) VALUES (?,?,?,?)", $bindArray);
}
?>
