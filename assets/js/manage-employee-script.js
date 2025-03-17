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

                    // Map franchisee values to display names
                    var franchiseeMapping = {
                        "auntie-anne": "Auntie Anne",
                        "macao-imperial": "Macao Imperial",
                        "potato-corner": "Potato Corner"
                    };

                    // Convert the stored franchisee value to lowercase to match our mapping keys
                    var displayFranchisee = franchiseeMapping[employee.franchisee.toLowerCase()] || employee.franchisee;

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
                                <span class="header-label2">Franchisee:</span> ${displayFranchisee} <br>
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


// ------------ GENERATE REPORTS ------------
function fetchReport(type) {
    // Get the selected franchise filter value
    let franchiseFilter = $("#reportFranchiseeFilter").val();
    
    $.ajax({
        url: "../../phpscripts/fetch-manpower-report.php",
        type: "GET",
        data: { 
            type: type,
            franchisee: franchiseFilter // pass filter value to PHP
        },
        dataType: "json",
        success: function (response) {
            let reportTitle = type === "fully_staffed" ? "Fully Staffed Branches Report" : "Understaffed Branches Report";
            $("#reportModalLabel").html(reportTitle);
            $("#reportData").empty();

            if (response.status === "success") {
                let reportContainer = $("#reportData");
                let groupedData = {};

                // Group branches by franchisee
                response.branches.forEach(function (branch) {
                    let franchisee = branch.franchisee;
                    if (!groupedData[franchisee]) {
                        groupedData[franchisee] = {
                            branches: [],
                            totalEmployees: 0
                        };
                    }
                    groupedData[franchisee].branches.push(branch);
                    groupedData[franchisee].totalEmployees += parseInt(branch.employee_count);
                });

                // Franchise logo mapping
                const franchiseData = {
                    "auntie-anne": { name: "Auntie Anne's", logo: "AuntieAnn.png" },
                    "macao-imperial": { name: "Macao Imperial", logo: "MacaoImp.png" },
                    "potato-corner": { name: "Potato Corner", logo: "PotCor.png" }
                };

                // Generate separate sections per franchisee
                Object.keys(groupedData).forEach(franchiseeKey => {
                    let franchise = groupedData[franchiseeKey];
                    let franchiseInfo = franchiseData[franchiseeKey.toLowerCase().replace(/\s+/g, '-')];
                    let franchiseName = franchiseInfo ? franchiseInfo.name : franchiseeKey;
                    let franchiseLogo = franchiseInfo ? `../../assets/images/${franchiseInfo.logo}` : "../../assets/images/default.png";

                    let franchiseSection = `
                        <div class="franchise-section mb-4 p-3 rounded bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap p-2">
                                <div class="d-flex align-items-center">
                                    <img src="${franchiseLogo}" alt="${franchiseName}" class="franchise-logo me-2">
                                    <h4 class="fw-bold mb-0 franchise-title-report text-dark">${franchiseName} (Total Branches: ${franchise.branches.length})</h4>
                                </div>
                                <span class="badge bg-primary fs-6 p-2 total-employee-badge">Total Employees: ${franchise.totalEmployees}</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped report-table rounded">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Branch Name</th>
                                            ${type === "understaffed" ? "<th>Required Staff</th>" : ""}
                                            <th>Employee</th>
                                            <th>Shift</th>
                                            <th>Phone Number</th>
                                            <th>Address</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-${franchiseeKey.replace(/\s+/g, '-')}">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;

                    reportContainer.append(franchiseSection);
                    let franchiseTableBody = $(`#table-${franchiseeKey.replace(/\s+/g, '-')}`);

                    // Append branch details to respective franchise section
                    franchise.branches.forEach((entry, index) => {
                        let employees = entry.employee_details.split(", ");
                        let shifts = entry.employee_shifts.split(", ");
                        let phoneNumbers = entry.phone_numbers ? entry.phone_numbers.split(", ") : [];
                        let addresses = entry.addresses ? entry.addresses.split(", ") : [];

                        employees.forEach((employee, index) => {
                            franchiseTableBody.append(`
                                <tr>
                                    <td>${index === 0 ? entry.branch_name : ""}</td>
                                    ${type === "understaffed" ? `<td>${index === 0 ? `${entry.employee_count}/${entry.min_employees}` : ""}</td>` : ""}
                                    <td>${employee}</td>
                                    <td>${shifts[index] || "N/A"}</td>
                                    <td>${phoneNumbers[index] || "No phone info available"}</td>
                                    <td>${addresses[index] || "No address info available"}</td>
                                </tr>
                            `);
                        });
                    });
                });
            } else {
                $("#reportData").append(`<div class="text-center fw-bold">No data available for the selected franchisee.</div>`);
            }

            // Show the modal
            $("#reportModal").modal("show");

            $("#exportCSV").off("click").on("click", function () {
                exportManpowerToCSV(type);
            });
            $("#exportPDF").off("click").on("click", function () {
                exportManpowerToPDF(type);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error:", xhr.responseText);
            $("#reportData").html(`<p>Error loading data</p>`);
        }
    });
}

// Trigger fetch when the "Generate Report" button is clicked or the filter changes
$(document).ready(function () {
    $("#generateFranchiseReport").click(function () {
        fetchReport("fully_staffed");
    });
    $("#reportFranchiseeFilter").change(function () {
        fetchReport("fully_staffed");
    });
});




// EXPORT TO CSV FUNCTION
function exportManpowerToCSV(type) {
    let csvContent = "data:text/csv;charset=utf-8,";

    // ✅ Add Report Title and Date
    let reportTitle = type === "fully_staffed" ? "Fully Staffed Branches Report" : "Understaffed Branches Report";
    let currentDate = new Date().toLocaleDateString();
    csvContent += `${reportTitle}\nDate Generated: ${currentDate}\n\n`;

    // ✅ Get all franchise sections
    let tables = document.querySelectorAll("#reportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: No report table data found.");
        return;
    }

    tables.forEach((table) => {
        let franchiseSection = table.closest(".franchise-section");
        let franchiseTitle = franchiseSection.querySelector(".franchise-title-report")?.innerText || "Unknown Franchise";

        // ✅ Add Franchise Title
        csvContent += `${franchiseTitle}\n`;

        // ✅ Extract Franchise Summary (Total Employees)
        let summaryBadge = franchiseSection.querySelector(".total-employee-badge");
        if (summaryBadge) {
            csvContent += `${summaryBadge.innerText}\n\n`;
        }

        // ✅ Extract Table Headers
        let headers = [];
        let headerRow = table.querySelector("thead tr");
        if (headerRow) {
            headerRow.querySelectorAll("th").forEach(th => {
                headers.push(th.innerText);
            });
            csvContent += headers.join(",") + "\n"; // Add headers to CSV
        }

        // ✅ Extract Table Data
        let rows = table.querySelectorAll("tbody tr");
        rows.forEach(row => {
            let rowData = [];
            row.querySelectorAll("td").forEach(td => {
                rowData.push(td.innerText);
            });
            csvContent += rowData.join(",") + "\n"; // Add row data
        });

        csvContent += "\n"; // Space between franchise tables
    });

    // ✅ Generate and Download CSV File
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `${type}_report_${new Date().toISOString().split("T")[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}



// EXPORT TO PDF FUNCTION
function exportManpowerToPDF(type) {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({
      orientation: "portrait",
      unit: "pt",
      format: "A4"
    });
  
    // Load the two background images
    let img1 = new Image();
    let img2 = new Image();
    img1.src = "../../assets/images/formDesign.png";    // First page background
    img2.src = "../../assets/images/formDesign2.png";     // Background for pages 2+
  
    // Start PDF generation once the first background image loads
    img1.onload = function () {
      // Draw the first page background
      doc.addImage(img1, "PNG", 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
  
      // Override addPage so every new page gets the alternate background
      const originalAddPage = doc.addPage.bind(doc);
      doc.addPage = function () {
        originalAddPage();
        doc.addImage(img2, "PNG", 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
      };
  
      // -------------------- Begin Report Content --------------------
      // Use titleY = 140 so the header design from image1 is visible
      let titleY = 140;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(22);
      let pageWidth = doc.internal.pageSize.getWidth();
      doc.text(
        type === "fully_staffed" ? "Fully Staffed Branches Report" : "Understaffed Branches Report",
        pageWidth / 2,
        titleY,
        { align: "center" }
      );
  
      // Add date at titleY + 40 (i.e., 180)
      let dateY = titleY + 40;
      let currentDate = new Date().toLocaleDateString();
      doc.setFontSize(10);
      doc.setFont("helvetica", "normal");
      doc.text(`Date Generated: ${currentDate}`, 50, dateY);
  
      // If needed, you can add exported by info right after (optional)
      let userY = dateY + 12;
      let exportedBy = typeof loggedInUser !== "undefined" ? loggedInUser : "Unknown User";
      doc.text(`Exported by: ${exportedBy}`, 50, userY);
  
      // Begin the main content below the header info
      let startY = userY + 35;
  
      // Retrieve tables from the report modal (manpower report)
      let tables = document.querySelectorAll("#reportModal .franchise-section .report-table");
  
      if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: No report table data found.");
        return;
      }
  
      tables.forEach((table) => {
        let franchiseSection = table.closest(".franchise-section");
        let franchiseTitle = franchiseSection.querySelector(".franchise-title-report")?.innerText || "Unknown Franchise";
  
        // Add franchise title
        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.text(franchiseTitle, 50, startY);
        startY += 20;
  
        // Add franchise summary if available
        let summaryBadge = franchiseSection.querySelector(".total-employee-badge");
        if (summaryBadge) {
          let summaryText = summaryBadge.innerText;
          doc.setFont("helvetica", "italic");
          doc.setFontSize(10);
          doc.text(summaryText, 50, startY);
          startY += 20;
        }
  
        // Extract table data: headers and rows
        let headers = [];
        let data = [];
        let rows = table.querySelectorAll("tr");
        rows.forEach((row, rowIndex) => {
          let rowData = [];
          let cols = row.querySelectorAll("th, td");
          cols.forEach((col) => {
            rowData.push(col.innerText);
          });
          if (rowIndex === 0) {
            headers = rowData;
          } else {
            data.push(rowData);
          }
        });
  
        // Generate table using autoTable plugin
        doc.autoTable({
          head: [headers],
          body: data,
          startY: startY,
          theme: "grid",
          styles: { fontSize: 9, cellPadding: 3 },
          headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: "bold" },
          alternateRowStyles: { fillColor: [245, 245, 245] },
          margin: { left: 40, right: 40 },
          tableWidth: "auto",
          columnStyles: { 0: { cellWidth: "auto" } }
        });
  
        // Update startY for the next table, adding spacing
        startY = doc.lastAutoTable.finalY + 40;
      });
      // -------------------- End Report Content --------------------
      doc.save(`${type}_report_${new Date().toISOString().split("T")[0]}.pdf`);
    };
  }
  
  
  

