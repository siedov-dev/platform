@include("common.blocks.loading-dots")
<script>
    if (window.self !== window.top) {
        parent.postMessage('closetFrameInModal', "*");
    } else {
        window.close();
        //if window doesn't close redirect to main
        setTimeout(() => {
            window.location.href = "{{$url?? '/'}}";
        }, 1000);
    }
</script>
