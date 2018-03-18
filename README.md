# wp-aws-s3-copy-files


This plugin copies files to aws s3 bucket. Works with WP Offload S3 Lite plugin.
No images will be actually removed from server, you have to remove them yourself after you check that everything works.

1. Install WP Offload S3 Lite plugin.

2. Configure AWS:
https://kinsta.com/knowledgebase/wordpress-amazon-s3/

3. Add bucket name to constant in wp_config:
define( 'ASCF_AWS_S3_BUCKET', 'your-bucket-name');

Script processes 100 original images plus all of its sizes.
Then it increases offset and ou are ready to go with the next 100.
If you want to change offset - please use the form with "set new offset" submit.
Offset number exist only until your logout.
