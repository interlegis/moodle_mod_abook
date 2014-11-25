<style type="text/css">
	.abwallpaper {
		background-image: url('<?php echo $OUTPUT->pix_url('splash', 'mod_abook'); ?>');
	}
	.abboard {
		height: 33%
	}
	</style>

<div class="panel-heading">
	<h3 id="titlepanel" class="panel-title"><?php echo $data['title']; ?></h3>
</div>
<div id="wallpaper" class="panel-body abwallpaper" style="<?php echo ($slide->frameheight > 0) ? "height: {$data['frameheight']}px;" : "";?> ">
	<div id="content" class="panel-body abboard <?php echo $data['contentanimation']; ?>" style="background-image: url('<?php echo $boardpix; ?>'); <?php if ($data['boardheight'] > 0) { echo "height: {$data['boardheight']}px;";} ?>">
		<?php echo $data['content']; ?>
	</div>
	<div id="content1" class="panel-body abboard <?php echo $data['contentanimation']; ?>" style="background-image: url('<?php echo $boardpix; ?>'); <?php if ($data['boardheight'] > 0) { echo "height: {$data['boardheight']}px;";} ?>">
		<?php echo $data['content1']; ?>
	</div>
	<div id="content2" class="panel-body abboard <?php echo $data['contentanimation']; ?>" style="background-image: url('<?php echo $boardpix; ?>'); <?php if ($data['boardheight'] > 0) { echo "height: {$data['boardheight']}px;";} ?>">
		<?php echo $data['content2']; ?>
	</div>
</div>
<div id="slidenavbar" class="panel-footer abfooter">
	<div class="btn-group" role="group" aria-label="navbar">
		<?php echo $data['navigation']; ?>
	</div>
</div>
