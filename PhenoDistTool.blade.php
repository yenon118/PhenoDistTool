@php
include resource_path() . '/views/system/config.blade.php';

$organism = $info['organism'];
$dataset = $info['dataset'];

@endphp


@extends('system.header')


@section('css')

<link rel="shortcut icon" href="{{ asset('css/images/Header/kbcommons_icon.ico') }}">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css"></link>

@endsection


@section('content')

<div class="title1">Phenotype Distribution Tool</div>
<br />


<form action="{{ route('system.tools.PhenoDistTool.viewGeneSummaryData', ['organism'=>$organism]) }}" onsubmit="return validateForm('{{ $organism }}', 'error_message_div')" method="get" target="_blank">
    <div id="accordion_1"></div>

    <br/><br/>

    <div style='margin-top:10px;' align='center'>
        <!-- <button type="button" onclick="uncheck_all_phenotypes('{{ $organism }}')" style="margin-right:20px;">Uncheck All Phenotypes</button> -->
        <!-- <button type="button" onclick="check_all_phenotypes('{{ $organism }}')" style="margin-right:20px;">Check All Phenotypes</button> -->

        <label for="dataset_1"><b>Dataset:</b></label>
        <select name="dataset_1" id="dataset_1"></select>

        <!-- <label for="chromosome_1" style="margin-left:20px;"><b>Chromosome:</b></label> -->
        <!-- <select name="chromosome_1" id="chromosome_1"></select> -->
    </div>

    <br/><br/>

    <div id='error_message_div' style='margin-top:10px;' align='center'></div>

    <div style='margin-top:10px;' align='center'>
        <input type="submit" value="Search">
    </div>
</form>

@endsection


@section('javascript')

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>

<script src="{{ asset('system/home/PhenoDistTool/js/PhenoDistTool.js') }}" type="text/javascript"></script>

<script>
    $(function() {
        $("#accordion_1").accordion({
            active: false,
            collapsible: true
        });
    });
</script>

<script type="text/javascript">
    updatePhenotypeSelections('accordion_1', '{{ $organism }}', '{{ $dataset }}');
    updateDatasetSelections('dataset_1', '{{ $organism }}');
    // updateChromosomeSelections('chromosome_1', '{{ $organism }}', '{{ $dataset }}');
</script>

@endsection
