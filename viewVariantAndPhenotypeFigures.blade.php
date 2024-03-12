@php
include resource_path() . '/views/system/config.blade.php';

$organism = $info['organism'];
$dataset = $info['dataset'];
$chromosome = $info['chromosome'];
$position = $info['position'];
$phenotype = $info['phenotype'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="shortcut icon" href="{{ asset('css/images/Header/kbcommons_icon.ico') }}">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css"></link>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script src="https://cdn.plot.ly/plotly-2.12.1.min.js"></script>
</head>


<body>

<!-- Back button -->
<a href="{{ route('system.tools.PhenoDistTool', ['organism'=>$organism]) }}"><button> &lt; Back </button></a>

<br />
<br />

<h3>Queried Variant and Phenotype:</h3>
<div style='width:auto; height:auto; overflow:visible; max-height:1000px;'>
<table style='text-align:center; border:3px solid #000;'>
    <tr>
        <th style="border:1px solid black; min-width:80px;">Dataset</th>
        <th style="border:1px solid black; min-width:80px;">Chromsome</th>
        <th style="border:1px solid black; min-width:80px;">Position</th>
        <th style="border:1px solid black; min-width:80px;">Phenotype</th>
    </tr>
    <tr bgcolor="#DDFFDD">
        <td style="border:1px solid black; min-width:80px;">{{ $dataset }}</td>
        <td style="border:1px solid black; min-width:80px;">{{ $chromosome }}</td>
        <td style="border:1px solid black; min-width:80px;">{{ $position }}</td>
        <td style="border:1px solid black; min-width:80px;">{{ $phenotype }}</td>
    </tr>
</table>

<br /><br />

<h3>Figures:</h3>
<div id="genotype_section_div">
	<div id="genotype_figure_div">Loading genotype plot...</div>
	<div id="genotype_summary_table_div">Loading genotype summary table...</div>
</div>
<hr />
<div id="improvement_status_summary_figure_div">Loading improvement status summary plot...</div>
<!-- <div id="improvement_status_figure_div">Loading improvement status plot...</div> -->
<!-- <div id="classification_figure_div">Loading classification plot...</div> -->

<div class="footer" style="margin-top:20px;float:right;">© Copyright 2024 KBCommons</div>
</body>


<script src="{{ asset('system/home/PhenoDistTool/js/viewVariantAndPhenotypeFigures.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    var organism = <?php if(isset($organism)) {echo json_encode($organism, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;
    var dataset = <?php if(isset($dataset)) {echo json_encode($dataset, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;
    var chromosome = <?php if(isset($chromosome)) {echo json_encode($chromosome, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;
    var position = <?php if(isset($position)) {echo json_encode($position, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;
    var phenotype = <?php if(isset($phenotype)) {echo json_encode($phenotype, JSON_INVALID_UTF8_IGNORE);} else {echo "";}?>;

	if (dataset && chromosome && position && phenotype) {
		$.ajax({
			url: 'queryVariantAndPhenotypeFigures/'+organism,
			type: 'GET',
			contentType: 'application/json',
			data: {
				Dataset: dataset,
				Chromosome: chromosome,
				Position: position,
				Phenotype: phenotype
			},
			success: function (response) {
                console.log(response);
				var res = JSON.parse(response);
                console.log(res);

				if (res && phenotype) {
                    console.log(res);

					document.getElementById("improvement_status_summary_figure_div").style.minHeight = "800px";
					document.getElementById("genotype_figure_div").style.minHeight = "800px";
					// document.getElementById("improvement_status_figure_div").style.minHeight = "800px";
					// document.getElementById("classification_figure_div").style.minHeight = "800px";

					// Summarize data
					var result_dict = summarizeQueriedData(
						JSON.parse(JSON.stringify(res)),
						phenotype,
						'Genotype'
					);

					var result_arr = result_dict['Data'];
					var summary_array = result_dict['Summary'];

					var genotypeAndImprovementStatusData = collectDataForFigure(result_arr, 'Improvement_Status', 'Genotype');
					var genotypeData = collectDataForFigure(result_arr, phenotype, 'Genotype');
					// var improvementStatusData = collectDataForFigure(result_arr, phenotype, 'Improvement_Status');
					// var classificationData = collectDataForFigure(result_arr, phenotype, 'Classification');

					plotFigure(genotypeAndImprovementStatusData, 'Genotype', 'Improvement_Status_Summary', 'improvement_status_summary_figure_div');
					plotFigure(genotypeData, 'Genotype', 'Genotype', 'genotype_figure_div');
					// plotFigure(improvementStatusData, 'Improvement_Status', 'Improvement_Status', 'improvement_status_figure_div');
					// plotFigure(classificationData, 'Classification', 'Classification', 'classification_figure_div');

					// Render summarized data
					document.getElementById('genotype_summary_table_div').innerText = "";
					document.getElementById('genotype_summary_table_div').innerHTML = "";
					document.getElementById('genotype_summary_table_div').appendChild(
						constructInfoTable(summary_array)
					);
					document.getElementById('genotype_summary_table_div').style.overflow = 'scroll';

				}
			},
			error: function (xhr, status, error) {
				console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
				document.getElementById('genotype_figure_div').innerText="";
				document.getElementById('genotype_summary_table_div').innerHTML="";
				document.getElementById('improvement_status_summary_figure_div').innerHTML="";
				// document.getElementById('improvement_status_figure_div').innerHTML="";
				// document.getElementById('classification_figure_div').innerHTML="";
				var p_tag = document.createElement('p');
				p_tag.innerHTML = "Genotype distribution figure is not available due to lack of data!!!";
				document.getElementById('genotype_figure_div').appendChild(p_tag);
				var p_tag = document.createElement('p');
				p_tag.innerHTML = "Genotype summary table is not available due to lack of data!!!";
				document.getElementById('genotype_summary_table_div').appendChild(p_tag);
				var p_tag = document.createElement('p');
				p_tag.innerHTML = "Improvement status summary figure is not available due to lack of data!!!";
				document.getElementById('improvement_status_summary_figure_div').appendChild(p_tag);
				// var p_tag = document.createElement('p');
				// p_tag.innerHTML = "Improvement status distribution figure is not available due to lack of data!!!";
				// document.getElementById('improvement_status_figure_div').appendChild(p_tag);
				// var p_tag = document.createElement('p');
				// p_tag.innerHTML = "Classification distribution figure is not available due to lack of data!!!";
				// document.getElementById('classification_figure_div').appendChild(p_tag);
			}
		});
	} else {
		document.getElementById('genotype_figure_div').innerText="";
		document.getElementById('genotype_summary_table_div').innerHTML="";
		document.getElementById('improvement_status_summary_figure_div').innerHTML="";
		// document.getElementById('improvement_status_figure_div').innerHTML="";
		// document.getElementById('classification_figure_div').innerHTML="";
		var p_tag = document.createElement('p');
		p_tag.innerHTML = "Genotype distribution figure is not available due to lack of data!!!";
		document.getElementById('genotype_figure_div').appendChild(p_tag);
		var p_tag = document.createElement('p');
		p_tag.innerHTML = "Genotype summary table is not available due to lack of data!!!";
		document.getElementById('genotype_summary_table_div').appendChild(p_tag);
		var p_tag = document.createElement('p');
		p_tag.innerHTML = "Improvement status summary figure is not available due to lack of data!!!";
		document.getElementById('improvement_status_summary_figure_div').appendChild(p_tag);
		// var p_tag = document.createElement('p');
		// p_tag.innerHTML = "Improvement status distribution figure is not available due to lack of data!!!";
		// document.getElementById('improvement_status_figure_div').appendChild(p_tag);
		// var p_tag = document.createElement('p');
		// p_tag.innerHTML = "Classification distribution figure is not available due to lack of data!!!";
		// document.getElementById('classification_figure_div').appendChild(p_tag);
	}

</script>
