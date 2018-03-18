<?php
/*
 *	Image management functions
 */

 $libpath = realpath(dirname( __FILE__ ).'/../../amazon-s3-and-cloudfront');

 require_once $libpath . '/vendor/Aws2/vendor/autoload.php';
 use DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\S3Client;

class ASCF_Images {

	public $count = 100;

	public function __construct() {
		if( !isset($_SESSION['ascf_offset']) ){
			$_SESSION['ascf_offset'] = 0;
		}
	}

	function copy_all() {

    $query_images_args = array(
        'post_type' => 'attachment',
        'post_mime_type' =>'image',
        'post_status' => 'inherit',
        //'posts_per_page' => -1,
				'posts_per_page' => $this->count,
				'offset'         => $_SESSION['ascf_offset'],
		    'order'          => 'DESC',
		    'orderby'        => 'ID'
    );

    $query_images = new WP_Query( $query_images_args );
    $this->images = array();

    //delete original images
    echo '<hr> Copy images from '.$_SESSION['ascf_offset'].' count '.$this->count.' <br><br>';
    echo '<hr> Files copied: <br><br>';
    $sumSizes = 0;
    $count = 0;
    $total_count = 0;

		$upload_dir = wp_upload_dir();

		$s3client = S3Client::factory(array(
			'credentials' => [
				'key' => DBI_AWS_ACCESS_KEY_ID,
				'secret' => DBI_AWS_SECRET_ACCESS_KEY
			],
			'region' => '',
			'version' => 'latest'
		));

    foreach ( $query_images->posts as $image_post) {
			$count++;

			$data = get_post_meta( $image_post->ID, '_wp_attachment_metadata', true );
			echo $image_post->ID.': '.$data['file'].': choosed';
			echo '<br>';

			if(!file_exists($upload_dir['basedir'].'/'.$data['file'])) {
				continue;
			}

			$prefix = pathinfo($data['file'], PATHINFO_DIRNAME);
			$file_paths = self::get_attachment_file_paths($image_post->ID, false, $data);

			foreach ($file_paths as $size => $file_path) {

				if(!file_exists($file_path)) {
					continue;
				}

		    try {
		      $result = $s3client->putObject(array(
		        'Bucket'     => ASCF_AWS_S3_BUCKET,
		        'Key'        => $prefix.'/'.basename($file_path),
		        'SourceFile' => $file_path,
						'ACL'        => 'public-read',
		      ));
		    } catch ( Exception $e ) {
		      echo $e->getMessage();
		    }

				$total_count++;
			}

			$s3object   = array(
				'region' => '',
				'bucket' => ASCF_AWS_S3_BUCKET,
				'key'    => $data['file'],
				'acl'    => 'public-read'
			);

			delete_post_meta( $image_post->ID, 'amazonS3_info' );

			add_post_meta( $image_post->ID, 'amazonS3_info', $s3object );

			// copy additional files
			$file_paths = self::get_attachment_file_paths($image_post->ID, false, $data);

			echo $image_post->ID.': '.$data['file'].': processed';
			echo '<br>';

  	}

		$_SESSION['ascf_offset'] += $count;

    echo '<br>';
    echo 'Different images: '.($count).' <br>';
    echo 'Total sizes of images: '.($total_count).' <br>';
    echo 'Next offset: '.($_SESSION['ascf_offset']).' <br>';
    echo '<br>';
    echo '<hr>';
    echo '<br>';
    echo '<br>';
	}



	public static function get_attachment_file_paths( $attachment_id, $exists_locally = true, $meta = false, $include_backups = true ) {
		$file_path = get_attached_file( $attachment_id, true );
		$paths     = array(
			'original' => $file_path,
		);

		if ( ! $meta ) {
			$meta = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
		}

		if ( is_wp_error( $meta ) ) {
			return $paths;
		}

		$file_name = wp_basename( $file_path );

		// If file edited, current file name might be different.
		if ( isset( $meta['file'] ) ) {
			$paths['file'] = str_replace( $file_name, wp_basename( $meta['file'] ), $file_path );
		}

		// Thumb
		if ( isset( $meta['thumb'] ) ) {
			$paths['thumb'] = str_replace( $file_name, $meta['thumb'], $file_path );
		}

		// Sizes
		if ( isset( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $size => $file ) {
				if ( isset( $file['file'] ) ) {
					$paths[ $size ] = str_replace( $file_name, $file['file'], $file_path );
				}
			}
		}

		$backups = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );

		// Backups
		if ( $include_backups && is_array( $backups ) ) {
			foreach ( $backups as $size => $file ) {
				if ( isset( $file['file'] ) ) {
					$paths[ $size ] = str_replace( $file_name, $file['file'], $file_path );
				}
			}
		}

		// Allow other processes to add files to be uploaded
		$paths = apply_filters( 'as3cf_attachment_file_paths', $paths, $attachment_id, $meta );

		// Remove duplicates
		$paths = array_unique( $paths );

		// Remove paths that don't exist
		if ( $exists_locally ) {
			foreach ( $paths as $key => $path ) {
				if ( ! file_exists( $path ) ) {
					unset( $paths[ $key ] );
				}
			}
		}

		return $paths;
	}


}
