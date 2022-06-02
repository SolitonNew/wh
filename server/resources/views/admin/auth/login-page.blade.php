@extends('index')

@section('body')
<script>
    window.addEventListener('load', function () {
        window.location.href = '{{ route('admin') }}';
    });
</script>
@endsection()