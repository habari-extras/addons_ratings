<li id="ratings_block">
	<span class="rate_title">Ratings</span>
	<hr>
	<div style="margin-left:55px;" data-average-rating="<?php echo $content->average_rating; ?>">
		<?php
		for ($z = 0; $z < 5; $z++):
			if ( $content->average_rating <= $z * 20 || 0 == $content->average_rating) {
				$class1 = '';
				$class2 = 'zero';
			}
			elseif ( $content->average_rating > $z * 20 && $content->average_rating < $z * 20 + 10 ) {
				$class1 = '';
				$class2 = 'fifty';
			}
			else {
				$class1 = 'hide';
				$class2 = 'hundred';
			}
			?>
			<div class="rating">
				<i class="icon-rating bottom <?php echo $class1; ?>">s</i>
				<i class="icon-rating top"><span class="amount <?php echo $class2; ?>">s</span></i>
			</div>
		<?php endfor; ?>
	</div>

	<ol>
		<?php for ($z = 5; $z > 0; $z--): ?>
			<li>
				<span class="number"><?php echo $z; ?> Stars</span>
				<span class="bar"><span class="color"
																style="width: <?php echo $content->rating_pct[$z]; ?>%;"></span></span>
			</li>
		<?php endfor; ?>
	</ol>

	<span class="rate_title">My Rating</span>
	<hr>
	<div id="my_rating" data-rating="<?php echo $content->your_rating; ?>">
		<?php
		for ($z = 0; $z < 5; $z++):
			if ( $content->your_rating < $z + 1 || 0 == $content->your_rating) {
				$class1 = '';
				$class2 = 'zero';
			}
			else {
				$class1 = 'hide';
				$class2 = 'hundred';
			}
			?>
			<div class="rating" data-value="<?php echo $z+1; ?>">
				<i class="icon-rating bottom <?php echo $class1; ?>">s</i>
				<i class="icon-rating top"><span class="amount <?php echo $class2; ?>">s</span></i>
			</div>
		<?php endfor; ?>
	</div>
</li>


<script>
	$(function(){
		$('body').on('click', '#my_rating .rating', function(){
			$.post(
				'<?php echo \Habari\URL::ajax('set_rating'); ?>',
				{
					rating: $(this).data('value'),
					post_id: <?php echo $theme->post->id; ?>,
				},
				function(){
					$('#ratings_block').load(window.location.href + ' #ratings_block > *');
				}
			)
		});
	});
</script>