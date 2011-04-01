<? Plex\View::insert("Plex.header"); ?>
		<div class="content">
			<h1>A Script Error Was Encountered</h1><br>
			<b>Severity: </b><?= $severity; ?><br>
			<b>File: </b><?= $file; ?><br>
			<b>Line: </b><?= $line; ?><br>
			<b>Message: </b><?= $message; ?><br>
		</div>
		<div class="content">
			<h1>Error Backtrace</h1><br>
			<?php foreach($backtraces as $backtrace):?>
				<?if(isset($backtrace['file'])):?>
				<b>File: </b><?=@$backtrace['file'];?><br>
				<?endif;?>
				<?if(isset($backtrace['line'])):?>
				<b>File: </b><?=@$backtrace['line'];?><br>
				<?endif;?>
				<b>Function: </b><?=$backtrace['function'];?><br>
					<?php if(isset($backtrace['class'])):?>
						<b>Class: </b><?=$backtrace['class'];?><br>
					<?php endif;?>
					<?php if(isset($backtrace['args'])):?>
						<b>Arguments:</b><br>
							<ol>
								<?php foreach($backtrace['args'] as $arg):?>
									<li><?= is_bool($arg) ? $arg?'True':'False':is_array($arg) ? print_r($arg,1):$arg; ?></li>
								<?php endforeach;?>
							</ol>
					<?php endif;?>
					<hr><br>
			<?php endforeach;?>
<? Plex\View::insert("Plex.footer"); ?>