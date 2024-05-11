<?php

namespace App\Http\Controllers\System\Tools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\KBCClasses\DBAdminWrapperClass;
use App\KBCClasses\DBKBCWrapperClass;

class KBCToolsPhenoDistToolController extends Controller
{


    function __construct()
    {
        $this->db_kbc_wrapper = new DBKBCWrapperClass;
    }


    public function getAlleleCatalogTableNames($organism, $dataset) {
        // Table names and datasets
        if ($organism == "Athaliana" && $dataset == "Arabidopsis1135") {
            $key_column = "Group";
            $gff_table = "act_Arabidopsis_TAIR10_GFF";
            $accession_mapping_table = "act_" . $dataset . "_Accession_Mapping";
            $phenotype_table = "act_" . $dataset . "_Phenotype_Data";
            $phenotype_selection_table = "act_" . $dataset . "_Phenotype_Selection";
        } elseif ($organism == "Osativa" && $dataset == "Rice3000") {
            $key_column = "Subpopulation";
            $gff_table = "act_Rice_Nipponbare_GFF";
            $accession_mapping_table = "act_" . $dataset . "_Accession_Mapping";
            $phenotype_table = "act_" . $dataset . "_Phenotype_Data";
            $phenotype_selection_table = "act_" . $dataset . "_Phenotype_Selection";
        } elseif ($organism == "Ptrichocarpa" && $dataset == "PopulusTrichocarpa882") {
            $key_column = "";
            $gff_table = "act_Ptrichocarpa_v3_1_GFF";
            $accession_mapping_table = "act_" . $dataset . "_Accession_Mapping";
            $phenotype_table = "act_" . $dataset . "_Phenotype_Data";
            $phenotype_selection_table = "act_" . $dataset . "_Phenotype_Selection";
        } else {
            $key_column = "";
            $gff_table = "";
            $accession_mapping_table = $dataset;
            $phenotype_table = "";
            $phenotype_selection_table = "";
        }

        return array(
            "key_column" => $key_column,
            "gff_table" => $gff_table,
            "accession_mapping_table" => $accession_mapping_table,
            "phenotype_table" => $phenotype_table,
            "phenotype_selection_table" => $phenotype_selection_table
        );
    }


    public function getDataPanelSelectionTableNames($organism) {
        // Table names and datasets
        if ($organism == "Athaliana") {
            $data_panel_selection_table = "pDist_Arabidopsis_Data_Panel_Selection";
        } elseif ($organism == "Osativa") {
            $data_panel_selection_table = "pDist_Rice_Data_Panel_Selection";
        } elseif ($organism == "Ptrichocarpa") {
            $data_panel_selection_table = "pDist_Ptrichocarpa_Data_Panel_Selection";
        } else {
            $data_panel_selection_table = "";
        }

        return array(
            "data_panel_selection_table" => $data_panel_selection_table
        );
    }


    public function getTableNames($organism, $dataset) {
        // Table names and datasets
        if ($organism == "Athaliana" && $dataset == "Arabidopsis1135") {
            $gff_table = "pDist_Arabidopsis_TAIR10_GFF";
            $gene_ranking_table = "pDist_" . $dataset . "_summary_data";
            $data_panel_selection_table = "pDist_Arabidopsis_Data_Panel_Selection";
            $phenotype_selection_table = "pDist_" . $dataset . "_Phenotype_Selection";
        } elseif ($organism == "Osativa" && $dataset == "Rice3000") {
            $gff_table = "pDist_Rice_Nipponbare_GFF";
            $gene_ranking_table = "pDist_" . $dataset . "_summary_data";
            $data_panel_selection_table = "pDist_Rice_Data_Panel_Selection";
            $phenotype_selection_table = "pDist_" . $dataset . "_Phenotype_Selection";
        } elseif ($organism == "Ptrichocarpa" && $dataset == "PopulusTrichocarpa882") {
            $gff_table = "pDist_Ptrichocarpa_v3_1_GFF";
            $gene_ranking_table = "pDist_" . $dataset . "_summary_data";
            $data_panel_selection_table = "pDist_Ptrichocarpa_Data_Panel_Selection";
            $phenotype_selection_table = "pDist_" . $dataset . "_Phenotype_Selection";
        } else {
            $gff_table = "";
            $gene_ranking_table = "";
            $data_panel_selection_table = "";
            $phenotype_selection_table = "";
        }

        return array(
            "gff_table" => $gff_table,
            "gene_ranking_table" => $gene_ranking_table,
            "data_panel_selection_table" => $data_panel_selection_table,
            "phenotype_selection_table" => $phenotype_selection_table
        );
    }


    public function getSummarizedDataQueryString($organism, $dataset, $db, $gff_table, $accession_mapping_table, $gene, $chromosome, $having = "") {
        if ($organism == "Zmays" && $dataset == "Maize1210") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Improved_Cultivar', 1, null)) AS Improved_Cultivar, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Landrace', 1, null)) AS Landrace, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Wild_Relative', 1, null)) AS Wild_Relative, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'exPVP', 1, null)) AS exPVP, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Other', 1, null)) AS Other, ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Improvement_Status, GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON CAST(AM.Accession AS BINARY) = CAST(GENO.Accession AS BINARY) ";
            $query_str = $query_str . " GROUP BY AM.Improvement_Status, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        } elseif ($organism == "Athaliana" && $dataset == "Arabidopsis1135") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(IF(ACD.Group = 'Central', 1, null)) AS Central, ";
            $query_str = $query_str . "COUNT(IF(ACD.Group = 'East', 1, null)) AS East, ";
            $query_str = $query_str . "COUNT(IF(ACD.Group = 'North', 1, null)) AS North, ";
            $query_str = $query_str . "COUNT(IF(ACD.Group = 'South', 1, null)) AS South, ";
            $query_str = $query_str . "COUNT(IF(ACD.Group = 'Other', 1, null)) AS Other, ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Group, GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Group, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        } elseif ($organism == "Osativa" && $dataset == "Rice166") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        } elseif ($organism == "Osativa" && $dataset == "Rice3000") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'admix', 1, null)) AS admix, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'aro', 1, null)) AS aro, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'aus', 1, null)) AS aus, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'ind1A', 1, null)) AS ind1A, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'ind1B', 1, null)) AS ind1B, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'ind2', 1, null)) AS ind2, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'ind3', 1, null)) AS ind3, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'indx', 1, null)) AS indx, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'japx', 1, null)) AS japx, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'subtrop', 1, null)) AS subtrop, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'temp', 1, null)) AS temp, ";
            $query_str = $query_str . "COUNT(IF(ACD.Subpopulation = 'trop', 1, null)) AS trop, ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Subpopulation, GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Subpopulation, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        } elseif ($organism == "Ptrichocarpa" && $dataset == "PopulusTrichocarpa882") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        } elseif ($organism == "Sbicolor" && $dataset == "Sorghum400") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Converted', 1, null)) AS Converted, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Improved', 1, null)) AS Improved, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Landrace', 1, null)) AS Landrace, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Wild', 1, null)) AS Wild, ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Improvement_Status, GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Improvement_Status, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        } elseif ($organism == "Sbicolor" && $dataset == "Sorghum499") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Converted', 1, null)) AS Converted, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Improved', 1, null)) AS Improved, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Landrace', 1, null)) AS Landrace, ";
            $query_str = $query_str . "COUNT(IF(ACD.Improvement_Status = 'Wild', 1, null)) AS Wild, ";
            $query_str = $query_str . "COUNT(ACD.Accession) AS Total, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Improvement_Status, GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Improvement_Status, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . "GROUP BY ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description ";
            $query_str = $query_str . $having;
            $query_str = $query_str . "ORDER BY ACD.Gene, Total DESC; ";
        }

        return $query_str;
    }


    public function getDataQueryString($organism, $dataset, $db, $gff_table, $accession_mapping_table, $gene, $chromosome, $where = "") {
        if ($organism == "Zmays" && $dataset == "Maize1210") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Kernel_Type, ACD.Improvement_Status, ACD.Country, ACD.State, ";
            $query_str = $query_str . "ACD.Accession, ACD.Panzea_Accession, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Kernel_Type, AM.Improvement_Status, AM.Country, AM.State, ";
            $query_str = $query_str . " GENO.Accession, AM.Panzea_Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON CAST(AM.Accession AS BINARY) = CAST(GENO.Accession AS BINARY) ";
            $query_str = $query_str . " GROUP BY AM.Kernel_Type, AM.Improvement_Status, AM.Country, AM.State, GENO.Accession, AM.Panzea_Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        } elseif ($organism == "Athaliana" && $dataset == "Arabidopsis1135") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Admixture_Group, ACD.Group, ACD.Country, ACD.State, ";
            $query_str = $query_str . "ACD.Accession, ACD.TAIR_Accession, ACD.Name, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Admixture_Group, AM.Group, AM.Country, AM.State, ";
            $query_str = $query_str . " GENO.Accession, AM.TAIR_Accession, AM.Name, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Admixture_Group, AM.Group, AM.Country, AM.State, GENO.Accession, AM.TAIR_Accession, AM.Name, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        } elseif ($organism == "Osativa" && $dataset == "Rice166") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Accession, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT ";
            $query_str = $query_str . " GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        } elseif ($organism == "Osativa" && $dataset == "Rice3000") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Subpopulation, ACD.Country, ";
            $query_str = $query_str . "ACD.Accession, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Subpopulation, AM.Country, ";
            $query_str = $query_str . " GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Subpopulation, AM.Country, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        } elseif ($organism == "Ptrichocarpa" && $dataset == "PopulusTrichocarpa882") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Accession, ACD.CBI_Coding_ID, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT ";
            $query_str = $query_str . " GENO.Accession, AM.CBI_Coding_ID, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY GENO.Accession, AM.CBI_Coding_ID, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        } elseif ($organism == "Sbicolor" && $dataset == "Sorghum400") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Name, ACD.PI_Number, ACD.Origin, ACD.Race, ACD.Improvement_Status, ";
            $query_str = $query_str . "ACD.Accession, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Name, AM.PI_Number, AM.Origin, AM.Race, AM.Improvement_Status, ";
            $query_str = $query_str . " GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Name, AM.PI_Number, AM.Origin, AM.Race, AM.Improvement_Status, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        } elseif ($organism == "Sbicolor" && $dataset == "Sorghum499") {
            // Generate SQL string
            $query_str = "SELECT ";
            $query_str = $query_str . "ACD.Name, ACD.PI_Number, ACD.Origin, ACD.Race, ACD.Improvement_Status, ";
            $query_str = $query_str . "ACD.Accession, ";
            $query_str = $query_str . "ACD.Gene, ACD.Chromosome, ACD.Position, ACD.Genotype, ACD.Genotype_Description, ACD.Imputation ";
            $query_str = $query_str . "FROM ( ";
            $query_str = $query_str . " SELECT AM.Name, AM.PI_Number, AM.Origin, AM.Race, AM.Improvement_Status, ";
            $query_str = $query_str . " GENO.Accession, ";
            $query_str = $query_str . "	COMB1.Gene, GENO.Chromosome, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Position ORDER BY GENO.Position ASC SEPARATOR ' ') AS Position, ";
            $query_str = $query_str . "	GROUP_CONCAT(GENO.Genotype ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype, ";
            $query_str = $query_str . "	GROUP_CONCAT(CONCAT_WS('|', GENO.Genotype, IFNULL( FUNC2.Functional_Effect, GENO.Category ), FUNC2.Amino_Acid_Change, GENO.Imputation) ORDER BY GENO.Position ASC SEPARATOR ' ') AS Genotype_Description, ";
            $query_str = $query_str . "	GROUP_CONCAT(IFNULL(GENO.Imputation, '-') ORDER BY GENO.Position ASC SEPARATOR ' ') AS Imputation ";
            $query_str = $query_str . "	FROM ( ";
            $query_str = $query_str . "		SELECT DISTINCT FUNC.Chromosome, FUNC.Position, GFF.ID As Gene ";
            $query_str = $query_str . "		FROM " . $db . "." . $gff_table . " AS GFF ";
            $query_str = $query_str . "		INNER JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC ";
            $query_str = $query_str . "		ON (FUNC.Chromosome = GFF.Chromosome) AND (FUNC.Position >= GFF.Start) AND (FUNC.Position <= GFF.End) ";
            $query_str = $query_str . "		WHERE (GFF.ID=\"" . $gene . "\") AND (GFF.Feature=\"gene\") AND (FUNC.Gene_Name LIKE '%" . $gene . "%') AND (FUNC.Chromosome=\"" . $chromosome . "\") ";
            $query_str = $query_str . "	) AS COMB1 ";
            $query_str = $query_str . "	INNER JOIN " . $db . ".act_" . $dataset . "_genotype_" . $chromosome . " AS GENO ";
            $query_str = $query_str . "	ON (GENO.Chromosome = COMB1.Chromosome) AND (GENO.Position = COMB1.Position) ";
            $query_str = $query_str . "	LEFT JOIN " . $db . ".act_" . $dataset . "_func_eff_" . $chromosome . " AS FUNC2 ";
            $query_str = $query_str . "	ON (FUNC2.Chromosome = GENO.Chromosome) AND (FUNC2.Position = GENO.Position) AND (FUNC2.Allele = GENO.Genotype) AND (FUNC2.Gene LIKE CONCAT('%', COMB1.Gene, '%')) ";
            $query_str = $query_str . " LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
            $query_str = $query_str . " ON AM.Accession = GENO.Accession ";
            $query_str = $query_str . " GROUP BY AM.Name, AM.PI_Number, AM.Origin, AM.Race, AM.Improvement_Status, GENO.Accession, COMB1.Gene, GENO.Chromosome ";
            $query_str = $query_str . ") AS ACD ";
            $query_str = $query_str . $where;
            $query_str = $query_str . "ORDER BY ACD.Gene; ";
        }

        return $query_str;
    }


    public function PhenoDistToolPage(Request $request, $organism) {
        $admin_db_wapper = new DBAdminWrapperClass;

        // Database
        $db = "KBC_" . $organism;

        if ($organism == "Athaliana") {
            $dataset = "Arabidopsis1135";
        } elseif ($organism == "Osativa") {
            $dataset = "Rice3000";
        } elseif ($organism == "Ptrichocarpa") {
            $dataset = "PopulusTrichocarpa882";
        } else {
            $dataset = "";
        }

        try {
            if ($dataset != "") {
                // Package variables that need to go to the view
                $info = [
                    'organism' => $organism,
                    'dataset' => $dataset,
                ];

                // Return to view
                return view('system/tools/PhenoDistTool/PhenoDistTool')->with('info', $info);
            } else {
                // Package variables that need to go to the view
                $info = [
                    'organism' => $organism
                ];

                // Return to view
                return view('system/tools/PhenoDistTool/PhenoDistToolNotAvailable')->with('info', $info);
            }
        } catch (\Exception $e) {
            // Package variables that need to go to the view
            $info = [
                'organism' => $organism
            ];

            // Return to view
            return view('system/tools/PhenoDistTool/PhenoDistToolNotAvailable')->with('info', $info);
        }

    }


    public function QueryDistinctDatasetsFromDataPanelSelection(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        // Table names and datasets
        $table_names = self::getDataPanelSelectionTableNames($organism);
        $data_panel_selection_table = $table_names["data_panel_selection_table"];

        // Generate SQL string
        $query_str = "SELECT DISTINCT Dataset ";
        $query_str = $query_str . "FROM " . $db . "." . $data_panel_selection_table . ";";

        try {
            $data_panel_array = DB::connection($db)->select($query_str);
        } catch (\Exception $e) {
            $data_panel_array = array();
        }

        $result_arr = [
            "Organism" => $organism,
            "Data_Panel_Array" => $data_panel_array
        ];

        return json_encode($result_arr);
    }


    public function QueryDistinctChromosomesFromDataPanelSelection(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;

        // Table names and datasets
        $table_names = self::getTableNames($organism, $dataset);
        $data_panel_selection_table = $table_names["data_panel_selection_table"];

        // Generate SQL string
        $query_str = "SELECT DISTINCT Chromosome ";
        $query_str = $query_str . "FROM " . $db . "." . $data_panel_selection_table . " ";
        $query_str = $query_str . "WHERE Dataset='" . $dataset . "';";

        try {
            $chromosome_array = DB::connection($db)->select($query_str);
        } catch (\Exception $e) {
            $chromosome_array = array();
        }

        $result_arr = [
            "Organism" => $organism,
            "Dataset" => $dataset,
            "Chromosome_Array" => $chromosome_array
        ];

        return json_encode($result_arr);
    }


    public function QueryPhenotypesFromPhenotypeSelection(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;

        // Table names and datasets
        $table_names = self::getTableNames($organism, $dataset);
        $phenotype_selection_table = $table_names["phenotype_selection_table"];

        // Generate SQL string
        $query_str = "SELECT * ";
        $query_str = $query_str . "FROM " . $db . "." . $phenotype_selection_table . ";";

        try {
            $phenotype_selection_array = DB::connection($db)->select($query_str);
        } catch (\Exception $e) {
            $phenotype_selection_array = array();
        }

        $result_arr = [
            "Organism" => $organism,
            "Dataset" => $dataset,
            "Phenotype_Selection_Array" => $phenotype_selection_array
        ];

        return json_encode($result_arr);
    }


    public function ViewGeneSummaryDataPage(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $phenotype = $request->phenotype;
        $dataset = $request->dataset_1;

        if (is_string($phenotype)) {
            $phenotype = trim($phenotype);
            $temp_phenotype_array = preg_split("/[;, \n]+/", $phenotype);
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        } elseif (is_array($phenotype)) {
            $temp_phenotype_array = $phenotype;
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        }

        // Package variables that need to go to the view
        $info = [
            'organism' => $organism,
            'dataset' => $dataset,
            'phenotype_array' => $phenotype_array
        ];

        // Return to view
        return view('system/tools/PhenoDistTool/viewGeneSummaryData')->with('info', $info);
    }


    public function QueryGeneRanking(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $phenotypes = $request->Phenotypes;

        if (is_string($phenotypes)) {
            $phenotypes = trim($phenotypes);
            $temp_phenotype_array = preg_split("/[;, \n]+/", $phenotypes);
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        } elseif (is_array($phenotypes)) {
            $temp_phenotype_array = $phenotypes;
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        }

        // Table names and datasets
        $table_names = self::getTableNames($organism, $dataset);
        $gff_table = $table_names["gff_table"];
        $gene_ranking_table = $table_names["gene_ranking_table"];
        $data_panel_selection_table = $table_names["data_panel_selection_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];

        $gene_ranking_array = array();

        if (isset($phenotype_array)) {
            if (!empty($phenotype_array)) {
                if (is_array($phenotype_array)) {
                    if (count($phenotype_array) > 0) {

                        for ($i = 0; $i < count($phenotype_array); $i++) {

                            // Generate SQL string
                            $query_str = "SELECT Gene, ";
                            $query_str = $query_str . "Phenotype, ";
                            $query_str = $query_str . "Phenotype_Data_Type, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Test_Method SEPARATOR '; ') AS Test_Method, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Minimum_Test_P_Value SEPARATOR '; ') AS Minimum_Test_P_Value, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Minimum_Negative_Log2_Test_P_Value SEPARATOR '; ') AS Minimum_Negative_Log2_Test_P_Value, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Maximum_Test_P_Value SEPARATOR '; ') AS Maximum_Test_P_Value, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Maximum_Negative_Log2_Test_P_Value SEPARATOR '; ') AS Maximum_Negative_Log2_Test_P_Value ";
                            $query_str = $query_str . "FROM ( ";
                            $query_str = $query_str . "    SELECT ";
                            $query_str = $query_str . "    Gene, ";
                            $query_str = $query_str . "    Phenotype, ";
                            $query_str = $query_str . "    Phenotype_Data_Type, ";
                            $query_str = $query_str . "    Test_Method, ";
                            $query_str = $query_str . "    Minimum_Test_P_Value, ";
                            $query_str = $query_str . "    Minimum_Negative_Log2_Test_P_Value, ";
                            $query_str = $query_str . "    Maximum_Test_P_Value, ";
                            $query_str = $query_str . "    Maximum_Negative_Log2_Test_P_Value ";
                            $query_str = $query_str . "    FROM " . $db . "." . $gene_ranking_table . " AS PHENO ";
                            $query_str = $query_str . "    WHERE (PHENO.Phenotype IN ('" . $phenotype_array[$i] . "')) ";
                            $query_str = $query_str . "    ORDER BY Minimum_Test_P_Value ";
                            $query_str = $query_str . ") AS PHENO2 ";
                            $query_str = $query_str . "GROUP BY PHENO2.Gene, PHENO2.Phenotype, PHENO2.Phenotype_Data_Type ";
                            $query_str = $query_str . "ORDER BY Minimum_Test_P_Value, Maximum_Test_P_Value ";
                            $query_str = $query_str . "; ";

                            try {
                                // Execute SQL string
                                $temp_gene_ranking_array = DB::connection($db)->select($query_str);

                                for ($j = 0; $j < count($temp_gene_ranking_array); $j++) {
                                    array_push($gene_ranking_array, $temp_gene_ranking_array[$j]);
                                }

                            } catch (\Exception $e) {
                                $temp_gene_ranking_array = array();
                            }

                        }
                    }
                }
            }
        }

        $result_arr = [
            "Organism" => $organism,
            "Dataset" => $dataset,
            "Phenotype_Array" => $phenotype_array,
            "Gene_Ranking_Array" => $gene_ranking_array
        ];

        return json_encode($result_arr);
    }


    public function ViewStatisticalTestingResultsPage(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $phenotype = $request->Phenotype;
        $dataset = $request->Dataset;
        $gene = $request->Gene;

        if (is_string($phenotype)) {
            $phenotype = trim($phenotype);
            $temp_phenotype_array = preg_split("/[;, \n]+/", $phenotype);
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        } elseif (is_array($phenotype)) {
            $temp_phenotype_array = $phenotype;
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        }

        // Package variables that need to go to the view
        $info = [
            'organism' => $organism,
            'dataset' => $dataset,
            'gene' => $gene,
            'phenotype_array' => $phenotype_array
        ];

        // Return to view
        return view('system/tools/PhenoDistTool/viewStatisticalTestingResults')->with('info', $info);
    }


    public function QueryPhenotypeDistribution(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $gene = $request->Gene;
        $phenotypes = $request->Phenotypes;

        if (is_string($phenotypes)) {
            $phenotypes = trim($phenotypes);
            $temp_phenotype_array = preg_split("/[;, \n]+/", $phenotypes);
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        } elseif (is_array($phenotypes)) {
            $temp_phenotype_array = $phenotypes;
            $phenotype_array = array();
            for ($i = 0; $i < count($temp_phenotype_array); $i++) {
                if (!empty(trim($temp_phenotype_array[$i]))) {
                    array_push($phenotype_array, trim($temp_phenotype_array[$i]));
                }
            }
        }

        // Table names and datasets
        $table_names = self::getTableNames($organism, $dataset);
        $gff_table = $table_names["gff_table"];
        $data_panel_selection_table = $table_names["data_panel_selection_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];

        // Generate SQL string
        $query_str = "";
        if ($query_str == "") {
            if (isset($gene)) {
                if (!empty($gene)) {
                    $query_str = "SELECT Chromosome, Start, End, Name AS Gene ";
                    $query_str = $query_str . "FROM " . $db . "." . $gff_table . " ";
                    $query_str = $query_str . "WHERE (Name = '" . $gene . "');";
                }
            }
        }

        if ($query_str == "") {
            exit();
        }

        $gene_result_arr = DB::connection($db)->select($query_str);

        // Use the chromosome to make phenotype_distribution_table
        $phenotype_distribution_table = "";
        if (isset($gene_result_arr)) {
            if (!empty($gene_result_arr)) {
                if (is_array($gene_result_arr)) {
                    $phenotype_distribution_table = "pDist_" . $dataset . "_" . $gene_result_arr[0]->Chromosome . "";
                }
            }
        }

        if ($phenotype_distribution_table == "") {
            exit();
        }

        $phenotype_distribution_array = array();

        if (isset($phenotype_array)) {
            if (!empty($phenotype_array)) {
                if (is_array($phenotype_array)) {
                    if (count($phenotype_array) > 0) {

                        for ($i = 0; $i < count($phenotype_array); $i++) {

                            // Generate SQL string
                            $query_str = "SELECT PHENO2.Chromosome, PHENO2.Position, PHENO2.Gene, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Allele ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Allele, ";
                            $query_str = $query_str . "PHENO2.Phenotype, ";
                            $query_str = $query_str . "PHENO2.Phenotype_Data_Type, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Test_Method ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Test_Method, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Phenotype_Category ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Phenotype_Category, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Accession_Count ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Accession_Count, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Normality_Statistic ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Normality_Statistic, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Normality_P_Value ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Normality_P_Value, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Test_Statistic ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Test_Statistic, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Test_P_Value ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Test_P_Value, ";
                            $query_str = $query_str . "GROUP_CONCAT(DISTINCT PHENO2.Negative_Log2_Test_P_Value ORDER BY PHENO2.Position ASC SEPARATOR '; ') AS Negative_Log2_Test_P_Value ";
                            $query_str = $query_str . "FROM ( ";
                            $query_str = $query_str . "	SELECT PHENO.Chromosome, ";
                            $query_str = $query_str . "	PHENO.Position, ";
                            $query_str = $query_str . "	PHENO.Gene, ";
                            $query_str = $query_str . "	CONCAT_WS(' vs ', PHENO.Allele_1, PHENO.Allele_2) AS Allele, ";
                            $query_str = $query_str . "	PHENO.Phenotype, ";
                            $query_str = $query_str . "	PHENO.Phenotype_Data_Type, ";
                            $query_str = $query_str . "	PHENO.Test_Method, ";
                            $query_str = $query_str . "	CONCAT_WS(' vs ', PHENO.Phenotype_Category_1, PHENO.Phenotype_Category_2) AS Phenotype_Category, ";
                            $query_str = $query_str . "	PHENO.Accession_Count, ";
                            $query_str = $query_str . "	PHENO.Normality_Statistic, ";
                            $query_str = $query_str . "	PHENO.Normality_P_Value, ";
                            $query_str = $query_str . "	PHENO.Test_Statistic, ";
                            $query_str = $query_str . "	PHENO.Test_P_Value, ";
                            $query_str = $query_str . "	PHENO.Negative_Log2_Test_P_Value ";

                            $query_str = $query_str . "	FROM " . $db . "." . $phenotype_distribution_table . " AS PHENO ";

                            $query_str = $query_str . "	WHERE (PHENO.Phenotype IN ('" . $phenotype_array[$i] . "')) ";
                            $query_str = $query_str . "	AND (PHENO.Gene = '" . $gene . "') ";

                            $query_str = $query_str . ") AS PHENO2 ";
                            $query_str = $query_str . "GROUP BY PHENO2.Chromosome, PHENO2.Position, PHENO2.Gene, PHENO2.Phenotype, PHENO2.Phenotype_Data_Type ";
                            $query_str = $query_str . "ORDER BY PHENO2.Phenotype, PHENO2.Chromosome, PHENO2.Position; ";

                            try {
                                // Execute SQL string
                                $temp_phenotype_distribution_array = DB::connection($db)->select($query_str);

                                for ($j = 0; $j < count($temp_phenotype_distribution_array); $j++) {
                                    array_push($phenotype_distribution_array, $temp_phenotype_distribution_array[$j]);
                                }

                            } catch (\Exception $e) {
                                $temp_phenotype_distribution_array = array();
                            }

                        }
                    }
                }
            }
        }

        $result_arr = [
            "Organism" => $organism,
            "Dataset" => $dataset,
            "Gene" => $gene,
            "Phenotype_Array" => $phenotype_array,
            "Phenotype_Distribution_Array" => $phenotype_distribution_array
        ];

        return json_encode($result_arr);
    }


    public function ViewVariantAndPhenotypeFiguresPage(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $chromosome = $request->Chromosome;
        $position = $request->Position;
        $phenotype = $request->Phenotype;

        // Package variables that need to go to the view
        $info = [
            'organism' => $organism,
            'dataset' => $dataset,
            'chromosome' => $chromosome,
            'position' => $position,
            'phenotype' => $phenotype
        ];

        // Return to view
        return view('system/tools/PhenoDistTool/viewVariantAndPhenotypeFigures')->with('info', $info);
    }


    public function QueryVariantAndPhenotypeFigures(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $chromosome = $request->Chromosome;
        $position = $request->Position;
        $phenotype = $request->Phenotype;

        // Table names and datasets
        $table_names = self::getAlleleCatalogTableNames($organism, $dataset);
        $key_column = $table_names["key_column"];
        $gff_table = $table_names["gff_table"];
        $accession_mapping_table = $table_names["accession_mapping_table"];
        $phenotype_table = $table_names["phenotype_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];
        $genotype_table = "act_" . $dataset . "_genotype_" . $chromosome;
        $functional_effect_table = "act_" . $dataset . "_func_eff_" . $chromosome;

        // Construct query string
        $query_str = "SELECT GENO.Chromosome, GENO.Position, GENO.Accession, ";
        if ($organism == "Osativa") {
            $query_str = $query_str . "AM.Accession_Name, AM.IRIS_ID, AM.Subpopulation, ";
        } elseif ($organism == "Athaliana") {
            $query_str = $query_str . "AM.TAIR_Accession, AM.Name, AM.Admixture_Group, ";
        } elseif ($organism == "Zmays") {
            $query_str = $query_str . "AM.Improvement_Status, ";
        } elseif ($organism == "Ptrichocarpa") {
            $query_str = $query_str . "AM.CBI_Coding_ID, ";
        }
        $query_str = $query_str . "GENO.Genotype, ";
        $query_str = $query_str . "COALESCE( FUNC.Functional_Effect, GENO.Category ) AS Functional_Effect, GENO.Imputation ";
        $query_str = $query_str . ", PH." . $phenotype . " ";

        $query_str = $query_str . "FROM ( ";
        $query_str = $query_str . "	SELECT G.Chromosome, G.Position, G.Accession, G.Genotype, G.Category, G.Imputation ";
        $query_str = $query_str . "	FROM " . $db . "." . $genotype_table . " AS G ";
        $query_str = $query_str . "	WHERE (G.Chromosome = '" . $chromosome . "') ";
        $query_str = $query_str . "	AND (G.Position = " . $position . ") ";

        $query_str = $query_str . ") AS GENO ";
        $query_str = $query_str . "LEFT JOIN ( ";
        $query_str = $query_str . "	SELECT F.Chromosome, F.Position, F.Allele, F.Functional_Effect, F.Gene, F.Amino_Acid_Change ";
        $query_str = $query_str . "	FROM " . $db . "." . $functional_effect_table . " AS F ";
        $query_str = $query_str . "	WHERE (F.Chromosome = '" . $chromosome . "') ";
        $query_str = $query_str . "	AND (F.Position = " . $position . ") ";

        $query_str = $query_str . ") AS FUNC ";
        $query_str = $query_str . "ON GENO.Chromosome = FUNC.Chromosome AND GENO.Position = FUNC.Position AND GENO.Genotype = FUNC.Allele ";
        $query_str = $query_str . "LEFT JOIN " . $db . "." . $accession_mapping_table . " AS AM ";
        $query_str = $query_str . "ON CAST(GENO.Accession AS BINARY) = CAST(AM.Accession AS BINARY) ";
        if (isset($phenotype) && !empty($phenotype)) {
            $query_str = $query_str . "LEFT JOIN " . $db . "." . $phenotype_table . " AS PH ";
            if ($organism == "Athaliana" && $dataset == "Arabidopsis1135") {
                $query_str = $query_str . "ON CAST(AM.TAIR_Accession AS BINARY) = CAST(PH.Accession AS BINARY) ";
            } else {
                $query_str = $query_str . "ON CAST(GENO.Accession AS BINARY) = CAST(PH.Accession AS BINARY) ";
            }
        }
        $query_str = $query_str . "ORDER BY GENO.Chromosome, GENO.Position, GENO.Genotype; ";

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }


    public function ViewAlleleCatalogPage(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $gene = $request->Gene;
        $chromosome = $request->Chromosome;
        $position = $request->Position;
        $phenotype = $request->Phenotype;

        // Table names and datasets
        $table_names = self::getAlleleCatalogTableNames($organism, $dataset);
        $key_column = $table_names["key_column"];
        $gff_table = $table_names["gff_table"];
        $accession_mapping_table = $table_names["accession_mapping_table"];
        $phenotype_table = $table_names["phenotype_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];
        $genotype_table = "act_" . $dataset . "_genotype_" . $chromosome;
        $functional_effect_table = "act_" . $dataset . "_func_eff_" . $chromosome;

        // Generate SQL string
        $query_str = "";
        if ($query_str == "") {
            if (isset($chromosome) && isset($position)) {
                if (!empty($chromosome) && !empty($position)) {
                    $query_str = "SELECT Chromosome, Start, End, Name AS Gene ";
                    $query_str = $query_str . "FROM " . $db . "." . $gff_table . " ";
                    $query_str = $query_str . "WHERE (Chromosome = '" . $chromosome . "') AND ((Start <= " . $position . ") AND (End >= " . $position . "));";
                }
            }
        }
        if ($query_str == "") {
            if (isset($gene)) {
                if (!empty($gene)) {
                    $query_str = "SELECT Chromosome, Start, End, Name AS Gene ";
                    $query_str = $query_str . "FROM " . $db . "." . $gff_table . " ";
                    $query_str = $query_str . "WHERE (Name = '" . $gene . "') ";
                }
            }
        }

        if ($query_str == "") {
            exit();
        }

        // Execute SQL string
        $gene_result_arr = DB::connection($db)->select($query_str);

        $phenotype_distribution_result_arr = array();
        $allele_catalog_result_arr = array();

        if (isset($gene_result_arr)) {
            if (!empty($gene_result_arr)) {
                if (is_array($gene_result_arr)) {
                    for ($i = 0; $i < count($gene_result_arr); $i++) {

                        try {
                            // Generate query string
                            $phenotype_distribution_table = "pDist_" . $dataset . "_" . $gene_result_arr[$i]->Chromosome . "";

                            $query_str = "SELECT DISTINCT PHENO.Chromosome, ";
                            $query_str = $query_str . "PHENO.Position, ";
                            $query_str = $query_str . "PHENO.Gene ";
                            $query_str = $query_str . "FROM " . $db . "." . $phenotype_distribution_table . " AS PHENO ";
                            $query_str = $query_str . "WHERE (PHENO.Phenotype IN ('" . $phenotype . "')) ";
                            $query_str = $query_str . "AND (PHENO.Gene = '" . $gene . "') ";
                            $query_str = $query_str . "ORDER BY PHENO.Position, PHENO.Chromosome, PHENO.Gene; ";

                            $result_arr = DB::connection($db)->select($query_str);

                            array_push($phenotype_distribution_result_arr, $result_arr);
                        } catch (\Exception $e) {
                        }

                        try {
                            // Generate SQL string
                            $query_str = self::getSummarizedDataQueryString(
                                $organism,
                                $dataset,
                                $db,
                                $gff_table,
                                $accession_mapping_table,
                                $gene_result_arr[$i]->Gene,
                                $gene_result_arr[$i]->Chromosome,
                                ""
                            );

                            $result_arr = DB::connection($db)->select($query_str);

                            array_push($allele_catalog_result_arr, $result_arr);
                        } catch (\Exception $e) {
                        }

                    }
                }
            }
        }

        // Package variables that need to go to the view
        $info = [
            'organism' => $organism,
            'dataset' => $dataset,
            'gene' => $gene,
            'chromosome' => $chromosome,
            'position' => $position,
            'phenotype' => $phenotype,
            'gene_result_arr' => $gene_result_arr,
            'phenotype_distribution_result_arr' => $phenotype_distribution_result_arr,
            'allele_catalog_result_arr' => $allele_catalog_result_arr
        ];

        // Return to view
        return view('system/tools/PhenoDistTool/viewAlleleCatalog')->with('info', $info);
    }


    public function QueryMetadataByImprovementStatusAndGenotypeCombination(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $key = $request->Key;
        $gene = $request->Gene;
        $chromosome = $request->Chromosome;
        $position = $request->Position;
        $genotype = $request->Genotype;
        $genotype_description = $request->Genotype_Description;

        // Table names and datasets
        $table_names = self::getAlleleCatalogTableNames($organism, $dataset);
        $key_column = $table_names["key_column"];
        $gff_table = $table_names["gff_table"];
        $accession_mapping_table = $table_names["accession_mapping_table"];
        $phenotype_table = $table_names["phenotype_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];
        $genotype_table = "act_" . $dataset . "_genotype_" . $chromosome;
        $functional_effect_table = "act_" . $dataset . "_func_eff_" . $chromosome;

        // Generate SQL string
        if ($key == "Total") {
            $query_str = "WHERE (ACD.Position = '" . $position . "') AND (ACD.Genotype = '" . $genotype . "')";
        } else {
            $query_str = "WHERE ";
            $query_str = $query_str . "(ACD." . $key_column . " = '" . $key . "') AND ";
            $query_str = $query_str . "(ACD.Position = '" . $position . "') AND ";
            $query_str = $query_str . "(ACD.Genotype = '" . $genotype . "')";
        }

        $query_str = self::getDataQueryString(
            $organism,
            $dataset,
            $db,
            $gff_table,
            $accession_mapping_table,
            $gene,
            $chromosome,
            $query_str
        );

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }


    public function QueryAllCountsByGene(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $gene = $request->Gene;
        $chromosome = $request->Chromosome;

        // Table names and datasets
        $table_names = self::getAlleleCatalogTableNames($organism, $dataset);
        $key_column = $table_names["key_column"];
        $gff_table = $table_names["gff_table"];
        $accession_mapping_table = $table_names["accession_mapping_table"];
        $phenotype_table = $table_names["phenotype_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];
        $genotype_table = "act_" . $dataset . "_genotype_" . $chromosome;
        $functional_effect_table = "act_" . $dataset . "_func_eff_" . $chromosome;

        // Generate query string
        $query_str = self::getSummarizedDataQueryString(
            $organism,
            $dataset,
            $db,
            $gff_table,
            $accession_mapping_table,
            $gene,
            $chromosome,
            ""
        );

        $result_arr = DB::connection($db)->select($query_str);

        return json_encode($result_arr);
    }


    public function QueryAllByGene(Request $request, $organism) {
        // Database
        $db = "KBC_" . $organism;

        $dataset = $request->Dataset;
        $gene = $request->Gene;
        $chromosome = $request->Chromosome;

        // Table names and datasets
        $table_names = self::getAlleleCatalogTableNames($organism, $dataset);
        $key_column = $table_names["key_column"];
        $gff_table = $table_names["gff_table"];
        $accession_mapping_table = $table_names["accession_mapping_table"];
        $phenotype_table = $table_names["phenotype_table"];
        $phenotype_selection_table = $table_names["phenotype_selection_table"];
        $genotype_table = "act_" . $dataset . "_genotype_" . $chromosome;
        $functional_effect_table = "act_" . $dataset . "_func_eff_" . $chromosome;

        // Generate query string
        $query_str = self::getDataQueryString(
            $organism,
            $dataset,
            $db,
            $gff_table,
            $accession_mapping_table,
            $gene,
            $chromosome,
            ""
        );

        $result_arr = DB::connection($db)->select($query_str);

        for ($i = 0; $i < count($result_arr); $i++) {
            if (preg_match("/\+/i", $result_arr[$i]->Imputation)) {
                $result_arr[$i]->Imputation = "+";
            } else{
                $result_arr[$i]->Imputation = "";
            }
        }

        return json_encode($result_arr);
    }
}
