#!/usr/local/bin/php
<?php

chdir(dirname(__FILE__));
include '../Plex_cli.php';
Error::$style=Error::LOOSE;

class Main {
	
	public function init() {
		
	}
	
	public function generateCreateService() {
		
	}
	
	
	
}



foreach($config as $model=>$config) {
	$modelNice = ucwords(str_replace('_', ' ', $model));
	$modelName = str_replace(' ', '_', $modelNice);
	
	CLI::startMSG(" * Creating Model ($modelName)");
	
	if(file_exists("../model/__$modelName.php")) {
		CLI::finishMSG("FILE ALREADY EXISTS", CLI::RED);
	} else {
		
		$add = isset($config['add']) ? 'noc.'.$config['add'] : false;
		$update = isset($config['update']) ? 'noc.'.$config['update'] : false;
		
		$modelFile = DB_PHP_Generator::modelCode($model, $add, $update);
		
		file_put_contents("../model/__$modelName.php", $modelFile);
		
		CLI::finishMSG("OK");		
	}
	
	if(isset($config['Web_Service'])) {
		if(!file_exists("../handlers/Web_Service/$modelName")) {
			mkdir("../handlers/Web_Service/$modelName");
		}
		
		
			if(isset($config['Web_Service']['Create']) && $config['Web_Service']['Create']) {
				$createService = <<<PHP
<?php

/**
 * Create $modelNice
 *
 * @author sthayne
 * @package Web_Service
 * @todo Lock Script
 */

///////////////////////////////////
############## LOCK ###############
///////////////////////////////////

//CM_Lock::load(script_ID);
//CM_Lock::has_access('action_required') or CM_Lock::bounce();

///////////////////////////////////
############# CREATE ##############
///////////////////////////////////

new Model_Create(new __$modelName, false, true);
PHP;
				
				
				$wsFile = "../handlers/Web_Service/$modelName/Create.php";
				CLI::colorEcho("  - Creating Web Service ($modelName.Create) ", false, CLI::WHITE, CLI::BLACK, true);
				if(!file_exists($wsFile)) {
					file_put_contents($wsFile, $createService);
					CLI::finishMSG("OK");
				} else {
					CLI::finishMSG("FILE ALREADY EXISTS", CLI::RED);
				}
			}
			if(isset($config['Web_Service']['Update']) && $config['Web_Service']['Update']) {
				$createService = <<<PHP
<?php

/**
 * Update $modelNice
 *
 * @author sthayne
 * @package Web_Service
 * @todo Lock Script
 */

///////////////////////////////////
############## LOCK ###############
///////////////////////////////////

//CM_Lock::load(script_ID);
//CM_Lock::has_access('action_required') or CM_Lock::bounce();

///////////////////////////////////
############# UPDATE ##############
///////////////////////////////////

new Model_Update(new __$modelName, false, true);
PHP;
				
				
				$wsFile = "../handlers/Web_Service/$modelName/Update.php";
				CLI::colorEcho("  - Creating Web Service ($modelName.Update) ", false, CLI::WHITE, CLI::BLACK, true);
				if(!file_exists($wsFile)) {
					file_put_contents($wsFile, $createService);
					CLI::finishMSG("OK");
				} else {
					CLI::finishMSG("FILE ALREADY EXISTS", CLI::RED);
				}
			}
			if(isset($config['Web_Service']['Search']) && $config['Web_Service']['Search']) {
				$createService = <<<PHP
<?php

/**
 * Search $modelNice
 *
 * @author sthayne
 * @package Web_Service
 * @todo Lock Script
 */

///////////////////////////////////
############## LOCK ###############
///////////////////////////////////

//CM_Lock::load(script_ID);
//CM_Lock::has_access('action_required') or CM_Lock::bounce();

///////////////////////////////////
############# SEARCH ##############
///////////////////////////////////

\$this->search(new Model_Search(new __$modelName));
PHP;
				
				
				$wsFile = "../handlers/Web_Service/$modelName/Search.php";
				CLI::colorEcho("  - Creating Web Service ($modelName.Search) ", false, CLI::WHITE, CLI::BLACK, true);
				if(!file_exists($wsFile)) {
					file_put_contents($wsFile, $createService);
					CLI::finishMSG("OK");
				} else {
					CLI::finishMSG("FILE ALREADY EXISTS", CLI::RED);
				}
			}
		}
	
}
