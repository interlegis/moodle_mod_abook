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
<div id="wallpaper" class="panel-body abwallpaper" style="<?php if ($data['frameheight'] > 0) { echo "height: {$data['frameheight']};";}?> ">
	<div id="content" class="panel-body abboard <?php echo $data['contentanimation']; ?>" style="background-image: url('<?php echo $data['boardpix']; ?>'); <?php if ($data['boardheight'] > 0) { echo "height: {$data['boardheight']};";} ?>">
		<?php echo $data['content']; ?>
	</div>
	<div id="content1" class="panel-body abboard <?php echo $data['contentanimation1']; ?>" style="background-image: url('<?php echo $data['boardpix1']; ?>'); <?php if ($data['boardheight1'] > 0) { echo "height: {$data['boardheight1']};";} ?>">
		<?php echo $data['content1']; ?>
	</div>
	<div id="content2" class="panel-body abboard <?php echo $data['contentanimation2']; ?>" style="background-image: url('<?php echo $data['boardpix2']; ?>'); <?php if ($data['boardheight2'] > 0) { echo "height: {$data['boardheight2']};";} ?>">
		<?php echo $data['content2']; ?>
	</div>
</div>
<div id="slidenavbar" class="panel-footer abfooter">
	<div class="btn-group" role="group" aria-label="navbar">
		<?php echo $data['navigation']; ?>
	</div>
</div>