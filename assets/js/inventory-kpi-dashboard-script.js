// document.addEventListener("DOMContentLoaded", function () {
//     loadFranchiseeButtons(); // Load franchisee buttons on page load
//     fetchKPIData(); // Load KPI Data including turnover charts
//     // loadTurnoverCharts();
// });

// function loadFranchiseeButtons() {
//     fetch("dashboard-inventory.php?json=true") // ✅ Ensure JSON response
//         .then(response => response.json())
//         .then(data => {
//             let franchiseeButtonsDiv = document.getElementById("franchiseeButtons");
//             franchiseeButtonsDiv.innerHTML = ""; // Clear existing buttons

//             data.franchisees.forEach(franchisee => {
//                 let button = document.createElement("button");
//                 button.classList.add("btn", "btn-outline-primary", "m-2", "franchisee-btn"); // ✅ Default styling
//                 button.innerText = franchisee.franchisee;
//                 button.dataset.value = franchisee.franchisee;
//                 button.addEventListener("click", toggleFranchiseeSelection);
//                 franchiseeButtonsDiv.appendChild(button);
//             });
//         })
//         .catch(error => console.error("Error loading franchisees:", error));
// }

// function toggleFranchiseeSelection(event) {
//     let button = event.target;
//     button.classList.toggle("btn-primary"); // ✅ Bootstrap class for visual effect
//     button.classList.toggle("btn-outline-primary"); // ✅ Toggle between selected & unselected
//     button.classList.toggle("btn-selected"); // ✅ Custom class to track selected items

//     let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
//         .map(btn => btn.dataset.value);

//     loadBranchButtons(selectedFranchisees);
//     fetchKPIData();
// }

// function loadBranchButtons(selectedFranchisees) {
//     if (selectedFranchisees.length === 0) {
//         document.getElementById("branchButtons").style.display = "none";
//         return;
//     }

//     fetch(`dashboard-inventory.php?json=true&franchisees=${selectedFranchisees.join(",")}`) // ✅ Ensure JSON response
//         .then(response => response.json())
//         .then(data => {
//             let branchButtonsDiv = document.getElementById("branchButtons");
//             branchButtonsDiv.innerHTML = ""; // Clear previous buttons
//             branchButtonsDiv.style.display = "block";

//             data.branches.forEach(branch => {
//                 let button = document.createElement("button");
//                 button.classList.add("btn", "btn-outline-secondary", "m-2", "branch-btn"); // ✅ Default styling
//                 button.innerText = branch.branch;
//                 button.dataset.value = branch.branch;
//                 button.addEventListener("click", toggleBranchSelection);
//                 branchButtonsDiv.appendChild(button);
//             });
//         })
//         .catch(error => console.error("Error loading branches:", error));
// }

// function toggleBranchSelection(event) {
//     let button = event.target;
//     button.classList.toggle("btn-secondary"); // ✅ Highlight selected
//     button.classList.toggle("btn-outline-secondary"); // ✅ Toggle unselected state
//     button.classList.toggle("btn-selected"); // ✅ Custom class to track selected items

//     fetchKPIData();
// }

// function fetchKPIData() {
//     let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
//         .map(btn => btn.dataset.value);
//     let selectedBranches = Array.from(document.querySelectorAll(".branch-btn.btn-selected"))
//         .map(btn => btn.dataset.value);

//     fetch(`dashboard-inventory.php?json=true`)
//         .then(response => response.json())
//         .then(data => {
//             console.log("Fetched Data for Graphs:", data); // ✅ Debugging Log

//             if (!data.highTurnoverData || data.highTurnoverData.length === 0) {
//                 console.warn("⚠ No data for High Stock Turnover chart.");
//                 document.getElementById("highTurnoverChart").style.display = "none";
//             } else {
//                 document.getElementById("highTurnoverChart").style.display = "block";
//                 createBarChart("highTurnoverChart", data.highTurnoverData, "Top 5 High Stock Turnover");
//             }

//             if (!data.lowTurnoverData || data.lowTurnoverData.length === 0) {
//                 console.warn("⚠ No data for Low Stock Turnover chart.");
//                 document.getElementById("lowTurnoverChart").style.display = "none";
//             } else {
//                 document.getElementById("lowTurnoverChart").style.display = "block";
//                 createBarChart("lowTurnoverChart", data.lowTurnoverData, "Top 5 Low Stock Turnover");
//             }
//         })
//         .catch(error => console.error("Error fetching KPI data:", error));
// }




// function loadTurnoverCharts() {
//     var highTurnoverElement = document.getElementById("highTurnoverData");
//     var lowTurnoverElement = document.getElementById("lowTurnoverData");

//     if (!highTurnoverElement || !lowTurnoverElement) {
//         console.warn("⚠ No turnover data elements found in the HTML.");
//         return;
//     }

//     try {
//         var highTurnoverData = JSON.parse(highTurnoverElement.textContent.trim());
//         var lowTurnoverData = JSON.parse(lowTurnoverElement.textContent.trim());

//         if (!highTurnoverData.length || !lowTurnoverData.length) {
//             console.warn("⚠ No data available for turnover charts.");
//             return;
//         }

//         createBarChart("highTurnoverChart", highTurnoverData, "Top 5 High Stock Turnover");
//         createBarChart("lowTurnoverChart", lowTurnoverData, "Top 5 Low Stock Turnover");
//     } catch (error) {
//         console.error("Error parsing turnover data:", error);
//     }
// }


// function createBarChart(chartId, data, title) {
//     var canvas = document.getElementById(chartId);
//     if (!canvas) {
//         console.error(`Error: Chart canvas '${chartId}' not found.`);
//         return;
//     }
//     var ctx = canvas.getContext("2d");

//     // Destroy existing chart instance if it exists
//     if (window[chartId] instanceof Chart) {
//         window[chartId].destroy();
//     }

//     // Create new chart
//     window[chartId] = new Chart(ctx, {
//         type: "bar",
//         data: {
//             labels: data.map(item => item.item_name),
//             datasets: [{
//                 label: "Stock Turnover Rate",
//                 data: data.map(item => parseFloat(item.turnover_rate).toFixed(2)),
//                 backgroundColor: "#42A5F5",
//                 borderWidth: 1
//             }]
//         },
//         options: {
//             responsive: true,
//             scales: {
//                 y: { beginAtZero: true }
//             }
//         }
//     });
// }


$(document).ready(function() {
    let selectedBranch = "";

    // Fetch and display branches when a franchise is clicked
    $(".franchise-btn").click(function() {
        $(this).toggleClass("btn-selected btn-primary btn-outline-primary"); // Toggle selected class
        let franchise = $(this).data("franchise");
        $("#branch-buttons").empty();

        $.ajax({
    url: "dashboard-inventory.php", 
    type: "POST",
    data: { franchise: franchise },
    dataType: "json", // ✅ Ensures jQuery automatically parses JSON
    success: function(data) { // ✅ Use 'data' instead of 'response'
        console.log("Raw Response:", data); // ✅ Debugging
        try {
            let branches = data.branches; // ✅ Correctly extracts the branches array
            console.log("Parsed JSON:", branches);
            $("#branch-buttons").empty();
            branches.forEach(branch => {
                $("#branch-buttons").append(`<button class="btn btn-secondary branch-btn" data-branch="${branch}">${branch}</button>`);
            });
        } catch (error) {
            console.error("JSON Parsing Error:", error, "Received:", data);
        }
    },
    error: function(xhr, status, error) {
        console.error("AJAX Error:", xhr.responseText);
    }
});

    });

    // Fetch and update KPIs & Graphs when a branch is clicked
    $(document).on("click", ".branch-btn", function() {
        $(this).toggleClass("btn-selected btn-secondary btn-outline-secondary"); // Toggle selected class
        selectedBranch = $(this).data("branch");
        updateAnalytics(selectedBranch);
    });

    function updateAnalytics(branch) {
    let startDate = $("#startDate").val() || ""; // ✅ Ensure value is always defined
    let endDate = $("#endDate").val() || ""; // ✅ Ensure value is always defined

    $.ajax({
        url: "dashboard-inventory.php",
        type: "POST",
        data: {
            branch: branch,
            startDate: startDate, // ✅ Properly formatted key-value pairs
            endDate: endDate
        },
        dataType: "json",
        success: function(data) {
            console.log("Analytics Response:", data); // ✅ Debugging output

            if (data.error) {
                console.error("Error:", data.error);
                return;
            }

            // ✅ Update KPIs
            $("#stock-level").text(data.stock_level);
            $("#stockout-count").text(data.stockout_count);
            $("#total-wastage").text(data.total_wastage);

            // ✅ Update Graphs
            updateGraphs(data.high_turnover, data.low_turnover);
            updateSellThroughGraph(data.sell_through_rate);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
        }
    });
}

// ✅ Ensure date inputs trigger updates
$("#startDate, #endDate").change(function () {
    if (selectedBranch) {
        updateAnalytics(selectedBranch);
    }
});




let sellThroughChart, highTurnoverChart, lowTurnoverChart; // ✅ Store chart instances globally

function updateSellThroughGraph(sellThroughRate) {
    console.log("Updating Sell-Through Rate Graph...", sellThroughRate);

    if (!sellThroughRate || !sellThroughRate.dates || sellThroughRate.dates.length === 0) {
        console.warn("⚠ No data available for Sell-Through Rate.");
        return;
    }

    // ✅ Destroy previous chart instance if exists
    if (sellThroughChart instanceof Chart) {
        sellThroughChart.destroy();
    }

    const ctx = document.getElementById("sellThroughChart").getContext("2d");

    sellThroughChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: sellThroughRate.dates, // X-axis (time)
            datasets: [{
                label: "Sell-Through Rate (%)",
                data: sellThroughRate.values, // Y-axis (percentage)
                borderColor: "blue",
                backgroundColor: "rgba(0, 0, 255, 0.1)",
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ✅ Ensures chart does not grow infinitely
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value + "%"; } // ✅ Show percentages
                    }
                }
            }
        }
    });

    console.log("Sell-Through Rate Graph Updated!");
}

function updateGraphs(highTurnover, lowTurnover) {
    console.log("Updating Graphs...");
    console.log("High Turnover Data:", highTurnover);
    console.log("Low Turnover Data:", lowTurnover);

    if (!highTurnover.labels || !lowTurnover.labels || highTurnover.labels.length === 0 || lowTurnover.labels.length === 0) {
        console.warn("⚠ No data for graphs.");
        return;
    }

    // Destroy previous instances before creating new charts
    if (highTurnoverChart instanceof Chart) highTurnoverChart.destroy();
    if (lowTurnoverChart instanceof Chart) lowTurnoverChart.destroy();

    const highTurnoverCanvas = document.getElementById("highTurnoverChart").getContext("2d");
    const lowTurnoverCanvas = document.getElementById("lowTurnoverChart").getContext("2d");

    highTurnoverChart = new Chart(highTurnoverCanvas, {
        type: "bar",
        data: {
            labels: highTurnover.labels,
            datasets: [{
                label: "High Turnover Rate",
                data: highTurnover.values,
                backgroundColor: "green"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ✅ Ensures chart stays contained
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    lowTurnoverChart = new Chart(lowTurnoverCanvas, {
        type: "bar",
        data: {
            labels: lowTurnover.labels,
            datasets: [{
                label: "Low Turnover Rate",
                data: lowTurnover.values,
                backgroundColor: "red"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ✅ Prevents infinite resizing
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    console.log("Graphs Updated Successfully!");
}




    // Date input change triggers new Sell-Through Rate calculation
    $("#startDate, #endDate").change(function() {
        if (selectedBranch) {
            updateAnalytics(selectedBranch);
        }
    });


});
const franchiseNameMap = {
    "Potato Corner": "Potato Corner",
    "Auntie Anne's": "Auntie Anne's",
    "Macao Imperial Tea": "Macao Imperial Tea"
};

    // GENERATE REPORT
    function generateReport() {
 // ✅ Get selected franchises
// ✅ Ensure selectedFranchisees and selectedBranches are always arrays
let selectedFranchisees = [...document.querySelectorAll(".franchise-btn.btn-selected")].map(btn => btn.dataset.franchise) || [];
let selectedBranches = [...document.querySelectorAll(".branch-btn.btn-selected")].map(btn => btn.dataset.branch) || [];


    // ✅ Ensure Franchise Names Are Mapped Correctly
    let franchiseeDisplay = selectedFranchisees.length > 0
        ? selectedFranchisees.map(f => franchiseNameMap[f] || f).join(", ")  // Map names if available
        : "All";  // Default if none selected

    // ✅ Update Modal Display
    document.getElementById("selectedFranchisees").innerText = franchiseeDisplay;
    document.getElementById("selectedBranches").innerText = selectedBranches.length > 0 
        ? selectedBranches.join(", ") 
        : "All";



    // ✅ Ensure startDate and endDate are always defined
let startDate = document.getElementById("startDate").value || "2000-01-01"; // Default to all-time
let endDate = document.getElementById("endDate").value || new Date().toISOString().split('T')[0]; // Default to today



    document.getElementById("selectedDateRange").innerText = (startDate && endDate) 
        ? `${startDate} to ${endDate}` 
        : "Not Set";

    // Show modal and fetch report
    $("#reportModal").modal("show");
    fetchReport("daily", selectedFranchisees, selectedBranches, startDate, endDate);
}

// fetch report
function fetchReport(reportType, selectedFranchisees, selectedBranches, startDate, endDate) {
    console.log("Fetching report:", reportType, selectedFranchisees, selectedBranches, startDate, endDate); // ✅ Debugging output

    $.ajax({
        url: "dashboard-inventory.php", // ✅ Fix: Add the correct URL
        type: "POST",
        data: {
            reportType: reportType,
            franchisees: selectedFranchisees,
            branches: selectedBranches,
            startDate: startDate,
            endDate: endDate
        },
        dataType: "json",
        success: function(response) {
    console.log("✅ Report Data Received:", response);
    
    // ✅ Log response details for debugging
    if (!response || typeof response !== "object") {
        console.error("❌ Invalid JSON response received:", response);
        return;
    }

    if (!response.data || !Array.isArray(response.data)) {
        console.warn("⚠ Response does not contain a valid 'data' array:", response);
        $("#reportTableBody").html("<tr><td colspan='5' class='text-center'>No valid data received.</td></tr>");
        return;
    }

            if (response.error) {
                console.error("⚠ Report Error:", response.error);
                $("#reportTableBody").html("<tr><td colspan='5' class='text-center text-danger'>Error fetching report data</td></tr>");
                return;
            }

            // ✅ Clear previous table data
            $("#reportTableBody").empty();

            if (!response.data || !Array.isArray(response.data) || response.data.length === 0) {
                console.warn("⚠ No data found or response is not an array:", response);
                $("#reportTableBody").html("<tr><td colspan='5' class='text-center'>No data available for selected filters.</td></tr>");
                return;
            }

            // ✅ Populate table with received data
            response.data.forEach(item => {
                $("#reportTableBody").append(`
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.sell_through_rate ? item.sell_through_rate : "N/A"}</td>

                        <td>${item.days_until_stockout ? item.days_until_stockout.toFixed(1) : "N/A"}</td>
                        <td>${item.average_sales ? item.average_sales.toFixed(2) : "N/A"}</td>
                        <td class="text-end">${item.stock_waste ? item.stock_waste.toFixed(2) : "0.00"}</td>
                    </tr>
                `);
            });

        },
        error: function(xhr, status, error) {
            console.error("❌ AJAX Error:", xhr.responseText);
            $("#reportTableBody").html("<tr><td colspan='5' class='text-center text-danger'>Failed to fetch report data</td></tr>");
        }
    });
}

function exportTableToCSV() {
    let csv = [];
    let franchise = document.getElementById("selectedFranchisees").innerText;
    let branch = document.getElementById("selectedBranches").innerText;
    let dateRange = document.getElementById("selectedDateRange").innerText;

    // ✅ Add report title and metadata
    csv.push('"Inventory Report"');
    csv.push(`"Franchise:","${franchise}"`);
    csv.push(`"Branch:","${branch}"`);
    csv.push(`"Date Range:","${dateRange}"`);
    csv.push(""); // Empty line before table

    let rows = document.querySelectorAll("#reportTable tr");

    // ✅ Loop through each row to extract the data
    for (let row of rows) {
        let cols = row.querySelectorAll("th, td");
        let rowData = [];

        for (let col of cols) {
            rowData.push('"' + col.innerText + '"'); // Wrap text in quotes to handle commas
        }

        csv.push(rowData.join(",")); // Join columns with commas
    }

    // ✅ Create a Blob (CSV File)
    let csvBlob = new Blob([csv.join("\n")], { type: "text/csv" });
    let csvUrl = URL.createObjectURL(csvBlob);

    // ✅ Create a Download Link
    let downloadLink = document.createElement("a");
    downloadLink.href = csvUrl;
    downloadLink.download = `Inventory_Report_${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}


function exportTableToPDF() {
    let { jsPDF } = window.jspdf;
    let doc = new jsPDF("p", "mm", "a4");

    // ✅ Fetch report details
    let franchise = document.getElementById("selectedFranchisees").innerText;
    let branch = document.getElementById("selectedBranches").innerText;
    let dateRange = document.getElementById("selectedDateRange").innerText;

    // ✅ Set Title and Metadata
    doc.setFontSize(14);
    doc.text("Inventory Report", 10, 10);
    doc.setFontSize(10);
    doc.text(`Franchise: ${franchise}`, 10, 20);
    doc.text(`Branch: ${branch}`, 10, 25);
    doc.text(`Date Range: ${dateRange}`, 10, 30);

    let rows = [];
    let headers = [];

    document.querySelectorAll("#reportTable thead tr th").forEach(th => {
        headers.push(th.innerText);
    });

    document.querySelectorAll("#reportTable tbody tr").forEach(tr => {
        let rowData = [];
        tr.querySelectorAll("td").forEach(td => {
            rowData.push(td.innerText);
        });
        rows.push(rowData);
    });

    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 35, // ✅ Move table below metadata
        theme: "grid"
    });

    doc.save(`Inventory_Report_${new Date().toISOString().split("T")[0]}.pdf`);
}