<?php
    get_header();
    // plugin directory echo plugin_dir_url( __FILE__ );
?>
<div id="primary">
		<div id="content" role="main">
        <?php
        if ( is_user_logged_in() ): ?>
			<h1 class="entry-title">Legalease Answer Form</h1>
				
			<?php //the_content(); ?>
				
            <form action="" method="post">
                <input name="legalese_answer_value" type="text" maxlength="40" pattern="^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$" value="<?php the_field('legalese_answer', 'user_'. get_current_user_id() ); ?>"></input>
                <button type="submit">Update Answer</button>
            </form>
        <?php else: ?>
            <h1 class="entry-title">Login to View Form</h1>
        <?php endif; ?>
		</div>
	</div>


<?php
get_footer();
?>