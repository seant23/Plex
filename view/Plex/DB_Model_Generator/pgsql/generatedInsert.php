/**
 * Generated Insert Call (<?=$database;?> / <?=$table;?>)
 *
 * @author DB_Model_Generator
 */
 
$statment = DB::prepare(<?=$connection;?>, '<?=$query;?>');

<? foreach($columns as $column): ?>
$statment->bind_param($input['<?=$column['column_name'];?>'],'<?=$column['Field_Type'];?>'); 		// <?=$column['column_name'];?>

<? endforeach; ?>

if(!$statment->execute()) {
	if($errorOut) {
		Error::show("Database Error","Unable To Add Row To <?=$table;?>");
	} else {
		return false;
	}
}