@php
include resource_path() . '/views/system/config.blade.php';

$organism = $info['organism'];
$dataset = $info['dataset'];
$phenotype_array = $info['phenotype_array'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="shortcut icon" href="{{ asset('css/images/Header/kbcommons_icon.ico') }}">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css"></link>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
</head>


<body>
    <!-- Back button -->
    <a href="{{ route('system.tools.PhenoDistTool', ['organism'=>$organism]) }}"><button> &lt; Back </button></a>

    <br />
    <br />

    <div id="accordion_2"></div>
    <div id="message_div_2"></div>


    <div class="footer" style="margin-top:20px;float:right;">© Copyright 2024 KBCommons</div>
</body>

<script src="{{ asset('system/home/PhenoDistTool/js/viewGeneSummaryData.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    var organism = <?php if(isset($organism)) {echo json_encode($organism, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;
    var dataset = <?php if(isset($dataset)) {echo json_encode($dataset, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;
    var phenotype_array = <?php if(isset($phenotype_array)) {echo json_encode($phenotype_array, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;

    updateGeneRanking('accordion_2', 'message_div_2', organism, dataset, phenotype_array);
</script>
