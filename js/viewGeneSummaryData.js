async function queryGeneRanking(organism, dataset, phenotypes) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'queryGeneRanking/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Dataset: dataset,
                Phenotypes: phenotypes
            },
            success: function (response) {
                var res = JSON.parse(response);

                resolve(res);
            }, error: function (xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
                reject([]);
            }
        });
    });
}

async function updateGeneRanking(phenotype_accordion_id, message_div_id, organism, dataset, phenotypes) {
    document.getElementById(message_div_id).innerHTML = "";
    var p_tag = document.createElement("p");
    p_tag.textContent = "Loading...";
    document.getElementById(message_div_id).appendChild(p_tag);

    var result_array = await queryGeneRanking(organism, dataset, phenotypes);
    var gene_ranking_array = result_array['Gene_Ranking_Array'];

    document.getElementById(message_div_id).innerHTML = "";

    if (gene_ranking_array) {
        if (gene_ranking_array.length > 0) {
            // Check the existance of accordion instance and remove if it exists
            var accordion_instance = $("#" + String(phenotype_accordion_id)).accordion("instance");
            if (accordion_instance != undefined) {
                $("#" + String(phenotype_accordion_id)).accordion("destroy");
                $("#" + String(phenotype_accordion_id)).empty();
            }
            document.getElementById(phenotype_accordion_id).innerHTML = "";

            phenotype_array = [];
            for (let i = 0; i < gene_ranking_array.length; i++) {
                var header_array = Object.keys(gene_ranking_array[i]);
                var current_gene = gene_ranking_array[i]['Gene'];
                var current_phenotype = gene_ranking_array[i]['Phenotype'];
                var current_phenotype_data_type = gene_ranking_array[i]['Phenotype_Data_Type'];

                if (!phenotype_array.includes(current_phenotype)) {
                    // Create a h3 tag
                    var h3_tag = document.createElement('h3');
                    // Set id attribute
                    h3_tag.id = "ui-id-h3-" + String(current_phenotype.replace(/ /g, "_"));
                    // Set aria-controls attribute
                    h3_tag.setAttribute("aria-controls", "ui-id-div-" + String(current_phenotype.replace(/ /g, "_")));
                    // Set inner HTML
                    h3_tag.innerHTML = current_phenotype;
                    // Append h3 tag
                    document.getElementById(phenotype_accordion_id).appendChild(h3_tag);

                    // Create a div tag
                    var div_tag = document.createElement('div');
                    // Set id attribute
                    div_tag.id = "ui-id-div-" + String(current_phenotype.replace(/ /g, "_"));
                    // Set aria-labelledby attribute for the div element
                    div_tag.setAttribute("aria-labelledby", "ui-id-h3-" + String(current_phenotype.replace(/ /g, "_")));
                    // Append div tag
                    document.getElementById(phenotype_accordion_id).appendChild(div_tag);

                    // Add table into div tag
                    var table_tag = document.createElement('table');
                    // Set id attribute
                    table_tag.id = "table-" + String(current_phenotype.replace(/ /g, "_"));
                    // Append div tag
                    div_tag.appendChild(table_tag);

                    // Add tr into table tag
                    var header_tr_tag = document.createElement("tr");
                    var header_th_tag = document.createElement("th");
                    header_th_tag.setAttribute("style", "border:1px solid black; min-width:100px; height:18.5px;");
                    header_tr_tag.appendChild(header_th_tag);
                    var header_th_tag = document.createElement("th");
                    header_th_tag.setAttribute("style", "border:1px solid black; min-width:100px; height:18.5px;");
                    header_tr_tag.appendChild(header_th_tag);
                    // Add headings
                    for (let i = 0; i < header_array.length; i++) {
                        var header_th_tag = document.createElement("th");
                        header_th_tag.setAttribute("style", "border:1px solid black; min-width:100px; height:18.5px;");
                        header_th_tag.innerHTML = header_array[i];
                        header_tr_tag.appendChild(header_th_tag);
                    }
                    // Append div tag
                    table_tag.appendChild(header_tr_tag);

                    phenotype_array.push(current_phenotype);
                }

                // Add content row
                var detail_tr_tag = document.createElement("tr");
                detail_tr_tag.style.backgroundColor = ((i % 2) ? "#FFFFFF" : "#DDFFDD");

                // Add a view Allele Catalog button column
                var detail_td_tag = document.createElement("td");
                detail_td_tag.setAttribute("style", "border:1px solid black; min-width:100px; height:18.5px;");
                detail_tr_tag.appendChild(detail_td_tag);
                var button_tag = document.createElement("button");
                button_tag.type = 'button';
                detail_td_tag.appendChild(button_tag);
                var a_tag = document.createElement("a");
                a_tag.target = '_blank';
                a_tag.href = '../viewAlleleCatalog/' + organism + '?';
                a_tag.href = a_tag.href + 'Dataset=' + String(dataset) + '&';
                a_tag.href = a_tag.href + 'Gene=' + String(current_gene) + '&';
                a_tag.href = a_tag.href + 'Phenotype=' + String(current_phenotype) + '';
                a_tag.innerHTML = 'View Allele Catalog';
                button_tag.appendChild(a_tag);

                // Add a view phenotype distribution button column
                var detail_td_tag = document.createElement("td");
                detail_td_tag.setAttribute("style", "border:1px solid black; min-width:100px; height:18.5px;");
                detail_tr_tag.appendChild(detail_td_tag);
                var button_tag = document.createElement("button");
                button_tag.type = 'button';
                detail_td_tag.appendChild(button_tag);
                var a_tag = document.createElement("a");
                a_tag.target = '_blank';
                a_tag.href = '../viewStatisticalTestingResults/' + organism + '?';
                a_tag.href = a_tag.href + 'Dataset=' + String(dataset) + '&';
                a_tag.href = a_tag.href + 'Gene=' + String(current_gene) + '&';
                a_tag.href = a_tag.href + 'Phenotype=' + String(current_phenotype) + '';
                a_tag.innerHTML = 'View Details';
                button_tag.appendChild(a_tag);

                for (let j = 0; j < header_array.length; j++) {
                    var detail_td_tag = document.createElement("td");
                    detail_td_tag.setAttribute("style", "border:1px solid black; min-width:100px; height:18.5px;");
                    detail_td_tag.innerHTML = gene_ranking_array[i][header_array[j]];
                    detail_tr_tag.appendChild(detail_td_tag);
                }
                document.getElementById("table-" + String(current_phenotype.replace(/ /g, "_"))).appendChild(detail_tr_tag);

            }

            // Make accordion
            $("#" + String(phenotype_accordion_id)).accordion({
                active: false,
                collapsible: true,
                icons: ""
            });

            // If there is only one phenotype, open the accordion
            if (phenotype_array.length == 1) {
                $("#" + String(phenotype_accordion_id)).accordion("option", "active", 0);
            }

            document.getElementById(message_div_id).innerHTML = "";
            var p_tag = document.createElement("p");
            p_tag.textContent = "* Only significant genes are shown and ranked in the table.";
            document.getElementById(message_div_id).appendChild(p_tag);
        } else {
            document.getElementById(message_div_id).innerHTML = "";
            var p_tag = document.createElement("p");
            p_tag.textContent = "No data found in the database. ";
            document.getElementById(message_div_id).appendChild(p_tag);
        }
    } else {
        document.getElementById(message_div_id).innerHTML = "";
        var p_tag = document.createElement("p");
        p_tag.textContent = "No data found in the database. ";
        document.getElementById(message_div_id).appendChild(p_tag);
    }
}