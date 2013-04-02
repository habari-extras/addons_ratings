<?php

namespace habari;

class AddonsRatingsPlugin extends Plugin
{
	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation( $file )
	{
		DB::register_table( 'ratings' );

		$sql = <<< SQL
CREATE TABLE {ratings} (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	post_id INT NOT NULL,
	rating TINYINT NOT NULL,
	updated INT NOT NULL,
	version_id INT DEFAULT NULL,
	user_id int DEFAULT NULL,
	ip INT( 10 ) NOT NULL,
	UNIQUE INDEX ( post_id , ip ),
	UNIQUE INDEX ( post_id , user_id )
);
SQL;

		DB::dbdelta( $sql );
	}

	public function filter_block_list( $blocklist )
	{
		$blocklist[ 'ratings' ] = _t( 'Ratings' );
		return $blocklist;
	}

	/**
	 * Add target site and checkout forms to cart block
	 */
	public function action_block_content_ratings( $block, $theme )
	{
		$post_id = intval($theme->post->id);
		$block->average_rating = 20 * DB::get_value('SELECT AVG(rating) FROM {ratings} WHERE post_id = :post_id', array('post_id' => $post_id));
		$pcts = DB::get_keyvalue('SELECT rating, COUNT(*) as rating_count FROM {ratings} WHERE post_id = :post_id GROUP BY rating', array('post_id' => $post_id));
		for($z = 1;$z <=5;$z++) {
			$pcts[(string)$z] = isset($pcts[$z]) ? $pcts[$z] : 0;
		}
		$block->rating_pct = $pcts;
		if(User::identify()->loggedin) {
			$block->your_rating = DB::get_value('SELECT COALESCE(rating,0) FROM {ratings} WHERE post_id = :post_id AND user_id = :user_id', array('post_id' => $post_id, 'user_id' => User::identify()->id));
		}
		else {
			$block->your_rating = DB::get_value('SELECT COALESCE(rating,0) FROM {ratings} WHERE post_id = :post_id AND ip = :ip', array('post_id' => $post_id, 'ip' => $ip = sprintf( '%u', ip2long( $_SERVER['REMOTE_ADDR'] ) )));
		}
	}

	public function action_init()
	{
		DB::register_table( 'ratings' );
		$this->add_template('block.ratings', dirname(__FILE__) . '/block.ratings.php');
	}

	public function action_ajax_set_rating()
	{
		$sql = <<< SQL_SET
INSERT INTO {ratings} (post_id, rating, updated, user_id, ip) VALUES (:post_id, :rating, :updated, :user_id, :ip) ON DUPLICATE KEY UPDATE rating = :rating, updated = :updated;
SQL_SET;

		DB::query($sql, array(
			'post_id' => $_POST['post_id'],
			'rating' => $_POST['rating'],
			'updated' => time(),
			'user_id' => User::identify()->loggedin ? User::identify()->id : 0,
			'ip' => sprintf( '%u', ip2long( $_SERVER['REMOTE_ADDR'] ) ),
		));

		$ar = new AjaxResponse(200, 'Your rating has been recorded.');
		$ar->out();
	}

}

?>