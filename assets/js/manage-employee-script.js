$(document).ready(function () {
    displayEmployees();
    showEmployeeDetails();
    addEmployee();
    displayRecentActivities();

    $("#franchisee").on("change", function () {
        var franchisee = $(this).val();

        if (franchisee) {
            $.ajax({
                url: "../../phpscripts/get-branches.php",
                type: "POST",
                data: { franchisee: franchisee },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        var branches = response.details;
                        $("#branch")
                            .empty()
                            .append(
                                '<option value="" disabled selected>Select Branch</option>'
                            );
                        $.each(branches, function (index, branch) {
                            $("#branch").append(
                                '<option value="' +
                                    branch.ac_id +
                                    '">' +
                                    branch.location +
                                    "</option>"
                            );
                        });
                    } else {
                        $("#branch")
                            .empty()
                            .append(
                                '<option value="" disabled selected>Select Branch</option>'
                            );
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                },
            });
        } else {
            $("#branch")
                .empty()
                .append(
                    '<option value="" disabled selected>Select Branch</option>'
                );
        }
    });
});

function displayEmployees() {
    var listContainer = $("#employeeList");
    $.ajax({
        method: "GET",
        url: "../../phpscripts/display-employees.php",
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                listContainer.empty();

                response.employees.forEach(function (employee) {
                    var employeeButton = $("<button>")
                        .addClass("box box1 check-employee border-0")
                        .attr("data-id", employee.user_id)
                        .html(
                            "<i class='bx bx-user'></i><span class='text emp-name'>" +
                                employee.user_name +
                                "</span>"
                        );

                    listContainer.append(employeeButton);
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

// Show Employee Details with Unassign Button
function showEmployeeDetails() {
    $(document).on("click", ".check-employee", function () {
        $(".check-employee").removeClass("active");
        $(this).addClass("active");

        var employeeId = $(this).data("id");

        $.ajax({
            method: "POST",
            url: "../../phpscripts/get-employee-details.php",
            data: { id: employeeId },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    var employee = response.employee[0];

                    var changeStatusButton = "";
                    if (employee.employee_status !== "resigned") {
                        changeStatusButton = `
                            <div class="header-section2 text-center">
                                <button class="btn btn-warning unassign-employee" data-id="${employee.user_id}">
                                    Change Status
                                </button>
                            </div>
                        `;
                    }

                    var htmlContent = `
                        <div class="container2">
                            <header class="header-report">Employee Information</header>
                            <div class="header-section2">
                                <span class="header-label">Personal Information</span>
                                <span class="header-label2">Name:</span> ${employee.user_name} <br>
                                <span class="header-label2">Address:</span> ${employee.user_address} <br>
                                <span class="header-label2">Date of Birth:</span> ${employee.user_birthdate}
                            </div>
                            <div class="header-section2">
                                <span class="header-label">Contact Information</span>
                                <span class="header-label2">Email:</span> ${employee.user_email} <br>
                                <span class="header-label2">Mobile:</span> ${employee.user_phone_number}
                            </div>
                            <div class="header-section2">
                                <span class="header-label">Employment Information</span>
                                <span class="header-label2">Branch Assignment:</span> ${employee.branch} <br>
                                <span class="header-label2">Employment Status:</span> ${employee.employee_status} <br>
                                ${employee.employee_status !== "assigned" && employee.certificate_file_name ? `<span class="header-label2">Remarks:</span> ${employee.certificate_file_name} <br>` : ""}
                            </div>
                            <div class="header-section2">
                                <span class="header-label">Certification Information</span>
                                <span class="header-label2">Certifications Held:</span> ${employee.certifications}
                            </div>
                            ${changeStatusButton}
                        </div>
                    `;

                    $("#employeeDetails").html(htmlContent);
                } else {
                    console.log(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
}


// Open Modal for Unassigning Employee
$(document).on("click", ".unassign-employee", function () {
    var employeeId = $(this).data("id");
    $("#unassignEmployeeId").val(employeeId); // Store employee ID in hidden input
    $("#unassignModal").modal("show"); // Show modal
});

// Handle Unassign Form Submission
$(document).ready(function () {
    // Handle selection of Unassign options (Resign / On Leave)
    $(".unassign-option").on("click", function () {
        $(".unassign-option").removeClass("active");
        $(this).addClass("active");
        $("#reasonType").val($(this).data("value"));
    });

    // Handle Unassign Form Submission
    $(document).on("click", "#confirmUnassign", function () {
        var employeeId = $("#unassignEmployeeId").val();
        var reasonType = $("#reasonType").val();
        var reasonText = $("#reasonText").val();

        if (!reasonType) {
            displayModal("Error", "Please select a reason.", "#dc3545");
            return;
        }

        console.log("Submitting:", { id: employeeId, reasonType: reasonType, reasonText: reasonText });

        $.ajax({
            method: "POST",
            url: "../../phpscripts/unassign-employee.php",
            data: { id: employeeId, reasonType: reasonType, reasonText: reasonText },
            dataType: "json",
            success: function (response) {
                console.log("Response:", response);

                if (response.status === "success") {
                    $("#unassignModal").modal("hide");
                    $("#employeeDetails").html("");
                    displayEmployees();
                } else {
                    console.error("Error:", response.message);
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                console.log("Server Response:", xhr.responseText);
                alert("AJAX Error: " + error);
            }
        });
    });
});





// Unassign Employee Functionality
$(document).on("click", ".unassign-employee", function () {
    var employeeId = $(this).data("id");
    $("#unassignEmployeeId").val(employeeId); // Store employee ID
    $("#unassignModal").modal("show"); // Show modal directly
});




function addEmployee() {
    $(document).on("click", ".add-employee", function () {
        var formData = $("#addEmployeeForm").serializeArray();
        var missingFields = [];
        
        // Get form values
        var employeeName = $("input[name='employeeName']").val();
        var dob = $("input[name='dob']").val();
        var address = $("input[name='address']").val();
        var email = $("input[name='email']").val();
        var mobile = $("input[name='mobile']").val();
        var userType = $("select[name='employeeRole']").val();

        // Check required fields
        if (!employeeName) missingFields.push("Employee Name");
        if (!dob) missingFields.push("Date of Birth");
        if (!address) missingFields.push("Address");
        if (!email) missingFields.push("Email");
        if (!mobile) missingFields.push("Mobile");
        if (!userType) missingFields.push("Employee Role");

        // If required fields are missing, show the modal with an error
        if (missingFields.length > 0) {
            displayModal(
                "Error",
                "Failed to create document. Please fill all required fields.",
                "#dc3545"
            );
            return;
        }

        $.ajax({
            method: "POST",
            url: "../../phpscripts/add-employee.php",
            data: $.param(formData), // Serialize Form Data
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("input, button, textarea, select, a").prop("disabled", true);
                    displayModal("Success", "Employee created successfully.", "#198754");

                    setTimeout(function () {
                        closeModal();
                        window.location.href = "manpower_dashboard";
                    }, 3000);

                    $("#addEmployeeForm")[0].reset();
                } else {
                    displayModal("Error", response.message, "#dc3545");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            },
        });
    });
}



function displayRecentActivities() {
    $.ajax({
        url: "../../phpscripts/fetch-recent-activities.php",
        method: "GET",
        dataType: "json",
        success: function (response) {
            var tableBody = $("#recentActivities tbody");
            tableBody.empty();

            if (response.length > 0) {
                response.forEach(function (activity) {
                    var date = new Date(activity.date);
                    var formattedDate =
                        date.getMonth() +
                        1 +
                        "/" +
                        date.getDate() +
                        "/" +
                        date.getFullYear().toString().substr(-2);

                    var activityType;
                    if (activity.activity === "manpower_employee_added") {
                        activityType = "Added new employee";
                    }

                    var row = `<tr>
                        <td>${activity.name}</td>
                        <td>${activityType}</td>
                        <td>${formattedDate}</td>
                    </tr>`;
                    tableBody.append(row);
                });
            } else {
                tableBody.append(
                    '<tr><td colspan="3">No recent activities found.</td></tr>'
                );
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        },
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
