<style type="text/css">
	.abboard {
		height: 100%
	}
</style>

<div class="panel-heading">
	<h3 id="titlepanel" class="panel-title"><?php echo $data['title']; ?></h3>
</div>
<div id="wallpaper" class="panel-body abwallpaper" style="<?php if ($data['frameheight'] > 0) { echo "height: {$data['frameheight']};";}?> ">
	<div id="content" class="panel-body abboard <?php echo $data['contentanimation']; ?>" style="background-image: url('<?php echo $data['boardpix']; ?>'); <?php if ($data['boardheight'] > 0) { echo "height: {$data['boardheight']};";} ?>">
		<?php echo $data['content']; ?>
	</div>
</div>
<div id="slidenavbar" class="panel-footer abfooter">
	<div class="btn-group" role="group" aria-label="navbar">
		<?php echo $data['navigation']; ?>
	</div>
</div>