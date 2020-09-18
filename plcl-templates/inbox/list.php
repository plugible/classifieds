<?php
$comments = $data[ 'comments' ];
?>

<table class='<?php echo apply_filters( 'plcl_table_class', '' ); ?>'>
	<tr>
		<th><?php _e( 'From' ); ?></th>
		<th><?php _e( 'To' ); ?></th>
		<th><?php _e( 'Ad' ); ?></th>
		<th><?php _e( 'Message' ); ?></th>
		<th><?php _e( 'Date' ); ?></th>
		<th><?php _e( 'Read' ); ?></th>
	</tr>

	<?php if ( ! $comments ) : ?>
		<tr>
			<td colspan="6"><?php _e( 'This folder is empty.') ?></td>
		</tr>
	<?php endif; ?>

	<?php foreach ( $comments as $comment ) : ?>
		<?php
			$comment_author     = get_userdata( get_comment_meta( $comment->comment_ID , 'comment_from', true ) )->display_name ?? '&ndash;';
			$comment_recipient  = get_userdata( get_comment_meta( $comment->comment_ID , 'comment_to', true ) )->display_name ?? '&ndash;';
			$comment_read       = get_comment_meta( $comment->comment_ID, 'comment_read', true );
			$comment_link       =  add_query_arg( plcl_get_param( 'discussion' ), plcl_encrypt( $comment->user_id ), get_comment_link( $comment ) );
			$comment_post_id    = $comment->comment_post_ID;
			$comment_post_title = get_the_title( $comment->comment_post_ID );
			$comment_excert     = $comment_read > -1
				? wp_trim_words( $comment->comment_content, 10 )
				: '🆕 ' . wp_trim_words( $comment->comment_content, 10 )
			;
			$comment_date       = mysql2date( 'U', $comment->comment_date ) < strtotime( '-1 week' )
				? ucfirst( get_comment_date( '', $comment->comment_ID ) )
				: ucfirst( sprintf( __( '%s ago' ), human_time_diff( mysql2date( 'U', $comment->comment_date ) ) ) )
			;
			if ( $comment_read != -1 ) {
				$x = date('Y-m-d H:i:s', $comment_read );
				$comment_read_date  = mysql2date( 'U', $x ) < strtotime( '-1 week' )
					? ucfirst( mysql2date( get_option( 'date_format' ), $x ) )
					: ucfirst( sprintf( __( '%s ago' ), human_time_diff( mysql2date( 'U', $x ) ) ) )
				;
			} else {
				$comment_read_date = '&ndash;';
			}
		?>
		<tr>
			<td><?php echo $comment_author; ?></td>
			<td><?php echo $comment_recipient; ?></td>
			<td>[<?php echo $comment_post_id; ?>] <?php echo $comment_post_title; ?></td>
			<td><a href="<?php echo $comment_link; ?>"><?php echo $comment_excert; ?></a></td>
			<td><?php echo $comment_date; ?></td>
			<td><?php echo $comment_read_date; ?></td>
		</tr>
	<?php endforeach; ?>	
</table>
