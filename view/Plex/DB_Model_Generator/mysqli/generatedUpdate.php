/**
 * Generated Update Call (<?=$database;?> / <?=$table;?>)
 *
 * @author DB_Model_Generator
 */
 
$statment = DB::prepare(<?=$connection;?>, '<?=$query;?>');

/**
 *	New Values
 */
<? foreach($columns as $column): ?>
$statment->bind_param($input['<?=$column['Field'];?>'],'<?=$column['Field_Type'];?>'); 		// <?=$column['Field'];?>

<? endforeach; ?>

/**
 *	Unique Primary Keys
 */
<? foreach($primaryKeys as $primaryKey): ?>
$statment->bind_param($input['<?=$primaryKey['Field'];?>'],'<?=$primaryKey['Field_Type'];?>'); 		// <?=$primaryKey['Field'];?>

<? endforeach; ?>

if(!$statment->execute()) {
	if($errorOut) {
		Error::show("Database Error","Unable To Update Row In <?=$table;?>");
	} else {
		return false;
	}
}