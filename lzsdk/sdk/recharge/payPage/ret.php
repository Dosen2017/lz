<?php

    file_put_contents('/tmp/CPCallbackData.txt', date('Y-m-d H:i:s') . '_' . json_encode($_POST) . "\n", FILE_APPEND);

    echo "SUCCESS";exit;
