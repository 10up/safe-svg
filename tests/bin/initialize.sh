#!/bin/bash
wp-env run tests-wordpress chmod -c ugo+w /var/www/html
wp-env run tests-cli wp rewrite structure '/%postname%/' --hard
