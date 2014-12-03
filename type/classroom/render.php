<style type="text/css">
	.abwallpaper {
		background-image: url('<?php echo $OUTPUT->pix_url('wallpaper', 'mod_abook'); ?>');
		min-height: 400px;
	}
</style>

<div class="panel-heading">
	<h3 id="titlepanel" class="panel-title"><?php echo $data['title']; ?></h3>
</div>
<div id="wallpaper" class="panel-body abwallpaper" style="background-image: url('<?php echo $data['wallpaper']; ?>'); <?php echo ($slide->frameheight > 0) ? "height: {$data['frameheight']}px;" : "";?> ">
	<div class="panel panel-default">
		<div id="content" class="panel-body abboard <?php echo $data['contentanimation']; ?>" style="background-image: url('<?php echo $boardpix; ?>'); <?php if ($data['boardheight'] > 0) { echo "height: {$data['boardheight']}px;";} ?>">
			<?php echo $data['content']; ?>
		</div>
	</div>
	<div id="floorpanel" class="abfloor <?php echo $data['footerpos'].' '.$data['footeranimation']; ?>">
		<img id="floorpix" class="img-responsive" src="<?php echo $data['footerpix']; ?>"/>
	</div>
	<div id="teacherpanel" class="abteacher <?php echo $data['teacherpos'].' '.$data['teacheranimation']; ?>">
		<img id="teacherpix" class="img-responsive" src="<?php echo $data['teacherpix']; ?>"/>
	</div>
</div>
<div id="slidenavbar" class="panel-footer abfooter">
	<div class="btn-group" role="group" aria-label="navbar">
		<?php echo $data['navigation']; ?>
	</div>
</div>
