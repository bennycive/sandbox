@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        toast:true,
        text: '{{ session('success') }}',
        confirmButtonColor: '#3085d6'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        toast:true,
        title: 'Login Failed',
        text: '{{ session('error') }}',
        confirmButtonColor: '#d33'
    });
</script>
@endif
