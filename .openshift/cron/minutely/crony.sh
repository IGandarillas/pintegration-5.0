 #!/bin/bash
php $OPENSHIFT_DATA_DIR/artisan schedule:run 1>> /dev/null 2>&1
