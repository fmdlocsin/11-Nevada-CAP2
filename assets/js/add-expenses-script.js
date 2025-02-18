$(document).ready(function () {
    addExpenses();

    $(".controllable-form").hide();
    $("#selectedExpense").change(function () {
        var selectedExpense = $(this).val();

        if (selectedExpense === "controllable-expenses") {
            $(".form-data1").show();
            $(".form-data2").hide();
            $(".form-data3").hide();
        } else if (selectedExpense === "non-controllable-expenses") {
            $(".form-data1").hide();
            $(".form-data2").show();
            $(".form-data3").hide();
        } else if (selectedExpense === "other-expenses") {
            $(".form-data1").hide();
            $(".form-data2").hide();
            $(".form-data3").show();
        }
    });

   // Map human-readable franchise names to database-friendly values
   var franchiseMap = {
    "Potato Corner": "potato-corner",
    "Auntie Anne's": "auntie-anne",
    "Macao Imperial Tea": "macao-imperial"
};

$("#selectedFranchise").change(function () {
    var selectedFranchise = $(this).val();
    var franchiseId = franchiseMap[selectedFranchise] || selectedFranchise; // Convert to database-friendly value

    console.log("Selected Franchise (User Input):", selectedFranchise);
    console.log("Mapped Franchise ID (Database Value):", franchiseId);

    $("#franchiseLocation").html('<option value="">Loading...</option>');

    if (franchiseId !== "") {
        $.ajax({
            url: "../../phpscripts/fetch-locations.php",
            method: "POST",
            data: { franchiseId: franchiseId },
            dataType: "json",
            success: function (response) {
                console.log("AJAX Response:", response);

                if (response.error) {
                    console.error("Error from fetch-locations.php:", response.error);
                    $("#franchiseLocation").html(`<option value="">${response.error}</option>`);
                    return;
                }

                var options = '<option value="">Select Location</option>';
                $.each(response, function (index, location) {
                    options += `<option value="${location.id}">${location.name}</option>`;
                });
                $("#franchiseLocation").html(options);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                $("#franchiseLocation").html('<option value="">Error loading locations</option>');
            }
        });
    } else {
        $("#franchiseLocation").html('<option value="">Select Franchisee First</option>');
    }
});
    
});


function addExpenses() {
    $(document).on("click", ".btn-submit-expenses", function () {
        var selectedFranchise = $("#selectedFranchise").val();
        var franchiseLocation = $("#franchiseLocation").val();
        var encoderName = $("#encoderName").val();
        var dateToday = $("#dateToday").val();
        var selectedExpense = $("#selectedExpense").val();

        var expenseType, otherDetails, amount, description;

        if (selectedExpense === "controllable-expenses") {
            expenseType = $(".form-data1 .selectedExpenseType").val();
            otherDetails = $(".form-data1 .otherExpenses").val();
            amount = $(".form-data1 .expensesAmount").val();
            description = $(".form-data1 .expensesDescription").val();
        } else if (selectedExpense === "non-controllable-expenses") {
            expenseType = $(".form-data2 .selectedExpenseType").val();
            otherDetails = $(".form-data2 .otherExpenses").val();
            amount = $(".form-data2 .expensesAmount").val();
            description = $(".form-data2 .expensesDescription").val();
        } else if (selectedExpense === "other-expenses") {
            expenseType = $("#expensesType").val();
            otherDetails = $(".form-data3 .otherPurpose").val();
            amount = $(".form-data3 .expensesAmount").val();
            description = $(".form-data3 .expensesDescription").val();
        }

        var formData = new FormData();
        formData.append("selectedFranchise", selectedFranchise);
        formData.append("franchiseLocation", franchiseLocation);
        formData.append("encoderName", encoderName);
        formData.append("dateToday", dateToday);
        formData.append("selectedExpense", selectedExpense);
        formData.append("expenseType", expenseType);
        formData.append("amount", amount);
        formData.append("description", description);
        formData.append("otherDetails", otherDetails);

        $.ajax({
            method: "POST",
            url: "../../phpscripts/add-expenses.php",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("input, button, textarea, select, a").prop(
                        "disabled",
                        true
                    );
                    displayModal("Success", response.message, "#198754");

                    setTimeout(function () {
                        closeModal();
                        window.location.href = window.location.href;
                    }, 3000);

                    $("#myForm")[0].reset();
                } else {
                    displayModal("Error", response.message, "#dc3545");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                displayModal(
                    "Error",
                    "An error occurred. Please try again later."
                );
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
