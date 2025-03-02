


$(document).ready(function() {
    let selectedBranch = "";

    // ✅ Function to Get Monday and Sunday of the Current Week
function getCurrentWeekDates() {
    let today = new Date();
    let firstDay = new Date(today.setDate(today.getDate() - today.getDay() )); // Monday
    let lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 7)); // Sunday

    // ✅ Format MM-DD-YYYY
    let formatDate = (date) => {
        let d = new Date(date);
        let month = String(d.getMonth() + 1).padStart(2, "0"); // Get month (01-12)
        let day = String(d.getDate()).padStart(2, "0"); // Get day (01-31)
        let year = d.getFullYear(); // Get full year
        return `${month}-${day}-${year}`;
    };

    return {
        monday: formatDate(firstDay),
        sunday: formatDate(lastDay)
    };
}

// ✅ Display the Current Week Range
let weekDates = getCurrentWeekDates();
$("#report-week-range").text(`Reports for ${weekDates.monday} to ${weekDates.sunday}`);


    // Fetch and display branches when a franchise is clicked
    $(document).ready(function () {
        let selectedFranchises = [];
        
        // ✅ Handle Franchise Selection
        $(".franchise-btn").click(function () {
            let franchise = $(this).data("franchise");
    
            // Toggle selection
            if ($(this).hasClass("btn-selected")) {
                $(this).removeClass("btn-selected btn-primary").addClass("btn-outline-primary");
                selectedFranchises = selectedFranchises.filter(f => f !== franchise); // Remove from selected
            } else {
                $(this).addClass("btn-selected btn-primary").removeClass("btn-outline-primary");
                selectedFranchises.push(franchise); // Add to selected
            }
    
            console.log("📌 Selected franchises:", selectedFranchises); // Debugging
    
            if (selectedFranchises.length === 0) {
                $("#branch-buttons").empty().hide(); // ✅ Hide if none selected
            } else {
                updateBranches(selectedFranchises);
            }
        });
    
        // ✅ Function to Fetch & Update Branches
        function updateBranches(franchises) {
            $("#branch-buttons").empty().hide();
            selectedBranches = []; // Reset selected branches when a new franchise filter is applied

    
            $.ajax({
                url: "dashboard-inventory.php",
                type: "POST",
                data: { franchise: JSON.stringify(franchises) }, // Send as JSON array
                dataType: "json",
                success: function (data) {
                    console.log("✅ Branches received:", data);
    
                    if (data.branches && data.branches.length > 0) {
                        $("#branch-buttons").show(); // Show container
                        data.branches.forEach(branch => {
                            $("#branch-buttons").append(
                                `<button class="btn btn-outline-secondary branch-btn" data-branch="${branch}">${branch}</button>`
                            );                            
                        });
                    } else {
                        console.warn("⚠ No branches found.");
                    }
                },
                error: function (xhr) {
                    console.error("❌ AJAX Error:", xhr.responseText);
                }
            });
        }
    });
    

    // Fetch and update KPIs & Graphs when a branch is clicked
    $(document).on("click", ".branch-btn", function () {
        let branch = $(this).data("branch");
    
        // Toggle selection style
        if ($(this).hasClass("btn-selected")) {
            $(this).removeClass("btn-selected btn-secondary").addClass("btn-outline-secondary");
        } else {
            $(this).addClass("btn-selected btn-secondary").removeClass("btn-outline-secondary");
        }
    
        // ✅ Get all selected branches
        let selectedBranches = $(".branch-btn.btn-selected").map(function () {
            return $(this).data("branch");
        }).get();
    
        console.log("📌 Selected branches:", selectedBranches); // Debugging
    
        // ✅ Update KPIs & Graphs based on selected branches
        if (selectedBranches.length > 0) {
            updateAnalytics(selectedBranches); // ✅ Pass the array of selected branches
        } else {
            console.warn("⚠ No branches selected.");
            resetKPIs(); // ✅ Clear KPI data when no branches are selected
        }        
    });
    

    function updateAnalytics(branches) {
        let startDate = $("#startDate").val() || new Date(new Date().setDate(new Date().getDate() - new Date().getDay())).toISOString().split('T')[0]; // Default to Monday
        let endDate = $("#endDate").val() || new Date(new Date().setDate(new Date().getDate() - new Date().getDay() + 7)).toISOString().split('T')[0]; // Default to Sunday

        console.log("📊 Fetching KPI Data for:", { branches, startDate, endDate });
    $.ajax({
        url: "dashboard-inventory.php",
        type: "POST",
        data: {
            branches: JSON.stringify(branches), // ✅ Send selected branches as an array
            startDate: startDate,
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
            if (!data.low_stock_items || !data.low_stock_items.labels || data.low_stock_items.labels.length === 0) {
                console.warn("⚠ No low stock items to display.");
                return;
            }
            updateLowStockChart(data.low_stock_items);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
        }
    });
}

// ✅ Ensure date inputs trigger updates
$("#startDate, #endDate").change(function () {
    let selectedBranches = $(".branch-btn.btn-selected").map(function () {
        return $(this).data("branch");
    }).get();

    console.log("📅 Date changed. Fetching new data for branches:", selectedBranches);

    if (selectedBranches.length > 0) {
        updateAnalytics(selectedBranches);
    } else {
        console.warn("⚠ No branches selected. KPI data will not be updated.");
        resetKPIs(); // Clear KPI data if no branches selected
    }
});


function resetKPIs() {
    $("#stock-level").text("0");
    $("#stockout-count").text("0");
    $("#total-wastage").text("0");
}


let sellThroughChart, highTurnoverChart, lowTurnoverChart; // ✅ Store chart instances globally

function updateSellThroughGraph(sellThroughRate) {
    console.log("Updating Sell-Through Rate Graph...", sellThroughRate);

    // ✅ Ensure it's an array
    if (!sellThroughRate || !sellThroughRate.data || !Array.isArray(sellThroughRate.data) || sellThroughRate.data.length === 0) {
        console.warn("⚠ No data available for Sell-Through Rate.");
        return;
    }

    // ✅ Destroy previous chart instance if exists
    if (sellThroughChart instanceof Chart) {
        sellThroughChart.destroy();
    }

    const ctx = document.getElementById("sellThroughChart").getContext("2d");

    let datasets = [];
    let branchColors = ["blue", "red", "green", "orange", "purple", "brown"]; // ✅ Assign different colors per branch
    let branchIndex = 0;

    // ✅ Organize data by branch
    let branchData = {};
    sellThroughRate.data.forEach(entry => {
        if (!branchData[entry.branch]) {
            branchData[entry.branch] = { dates: [], values: [] };
        }
        branchData[entry.branch].dates.push(entry.sale_date);
        branchData[entry.branch].values.push(entry.sell_through_rate);
    });

    // ✅ Prepare dataset for each branch
    Object.keys(branchData).forEach(branch => {
        datasets.push({
            label: `${branch} Sell-Through Rate (%)`,
            data: branchData[branch].values, // ✅ Y-axis (percentage)
            borderColor: branchColors[branchIndex % branchColors.length], // ✅ Assign unique color
            backgroundColor: branchColors[branchIndex % branchColors.length] + "33", // ✅ Lighter color for fill
            fill: true
        });
        branchIndex++;
    });

    sellThroughChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: branchData[Object.keys(branchData)[0]].dates || [], // ✅ Use dates from the first branch as x-axis
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value + "%"; }
                    }
                }
            }
        }
    });

    console.log("✅ Sell-Through Rate Graph Updated!");
}

function updateLowStockChart(lowStockData) {
    console.log("Updating Low Stock Items Chart...", lowStockData);

    if (!lowStockData || !lowStockData.labels || lowStockData.labels.length === 0) {
        console.warn("⚠ No low stock items to display.");
        return;
    }

    // ✅ Destroy previous chart instance if exists
    if (window.lowStockChart instanceof Chart) {
        window.lowStockChart.destroy();
    }

    const ctx = document.getElementById("lowStockChart").getContext("2d");

    // ✅ Assign unique colors per branch dynamically
    let uniqueBranches = [...new Set(lowStockData.branches)];
    let branchColors = {};
    let availableColors = ["red", "blue", "green", "orange", "purple", "brown", "cyan", "magenta"];
    
    uniqueBranches.forEach((branch, index) => {
        branchColors[branch] = availableColors[index % availableColors.length]; // Cycle colors if more branches
    });

    // ✅ Prepare dataset
    let datasets = uniqueBranches.map(branch => {
        return {
            label: `Branch: ${branch}`,
            data: lowStockData.labels.map((item, index) => (lowStockData.branches[index] === branch ? lowStockData.values[index] : 0)),
            backgroundColor: branchColors[branch]
        };
    });

    window.lowStockChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: lowStockData.labels, // ✅ Item Names
            datasets: datasets
        },
        options: {
            responsive: true,
            indexAxis: 'y', // ✅ Horizontal bar chart
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true },
                y: { stacked: true }
            }
        }
    });

    console.log("✅ Low Stock Items Chart Updated!");
}





function updateGraphs(highTurnover, lowTurnover) {
    console.log("Updating Graphs...");
    console.log("📊 High Turnover Data:", highTurnover);
    console.log("📊 Low Turnover Data:", lowTurnover);

    // ✅ If data is empty, hide the charts
    if (!highTurnover.labels.length || !lowTurnover.labels.length) {
        console.warn("⚠ No data for graphs.");
        document.getElementById("highTurnoverChart").style.display = "none";
        document.getElementById("lowTurnoverChart").style.display = "none";
        return;
    } else {
        document.getElementById("highTurnoverChart").style.display = "block";
        document.getElementById("lowTurnoverChart").style.display = "block";
    }

    // ✅ Destroy previous instances before creating new charts
    if (highTurnoverChart instanceof Chart) highTurnoverChart.destroy();
    if (lowTurnoverChart instanceof Chart) lowTurnoverChart.destroy();

    const highTurnoverCanvas = document.getElementById("highTurnoverChart").getContext("2d");
    const lowTurnoverCanvas = document.getElementById("lowTurnoverChart").getContext("2d");

    // ✅ Update High Turnover Chart
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
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // ✅ Update Low Turnover Chart
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
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    console.log("✅ Graphs Updated Successfully!");
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
// ✅ Define getStockStatus() FIRST
function getStockStatus($currentStock, $turnoverRate) {
    if ($currentStock === 0) return "Stockout";
    if ($turnoverRate === 0) return "Unknown"; // No sales data available

    $stockDays = $currentStock; // Estimate how long stock will last

    if ($stockDays > 14) return "High";
    if ($stockDays >= 7) return "Moderate";
    if ($stockDays > 0) return "Low";
    return "Stockout";
}


function generateExceptionReport() {
    let selectedFranchisees = [...document.querySelectorAll(".franchise-btn.btn-selected")].map(btn => btn.dataset.franchise) || [];
    let selectedBranches = [...document.querySelectorAll(".branch-btn.btn-selected")].map(btn => btn.dataset.branch) || [];

    let today = new Date();
    let firstDay = new Date(today.setDate(today.getDate() - today.getDay())); // Sunday
    let lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6)); // Saturday

    let formatDate = (date) => {
        let d = new Date(date);
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    };

    let startDate = document.getElementById("startDate").value || formatDate(firstDay);
    let endDate = document.getElementById("endDate").value || formatDate(lastDay);

    document.getElementById("exceptionSelectedFranchisees").innerText = selectedFranchisees.length > 0 ? selectedFranchisees.join(", ") : "All";
    document.getElementById("exceptionSelectedBranches").innerText = selectedBranches.length > 0 ? selectedBranches.join(", ") : "All";
    document.getElementById("exceptionSelectedDateRange").innerText = `${startDate} to ${endDate}`;

    $("#exceptionReportModal").modal("show");

    fetchExceptionReport(selectedFranchisees, selectedBranches, startDate, endDate);
}

// Fetch Exception Report Data
const franchiseDisplayMap = {
    "potato-corner": "Potato Corner",
    "auntie-anne": "Auntie Anne's",
    "macao-imperial": "Macao Imperial"
};

function fetchExceptionReport(franchisees, branches, startDate, endDate) {
    console.log("📌 Fetching Exception Report", { franchisees, branches, startDate, endDate });

    $.ajax({
        url: "dashboard-inventory.php",
        type: "POST",
        data: {
            exceptionReport: true,
            franchisees: franchisees,
            branches: JSON.stringify(branches),
            startDate: startDate,
            endDate: endDate
        },
        dataType: "json",
        success: function(response) {
            console.log("✅ Raw Exception Report Data Received:", response);

            if (!response || typeof response !== "object") {
                console.warn("⚠ No valid data received.");
                $("#exceptionReportTablesContainer").html("<p class='text-center text-danger'>No data available</p>");
                return;
            }

            let data = response.exception_report;
            console.log("📌 Exception Report Data Array:", data);

            if (!data || !Array.isArray(data) || data.length === 0) {
                console.warn("⚠ No exception data available.");
                $("#exceptionReportTablesContainer").html("<p class='text-center'>No data available</p>");
                return;
            }

            // ✅ Clear previous content
            $("#exceptionReportTablesContainer").empty();

            // ✅ Group data by franchise & branch
            let groupedData = {};
            data.forEach(item => {
                let formattedFranchise = franchiseDisplayMap[item.franchisee] || item.franchisee; // ✅ Convert franchise to display format
                let key = `${formattedFranchise} - ${item.branch}`; // ✅ Franchise + Branch Grouping
                if (!groupedData[key]) {
                    groupedData[key] = [];
                }
                groupedData[key].push(item);
            });

            // ✅ Create separate tables for each franchise-branch combination
            Object.keys(groupedData).forEach(group => {
                let items = groupedData[group];

                let tableHtml = `
                    <h4 class="mt-4 text-center">${group}</h4> 
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Current Stock</th>
                                    <th>Stock Status</th>
                                    <th>Waste Status</th>
                                    <th>Waste Percentage</th>
                                    <th>Turnover Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                items.forEach(item => {
                    let stockStatus = getStockStatus(item.current_stock, item.turnover_rate);
                    tableHtml += `
                        <tr>
                            <td>${item.item_name}</td>
                            <td>${item.current_stock}</td>
                            <td>${stockStatus}</td>
                            <td>${item.waste_status}</td>
                            <td>${parseFloat(item.waste_percentage || 0).toFixed(2)}%</td>
                            <td>${parseFloat(item.turnover_rate || 0).toFixed(2)}%</td>
                        </tr>
                    `;
                });

                tableHtml += `
                            </tbody>
                        </table>
                    </div>
                `;

                $("#exceptionReportTablesContainer").append(tableHtml);
            });
        },
        error: function(xhr) {
            console.error("❌ AJAX Error:", xhr.responseText);
            $("#exceptionReportTablesContainer").html("<p class='text-center text-danger'>Failed to fetch report</p>");
        }
    });
}






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