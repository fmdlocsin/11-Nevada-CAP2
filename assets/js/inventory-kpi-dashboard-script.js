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
    
        const franchiseDisplayMap = {
            "auntie-anne": "Auntie Anne's",
            "macao-imperial": "Macao Imperial",
            "potato-corner": "Potato Corner"
        };
        
        // Define the fixed order of franchises
        const fixedFranchiseOrder = ["auntie-anne", "macao-imperial", "potato-corner"];
        
        function updateBranches(franchises) {
            $("#branch-buttons").empty().hide();
            selectedBranches = [];
        
            $.ajax({
                url: "dashboard-inventory.php",
                type: "POST",
                data: { franchise: JSON.stringify(franchises) },
                dataType: "json",
                success: function (data) {
                    console.log("✅ Branches received:", data.branches);
        
                    // Clear and show the main container
                    $("#branch-buttons").empty().show();
        
                    // Group branches by franchise
                    let grouped = {};
                    data.branches.forEach(function (item) {
                        let fr = item.franchise;
                        if (!grouped[fr]) grouped[fr] = [];
                        grouped[fr].push(item.branch);
                    });
        
                    // Create a single container for the entire grid
                    let $gridContainer = $('<div class="branch-grid"></div>');
        
                    // Render franchises in the correct fixed order
                    fixedFranchiseOrder.forEach(function (franchiseName) {
                        if (grouped[franchiseName]) { // Only display if it has branches
                            let displayName = franchiseDisplayMap[franchiseName] || franchiseName;
                            let $franchiseColumn = $(`
                                <div class="franchise-column">
                                    <h5 class="franchise-title">${displayName}</h5>
                                    <div class="branch-container"></div>
                                </div>
                            `);
        
                            // Append each branch button into this column
                            grouped[franchiseName].forEach(function (branch) {
                                let $button = $(`
                                    <button class="btn btn-outline-secondary branch-btn" data-branch="${branch}">
                                        ${branch}
                                    </button>
                                `);
                                $franchiseColumn.find(".branch-container").append($button);
                            });
        
                            // Add this column to the grid container
                            $gridContainer.append($franchiseColumn);
                        }
                    });
        
                    // Finally, add the grid container to #branch-buttons
                    $("#branch-buttons").append($gridContainer);
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

    if (sellThroughChart instanceof Chart) {
        sellThroughChart.destroy();
    }

    if (!sellThroughRate || !sellThroughRate.data || !Array.isArray(sellThroughRate.data) || sellThroughRate.data.length === 0) {
        console.warn("⚠ No data available for Sell-Through Rate.");
        return;
    }

    const ctx = document.getElementById("sellThroughChart").getContext("2d");

    let datasets = [];
    let branchColors = [
        "rgba(56, 162, 233, 0.8)",  // Soft Blue 
        "rgba(255, 99, 132, 0.8)",  // Soft Red
        "rgba(75, 192, 192, 0.8)",  // Teal Green
        "rgba(255, 159, 64, 0.8)",  // Warm Orange
        "rgba(153, 102, 255, 0.8)", // Lavender
        "rgba(255, 206, 86, 0.8)"   // Soft Yellow
    ];
    
    let branchIndex = 0;
    let branchData = {};

    sellThroughRate.data.forEach(entry => {
        if (!branchData[entry.branch]) {
            branchData[entry.branch] = { dates: [], values: [] };
        }
        branchData[entry.branch].dates.push(entry.sale_date);
        branchData[entry.branch].values.push(entry.sell_through_rate);
    });

    Object.keys(branchData).forEach(branch => {
        datasets.push({
            label: `${branch} Inventory Usage Rate (%)`,
            data: branchData[branch].values,
            borderColor: branchColors[branchIndex % branchColors.length],
            backgroundColor: branchColors[branchIndex % branchColors.length].replace("0.8", "0.3"), // Soft fill
            borderWidth: 2,
            pointRadius: 5,
            tension: 0.4, // Smooth line effect
            fill: true
        });
        branchIndex++;
    });

    sellThroughChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: branchData[Object.keys(branchData)[0]].dates || [],
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
                    },
                    grid: {
                        color: "rgba(200, 200, 200, 0.3)", // Light grey
                        borderDash: [5, 5] // Dashed grid lines
                    }
                },
                x: {
                    grid: {
                        display: false // Hide x-axis grid for cleaner design
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: "black",
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });

    console.log("✅ Sell-Through Rate Graph Updated!");
}

function updateLowStockChart(lowStockData) {
    console.log("Updating Low Stock Items Chart...", lowStockData);

            // ✅ If data is empty, hide the charts
            if (!lowStockData || !lowStockData.labels || lowStockData.labels.length === 0) {
                console.warn("⚠ No data for graphs.");
                document.getElementById("lowStockChart").style.display = "none";
                return;
            } else {
                document.getElementById("lowStockChart").style.display = "block";
            }
        
            // ✅ Destroy previous instances before creating new charts
            if (lowStockChart instanceof Chart) lowStockChart.destroy();
    
    

    if (!lowStockData || !lowStockData.labels || lowStockData.labels.length === 0) {
        console.warn("⚠ No low stock items to display.");
        if (lowStockChart instanceof Chart) lowStockChart.destroy();
        return;
    }


    
    const ctx = document.getElementById("lowStockChart").getContext("2d");

    // ✅ Assign unique colors per branch dynamically
    let uniqueBranches = [...new Set(lowStockData.branches)];
    let branchColors = {};
    let availableColors = ["rgba(91, 174, 183, 1)", "rgba(30, 128, 193, 1)", "rgba(65, 76, 107, 1)", "orange", "purple", "brown", "cyan", "magenta"];
    
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
                backgroundColor: "rgba(91, 174, 183, 1)"
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
                backgroundColor: "rgba(30, 128, 193, 1)"
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
        // ✅ Get selected franchises & branches
        let selectedFranchisees = [...document.querySelectorAll(".franchise-btn.btn-selected")].map(btn => btn.dataset.franchise);
        let selectedBranches = [...document.querySelectorAll(".branch-btn.btn-selected")].map(btn => btn.dataset.branch);
    
        // ✅ Ensure Franchise Names Are Mapped Correctly
        let franchiseeDisplay = selectedFranchisees.length > 0
            ? selectedFranchisees.map(f => franchiseNameMap[f] || f).join(", ")
            : "All";
    
        // ✅ Ensure Branch Names Are Correctly Formatted
        let branchDisplay = selectedBranches.length > 0
            ? selectedBranches.join(", ")
            : "All";
    
        // ✅ Update Modal Display
        document.getElementById("selectedFranchisees").innerText = franchiseeDisplay;
        document.getElementById("selectedBranches").innerText = branchDisplay;
    
        // ✅ Ensure startDate and endDate are correctly set to current week
        let startDate = document.getElementById("startDate").value || new Date(new Date().setDate(new Date().getDate() - new Date().getDay())).toISOString().split('T')[0];
        let endDate = document.getElementById("endDate").value || new Date(new Date().setDate(new Date().getDate() - new Date().getDay() + 7)).toISOString().split('T')[0];
    
        document.getElementById("selectedDateRange").innerText = `${startDate} to ${endDate}`;
    
        // ✅ Open the modal before fetching data
        $("#reportModal").modal("show");
    
        // ✅ Ensure that selectedBranches is encoded as JSON
        fetchReport("daily", selectedFranchisees, JSON.stringify(selectedBranches), startDate, endDate);
    }
    

// fetch report




// ✅ Function to determine stock status
function getStockStatus1(currentStock) {
    if (currentStock === 0) return "No Stock";
    if (currentStock < 10) return "Low";
    if (currentStock < 50) return "Moderate";
    return "High";
}

function fetchReport(reportType, selectedFranchisees, selectedBranches, startDate, endDate) {
    console.log("📌 Fetching Report", { reportType, selectedFranchisees, selectedBranches, startDate, endDate });

    $.ajax({
        url: "dashboard-inventory.php",
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

            if (!response || typeof response !== "object" || !response.data || !Array.isArray(response.data)) {
                console.warn("⚠ No valid data received.");
                $("#reportTablesContainer").html("<p class='text-center text-danger'>No data available</p>");
                return;
            }

            let data = response.data;
            console.log("Processed Report Data:", data);

            // <-- Insert debug logging here:
            data.forEach(item => {
                console.log("Franchisee value:", item.franchisee);
                // If needed, adjust the value (e.g., trim and lowercase)
                let key = item.franchisee.trim().toLowerCase();
                let formattedFranchise = franchiseDisplayMap[key] || item.franchisee;
                console.log("Formatted Franchise:", formattedFranchise);
            });
            // <-- End debug logging

            if (!data || data.length === 0) {
                console.warn("⚠ No report data available.");
                $("#reportTablesContainer").html("<p class='text-center'>No data available</p>");
                return;
            }

            // ✅ Clear previous content
            $("#reportTablesContainer").empty();

            // ✅ Group data by franchise & branch
            let groupedData = {};
            data.forEach(item => {
                let key = `${(franchiseDisplayMap[item.franchisee.trim().toLowerCase()] || item.franchisee)} - ${item.branch}`;
                if (!groupedData[key]) {
                    groupedData[key] = [];
                }
                groupedData[key].push(item);
            });

            // ✅ Create separate tables for each franchise-branch combination
            Object.keys(groupedData).forEach(group => {
                let items = groupedData[group];

                // Wrap the heading and table in a container with "franchise-section"
                let tableHtml = `
                    <div class="franchise-section">
                        <h4 class="mt-4 text-center franchise-title">${group}</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Inventory Usage Rate</th>
                                        <th>Days Until Stockout</th>
                                        <th>Average Sales</th>
                                        <th>Stock Waste</th>
                                        <th>Current Stock</th>
                                        <th>Stock Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                items.forEach(item => {
                    let stockStatus = getStockStatus1(item.current_stock);
                    tableHtml += `
                                    <tr>
                                        <td>${item.item_name}</td>
                                        <td>${item.sell_through_rate}</td>
                                        <td>${item.days_until_stockout}</td>
                                        <td>${item.average_sales}</td>
                                        <td>${item.stock_waste}</td>
                                        <td>${item.current_stock}</td>
                                        <td>${stockStatus}</td>
                                    </tr>
                    `;
                });

                tableHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
                `;

                $("#reportTablesContainer").append(tableHtml);
            });

            console.log("✅ Report tables updated successfully!");
        },
        error: function(xhr) {
            console.error("❌ AJAX Error:", xhr.responseText);
            $("#reportTablesContainer").html("<p class='text-center text-danger'>Failed to fetch report</p>");
        }
    });
}


function generateMonthlyReport() {
    // Get selected franchises & branches
    let selectedFranchisees = [...document.querySelectorAll(".franchise-btn.btn-selected")].map(btn => btn.dataset.franchise);
    let selectedBranches = [...document.querySelectorAll(".branch-btn.btn-selected")].map(btn => btn.dataset.branch);

    // Get the first and last day of the current month
    let today = new Date();
    let firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
    let lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];

    // Get the month and year for the summary title
    let monthYear = today.toLocaleString('default', { month: 'long', year: 'numeric' });

    // Ensure franchise and branch names are correctly displayed
    let franchiseeDisplay = selectedFranchisees.length > 0 ? selectedFranchisees.map(f => franchiseNameMap[f] || f).join(", ") : "All";
    let branchDisplay = selectedBranches.length > 0 ? selectedBranches.join(", ") : "All";

    // Update modal display
    document.getElementById("selectedFranchisees").innerText = franchiseeDisplay;
    document.getElementById("selectedBranches").innerText = branchDisplay;
    document.getElementById("selectedDateRange").innerText = `${firstDay} to ${lastDay}`;

    // Open the modal before fetching data
    $("#reportModal").modal("show");

    // Fetch report data
    fetchMonthlyReport("monthly", selectedFranchisees, JSON.stringify(selectedBranches), firstDay, lastDay, monthYear);
}

function fetchMonthlyReport(reportType, selectedFranchisees, selectedBranches, startDate, endDate, monthYear) {
    console.log("📌 Fetching Monthly Report", { reportType, selectedFranchisees, selectedBranches, startDate, endDate });

    $.ajax({
        url: "dashboard-inventory.php",
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
            console.log("✅ Monthly Report Data Received:", response);

            if (!response || typeof response !== "object" || !response.data || !Array.isArray(response.data)) {
                console.warn("⚠ No valid data received.");
                $("#reportTablesContainer").html("<p class='text-center text-danger'>No data available</p>");
                return;
            }

            let data = response.data;
            console.log("📌 Processed Monthly Report Data:", data);

            if (!data || data.length === 0) {
                console.warn("⚠ No report data available.");
                $("#reportTablesContainer").html("<p class='text-center'>No data available</p>");
                return;
            }

            // ✅ Clear previous content
            $("#reportTablesContainer").empty();

            // ✅ Group data by franchise & branch
            let groupedData = {};
            data.forEach(item => {
                let formattedFranchise = franchiseDisplayMap[item.franchisee] || item.franchisee;
                let key = `${formattedFranchise} - ${item.branch}`;
                if (!groupedData[key]) {
                    groupedData[key] = [];
                }
                groupedData[key].push(item);
            });

            // ✅ Create separate tables for each franchise-branch combination
            Object.keys(groupedData).forEach(group => {
                let items = groupedData[group];

                let tableHtml = `
                    <div class="franchise-section">
                        <h3 class="mt-4 text-center">Summary Report for the Month: ${monthYear}</h3>
                        <h4 class="mt-2 text-center franchise-title">${group}</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Inventory Usage Rate</th>
                                        <th>Days Until Stockout</th>
                                        <th>Average Sales</th>
                                        <th>Stock Waste</th>
                                        <th>Current Stock</th>
                                        <th>Stock Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                items.forEach(item => {
                    let stockStatus = getStockStatus1(item.current_stock);
                    tableHtml += `
                                    <tr>
                                        <td>${item.item_name}</td>
                                        <td>${item.sell_through_rate}</td>
                                        <td>${item.days_until_stockout}</td>
                                        <td>${item.average_sales}</td>
                                        <td>${item.stock_waste}</td>
                                        <td>${item.current_stock}</td>
                                        <td>${stockStatus}</td>
                                    </tr>
                    `;
                });

                tableHtml += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;

                $("#reportTablesContainer").append(tableHtml);
            });

            console.log("✅ Monthly Report tables updated successfully!");
        },
        error: function(xhr) {
            console.error("❌ AJAX Error:", xhr.responseText);
            $("#reportTablesContainer").html("<p class='text-center text-danger'>Failed to fetch report</p>");
        }
    });
}


function exportReportToCSV(reportType) {
    let csv = [];
    let dateRange, containerId, franchisees, branches;

    if (reportType === "exception") {
        dateRange = document.getElementById("exceptionSelectedDateRange").innerText;
        franchisees = document.getElementById("exceptionSelectedFranchisees").innerText.split(", ");
        branches = document.getElementById("exceptionSelectedBranches").innerText.split(", ");
        containerId = "exceptionReportTablesContainer";
    } else {
        dateRange = document.getElementById("selectedDateRange").innerText;
        franchisees = document.getElementById("selectedFranchisees").innerText.split(", ");
        branches = document.getElementById("selectedBranches").innerText.split(", ");
        containerId = "reportTablesContainer";
    }

    csv.push(`"${reportType.toUpperCase()} Report"`);
    csv.push(`"Date Range:","${dateRange}"`);
    csv.push(""); // Empty line before tables

    let tables = document.querySelectorAll(`#${containerId} table`);

    tables.forEach((table, index) => {
        // ✅ Get the correct franchise and branch based on index
        let franchise = franchisees[index] || "Unknown Franchise";
        let branch = branches[index] || "Unknown Branch";
        let headerText = `${franchise} - ${branch}`;

        csv.push(`"${headerText}"`); // ✅ Add Franchise-Branch header

        let rows = table.querySelectorAll("tr");
        rows.forEach((row) => {
            let cols = row.querySelectorAll("th, td");
            let rowData = [];
            cols.forEach((col) => {
                rowData.push('"' + col.innerText + '"'); // Wrap text in quotes
            });
            csv.push(rowData.join(",")); // Join columns with commas
        });

        csv.push(""); // Add space between tables
    });

    let csvBlob = new Blob([csv.join("\n")], { type: "text/csv" });
    let csvUrl = URL.createObjectURL(csvBlob);

    let downloadLink = document.createElement("a");
    downloadLink.href = csvUrl;
    downloadLink.download = `${reportType}_Report_${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Helper function to determine header text for non-exception reports
function getHeaderText(reportType) {
    let normalizedType = reportType.trim().toLowerCase();
    if (normalizedType.includes("detailed")) {
      return "DETAILED Report";
    } else if (normalizedType.includes("monthly")) {
      return "MONTHLY Report";
    } else if (normalizedType === "exception") {
      return "EXCEPTION Report";
    } else {
      return reportType.toUpperCase() + " Report";
    }
  }
  
  function exportReportToPDF(reportType) {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({
      orientation: "portrait",
      unit: "pt",
      format: "A4"
    });
    let pageWidth = doc.internal.pageSize.getWidth();
    let pageHeight = doc.internal.pageSize.getHeight();
    
    // Set up variables based on reportType
    let dateRange, containerId, franchisees, branches;
    if (reportType === "exception") {
      dateRange = document.getElementById("exceptionSelectedDateRange").innerText;
      franchisees = document.getElementById("exceptionSelectedFranchisees").innerText.split(", ");
      branches = document.getElementById("exceptionSelectedBranches").innerText.split(", ");
      containerId = "exceptionReportTablesContainer";
    } else {
      dateRange = document.getElementById("selectedDateRange").innerText;
      franchisees = document.getElementById("selectedFranchisees").innerText.split(", ");
      branches = document.getElementById("selectedBranches").innerText.split(", ");
      containerId = "reportTablesContainer";
    }
    
    // Preload background images (for all report types)
    let imgFirst = new Image();
    let imgOther = new Image();
    imgFirst.src = "assets/images/formDesign.png";    // First page background image
    imgOther.src = "assets/images/formDesign2.png";     // Subsequent pages background image
    
    imgFirst.onload = function () {
      // Apply background image for first page
      doc.addImage(imgFirst, "PNG", 0, 0, pageWidth, pageHeight);
    
      // Override addPage so that new pages get the secondary background image
      const originalAddPage = doc.addPage.bind(doc);
      doc.addPage = function () {
        originalAddPage();
        doc.addImage(imgOther, "PNG", 0, 0, pageWidth, pageHeight);
      };
    
      // Header section (same for all reports)
      let titleY = 140;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(22);
      // For exception report, header text is "EXCEPTION Report", otherwise "DETAILED/MONTHLY Report"
      let headerText = (reportType === "exception") ? "EXCEPTION Report" : "DETAILED/MONTHLY Report";
      doc.text(headerText, pageWidth / 2, titleY, { align: "center" });
    
      // Display date range and other details
      let dateY = titleY + 40;
      doc.setFontSize(8);
      doc.setFont("helvetica", "normal");
      doc.text(`Date Range: ${dateRange}`, 50, dateY);
      let currentDate = new Date().toLocaleDateString();
      doc.text(`Date Generated: ${currentDate}`, 50, dateY + 12);
      let exportedBy = typeof loggedInUser !== "undefined" ? loggedInUser : "Unknown User";
      doc.text(`Exported by: ${exportedBy}`, 50, dateY + 24);
    
      // Add extra spacing between "Exported by:" and the first franchise title
      let startY = dateY + 50; // Increase spacing as needed
    
      // Get all tables from the container
      let tables = document.querySelectorAll(`#${containerId} table`);
      if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Report table not found.");
        return;
      }
    
      // Process each table
      tables.forEach((table, index) => {
        let franchiseTitle = "Unknown Franchise";
        if (reportType === "exception") {
          // For exception reports, use the franchise and branch arrays (by index)
          // This assumes the order of tables corresponds to the order of selections
          let f = franchisees[index] || "Unknown Franchise";
          let b = branches[index] || "Unknown Branch";
          franchiseTitle = `${f} - ${b}`;
        } else {
          // For detailed/monthly reports, try to read from the DOM (similar to your original logic)
          let franchiseSection = table.closest(".franchise-section");
          if (franchiseSection) {
            franchiseTitle = franchiseSection.querySelector(".franchise-title")?.innerText || "Unknown Franchise";
          } else {
            let prevElem = table.previousElementSibling;
            if (prevElem && prevElem.tagName.toLowerCase() === "h4") {
              franchiseTitle = prevElem.innerText;
            }
          }
        }
    
        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(0, 0, 0);
        doc.text(franchiseTitle, 50, startY);
        startY += 20;
    
        // Extract table headers and rows
        let headers = [];
        let data = [];
        let rows = table.querySelectorAll("tr");
        rows.forEach((row, rowIndex) => {
          let rowData = [];
          let cols = row.querySelectorAll("th, td");
          cols.forEach(col => {
            rowData.push(col.innerText);
          });
          if (rowIndex === 0) {
            headers = rowData;
          } else {
            data.push(rowData);
          }
        });
    
        // Generate table using autoTable
        doc.autoTable({
          head: [headers],
          body: data,
          startY: startY,
          theme: "grid",
          styles: { fontSize: 10, cellPadding: 4 },
          headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: "bold" },
          alternateRowStyles: { fillColor: [245, 245, 245] },
          margin: { left: 40, right: 40 },
          tableWidth: "auto",
          columnStyles: { 0: { cellWidth: "auto" } }
        });
    
        startY = doc.lastAutoTable.finalY + 50;
      });
    
      let filenamePrefix = (reportType === "exception") ? "exception_report" : "inventory_report";
      doc.save(`${filenamePrefix}_${new Date().toISOString().split("T")[0]}.pdf`);
    };
  }
  
  
  
  
  
  
  
  
  

function getCurrentWeekDates() {
    let today = new Date();
    let firstDay = new Date(today.setDate(today.getDate() - today.getDay())); // Monday
    let lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6)); // Sunday

    // ✅ Format YYYY-MM-DD for <input type="date">
    let formatDate = (date) => date.toISOString().split("T")[0];

    return {
        monday: formatDate(firstDay),
        sunday: formatDate(lastDay),
    };
}

// ✅ Set the date inputs when the page loads
let weekDates = getCurrentWeekDates();
$("#startDate").val(weekDates.monday);
$("#endDate").val(weekDates.sunday);
