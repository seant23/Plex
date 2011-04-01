/**
 * Generated Delete Call (<?=$database;?> / <?=$table;?>)
 *
 * @author DB_Model_Generator
 */
 
$statment = DB::prepare(<?=$connection;?>, '<?=$query;?>');

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
		Error::show("Database Error","Unable To Delete Row From <?=$table;?>");
	} else {
		return false;
	}
} 