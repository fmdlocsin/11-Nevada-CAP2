$(document).ready(function () {
    createEncodedSales();
    var urlParams = getEatTypeAndBranchFromUrl();
    updateSalesSectionForm(urlParams);
    calculateGrandTotal();

    // Ensure CSV Upload Button Exists
    if ($("#csvUploadSales").length === 0) {
        $(".form-group").append(`
            <label for="csvUploadSales" class="form-label">Upload Sales CSV</label>
            <input type="file" id="csvUploadSales" accept=".csv" class="form-control">
        `);
    }

    // CSV Upload Event Listener
    $("#csvUploadSales").on("change", function () {
        uploadCsvFileSales();
    });

    function uploadCsvFileSales() {
        let fileInput = document.getElementById("csvUploadSales");
        if (fileInput.files.length === 0) {
            alert("Please select a CSV file.");
            return;
        }

        let file = fileInput.files[0];
        let reader = new FileReader();

        reader.onload = function (event) {
            let csvData = event.target.result;
            let rows = csvData.split("\n").map(row => row.split(",")); // Split CSV into rows and columns

            let urlParams = getEatTypeAndBranchFromUrl();
            let eatType = urlParams.eatTypeFormatted.trim();

            console.log("CSV Data Loaded:", rows);
            console.log("Detected Transaction Type:", eatType);

            setTimeout(() => {
                let productNameField = document.querySelector(".input-product-name"); // Directly reference field
                let cashCardField = document.querySelector(".input-cash-card");
                let gcashField = document.querySelector(".input-gcash");
                let paymayaField = document.querySelector(".input-paymaya");
                let otherSalesField = document.querySelector(".input-total-sales");
                let grabFoodField = document.querySelector(".input-grab-food");
                let foodPandaField = document.querySelector(".input-food-panda");

                if (!productNameField) {
                    console.warn("Product Name field not found. Retrying...");
                    return;
                }

                if (rows.length > 1) { // Skip header row
                    let data = rows[1].map(value => value.trim()); // Trim whitespace from each value

                    let requiredColumns = (eatType === "Delivery") ? 4 : 5; // Delivery needs 4, others need 5

                    if (data.length < requiredColumns) {
                        console.warn(`CSV row does not have enough columns. Expected ${requiredColumns}, but got ${data.length}.`);
                        return;
                    }

                    console.log("Assigning Product Name:", data[0]);
                    productNameField.value = data[0] || "";
                    productNameField.dispatchEvent(new Event("input"));

                    if (eatType === "Dine-In" || eatType === "Take-Out") {
                        if (cashCardField) {
                            cashCardField.value = data[1] || 0;
                            cashCardField.dispatchEvent(new Event("input"));
                        }
                        if (gcashField) {
                            gcashField.value = data[2] || 0;
                            gcashField.dispatchEvent(new Event("input"));
                        }
                        if (paymayaField) {
                            paymayaField.value = data[3] || 0;
                            paymayaField.dispatchEvent(new Event("input"));
                        }
                        if (otherSalesField) {
                            otherSalesField.value = data[4] || 0;
                            otherSalesField.dispatchEvent(new Event("input"));
                        }
                    } else if (eatType === "Delivery") {
                        if (grabFoodField) {
                            grabFoodField.value = data[1] || 0;
                            grabFoodField.dispatchEvent(new Event("input"));
                        }
                        if (foodPandaField) {
                            foodPandaField.value = data[2] || 0;
                            foodPandaField.dispatchEvent(new Event("input"));
                        }
                        if (otherSalesField) {
                            otherSalesField.value = data[3] || 0;
                            otherSalesField.dispatchEvent(new Event("input"));
                        }
                    }
                } else {
                    console.warn("CSV file does not contain data rows.");
                }
            }, 500); // Ensure form fields exist before inserting values
        };

        reader.readAsText(file);
    }
});

function calculateGrandTotal() {
    $(".amount-input").on("input", function () {
        let grandTotal = 0;

        $(".amount-input").each(function () {
            let value = parseFloat($(this).val());
            if (!isNaN(value)) {
                grandTotal += value;
            }
        });

        $(".input-grand-total").val(grandTotal.toFixed(2));
    });

    // Auto-trigger calculation after CSV upload
    setTimeout(() => {
        let grandTotal = 0;
        $(".amount-input").each(function () {
            let value = parseFloat($(this).val());
            if (!isNaN(value)) {
                grandTotal += value;
            }
        });

        $(".input-grand-total").val(grandTotal.toFixed(2));
    }, 600);
}





function getEatTypeAndBranchFromUrl() {
    var urlParams = new URLSearchParams(window.location.search);

    var eatType = urlParams.get("tp") || ""; // Get eat type
    var franchise = urlParams.get("franchise") || ""; // Get franchise
    var location = urlParams.get("location") || ""; // Get location

    // Map franchise names to database-compatible formats
    var franchiseFormattedMap = {
        "PotatoCorner": "potato-corner",
        "MacaoImperial": "macao-imperial",
        "AuntieAnne": "auntie-anne"
    };

    var eatTypeFormattedMap = {
        "dinein": "Dine-In",
        "takeout": "Take-Out",
        "delivery": "Delivery"
    };
    
    var eatTypeLower = eatType.toLowerCase().replace(/\s/g, ''); // Normalize input
    var eatTypeFormatted = eatTypeFormattedMap[eatTypeLower] || eatType;
    

    // Format retrieved values
    var franchiseFormatted = franchiseFormattedMap[franchise] || franchise;
    // var eatTypeFormatted = eatTypeFormattedMap[eatType] || eatType;
    var locationFormatted = decodeURIComponent(location); // Ensure location is properly decoded

    console.log("Extracted Parameters:", {
        eatType,
        franchise,
        location: locationFormatted,
        eatTypeFormatted,
        franchiseFormatted
    });

    return {
        eatType: eatType,
        franchise: franchise,
        location: locationFormatted,
        eatTypeFormatted: eatTypeFormatted,
        franchiseFormatted: franchiseFormatted
    };
}

function updateSalesSectionForm(urlParams) {
    var salesSectionHTML = "";

    if (urlParams.eatTypeFormatted === "Take-Out" || urlParams.eatTypeFormatted === "Dine-In") {
        salesSectionHTML = `
            <div class="sales-section">
                <div class="details transactionType">
                    <p>${urlParams.eatTypeFormatted}</p>
                    <div class="fields">
                        <div class="input-field transactionType">
                            <label>Cash/Card:</label>
                            <input type="number" class="amount-input input-cash-card" placeholder="Enter Amount" required>
                        </div>
                        <div class="input-field transactionType">
                            <label>GCash:</label>
                            <input type="number" class="amount-input input-gcash" placeholder="Enter Amount" required>
                        </div>
                        <div class="input-field transactionType">
                            <label>Paymaya:</label>
                            <input type="number" class="amount-input input-paymaya" placeholder="Enter Amount" required>
                        </div>
                        <div class="input-field transactionType">
                            <label>Other Sales:</label>
                            <input type="number" class="amount-input input-total-sales" placeholder="Enter Amount" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        salesSectionHTML = `
            <div class="sales-section">
                <div class="details transactionType">
                    <p>${urlParams.eatTypeFormatted}</p>
                    <div class="fields">
                        <div class="input-field transactionType">
                            <label>GrabFood:</label>
                            <input type="number" class="amount-input input-grab-food" placeholder="Enter Amount" required>
                        </div>
                        <div class="input-field transactionType">
                            <label>FoodPanda:</label>
                            <input type="number" class="amount-input input-food-panda" placeholder="Enter Amount" required>
                        </div>
                        <div class="input-field transactionType">
                            <label>Other Sales:</label>
                            <input type="number" class="amount-input input-total-sales" placeholder="Enter Amount" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    $("#salesSectionForm").html(salesSectionHTML);

    // Wait for form update before setting values
    setTimeout(() => {
        var encoderName = $("body").data("user-name");
        var branchLocation = urlParams.location; // Capture the correct location

        console.log("Sending Franchise & Location:", { formattedFranchisee: urlParams.franchiseFormatted, branchLocation });

        $.ajax({
            method: "POST",
            data: { formattedFranchisee: urlParams.franchiseFormatted, branchLocation: branchLocation },
            url: "../../phpscripts/display-branches.php",
            dataType: "json",
            success: function (response) {
                if (response.status === "success" && response.details.length > 0) {
                    var info = response.details[0];

                    console.log("Branch Info Retrieved:", info); // Verify correct branch is returned

                    $(".input-franchise-name").val(urlParams.franchiseFormatted);
                    $(".input-location").val(info.location); // Ensure correct location is set
                    $(".input-encoders-name").val(encoderName);
                    $(".input-date").val(new Date().toISOString().slice(0, 10));

                    $(".save-encoded-sales").attr("data-ac-id", info.ac_id);
                    $(".save-encoded-sales").attr("data-services", urlParams.eatTypeFormatted);
                    $(".save-encoded-sales").attr("data-franchise", urlParams.franchiseFormatted);
                } else {
                    console.error("Branch details not found:", response);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error fetching branch details:", error);
            },
        });
    }, 500);
}





function addAnotherTransaction() {
    $(document).on("click", ".add-transaction-btn", function () {
        var newForm = $(".transaction-form").first().clone();
        newForm.find("input").val("");
        $(".transaction-forms-container").append(newForm);
    });
}


function createEncodedSales() {
    $(document).on("click", ".save-encoded-sales", function () {
        var urlParams = getEatTypeAndBranchFromUrl();
        var encoderName = $("body").data("user-name");
        var franchise = $(".input-franchise-name").val();
        var location = $(".input-location").val();
        var date = $(".input-date").val();
        var acId = $(this).attr("data-ac-id");
        var services = urlParams.eatTypeFormatted; // Ensure correct service type

        var transactions = [];
        $(".transaction-form").each(function () {
            var productName = $(this).find(".input-product-name").val();
            var cashCard = $(this).find(".input-cash-card").val() || 0;
            var gCash = $(this).find(".input-gcash").val() || 0;
            var paymaya = $(this).find(".input-paymaya").val() || 0;
            var grabFood = $(this).find(".input-grab-food").val() || 0;
            var foodPanda = $(this).find(".input-food-panda").val() || 0;
            var totalSales = $(this).find(".input-total-sales").val() || 0;
            var grandTotal = $(".input-grand-total").val() || 0;

            transactions.push({
                encoderName: encoderName,
                franchise: franchise,
                location: location,
                date: date,
                productName: productName,
                cashCard: cashCard,
                gCash: gCash,
                paymaya: paymaya,
                grabFood: grabFood,
                foodPanda: foodPanda,
                totalSales: totalSales,
                grandTotal: grandTotal,
                acId: acId,
                services: services
            });
        });

        console.log("Submitting Transactions:", transactions);

        $.ajax({
            method: "POST",
            url: "../../phpscripts/save-encoded-sales.php",
            data: JSON.stringify(transactions),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                console.log("AJAX Response:", response);
                if (response.status === "success") {
                    $("input, button, textarea, select, a").prop("disabled", true);
                    displayModal("Success", response.message, "#198754");

                    setTimeout(function () {
                        closeModal();
                        window.location.href = "../salesPerformance/sales";
                    }, 3000);
                } else {
                    displayModal("Error", response.message, "#dc3545");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                displayModal("Error", "Failed to save sales report. Please try again.", "#dc3545");
            },
        });
    });
}





function displayModal(title, message, backgroundColor) {
    $("#modalTitle").text(title);
    $("#modalMessage").text(message);
    $("#modalOverlay").fadeIn();
    $("#modalBox").css("background-color", backgroundColor);
    setTimeout(closeModal, 3000);
}

function closeModal() {
    $("#modalOverlay").fadeOut();
}

$(document).on("click", "#modalCloseBtn", closeModal);

$(document).ready(function () {
    $("#uploadCsvSalesBtn").on("click", function () {
        $("#csvUploadSales").click();
    });

    $("#csvUploadSales").on("change", function () {
        let fileName = this.files.length > 0 ? this.files[0].name : "No file selected";
        $("#fileNameDisplay").text(fileName);
        $("#uploadCsvSalesBtn").text("File Selected ✓").addClass("btn-success");
    });
});


$(document).ready(function () {
    urlParams = getEatTypeAndBranchFromUrl(); // Assign global variable

    console.log("Captured URL Parameters:", urlParams); // Debugging Log

    updateSalesSectionForm(urlParams);
    createEncodedSales();
    calculateGrandTotal();
});

