<? 
namespace Plex;
View::insert("Plex.header"); 
?>
		<div class="content">
			<h1><?= $title; ?></h1>
			<?= $body; ?>
		</div>
<? View::insert("Plex.footer"); ?>