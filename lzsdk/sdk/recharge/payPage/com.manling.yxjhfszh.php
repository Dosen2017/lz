<script>

    let bundle;
    bundle = "<?php
        $urlBaseName = basename($_SERVER['REQUEST_URI']);
        echo substr($urlBaseName, 0, strpos(basename($urlBaseName), '.php'));
        ?>";

    (function(){
           doBack();
    })();

    function doBack() {
          location.href = bundle+"://";
    };
    
</script>
