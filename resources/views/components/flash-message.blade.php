@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.toast) {
                window.toast.success({!! json_encode((string) session('success')) !!});
            }
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.toast) {
                window.toast.error({!! json_encode((string) session('error')) !!});
            }
        });
    </script>
@endif

@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.toast) {
                window.toast.warning({!! json_encode((string) session('warning')) !!});
            }
        });
    </script>
@endif

@if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.toast) {
                window.toast.info({!! json_encode((string) session('info')) !!});
            }
        });
    </script>
@endif
