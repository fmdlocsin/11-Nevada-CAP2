$(document).ready(function () {
    displayStoreSchedules();
    showStoreDetails();
});

function getUrlParameter(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function displayStoreSchedules() {
    var listContainer = $("#branchList");
    var str = getUrlParameter("str");

    $.ajax({
        method: "POST",
        url: "../../phpscripts/display-stores-schedules.php",
        data: { str: str },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                listContainer.empty();
                response.employees.forEach(function (employee) {
                    var img = getFranchiseImage(employee.franchisee);
                    var formattedFranchisee = formatFranchiseeName(employee.franchisee);
                    var storeHTML = `
                        <img class="logo brand-logo" src="../../assets/images/${img}" alt="${formattedFranchisee}">
                        <button class="select-branch" data-ac-id="${employee.assigned_at}">${employee.branch}</button>
                    `;
                    listContainer.append(storeHTML);
                });
            } else {
                console.log(response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        },
    });
}

function showStoreDetails() {
    $(document).on("click", ".select-branch", function () {
        // Remove active class from all buttons
        $(".select-branch").removeClass("active");
        // Add active class to the clicked button
        $(this).addClass("active");

        var id = $(this).data("ac-id");

        $.ajax({
            method: "POST",
            url: "../../phpscripts/get-store-details.php",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    var employees = response.employees;
                    var htmlContent = "";
                    employees.forEach(function (employee) {
                        htmlContent += `
                            <tr>
                                <td>${employee.user_name}</td>
                                <td>${employee.user_shift || "Unscheduled"}</td>
                            </tr>
                        `;
                    });
                    $("#employees-section tbody").html(htmlContent);
                    // Use the min_employees value returned in the response to update the count display
                    var currentCount = employees.length;
                    var minEmployees = response.min_employees || 0;
                    $(".count-title").text(currentCount + "/" + minEmployees);
                } else {
                    console.log(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            },
        });
    });
}

function formatFranchiseeName(name) {
    return name
        .split("-")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
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
