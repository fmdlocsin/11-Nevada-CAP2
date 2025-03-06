var table = $("#totalExpensesTbl");

$(document).ready(function () {
    displayExpenses();

    $(document).on("click", ".btn-ex-data", function () {
        var id = $(this).data("ex-id");
        window.location.href = "viewExpenses.php?id=" + id;
    });
});

function displayExpenses(franchise, location, category, startDate, endDate) {
    $.ajax({
        method: "GET",
        url: "../../phpscripts/display-expenses.php",
        data: { 
            franchise: franchise,
            location: location,
            category: category,
            startDate: startDate,
            endDate: endDate
        },
        dataType: "json",
        success: function (response) {
            console.log("Expense Response:", response); // Debug log
            if (response.status === "success") {
                var tableBody = $("#totalExpensesTbl tbody");
                tableBody.empty();
                response.details.forEach(function (info) {
                    var img = getFranchiseImage(info.franchisee);
                    var formattedGrandTotal = parseFloat(info.expense_amount).toLocaleString();
                    var row = `
                        <tr class="btn-ex-data" data-ex-id="${info.ex_id}">
                            <td>
                                <img src="../../assets/images/${img}" alt="img" class="franchise-logo">
                            </td>
                            <td>₱${formattedGrandTotal}</td>
                            <td>${mapExpenseType(info.expense_type)}</td>
                            <td>${info.date_added}</td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                console.log("No expense data found.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching expenses:", error);
        }
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

function mapExpenseType(type) {
    switch (type) {
        case "franchiseFees":
            return "Franchise Fees";
        case "rentalsFees":
            return "Rental Fees";
        case "royaltyFees":
            return "Royalty Fees";
        case "maintenanceFees":
            return "Maintenance Fees";
        case "utilitiesFees":
            return "Utilities Fees";
        case "agencyFees":
            return "Agency Fees";
        default:
            return type;
    }
}
