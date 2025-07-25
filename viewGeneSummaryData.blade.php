@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $dataset = $info['dataset'];
    $phenotype_array = $info['phenotype_array'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    </link>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"
        integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
</head>


<body>
    <!-- Back button -->
    <a href="{{ route('system.tools.PhenoDistTool', ['organism' => $organism]) }}"><button> &lt; Back </button></a>

    <br />
    <br />

    <div id="message_div_2"></div>
    <div id="accordion_2"></div>

</body>

<script src="{{ asset('system/home/PhenoDistTool/js/viewGeneSummaryData.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    var organism = <?php if (isset($organism)) {
        echo json_encode($organism, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var dataset = <?php if (isset($dataset)) {
        echo json_encode($dataset, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var phenotype_array = <?php if (isset($phenotype_array)) {
        echo json_encode($phenotype_array, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;

    updateGeneRanking('accordion_2', 'message_div_2', organism, dataset, phenotype_array);
</script>
