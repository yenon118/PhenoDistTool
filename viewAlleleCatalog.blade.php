@php
    include resource_path() . '/views/system/config.blade.php';

    $organism = $info['organism'];
    $dataset = $info['dataset'];
    $gene = $info['gene'];
    $chromosome = $info['chromosome'];
    $position = $info['position'];
    $phenotype = $info['phenotype'];

    $gene_result_arr = $info['gene_result_arr'];
    $phenotype_distribution_result_arr = $info['phenotype_distribution_result_arr'];
    $allele_catalog_result_arr = $info['allele_catalog_result_arr'];

@endphp


<head>
    <title>{{ $config_organism }}-KB</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    </link>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"
        integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('system/home/PhenoDistTool/css/modal.css') }}">
    </link>
</head>


<body>

    <!-- Back button -->
    <a href="{{ route('system.tools.PhenoDistTool', ['organism' => $organism]) }}"><button> &lt; Back </button></a>

    <br />
    <br />

    <!-- Modal -->
    <div id="info-modal" class="info-modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div id="modal-content-div"
                style='width:100%; height:auto; border:3px solid #000; overflow:scroll;max-height:1000px;'></div>
            <div id="modal-content-comment"></div>
        </div>
    </div>


    @php

        // Color for functional effects
        $ref_color_code = '#D1D1D1';
        $missense_variant_color_code = '#7FC8F5';
        $frameshift_variant_color_code = '#F26A55';
        $exon_loss_variant_color_code = '#F26A55';
        $lost_color_code = '#F26A55';
        $gain_color_code = '#F26A55';
        $disruptive_color_code = '#F26A55';
        $conservative_color_code = '#FF7F50';
        $splice_color_code = '#9EE85C';

        // Render result to a table
        if (
            isset($allele_catalog_result_arr) &&
            is_array($allele_catalog_result_arr) &&
            !empty($allele_catalog_result_arr)
        ) {
            echo '<p>* Significant position(s) are highlighted in red. </p>';

            for ($i = 0; $i < count($allele_catalog_result_arr); $i++) {
                $phenotype_distribution_position_array = [];
                for ($j = 0; $j < count($phenotype_distribution_result_arr[$i]); $j++) {
                    array_push(
                        $phenotype_distribution_position_array,
                        $phenotype_distribution_result_arr[$i][$j]->Position,
                    );
                }

                // Make table
                echo "<div style='width:100%; height:auto; border:3px solid #000; overflow:scroll; max-height:1000px;'>";
                echo "<table style='text-align:center;'>";

                // Table header
                echo '<tr>';
                echo '<th></th>';
                foreach ($allele_catalog_result_arr[$i][0] as $key => $value) {
                    if (
                        $key != 'Gene' &&
                        $key != 'Chromosome' &&
                        $key != 'Position' &&
                        $key != 'Genotype' &&
                        $key != 'Genotype_Description'
                    ) {
                        // Improvement status count section
                        echo "<th style=\"border:1px solid black; min-width:80px;\">" . $key . '</th>';
                    } elseif ($key == 'Gene') {
                        echo "<th style=\"border:1px solid black; min-width:80px;\">" . $key . '</th>';
                    } elseif ($key == 'Chromosome') {
                        echo "<th style=\"border:1px solid black; min-width:80px;\">" . $key . '</th>';
                    } elseif ($key == 'Position') {
                        // Position and genotype_description section
                        $position_array = preg_split("/[;, \n]+/", $value);
                        for ($j = 0; $j < count($position_array); $j++) {
                            if (in_array($position_array[$j], $phenotype_distribution_position_array)) {
                                echo "<th style=\"border:1px solid black; min-width:80px; color:red;\"><a style=\"color:red;\" href=\"../viewVariantAndPhenotypeFigures/" .
                                    $organism .
                                    '?Dataset=' .
                                    $dataset .
                                    '&Chromosome=' .
                                    $allele_catalog_result_arr[$i][0]->Chromosome .
                                    '&Position=' .
                                    $position_array[$j] .
                                    '&Phenotype=' .
                                    $phenotype .
                                    "\" target=\"_blank\">" .
                                    $position_array[$j] .
                                    '</a></th>';
                            } else {
                                echo "<th style=\"border:1px solid black; min-width:80px;\">" .
                                    $position_array[$j] .
                                    '</th>';
                            }
                        }
                    }
                }
                echo '<th></th>';
                echo '</tr>';

                // Table body
                for ($j = 0; $j < count($allele_catalog_result_arr[$i]); $j++) {
                    $tr_bgcolor = $j % 2 ? '#FFFFFF' : '#DDFFDD';

                    $row_id_prefix =
                        $allele_catalog_result_arr[$i][$j]->Gene .
                        '_' .
                        $allele_catalog_result_arr[$i][$j]->Chromosome .
                        '_' .
                        $j;

                    echo "<tr bgcolor=\"" . $tr_bgcolor . "\">";
                    echo "<td><input type=\"checkbox\" id=\"" .
                        $row_id_prefix .
                        '_l' .
                        "\" name=\"" .
                        $row_id_prefix .
                        '_l' .
                        "\" value=\"" .
                        $row_id_prefix .
                        '_l' .
                        "\" onclick=\"checkHighlight(this)\"></td>";

                    foreach ($allele_catalog_result_arr[$i][$j] as $key => $value) {
                        if (
                            $key != 'Gene' &&
                            $key != 'Chromosome' &&
                            $key != 'Position' &&
                            $key != 'Genotype' &&
                            $key != 'Genotype_Description'
                        ) {
                            // Improvement status count section
                            if (intval($value) > 0) {
                                echo "<td style=\"border:1px solid black;min-width:80px;\">";
                                echo "<a href=\"javascript:void(0);\" onclick=\"queryMetadataByImprovementStatusAndGenotypeCombination('" .
                                    $organism .
                                    "', '" .
                                    strval($dataset) .
                                    "', '" .
                                    strval($key) .
                                    "', '" .
                                    $allele_catalog_result_arr[$i][$j]->Gene .
                                    "', '" .
                                    $allele_catalog_result_arr[$i][$j]->Chromosome .
                                    "', '" .
                                    $allele_catalog_result_arr[$i][$j]->Position .
                                    "', '" .
                                    $allele_catalog_result_arr[$i][$j]->Genotype .
                                    "', '" .
                                    $allele_catalog_result_arr[$i][$j]->Genotype_Description .
                                    "')\">";
                                echo $value;
                                echo '</a>';
                                echo '</td>';
                            } else {
                                echo "<td style=\"border:1px solid black;min-width:80px;\">" . $value . '</td>';
                            }
                        } elseif ($key == 'Gene') {
                            echo "<td style=\"border:1px solid black;min-width:80px;\">" . $value . '</td>';
                        } elseif ($key == 'Chromosome') {
                            echo "<td style=\"border:1px solid black;min-width:80px;\">" . $value . '</td>';
                        } elseif ($key == 'Genotype_Description') {
                            // Position and genotype_description section
                            $position_array = preg_split("/[;, \n]+/", $allele_catalog_result_arr[$i][$j]->Position);
                            $genotype_description_array = preg_split("/[;, \n]+/", $value);
                            for ($k = 0; $k < count($genotype_description_array); $k++) {
                                // Change genotype_description background color
                                $td_bg_color = '#FFFFFF';
                                if (preg_match('/missense.variant/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $missense_variant_color_code;
                                    $temp_value_arr = preg_split("/[;, |\n]+/", $genotype_description_array[$k]);
                                    $genotype_description_array[$k] =
                                        count($temp_value_arr) > 2
                                            ? $temp_value_arr[0] . '|' . $temp_value_arr[2]
                                            : $genotype_description_array[$k];
                                } elseif (preg_match('/frameshift/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $frameshift_variant_color_code;
                                } elseif (preg_match('/exon.loss/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $exon_loss_variant_color_code;
                                } elseif (preg_match('/lost/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $lost_color_code;
                                    $temp_value_arr = preg_split("/[;, |\n]+/", $genotype_description_array[$k]);
                                    $genotype_description_array[$k] =
                                        count($temp_value_arr) > 2
                                            ? $temp_value_arr[0] . '|' . $temp_value_arr[2]
                                            : $genotype_description_array[$k];
                                } elseif (preg_match('/gain/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $gain_color_code;
                                    $temp_value_arr = preg_split("/[;, |\n]+/", $genotype_description_array[$k]);
                                    $genotype_description_array[$k] =
                                        count($temp_value_arr) > 2
                                            ? $temp_value_arr[0] . '|' . $temp_value_arr[2]
                                            : $genotype_description_array[$k];
                                } elseif (preg_match('/disruptive/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $disruptive_color_code;
                                } elseif (preg_match('/conservative/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $conservative_color_code;
                                } elseif (preg_match('/splice/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $splice_color_code;
                                } elseif (preg_match('/ref/i', $genotype_description_array[$k])) {
                                    $td_bg_color = $ref_color_code;
                                }

                                echo "<td id=\"" .
                                    $row_id_prefix .
                                    '_' .
                                    $position_array[$k] .
                                    "\" style=\"border:1px solid black;min-width:80px;background-color:" .
                                    $td_bg_color .
                                    "\">" .
                                    $genotype_description_array[$k] .
                                    '</td>';
                            }
                        }
                    }

                    echo "<td><input type=\"checkbox\" id=\"" .
                        $row_id_prefix .
                        '_r' .
                        "\" name=\"" .
                        $row_id_prefix .
                        '_r' .
                        "\" value=\"" .
                        $row_id_prefix .
                        '_r' .
                        "\" onclick=\"checkHighlight(this)\"></td>";
                    echo '</tr>';
                }

                echo '</table>';
                echo '</div>';

                echo "<div style='margin-top:10px;' align='right'>";
                echo "<button onclick=\"queryAllCountsByGene('" .
                    $organism .
                    "', '" .
                    $dataset .
                    "', '" .
                    $allele_catalog_result_arr[$i][0]->Gene .
                    "', '" .
                    $allele_catalog_result_arr[$i][0]->Chromosome .
                    "')\" style=\"margin-right:20px;\"> Download (Accession Counts)</button>";
                echo "<button onclick=\"queryAllByGene('" .
                    $organism .
                    "', '" .
                    $dataset .
                    "', '" .
                    $allele_catalog_result_arr[$i][0]->Gene .
                    "', '" .
                    $allele_catalog_result_arr[$i][0]->Chromosome .
                    "')\"> Download (All Accessions)</button>";
                echo '</div>';

                echo '<br />';
                echo '<br />';
            }
        } else {
            echo '<p>No Allele Catalog data found in database!!!</p>';
        }

    @endphp


    <div class="footer" style="margin-top:20px;float:right;">© Copyright 2024 KBCommons</div>
</body>


<script src="{{ asset('system/home/PhenoDistTool/js/modal.js') }}" type="text/javascript"></script>
<script src="{{ asset('system/home/PhenoDistTool/js/viewAlleleCatalog.js') }}" type="text/javascript"></script>

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
    var chromosome = <?php if (isset($chromosome)) {
        echo json_encode($chromosome, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var position = <?php if (isset($position)) {
        echo json_encode($position, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
    var phenotype = <?php if (isset($phenotype)) {
        echo json_encode($phenotype, JSON_INVALID_UTF8_IGNORE);
    } else {
        echo '';
    } ?>;
</script>
