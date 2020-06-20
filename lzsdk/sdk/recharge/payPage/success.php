<script>

    let bundle;
    bundle = "<?php echo $_GET['bundle'];?>";

    (function(){
           doBack();
    })();

    function doBack() {
        try{
            window.closeMyself();
        }catch (e) {
            window.parent.postMessage({call:'showNormal'},'*');
            if(bundle){
                location.href = bundle+"://";
            }
        }
    };
    
</script>
