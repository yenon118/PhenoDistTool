@php
include resource_path() . '/views/system/config.blade.php';

$organism = $info['organism'];

@endphp


@extends('system.header')


@section('content')

<div class="title1">Phenotype Distribution Tool</div>
<br />

<br />
<p>Phenotype Distribution Tool is not available for this organism.</p>
<br />
<br />
<br />
<br />

@endsection


@section('javascript')

<script type="text/javascript">
</script>

@endsection