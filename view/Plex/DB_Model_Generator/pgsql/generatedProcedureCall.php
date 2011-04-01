/**
 * Procedure Call (<?=$database;?>} / <?=$name;?>)
 */
$statment = DB::prepare(<?=connection;?>, '<?=$statement;?>');

<? foreach($params as $param): ?>
$statment->bind_param($input['<?=$param['key'];?>'],'<?=$param['type'];?>'); 		// <?=$param['name'];?>
<? endforeach; ?>

if(!$statment->execute()) {
	if($errorOut) {
		Error::show("Database Error","Unable To <?=$errorMsg;?>");
	} else {
		return false;
	}
}