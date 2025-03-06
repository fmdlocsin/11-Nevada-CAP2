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
    var urlParams = new URLSearchParams(window.location.search);

    var eatType = urlParams.get("tp") || ""; // Get eat type or default to empty string
    var franchise = urlParams.get("franchise") || ""; // Get franchise or default to empty string
    var location = urlParams.get("location") || ""; // Get location or default to empty string

    // Map franchise names to database-compatible formats
    var franchiseFormattedMap = {
        "PotatoCorner": "Potato Corner",
        "MacaoImperial": "Macao Imperial",
        "AuntieAnne": "Auntie Anne"
    };

    var eatTypeFormattedMap = {
        "DineIn": "Dine-In",
        "TakeOut": "Take-Out",
        "Delivery": "Delivery"
    };

    // Format retrieved values
    var franchiseFormatted = franchiseFormattedMap[franchise] || franchise;
    var eatTypeFormatted = eatTypeFormattedMap[eatType] || eatType;
    var locationFormatted = decodeURIComponent(location); // Ensure location is properly decoded

    return {
        eatType: eatType,
        franchise: franchise,
        location: locationFormatted, // Use the correctly formatted location
        eatTypeFormatted: eatTypeFormatted,
        franchiseFormatted: franchiseFormatted
    };
}


function displaySalesReport(urlParams) {
    var dashFranchise = urlParams.franchiseFormatted
        .toLowerCase()
        .replace(/\s+/g, "-");
    var dashServices = urlParams.eatTypeFormatted.toLowerCase();
    var branchLocation = urlParams.location; // Capture location

    $.ajax({
        method: "POST",
        data: { dashFranchise, dashServices, branchLocation },
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
                            <td>${sale.location}</td>
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