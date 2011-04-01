<?="<?php";?>

namespace Plex;

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

$this->search(new Model_Search(new __<?=$modelName;?>));