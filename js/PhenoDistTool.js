
function validateForm(organism, error_message_div_id) {
    let checkbox_ids = document.querySelectorAll('input[id^=' + organism + '_phenotype_]');
    let check_flag = false;
    for (let i = 0; i < checkbox_ids.length; i++) {
        if (checkbox_ids[i].checked) {
            check_flag = true;
        }
    }
    if (check_flag) {
        return true;
    } else {
        if (document.getElementById(error_message_div_id).innerHTML === "") {
            var p_tag = document.createElement('p');
            p_tag.style.color = "red";
            p_tag.innerHTML = "Please select at least one phenotype.";
            document.getElementById(error_message_div_id).appendChild(p_tag);

            setTimeout(() => { document.getElementById(error_message_div_id).innerHTML = ""; }, 5000);
        }
        return false
    }
}

function uncheck_all_phenotypes(organism) {
    let ids = document.querySelectorAll('input[id^=' + organism + '_phenotype_]');

    for (let i = 0; i < ids.length; i++) {
        if (ids[i].checked) {
            ids[i].checked = false;
        }
    }
}

function check_all_phenotypes(organism) {
    let ids = document.querySelectorAll('input[id^=' + organism + '_phenotype_]');

    for (let i = 0; i < ids.length; i++) {
        if (!ids[i].checked) {
            ids[i].checked = true;
        }
    }
}

async function queryPhenotypesFromPhenotypeSelection(organism, dataset) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'queryPhenotypesFromPhenotypeSelection/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Dataset: dataset
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

async function queryDistinctDatasetsFromDataPanelSelection(organism) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'queryDistinctDatasetsFromDataPanelSelection/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism
            },
            success: function (response) {
                res = JSON.parse(response);

                resolve(res);
            }, error: function (xhr, status, error) {
                console.log('Error with code ' + xhr.status + ': ' + xhr.statusText);
                reject([]);
            }
        });
    });
}


async function queryDistinctChromosomesFromDataPanelSelection(organism, dataset) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'queryDistinctChromosomesFromDataPanelSelection/' + organism,
            type: 'GET',
            contentType: 'application/json',
            data: {
                Organism: organism,
                Dataset: dataset
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


async function updatePhenotypeSelections(phenotype_accordion_id, organism, dataset) {
    var result_array = await queryPhenotypesFromPhenotypeSelection(organism, dataset);
    var phenotype_selection_array = result_array['Phenotype_Selection_Array'];
    if (phenotype_selection_array) {
        // Check the existance of accordion instance and remove if it exists
        var accordion_instance = $("#" + String(phenotype_accordion_id)).accordion("instance");
        if (accordion_instance != undefined) {
            $("#" + String(phenotype_accordion_id)).accordion("destroy");
            $("#" + String(phenotype_accordion_id)).empty();
        }
        document.getElementById(phenotype_accordion_id).innerHTML = "";

        phenotype_group_array = [];
        for (let i = 0; i < phenotype_selection_array.length; i++) {
            current_id = phenotype_selection_array[i]['ID'];
            current_phenotype_group = phenotype_selection_array[i]['Phenotype_Group'];
            current_phenotype = phenotype_selection_array[i]['Phenotype'];

            if (!phenotype_group_array.includes(current_phenotype_group)) {
                // Create a h3 tag
                var h3_tag = document.createElement('h3');
                // Set id attribute
                h3_tag.id = "ui-id-h3-" + String(current_phenotype_group.replace(/ /g, "_"));
                // Set aria-controls attribute
                h3_tag.setAttribute("aria-controls", "ui-id-div-" + String(current_phenotype_group.replace(/ /g, "_")));
                // Set inner HTML
                h3_tag.innerHTML = current_phenotype_group;
                // Append h3 tag
                document.getElementById(phenotype_accordion_id).appendChild(h3_tag);

                // Create a div tag
                var div_tag = document.createElement('div');
                // Set id attribute
                div_tag.id = "ui-id-div-" + String(current_phenotype_group.replace(/ /g, "_"));
                // Set aria-labelledby attribute for the div element
                div_tag.setAttribute("aria-labelledby", "ui-id-h3-" + String(current_phenotype_group.replace(/ /g, "_")));
                // Append div tag
                document.getElementById(phenotype_accordion_id).appendChild(div_tag);

                phenotype_group_array.push(current_phenotype_group);
            }

            // Create an input tag
            var input_tag = document.createElement('input');
            // input_tag.type = 'checkbox';
            input_tag.type = 'radio';
            input_tag.id = current_id;
            input_tag.name = 'phenotype[]';
            input_tag.value = current_phenotype;

            // Create a label tag
            const label_tag = document.createElement('label');
            label_tag.setAttribute('for', current_id);
            label_tag.style.marginLeft = '2px';
            label_tag.style.marginRight = '10px';
            label_tag.textContent = current_phenotype;

            // Append input tag and label tag to the body or another container
            document.getElementById("ui-id-div-" + String(current_phenotype_group.replace(/ /g, "_"))).appendChild(input_tag);
            document.getElementById("ui-id-div-" + String(current_phenotype_group.replace(/ /g, "_"))).appendChild(label_tag);
        }

        // Make accordion
        $("#" + String(phenotype_accordion_id)).accordion({
            active: false,
            collapsible: true,
            icons: ""
        });

        // var options = $("#"+String(phenotype_accordion_id)).accordion("option");
        // console.log(options);
    }
}


async function updateDatasetSelections(dataset_selection_id, organism) {
    var result_array = await queryDistinctDatasetsFromDataPanelSelection(organism);
    var data_panel_array = result_array['Data_Panel_Array'];
    if (data_panel_array) {
        document.getElementById(dataset_selection_id).innerHTML = "";
        for (let i = 0; i < data_panel_array.length; i++) {
            var option_tag = document.createElement('option');
            option_tag.value = data_panel_array[i]['Dataset'];
            option_tag.innerHTML = data_panel_array[i]['Dataset'];
            document.getElementById(dataset_selection_id).appendChild(option_tag);
        }
    }
}


async function updateChromosomeSelections(chromosome_selection_id, organism, dataset) {
    var result_array = await queryDistinctChromosomesFromDataPanelSelection(organism, dataset);
    var chromosome_array = result_array['Chromosome_Array'];
    if (chromosome_array) {
        document.getElementById(chromosome_selection_id).innerHTML = "";
        for (let i = 0; i < chromosome_array.length; i++) {
            var option_tag = document.createElement('option');
            option_tag.value = chromosome_array[i]['Chromosome'];
            option_tag.innerHTML = chromosome_array[i]['Chromosome'];
            document.getElementById(chromosome_selection_id).appendChild(option_tag);
        }
    }
}
