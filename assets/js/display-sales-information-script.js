var table = $("#salesReportTbl");

$(document).ready(function () {
    var urlParams = getEatTypeAndBranchFromUrl();
    displaySalesReport(urlParams);

    $(document).on("click", ".btn-si-data", function () {
        var id = $(this).data("rid");
        window.location.href = "salesReport.php?id=" + id;
    });
});

function getEatTypeAndBranchFromUrl() {
    var queryString = window.location.search.substring(1);
    var queryParts = queryString.split("/");

    var eatType = "";
    var franchise = "";

    queryParts.forEach(function (part) {
        if (part.startsWith("tp=")) {
            eatType = part.substring(3);
        } else if (part.startsWith("franchise=")) {
            franchise = part.substring(10);
        }
    });

    var franchiseFormattedMap = {
        PotatoCorner: "Potato Corner",
        MacaoImperial: "Macao Imperial",
        AuntieAnne: "Auntie Anne",
    };

    var eatTypeFormattedMap = {
        DineIn: "Dine-In",
        TakeOut: "Take-Out",
        Delivery: "Delivery",
    };

    var franchiseFormatted = franchiseFormattedMap[franchise] || franchise;
    var eatTypeFormatted = eatTypeFormattedMap[eatType] || eatType;

    return {
        eatType: eatType,
        franchise: franchise,
        eatTypeFormatted: eatTypeFormatted,
        franchiseFormatted: franchiseFormatted,
    };
}

function displaySalesReport(urlParams) {
    var dashFranchise = urlParams.franchiseFormatted
        .toLowerCase()
        .replace(/\s+/g, "-");
    var dashServices = urlParams.eatTypeFormatted.toLowerCase();

    $.ajax({
        method: "POST",
        data: { dashFranchise, dashServices },
        url: "../../phpscripts/display-sales-information.php",
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                var tableBody = $("#salesReportTbl tbody");
                tableBody.empty();

                response.sales_info.forEach(function (sale) {
                    var img = getFranchiseImage(sale.franchisee);
                    var formattedGrandTotal = parseFloat(
                        sale.grand_total
                    ).toLocaleString();

                    var row = `
                        <tr class="btn-si-data" data-rid="${sale.report_id}">
                            <td>
                                <img src="../../assets/images/${img}" alt="img" class="franchise-logo">
                            </td>
                            <td>₱ ${formattedGrandTotal}</td>
                            <td>${toTitleCase(sale.services)}</td>
                            <td>${sale.date_added}</td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        },
    });
}

function getFranchiseImage(franchise) {
    switch (franchise) {
        case "potato-corner":
            return "PotCor.png";
        case "auntie-anne":
            return "AuntieAnn.png";
        case "macao-imperial":
            return "MacaoImp.png";
        default:
            return "default-image.png";
    }
}

function toTitleCase(str) {
    return str.replace(/\w\S*/g, function (text) {
        return text.charAt(0).toUpperCase() + text.substr(1).toLowerCase();
    });
}


$(document).ready(function () {
    $("#file-upload").on("change", function () {
        var file = this.files[0];
        if (file) {
            // Validate file type by extension
            var allowedExtensions = /(\.xls|\.xlsx|\.csv)$/i;
            if (!allowedExtensions.exec(file.name)) {
                alert("Error: Please upload a valid file (Excel or CSV: .xls, .xlsx, .csv only).");
                this.value = ""; // Clear the file input
                return;
            }

            // Create FormData to send file to the server
            var formData = new FormData();
            formData.append("sales_report", file);

            // Send the file via AJAX
            $.ajax({
                url: "../../phpscripts/upload-sales-report.php", // Server-side script to handle the upload
                type: "POST",
                data: formData,
                processData: false, // Prevent jQuery from converting the FormData object
                contentType: false, // Set content type to false for file uploads
                success: function (response) {
                    if (response.status === "success") {
                        // Show success message
                        alert("Upload Successful: The file was uploaded successfully.");
                        // Redirect to encodeSales.php after a delay
                        setTimeout(() => {
                            window.location.href = "encodeSales.php";
                        }, 2000);
                    } else {
                        // Show error message from the server
                        alert("Error: " + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    // Show general error message
                    alert("Error: An unexpected error occurred while uploading the file.");
                    console.error(error);
                },
            });
        }
    });
});



