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
$statment->bind_param($input['<?=$column['column_name'];?>'],'<?=$column['Field_Type'];?>'); 		// <?=$column['column_name'];?>

<? endforeach; ?>

/**
 *	Unique Primary Keys
 */
<? foreach($primaryKeys as $primaryKey): 
   $keys = explode(',', $primaryKey['column_list']); 
   foreach ($keys as $key) :
?>
$statment->bind_param($input['<?=trim($key);?>'],'s'); 		// <?=$key;?>

<? endforeach; ?>
<? endforeach; ?>

if(!$statment->execute()) {
	if($errorOut) {
		Error::show("Database Error","Unable To Update Row In <?=$table;?>");
	} else {
		return false;
	}
} 