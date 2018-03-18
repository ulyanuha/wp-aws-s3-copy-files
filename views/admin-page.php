<div class="wrap">
    <h2>AWS S3 Copy Files</h2>
    <?php if (!empty($message)): ?>
        <p class="error" style="margin-top:10px;">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <?php if (count(get_settings_errors()) > 0) : ?>
        <div class="error">
            <?php
            foreach (get_settings_errors() as $errorArr) {
                echo $errorArr['message']
                . '<br>' . "\n";
            };
            ?>
        </div>
    <?php endif; ?>

    <p>
        This plugin copies files to aws s3 bucket. Works with WP Offload S3 Lite plugin.
        <br/>
        No images will be actually removed from server, you have to remove them yourself after you check that everything works.
    </p>

    <p>
      1. Install WP Offload S3 Lite plugin.
      <br/><br/>
      2. Configure AWS:
      <br/>
      https://kinsta.com/knowledgebase/wordpress-amazon-s3/
      <br/><br/>
      3. Add bucket name to constant in wp_config:
      <br/>
      define( 'ASCF_AWS_S3_BUCKET', 'your-bucket-name');
    </p>

    <p>
      Script processes 100 original images plus all of its sizes.
      Then it increases offset and ou are ready to go with the next 100.
      If you want to change offset - please use the form with "set new offset" submit.
      Offset number exist only until your logout.
    </p>

    <hr>

    <br>

    <form method="post" action="#" id="awss3_copy_images" >
        <input type="hidden" name="awss3_copy_images" value="copy" />
        <?php submit_button('Copy all files to s3'); ?>
    </form>

    <br>
    <hr>
    <br>

    <form method="post" action="#" id="awss3_offset" >
        <input type="type" name="awss3_offset" value="<?php echo $_SESSION['ascf_offset'] ?>" />
        <?php submit_button('Set new offset'); ?>
    </form>

</div>
