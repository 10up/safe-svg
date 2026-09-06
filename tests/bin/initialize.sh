#!/bin/bash
wp-env run tests-wordpress chmod -c ugo+w /var/www/html
wp-env run tests-cli wp rewrite structure '/%postname%/' --hard

# Turn off the block editor welcome guide.
wp-env run tests-cli wp user meta update admin wp_persisted_preferences '{"core/edit-post":{"welcomeGuide":false},"core/edit-site":{"welcomeGuide":false,"welcomeGuideStyles":false}}' --format=json
