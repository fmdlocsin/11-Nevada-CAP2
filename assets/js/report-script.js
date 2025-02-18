$(document).ready(function () {
    displayLocation();

    // Reset modal content when hidden
    $("#modalReport").on("hidden.bs.modal", function () {
        var defaultContent = `
            <div class="row flex-column gap-4">
                <div class="col text-center">
                    <button class="btn bg-secondary-subtle w-100 select-franchisee-report" data-franchisee="potato-corner">
                        <img src="../../assets/images/PotCor.png" height="100%" width="150px" alt="img">
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn bg-secondary-subtle w-100 select-franchisee-report" data-franchisee="auntie-anne">
                        <img src="../../assets/images/AuntieAnn.png" height="100%" width="150px" alt="img">
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn bg-secondary-subtle w-100 select-franchisee-report" data-franchisee="macao-imperial">
                        <img src="../../assets/images/MacaoImp.png" height="100%" width="150px" alt="img">
                    </button>
                </div>
            </div>
        `;

        $(this).find(".modal-body").html(defaultContent);
    });

    $(document).on("change", "#branchSelect", function () {
        var selectedOption = $(this).find("option:selected");
        var location = selectedOption.val();
        var acId = selectedOption.data("ac-id");
        var franchisee = $(this).data("franchisee");

        if (acId) {
            window.location.href =
                "inventoryNewReport?id=" +
                acId +
                "&franchisee=" +
                franchisee +
                "&branch=" +
                location;
        }
    });

    displayItemsInTable();  

    $("#save-button").on("click", function () {
        if (validateInputs()) {
            $("#confirmationModal").show();
        } else {
            $("#liveToast .toast-body p")
                .text("Please fill in all required fields.")
                .addClass("text-danger");
            $("#liveToast").toast("show");
        }
    });

    $("#cancelButton").on("click", function () {
        $("#confirmationModal").hide();
    });

    $("#confirmSaveButton").on("click", function () {
        $("#confirmationModal").hide();
        saveData();
    });

    $("#nextButton").on("click", function () {
        // Logic for the 'Next' button
        // E.g., navigate to the next page or step
    });

    $("#downloadButton").on("click", function () {
        // Logic for the 'Download' button
        // E.g., initiate a file download or generate a report
    });

    $(document).ready(function () { // this one 
        $("#csvUpload").on("change", function () {
            uploadCsvFile();
        });
    });
    
    function uploadCsvFile() {
        let fileInput = document.getElementById("csvUpload");
        if (fileInput.files.length === 0) {
            alert("Please select a CSV file.");
            return;
        }
    
        let file = fileInput.files[0];
        let reader = new FileReader();
    
        reader.onload = function (event) {
            let csvData = event.target.result;
            let rows = csvData.split("\n").map(row => row.split(",")); // Split CSV into rows and columns
    
            let tableRows = document.querySelectorAll(".clickedInventory-table tbody tr");
    
            rows.forEach((data, index) => {
                if (index === 0) return; // Skip header row
    
                let row = tableRows[index - 1]; // Match CSV row to table row
    
                if (row) {
                    row.querySelector("input[name^='beginning']").value = data[1] || 0; // Beginning
                    row.querySelector("input[name^='delivery']").value = data[2] || 0; // Delivery
                    row.querySelector("input[name^='waste']").value = data[3] || 0; // Waste
                    row.querySelector("input[name^='sold']").value = data[4] || 0; // Sold
                    row.querySelector("input[name^='remarks']").value = data[5] || ""; // Remarks
                }
            });
        };
    
        reader.readAsText(file);
    }
    

    displayReports();

    $(document).on("click", ".check-report-details", function () {
        var franchisee = $(this).data("franchisee");
        var location = $(this).data("location");
        var datetimeAdded = $(this).data("date-added");

        var reportDetailsUrl = `clickedInventory?franchisee=${encodeURIComponent(
            franchisee
        )}&location=${encodeURIComponent(
            location
        )}&datetime_added=${encodeURIComponent(datetimeAdded)}`;

        // Navigate to the report details page
        window.location.href = reportDetailsUrl;

        console.log("Navigating to:", reportDetailsUrl);
    });

    displaySelectedReport();
});

function displayLocation() {
    $(document).on("click", ".select-franchisee-report", function () {
        var modal = $("#modalReport");
        var franchisee = $(this).data("franchisee");

        $.ajax({
            method: "POST",
            url: "../../phpscripts/get-branches.php",
            data: { franchisee: franchisee },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    var imgFile = "";
                    if (franchisee == "potato-corner") {
                        imgFile = "PotCor.png";
                    } else if (franchisee == "auntie-anne") {
                        imgFile = "AuntieAnn.png";
                    } else if (franchisee == "macao-imperial") {
                        imgFile = "MacaoImp.png";
                    }

                    var htmlContent = `
                        <div class="text-center">
                            <img src="../../assets/images/${imgFile}" height="100%" width="150px" alt="img">
                        </div>
                        <div class="mt-3">
                            <select class="form-select" id="branchSelect" data-franchisee="${franchisee}">
                                <option selected disabled>Select a branch</option>`;

                    response.details.forEach(function (detail) {
                        htmlContent += `<option value="${detail.location}" data-ac-id="${detail.ac_id}">${detail.location}</option>`;
                    });

                    htmlContent += `
                            </select>
                        </div>`;

                    modal.find(".modal-body").html(htmlContent);
                    modal.modal("show");
                } else {
                    $("#liveToast .toast-body p")
                        .text(response.message)
                        .addClass("text-danger");
                    $("#liveToast").toast("show");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            },
        });
    });
}

function getUrlParameters() {
    var params = {};
    var queryString = window.location.search.substring(1); // Remove the '?' at the start of the query string
    var pairs = queryString.split("&");

    pairs.forEach(function (pair) {
        var [key, value] = pair.split("=");
        if (key && value) {
            params[key] = decodeURIComponent(value.replace(/\+/g, " "));
        }
    });

    return params;
}

function displayItemsInTable() {
    var params = getUrlParameters();
    var franchisee = params.franchisee;

    if (!franchisee) {
        console.error("Franchisee parameter is missing in the URL.");
        return;
    }

    $.ajax({
        method: "POST",
        url: "../../phpscripts/get-items.php",
        data: { franchisee: franchisee },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                var tableBody = $(".clickedInventory-table tbody");
                tableBody.empty();

                response.items.forEach(function (item) {
                    var row = `
                        <tr data-item-id="${item.item_id}">
                            <td>${item.item_name}</td>
                            <td><input type="number" name="beginning-${item.item_id}" required></td>
                            <td><input type="number" name="delivery-${item.item_id}" required></td>
                            <td><input type="number" name="waste-${item.item_id}" required></td>
                            <td><input type="number" name="sold-${item.item_id}" required></td>
                            <td><input type="text" name="remarks-${item.item_id}"></td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        },
    });
}

function validateInputs() {
    var isValid = true;

    $(".clickedInventory-table tbody tr").each(function () {
        var itemId = $(this).data("item-id");
        var beginning = $(this)
            .find('input[name="beginning-' + itemId + '"]')
            .val();
        var delivery = $(this)
            .find('input[name="delivery-' + itemId + '"]')
            .val();
        var waste = $(this)
            .find('input[name="waste-' + itemId + '"]')
            .val();
        var sold = $(this)
            .find('input[name="sold-' + itemId + '"]')
            .val();

        if (
            beginning === "" ||
            delivery === "" ||
            waste === "" ||
            sold === ""
        ) {
            isValid = false;
            return false; // Exit the .each() loop
        }
    });

    return isValid;
}

function saveData() {
    var itemsData = [];

    $(".clickedInventory-table tbody tr").each(function () {
        var itemId = $(this).data("item-id");
        var beginning = $(this)
            .find('input[name="beginning-' + itemId + '"]')
            .val();
        var delivery = $(this)
            .find('input[name="delivery-' + itemId + '"]')
            .val();
        var waste = $(this)
            .find('input[name="waste-' + itemId + '"]')
            .val();
        var sold = $(this)
            .find('input[name="sold-' + itemId + '"]')
            .val();
        var remarks = $(this)
            .find('input[name="remarks-' + itemId + '"]')
            .val();

        itemsData.push({
            item_id: itemId,
            beginning: beginning,
            delivery: delivery,
            waste: waste,
            sold: sold,
            remarks: remarks,
        });
    });

    $.ajax({
        method: "POST",
        url: "../../phpscripts/save-items.php",
        data: {
            items: JSON.stringify(itemsData),
            franchisee: getUrlParameters().franchisee,
            branch: getUrlParameters().branch,
        },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                // $("#successModal").modal("show");

                alert("Data saved successfully!");

                window.location.href = "inventory2.php";
            } else {
                $("#liveToast .toast-body p")
                    .text(response.message)
                    .addClass("text-danger");
                $("#liveToast").toast("show");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        },
    });
}

function displayReports() {
    $.ajax({
        method: "POST",
        url: "../../phpscripts/get-reports.php",
        dataType: "json",
        success: function (response) {
            console.log("User ID:", response.user_id);  // ✅ Log User ID
            console.log("User Type:", response.user_type);  // ✅ Log User Type
            console.log("User Branch:", response.branch);  // ✅ Log Branch
            console.log("Full Response:", response);
            
            if (response.status === "success") {
                var tableBody = $(".inventory-table tbody");
                tableBody.empty();

                response.reports.forEach(function (report) {
                    var date = new Date(report.datetime_added);
                    var formattedDate = date.toLocaleDateString("en-US", {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                    });

                    var franchiseeName = "";
                    var franchiseeImg = "";

                    if (report.franchisee == "potato-corner") {
                        franchiseeName = "Potato Corner";
                        franchiseeImg = "PotCor.png";
                    } else if (report.franchisee == "auntie-anne") {
                        franchiseeName = "Auntie Anne's";
                        franchiseeImg = "AuntieAnn.png";
                    } else if (report.franchisee == "macao-imperial") {
                        franchiseeName = "Macao Imperial";
                        franchiseeImg = "MacaoImp.png";
                    }

                    var row = `
                        <tr class="check-report-details" data-franchisee="${report.franchisee}" data-location="${report.location}" data-date-added="${report.datetime_added}">
                            <td><img src="../../assets/images/${franchiseeImg}" alt="img"> ${franchiseeName}</td>
                            <td>${report.location}</td>
                            <td>${formattedDate}</td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                console.error("Error:", response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            console.log("XHR Response:", xhr.responseText);
            console.log("Status:", status);
        },
    });
}



function displaySelectedReport() {
    var params = getUrlParameters();
    var franchisee = params.franchisee;
    var location = params.location;
    var datetimeAdded = params.datetime_added;

    if (franchisee && location && datetimeAdded) {
        $.ajax({
            method: "POST",
            url: "../../phpscripts/get-selected-report.php",
            data: {
                franchisee: franchisee,
                location: location,
                datetime_added: datetimeAdded,
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    var tableBody = $("#clickedInventory2Tbl tbody");
                    tableBody.empty();
                    response.items.forEach(function (item) {
                        var beginning = Math.max(0, item.beginning || 0);
                        var delivery = Math.max(0, item.delivery || 0);
                        var waste = Math.max(0, item.waste || 0);
                        var sold = Math.max(0, item.sold || 0);

                        var onHand = beginning + delivery;
                        var ending = Math.max(0, onHand - sold);

                        var row = `
                            <tr data-item-id="${item.item_id}">
                                <td>${item.item_name || "N/A"}</td>
                                <td>${item.uo || "0"}</td>
                                <td>${beginning}</td>
                                <td>${delivery}</td>
                                <td>${waste}</td>
                                <td>${onHand}</td>
                                <td>${ending}</td>
                                <td>${sold}</td>
                                <td>${item.remarks}</td>
                            </tr>
                        `;

                        tableBody.append(row);
                    });

                    franchiseeName = "";
                    if (response.franchisee == "potato-corner") {
                        franchiseeName = "Potato Corner";
                    } else if (response.franchisee == "auntie-anne") {
                        franchiseeName = "Auntie Anne's";
                    } else if (response.franchisee == "macao-imperial") {
                        franchiseeName = "Macao Imperial";
                    }

                    $("#franchisee").text(franchiseeName);
                    $("#filledBy").text(
                        response.user ? response.user.user_name : "N/A"
                    );
                    $("#branch").text(response.location);

                    var date = new Date(datetimeAdded);
                    var month = String(date.getMonth() + 1).padStart(2, "0");
                    var day = String(date.getDate()).padStart(2, "0");
                    var year = String(date.getFullYear()).slice(-2);

                    $("#date").text(`${month}/${day}/${year}`);
                } else {
                    console.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            },
        });
    }
}

$(document).ready(function () {
    $("#uploadCsvBtn").on("click", function () {
        if (!$(this).data("clicked")) {
            $(this).text("Choose File").data("clicked", true); // Change button text on first click
            $("#csvUpload").removeClass("d-none").insertAfter($(this)).click(); // Show file input and move it
        } else {
            $("#csvUpload").click(); // If already clicked, just trigger file selection
        }
    });

    $("#csvUpload").on("change", function () {
        let file = this.files[0]; // Get the selected file
        if (file) {
            $("#fileName").text(file.name); // Display only the file name
            $("#uploadCsvBtn").text("File Selected ✓").addClass("btn-success"); // Update button text
        }
    });
});



