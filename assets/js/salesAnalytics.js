document.addEventListener("DOMContentLoaded", function () {
    loadFranchiseeButtons(); // Load franchisee buttons on page load
    fetchKPIData(); // Load initial KPI data
});

// 🎯 Franchise Name Mapping
const franchiseNameMap = {
    "auntie-anne": "Auntie Anne's",
    "macao-imperial": "Macao Imperial",
    "potato-corner": "Potato Corner"
};

// 🎯 Franchise Logo Mapping
const franchiseLogoMap = {
    "auntie-anne": "AuntieAnn.png",
    "macao-imperial": "MacaoImp.png",
    "potato-corner": "PotCor.png"
};

// 🎯 Predefined Order for Franchisees
const franchiseOrder = ["auntie-anne", "macao-imperial", "potato-corner"];

// 🎯 Load Franchisee Filter Buttons with Logos
function loadFranchiseeButtons() {
    fetch("dashboard-sales.php?json=true")
        .then(response => response.json())
        .then(data => {
            let franchiseeButtonsDiv = document.getElementById("franchiseeButtons");
            franchiseeButtonsDiv.innerHTML = ""; // Clear existing buttons

            // 🔥 Sort franchisees based on predefined order
            let sortedFranchisees = data.franchisees.sort((a, b) => {
                return franchiseOrder.indexOf(a.franchisee) - franchiseOrder.indexOf(b.franchisee);
            });

            // 🔥 Create buttons in correct order
            sortedFranchisees.forEach(franchisee => {
                let franchiseKey = franchisee.franchisee.toLowerCase().replace(/\s+/g, "-"); // Match key format
                let formattedName = franchiseNameMap[franchiseKey] || franchisee.franchisee; // Get formatted name
                
                // Get corresponding image filename from the map
                let logoFilename = franchiseLogoMap[franchiseKey] || "default.png"; // Use default if not found
                let logoSrc = `assets/images/${logoFilename}`; // Adjust path as needed

                let button = document.createElement("button");
                button.classList.add("btn", "btn-outline-primary", "m-2", "franchisee-btn");
                button.dataset.value = franchisee.franchisee;
                button.addEventListener("click", toggleFranchiseeSelection);

                // Create image element
                let img = document.createElement("img");
                img.src = logoSrc;
                img.alt = formattedName;
                img.classList.add("franchise-logo");

                // Add image & text inside button
                button.appendChild(img);
                button.appendChild(document.createTextNode(` ${formattedName}`)); // Add space & text
                franchiseeButtonsDiv.appendChild(button);
            });
        })
        .catch(error => console.error("Error loading franchisees:", error));
}



// 🎯 Handle Franchisee Selection
function toggleFranchiseeSelection(event) {
    let button = event.target;
    button.classList.toggle("btn-primary");
    button.classList.toggle("btn-outline-primary");
    button.classList.toggle("btn-selected");

    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);

    loadBranchButtons(selectedFranchisees);
    fetchKPIData();
}

// 🎯 Load Branch Filter Buttons
function loadBranchButtons(selectedFranchisees) {
    let branchButtonsDiv = document.getElementById("branchButtons");

    if (selectedFranchisees.length === 0) {
        branchButtonsDiv.style.display = "none";
        return;
    }

    fetch(`dashboard-sales.php?json=true&franchisees=${selectedFranchisees.join(",")}`)
        .then(response => response.json())
        .then(data => {
            console.log("🔍 JSON Response for Branches:", data);

            branchButtonsDiv.innerHTML = "";
            branchButtonsDiv.style.display = "block";

            if (!data.branchSales || typeof data.branchSales !== "object") {
                console.warn("⚠️ No valid branches returned!");
                return;
            }

            // Create a grid container
            let gridContainer = document.createElement("div");
            gridContainer.classList.add("branch-grid");

            Object.keys(data.branchSales).forEach(franchisee => {
                let franchiseColumn = document.createElement("div");
                franchiseColumn.classList.add("franchise-column");

                let title = document.createElement("h5");
                title.classList.add("franchise-title");
                title.innerText = franchisee;
                franchiseColumn.appendChild(title);

                let branchContainer = document.createElement("div");
                branchContainer.classList.add("branch-container"); // Flexbox layout for branches

                data.branchSales[franchisee].forEach(branch => {
                    let button = document.createElement("button");
                    button.classList.add("btn", "btn-outline-secondary", "branch-btn");
                    button.innerText = branch.location;
                    button.dataset.value = branch.location;
                    button.addEventListener("click", toggleBranchSelection);
                    branchContainer.appendChild(button);
                });

                franchiseColumn.appendChild(branchContainer);
                gridContainer.appendChild(franchiseColumn);
            });

            branchButtonsDiv.appendChild(gridContainer);
        })
        .catch(error => console.error("❌ Error loading branches:", error));
}



// 🎯 Handle Branch Selection
function toggleBranchSelection(event) {
    let button = event.target;
    button.classList.toggle("btn-secondary");
    button.classList.toggle("btn-outline-secondary");
    button.classList.toggle("btn-selected");

    fetchKPIData();
}

// 🎯 Fetch KPI Data and Update Cards
function fetchKPIData(forceReload = false) {
    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);
    let selectedBranches = Array.from(document.querySelectorAll(".branch-btn.btn-selected"))
        .map(btn => btn.dataset.value);

    let startDateInput = document.getElementById("startDate");
    let endDateInput = document.getElementById("endDate");

    // ✅ Force reset dates if page is loaded (fixes cache issue)
    if (forceReload || !startDateInput.value || !endDateInput.value) {
        let today = new Date();
        let firstDayOfYear = new Date(today.getFullYear(), 0, 2);

        startDateInput.value = firstDayOfYear.toISOString().split("T")[0];
        endDateInput.value = today.toISOString().split("T")[0];

        console.log("🔄 Start Date Reset to:", startDateInput.value);
        console.log("🔄 End Date Reset to:", endDateInput.value);
    }

    let startDate = startDateInput.value;
    let endDate = endDateInput.value;

    let url = "dashboard-sales.php?json=true";

    // ✅ Append selected filters to URL
    if (selectedFranchisees.length > 0) {
        url += `&franchisees=${selectedFranchisees.join(",")}`;
    }
    if (selectedBranches.length > 0) {
        url += `&branches=${selectedBranches.join(",")}`;
    }
    if (startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }

    console.log("📡 Fetching KPI Data with URL:", url);

    fetch(url)
    .then(response => response.json())
    .then(data => {
        console.log("✅ JSON Response for KPI Data:", data);
        console.log("📌 Selected Branches:", selectedBranches);
        console.log("📌 Total Expenses from Backend:", data.totalExpenses);

        let totalSales = parseFloat(data.totalSales) || 0;
        let totalExpenses = parseFloat(data.totalExpenses) || 0;
        let profit = totalSales - totalExpenses;

        document.getElementById("totalSales").innerText = totalSales.toLocaleString();
        document.getElementById("totalExpenses").innerText = totalExpenses.toLocaleString();
        document.getElementById("profit").innerText = profit.toLocaleString();

        updateSalesCharts(data);
        updateBestSellingChart(data.bestSelling);
        updateWorstSellingChart(data.worstSelling);
        updateYearlySalesChart(data);  // Yearly Sales Chart
    })
    .catch(error => console.error("❌ Error fetching KPI data:", error));
}



// 🎯 Update Sales Performance Charts
function updateSalesCharts(data) {
    let franchiseSalesData = data.franchiseSales;
    let franchiseBranchSalesData = data.branchSales;

    updateFranchiseSalesChart(franchiseSalesData);
    updateFranchiseBranchSalesChart(franchiseBranchSalesData);
}

// 🎯 Update Franchise Sales Chart
function updateFranchiseSalesChart(franchiseSalesData) {
    let franchiseNames = franchiseSalesData.map(item => item.franchise);
    let franchiseSales = franchiseSalesData.map(item => item.sales);

    let franchiseColors = {
        "Potato Corner": "#2E7D32",
        "Auntie Anne's": "#1565C0",
        "Macao Imperial": "#B71C1C"
    };

    let colors = franchiseNames.map(name => franchiseColors[name] || "#ffcc00");

    let ctx = document.getElementById("franchiseSalesChart").getContext("2d");

    if (window.franchiseSalesChart instanceof Chart) {
        window.franchiseSalesChart.destroy();
    }

    window.franchiseSalesChart = new Chart(ctx, {
        type: "pie",
        data: {
            labels: franchiseNames,
            datasets: [{
                label: "Sales per Franchise",
                data: franchiseSales,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    color: "white",
                    font: { size: 14, weight: "bold" },
                    anchor: "center",
                    align: "center",
                    formatter: value => value.toLocaleString()
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    let legendContainer = document.getElementById("franchiseLegend");
    legendContainer.innerHTML = "";
    franchiseNames.forEach((name, index) => {
        let legendItem = document.createElement("div");
        legendItem.innerHTML = `<span style="background-color: ${colors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> ${name}`;
        legendContainer.appendChild(legendItem);
    });
}

// 🎯 Update Franchise Branch Sales Chart
function updateFranchiseBranchSalesChart(franchiseBranchSalesData) {
    let ctx = document.getElementById("franchiseBranchChart").getContext("2d");
    let branchLegendContainer = document.getElementById("branchLegend");
    let labels = [];
    let salesData = [];
    let colors = [];

    let franchiseColors = {
        "Potato Corner": ["#2E7D32", "#388E3C", "#43A047"],
        "Auntie Anne's": ["#1565C0", "#1976D2", "#1E88E5"],
        "Macao Imperial": ["#B71C1C", "#C62828", "#D32F2F"]
    };

    Object.keys(franchiseBranchSalesData).forEach(franchise => {
        franchiseBranchSalesData[franchise].forEach((branch, index) => {
            labels.push(`${franchise} - ${branch.location}`);
            salesData.push(branch.sales);
            colors.push(franchiseColors[franchise][index % franchiseColors[franchise].length] || "#BDBDBD");
        });
    });

    if (window.franchiseBranchSalesChart instanceof Chart) {
        window.franchiseBranchSalesChart.destroy();
    }

    window.franchiseBranchSalesChart = new Chart(ctx, {
        type: "pie",
        data: {
            labels: labels,
            datasets: [{
                label: "Sales per Branch",
                data: salesData,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: {
                    color: "white",
                    font: { size: 12, weight: "bold" },
                    anchor: "center",
                    align: "center",
                    formatter: value => value.toLocaleString()
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    branchLegendContainer.innerHTML = "";
    labels.forEach((label, index) => {
        let legendItem = document.createElement("div");
        legendItem.innerHTML = `<span style="background-color: ${colors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> ${label}`;
        branchLegendContainer.appendChild(legendItem);
    });
}



const franchiseColors = {
    "Potato Corner": ["#2E7D32", "#388E3C", "#43A047", "#4CAF50", "#66BB6A"],  // Green Shades
    "Auntie Anne's": ["#1565C0", "#1976D2", "#1E88E5", "#2196F3", "#42A5F5"],  // Blue Shades
    "Macao Imperial": ["#B71C1C", "#C62828", "#D32F2F", "#E53935", "#F44336"]  // Red Shades
};



// 🎯 Update Best-Selling Products Chart
function updateBestSellingChart(bestSellingData) {
    console.log("🟢 Best-Selling Chart Data Received:", bestSellingData);

    if (!bestSellingData || bestSellingData.length === 0) {
        console.warn("⚠️ No data available for Best-Selling Products chart.");
        return;
    }

    let productNames = bestSellingData.map((item, index) => (index + 1).toString()); // Use ranking numbers instead of names

    let productSales = bestSellingData.map(item => item.sales);

    // Assign different shades per franchise
    let productColors = bestSellingData.map((item, index) => {
        let shades = franchiseColors[item.franchise] || ["#999999"];  // Default gray if not found
        return shades[index % shades.length]; // Cycle through available shades
    });

    let ctx = document.getElementById("bestSellingChart").getContext("2d");

    if (window.bestSellingChart instanceof Chart) {
        window.bestSellingChart.destroy();
    }

    window.bestSellingChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: productNames,
            datasets: [{
                label: "Best-Selling Products",
                data: productSales,
                backgroundColor: productColors,
                borderColor: productColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            let index = tooltipItems[0].dataIndex;
                            return `#${index + 1}: ${bestSellingData[index].product}`; // Show full product name
                        },
                        label: function(tooltipItem) {
                            let index = tooltipItem.dataIndex;
                            let sales = tooltipItem.raw.toLocaleString();
                            return `Sales: ${sales}`;
                        }
                    }
                },
                datalabels: {
                    color: "black",
                    font: { size: 12, weight: "bold" },
                    anchor: "end",
                    align: "top",
                    formatter: value => value.toLocaleString()
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 14 }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => value.toLocaleString() }
                }
            }
        },
        
        plugins: [ChartDataLabels]
    });

  // 🏆 Update Legend
let legendContainer = document.getElementById("bestSellingLegend");
legendContainer.innerHTML = ""; // Clear previous legend

bestSellingData.forEach((item, index) => {
    let legendItem = document.createElement("div");
    legendItem.innerHTML = `
        <span style="background-color: ${productColors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> 
        <strong>#${index + 1}: ${item.product}</strong> - ${item.franchise} (${item.location})
    `;
    legendContainer.appendChild(legendItem);
});
}


// 🎯 Update Worst-Selling Products Chart
function updateWorstSellingChart(worstSellingData) {
    console.log("🔴 Worst-Selling Chart Data Received:", worstSellingData);

    if (!worstSellingData || worstSellingData.length === 0) {
        console.warn("⚠️ No data available for Worst-Selling Products chart.");
        return;
    }

    let productNames = worstSellingData.map((item, index) => (index + 1).toString());
    let productSales = worstSellingData.map(item => item.sales);

    // Assign different shades per franchise
    let productColors = worstSellingData.map((item, index) => {
        let shades = franchiseColors[item.franchise] || ["#999999"];  // Default gray if not found
        return shades[index % shades.length]; // Cycle through available shades
    });

    let ctx = document.getElementById("worstSellingChart").getContext("2d");

    if (window.worstSellingChart instanceof Chart) {
        window.worstSellingChart.destroy();
    }

    window.worstSellingChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: productNames,
            datasets: [{
                label: "Worst-Selling Products",
                data: productSales,
                backgroundColor: productColors,
                borderColor: productColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                 // Add this tooltip block
                tooltip: {
                    callbacks: {
                    title: function(tooltipItems) {
                        let index = tooltipItems[0].dataIndex;
                        return `#${index + 1}: ${worstSellingData[index].product}`; 
                    },
                    label: function(tooltipItem) {
                        let index = tooltipItem.dataIndex;
                        let sales = tooltipItem.raw.toLocaleString();
                        return `Sales: ${sales}`;
                    }
                    }
                },
                datalabels: {
                    color: "black",
                    font: { size: 12, weight: "bold" },
                    anchor: "end",
                    align: "top",
                    formatter: value => value.toLocaleString()
                }
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => value.toLocaleString() }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // 🏆 Update Legend
    let legendContainer = document.getElementById("worstSellingLegend");
    legendContainer.innerHTML = ""; // Clear previous legend

    worstSellingData.forEach((item, index) => {
        let legendItem = document.createElement("div");
        legendItem.innerHTML = `
        <span style="background-color: ${productColors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> 
        <strong>#${index + 1}: ${item.product}</strong> - ${item.franchise} (${item.location})
      `;
        legendContainer.appendChild(legendItem);
    });
}




// GENERATE 
// 🎯 Format Date Range for User-Friendly Display
function formatDateRange(startDate, endDate) {
    let options = { year: "numeric", month: "long", day: "numeric" };

    let formattedStart = new Date(startDate).toLocaleDateString("en-US", options);
    let formattedEnd = new Date(endDate).toLocaleDateString("en-US", options);

    return `${formattedStart} - ${formattedEnd}`;
}


function generateReport() {
    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);
    let selectedBranches = Array.from(document.querySelectorAll(".branch-btn.btn-selected"))
        .map(btn => btn.dataset.value);
    let startDate = document.getElementById("startDate").value;
    let endDate = document.getElementById("endDate").value;

    // ✅ Map Franchisee Names for Display
    let franchiseeDisplay = selectedFranchisees.length > 0
        ? selectedFranchisees.map(f => franchiseNameMap[f] || f).join(", ")
        : "All";

    // ✅ Update Modal with Mapped Franchisee Names
    document.getElementById("selectedFranchisees").innerText = franchiseeDisplay;
    document.getElementById("selectedBranches").innerText = selectedBranches.length > 0 
        ? selectedBranches.join(", ") 
        : "All";

    document.getElementById("selectedDateRange").innerText = (startDate && endDate) 
        ? formatDateRange(startDate, endDate) 
        : "Not Set";
    

    // Show modal and fetch report
    $("#reportModal").modal("show");
    fetchReport("daily", selectedFranchisees, selectedBranches, startDate, endDate);
}



function fetchReport(type) {
    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);
    let selectedBranches = Array.from(document.querySelectorAll(".branch-btn.btn-selected"))
        .map(btn => btn.dataset.value);

    let startDate = document.getElementById("startDate").value;
    let endDate = document.getElementById("endDate").value;

    let url = `phpscripts/fetch-report.php?type=${type}`;

    if (selectedFranchisees.length > 0) {
        url += `&franchisees=${selectedFranchisees.join(",")}`;
    }
    if (selectedBranches.length > 0) {
        url += `&branches=${selectedBranches.join(",")}`;
    }
    if (startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log(`📊 ${type.toUpperCase()} Report Data Received:`, data);

            let reportTableBody = document.getElementById("reportTableBody");
            reportTableBody.innerHTML = "";

            if (data.length === 0) {
                reportTableBody.innerHTML = "<tr><td colspan='7' class='text-center'>No data available</td></tr>";
                return;
            }

            if (type === "weekly") {
                let weeklyGroupedData = {}; // Store grouped data for weekly report
            
                data.forEach(row => {
                    let formattedFranchise = franchiseNameMap[row.franchise] || row.franchise;
                    let productDisplay = row.product_name ? row.product_name.replace(/,/g, ", ") : "N/A";
            
                    // ✅ Format Weekly Date Range
                    let formattedDate = row.date;
                    let match = row.date.match(/Week (\d+) of (\d+)/);
                    if (match) {
                        let weekNumber = parseInt(match[1], 10);
                        let year = parseInt(match[2], 10);
            
                        let firstDayOfWeek = new Date(year, 0, (weekNumber - 1) * 7 + 1);
                        let lastDayOfWeek = new Date(year, 0, (weekNumber - 1) * 7 + 7);
            
                        formattedDate = `${firstDayOfWeek.toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" })} - ${lastDayOfWeek.toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" })}`;
                    }
            
                    // ✅ Group by franchise first, then by date and branch
                    if (!weeklyGroupedData[formattedFranchise]) {
                        weeklyGroupedData[formattedFranchise] = {};
                    }
            
                    let key = `${formattedDate}-${row.branch}`;
                    if (!weeklyGroupedData[formattedFranchise][key]) {
                        weeklyGroupedData[formattedFranchise][key] = {
                            franchise: formattedFranchise,
                            branch: row.branch,
                            date: formattedDate,
                            products: [],
                            totalSales: 0,
                        };
                    }
            
                    // ✅ Add product-specific data
                    weeklyGroupedData[formattedFranchise][key].products.push({
                        product: productDisplay,
                        sales: parseFloat(row.total_sales.replace(/,/g, "")),
                    });
            
                    // ✅ Accumulate totals for the weekly summary row
                    weeklyGroupedData[formattedFranchise][key].totalSales += parseFloat(row.total_sales.replace(/,/g, ""));
                });
            
                // ✅ Clear previous tables
                reportTableBody.innerHTML = "";
            
                // ✅ Franchise display order
                const franchiseOrder = ["Auntie Anne's", "Macao Imperial", "Potato Corner"];
            
                // ✅ Franchise logo mapping
                const franchiseLogos = {
                    "Auntie Anne's": "AuntieAnn.png",
                    "Macao Imperial": "MacaoImp.png",
                    "Potato Corner": "PotCor.png"
                };
            
                // ✅ Generate separate tables per franchise
                franchiseOrder.forEach(franchise => {
                    if (!weeklyGroupedData[franchise]) return; // Skip if no data exists
            
                    // ✅ Calculate total franchise sales
                    let totalFranchiseSales = Object.values(weeklyGroupedData[franchise]).reduce((sum, entry) => sum + entry.totalSales, 0);
            
                    // ✅ Get correct logo or fallback
                    let franchiseLogo = franchiseLogos[franchise] ? `assets/images/${franchiseLogos[franchise]}` : "assets/images/default.png";
            
                    let franchiseSection = document.createElement("div");
                    franchiseSection.classList.add("franchise-section", "mb-4", "p-3", "border", "rounded", "bg-white", "shadow-sm");
            
                    franchiseSection.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                            <div class="d-flex align-items-center">
                                <div class="franchise-logo-container me-2">
                                    <img src="${franchiseLogo}" alt="${franchise}" class="franchise-logo">
                                </div>
                                <h4 class="fw-bold mb-0 franchise-title-report">${franchise}</h4>
                            </div>
                            <span class="badge bg-primary fs-6 p-2 total-sales-badge">Total Sales: ₱ ${totalFranchiseSales.toLocaleString()}</span>
                        </div>
                        <table class="table table-bordered table-striped shadow-sm report-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Week Range</th>
                                    <th>Branch</th>
                                    <th>Product</th>
                                    <th class="text-end">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody id="table-${franchise.toLowerCase().replace(/\s+/g, '-')}">
                            </tbody>
                        </table>
                    `;
            
                    reportTableBody.appendChild(franchiseSection);
                    let franchiseTableBody = document.getElementById(`table-${franchise.toLowerCase().replace(/\s+/g, '-')}`);
            
                    // ✅ Append weekly grouped data for this franchise
                    Object.values(weeklyGroupedData[franchise]).sort((a, b) => {
                        let dateA = new Date(a.date.split(" - ")[1]); // Get the end date of the range
                        let dateB = new Date(b.date.split(" - ")[1]); // Get the end date of the range
                        return dateB - dateA; // Sort latest first
                    }).forEach(entry => {
                        let firstRow = true;
                        entry.products.forEach(productData => {
                            let tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${firstRow ? entry.date : ""}</td>
                                <td>${firstRow ? entry.branch : ""}</td>
                                <td>${productData.product}</td>
                                <td class="text-end">${productData.sales.toLocaleString()}</td>
                            `;
                            franchiseTableBody.appendChild(tr);
                            firstRow = false; // Prevents duplicate franchise/branch names
                        });
            
                        // ✅ Add total sales row at the end of the weekly section
                        let totalRow = document.createElement("tr");
                        totalRow.classList.add("table-warning", "fw-bold");
                        totalRow.innerHTML = `
                            <td colspan="3" class="text-end fw-bold">TOTAL WEEKLY SALES</td>
                            <td class="text-end fw-bold">${entry.totalSales.toLocaleString()}</td>
                        `;
                        franchiseTableBody.appendChild(totalRow);
                    });
                });

            } else if (type === "monthly") {
                let monthlyGroupedData = {}; // Store grouped data for monthly report
            
                data.forEach(row => {
                    let formattedFranchise = franchiseNameMap[row.franchise] || row.franchise;
                    let formattedDate = row.date;
            
                    // ✅ Extract "Month Year" format
                    let match = row.date.match(/(\w+ \d{4})/);
                    if (match) {
                        formattedDate = match[1];
                    }
            
                    // ✅ Group by franchise first, then by month and branch
                    if (!monthlyGroupedData[formattedFranchise]) {
                        monthlyGroupedData[formattedFranchise] = {};
                    }
            
                    let key = `${formattedDate}-${row.branch}`;
                    if (!monthlyGroupedData[formattedFranchise][key]) {
                        monthlyGroupedData[formattedFranchise][key] = {
                            franchise: formattedFranchise,
                            branch: row.branch,
                            date: formattedDate,
                            products: [],
                            totalSales: 0,
                            totalExpenses: parseFloat(row.total_expenses.replace(/,/g, "")) || 0, // Ensure valid number
                            profit: 0
                        };
                    }
            
                    // ✅ Ensure products are stored separately
                    let productEntry = {
                        product: row.product_name,
                        sales: parseFloat(row.total_sales.replace(/,/g, "")) || 0,
                        expenses: parseFloat(row.total_expenses.replace(/,/g, "")) || 0, 
                        profit: parseFloat(row.profit.replace(/,/g, "")) || 0
                    };
            
                    monthlyGroupedData[formattedFranchise][key].products.push(productEntry);
                    monthlyGroupedData[formattedFranchise][key].totalSales += productEntry.sales;
                });
            
                // ✅ Clear previous tables
                reportTableBody.innerHTML = "";
            
                // ✅ Franchise display order
                const franchiseOrder = ["Auntie Anne's", "Macao Imperial", "Potato Corner"];
            
                // ✅ Franchise logo mapping
                const franchiseLogos = {
                    "Auntie Anne's": "AuntieAnn.png",
                    "Macao Imperial": "MacaoImp.png",
                    "Potato Corner": "PotCor.png"
                };
            
                // ✅ Generate separate tables per franchise
                franchiseOrder.forEach(franchise => {
                    if (!monthlyGroupedData[franchise]) return; // Skip if franchise data does not exist
            
                    // ✅ Calculate total sales for this franchise
                    let totalFranchiseSales = Object.values(monthlyGroupedData[franchise]).reduce((sum, entry) => sum + entry.totalSales, 0);
            
                    // ✅ Get correct logo or fallback
                    let franchiseLogo = franchiseLogos[franchise] ? `assets/images/${franchiseLogos[franchise]}` : "assets/images/default.png";
            
                    let franchiseSection = document.createElement("div");
                    franchiseSection.classList.add("franchise-section", "mb-4", "p-3", "border", "rounded", "bg-white", "shadow-sm");
            
                    franchiseSection.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                            <div class="d-flex align-items-center">
                                <div class="franchise-logo-container me-2">
                                    <img src="${franchiseLogo}" alt="${franchise}" class="franchise-logo">
                                </div>
                                <h4 class="fw-bold mb-0 franchise-title-report">${franchise}</h4>
                            </div>
                            <span class="badge bg-primary fs-6 p-2 total-sales-badge">Total Sales: ₱ ${totalFranchiseSales.toLocaleString()}</span>
                        </div>
                        <table class="table table-bordered table-striped shadow-sm report-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Month</th>
                                    <th>Branch</th>
                                    <th>Product</th>
                                    <th class="text-end">Total Sales</th>
                                    <th class="text-end">Expenses</th>
                                    <th class="text-end">Profit</th>
                                </tr>
                            </thead>
                            <tbody id="table-${franchise.toLowerCase().replace(/\s+/g, '-')}">
                            </tbody>
                        </table>
                    `;
            
                    reportTableBody.appendChild(franchiseSection);
                    let franchiseTableBody = document.getElementById(`table-${franchise.toLowerCase().replace(/\s+/g, '-')}`);
            
                    // ✅ Append monthly grouped data for this franchise
                    Object.values(monthlyGroupedData[franchise]).reverse().forEach(entry => {
                        let firstRow = true;
                        entry.products.forEach(productData => {
                            let tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${firstRow ? entry.date : ""}</td>
                                <td>${firstRow ? entry.branch : ""}</td>
                                <td>${productData.product}</td>
                                <td class="text-end">${productData.sales.toLocaleString()}</td>
                                <td class="text-end">-</td>  <!-- ✅ Hide per-product expenses -->
                                <td class="text-end">-</td>  <!-- ✅ Replace per-product profit with "-" -->
                            `;
                            franchiseTableBody.appendChild(tr);
                            firstRow = false; // Prevents duplicate franchise/branch names
                        });
            
                        // ✅ Add total monthly sales row per branch
                        let totalRow = document.createElement("tr");
                        totalRow.classList.add("table-warning", "fw-bold");
                        totalRow.innerHTML = `
                            <td colspan="3" class="text-end fw-bold">TOTAL MONTHLY SALES</td>
                            <td class="text-end fw-bold">${entry.totalSales.toLocaleString()}</td>
                            <td class="text-end fw-bold">${entry.totalExpenses.toLocaleString()}</td>
                            <td class="text-end fw-bold">${(entry.totalSales - entry.totalExpenses).toLocaleString()}</td>
                        `;
                        franchiseTableBody.appendChild(totalRow);
                    });
                });
        
            } else {
                // ✅ DAILY REPORT - Separate tables for each franchisee
                let franchiseGroupedData = {}; // Store grouped data per franchise
            
                data.forEach(row => {
                    let formattedFranchise = franchiseNameMap[row.franchise] || row.franchise;
                    let productDisplay = row.product_name ? row.product_name.replace(/,/g, ", ") : "N/A";
                    let formattedDate = new Date(row.date).toLocaleDateString("en-US", {
                        year: "numeric",
                        month: "long",
                        day: "numeric"
                    });
            
                    if (!franchiseGroupedData[formattedFranchise]) {
                        franchiseGroupedData[formattedFranchise] = [];
                    }
            
                    franchiseGroupedData[formattedFranchise].push({
                        date: formattedDate,
                        branch: row.branch,
                        product: productDisplay,
                        total_sales: row.total_sales
                    });
                });
            
                // Clear previous tables
                reportTableBody.innerHTML = "";
            
                // ✅ Generate separate tables per franchise
                const franchiseOrder = ["Auntie Anne's", "Macao Imperial", "Potato Corner"]; // Define specific order

                // Franchise logo mapping
                const franchiseLogos = {
                    "Auntie Anne's": "AuntieAnn.png",
                    "Macao Imperial": "MacaoImp.png",
                    "Potato Corner": "PotCor.png"
                };

                franchiseOrder.forEach(franchise => {
                    if (!franchiseGroupedData[franchise]) return; // Skip if franchise data does not exist

                    let totalFranchiseSales = franchiseGroupedData[franchise].reduce((sum, entry) => sum + parseFloat(entry.total_sales.replace(/,/g, "")), 0);
                    
                    // Get the correct logo filename
                    let franchiseLogo = franchiseLogos[franchise] || "default.png"; // Fallback to default logo if not found

                    let franchiseSection = document.createElement("div");
                    franchiseSection.classList.add("franchise-section", "mb-4", "p-3", "border", "rounded", "bg-white", "shadow-sm");

                    franchiseSection.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                        <div class="d-flex align-items-center">
                            <div class="franchise-logo-container me-2">
                                <img src="assets/images/${franchiseLogo}" alt="${franchise}" class="franchise-logo">
                            </div>
                            <h4 class="fw-bold mb-0 franchise-title-report">${franchise}</h4>
                        </div>
                        <span class="badge bg-primary fs-6 p-2 total-sales-badge">Total Sales: ₱ ${totalFranchiseSales.toLocaleString()}</span>
                    </div>
                    <table class="table table-bordered table-striped shadow-sm report-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Branch</th>
                                <th>Product</th>
                                <th class="text-end">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody id="table-${franchise.replace(/\s+/g, '-')}">
                        </tbody>
                    </table>
                `;
                    reportTableBody.appendChild(franchiseSection);

                    let franchiseTableBody = document.getElementById(`table-${franchise.replace(/\s+/g, '-')}`);

                    franchiseGroupedData[franchise].forEach(entry => {
                        let tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${entry.date}</td>
                            <td>${entry.branch}</td>
                            <td>${entry.product}</td>
                            <td class="text-end">${entry.total_sales}</td>
                        `;
                        franchiseTableBody.appendChild(tr);
                    });
                });
            }
            
        })
        .catch(error => console.error("❌ Error fetching report data:", error));

    setActiveReportButton(type); // ✅ Highlight the active report type
    toggleColumns(type);

}

function toggleColumns(type) {
    let expenseHeader = document.querySelector("th:nth-child(6)");
    let profitHeader = document.querySelector("th:nth-child(7)");

    let tableRows = document.querySelectorAll("#reportTableBody tr");

    if (type === "daily" || type === "weekly") {
        // Hide the headers
        if (expenseHeader) expenseHeader.style.display = "none";
        if (profitHeader) profitHeader.style.display = "none";

        // Hide the column cells
        tableRows.forEach(row => {
            let expenseCell = row.querySelector("td:nth-child(6)");
            let profitCell = row.querySelector("td:nth-child(7)");
            if (expenseCell) expenseCell.style.display = "none";
            if (profitCell) profitCell.style.display = "none";
        });
    } else {
        // Show the headers
        if (expenseHeader) expenseHeader.style.display = "";
        if (profitHeader) profitHeader.style.display = "";

        // Show the column cells
        tableRows.forEach(row => {
            let expenseCell = row.querySelector("td:nth-child(6)");
            let profitCell = row.querySelector("td:nth-child(7)");
            if (expenseCell) expenseCell.style.display = "";
            if (profitCell) profitCell.style.display = "";
        });
    }
}



function setActiveReportButton(type) {
    document.querySelectorAll(".report-btn").forEach(btn => {
        btn.classList.remove("active");
    });

    let activeButton = document.querySelector(`.report-btn[onclick="fetchReport('${type}')"]`);
    if (activeButton) {
        activeButton.classList.add("active");
    }
}


// EXPORT CSV
function exportTableToCSV() {
    let csvLines = [];
  
    // Get the selected report type and filters
    let reportType =
      document.querySelector(".report-btn.active")?.innerText || "Unknown Report Type";
    let franchiseFilter =
      document.getElementById("selectedFranchisees")?.innerText || "All";
    let branchFilter = document.getElementById("selectedBranches")?.innerText || "All";
    let dateRange = document.getElementById("selectedDateRange")?.innerText || "Not Set";
  
    // Add header information
    csvLines.push('"Sales Report"');
    csvLines.push(`"Report Type:","${reportType}"`);
    csvLines.push(`"Franchise(s):","${franchiseFilter}"`);
    csvLines.push(`"Branch(es):","${branchFilter}"`);
    csvLines.push(`"Date Range:","${dateRange}"`);
    csvLines.push(""); // Blank line
  
    // Check if there are separate franchise sections (for daily/weekly/monthly)
    let sections = document.querySelectorAll(".franchise-section");
    if (sections.length > 0) {
      sections.forEach(section => {
        // Get the franchise title
        let titleEl = section.querySelector(".franchise-title-report");
        let title = titleEl ? titleEl.innerText.trim() : "Franchise";
        // Add the franchise heading row
        csvLines.push(`"${title}"`);
  
        // Get the table in this section
        let table = section.querySelector("table");
        if (!table) return; // Skip if not found
  
        // Extract all rows from the table
        let rows = table.querySelectorAll("tr");
        let sectionRows = [];
        rows.forEach((row, rowIndex) => {
          let cols = row.querySelectorAll("th, td");
          let rowData = [];
          cols.forEach(col => {
            // Escape the cell value by wrapping in quotes
            rowData.push(`"${col.innerText.trim()}"`);
          });
          sectionRows.push(rowData.join(","));
        });
  
        // Process totals based on report type
        if (reportType.toLowerCase() === "daily") {
          // Compute total sales (assumed last column) from data rows (skip header)
          let totalSalesValue = 0;
          for (let i = 1; i < rows.length; i++) {
            let cells = rows[i].querySelectorAll("th, td");
            if (cells.length > 0) {
              let valueText = cells[cells.length - 1].innerText.trim().replace(/,/g, "");
              let val = parseFloat(valueText);
              if (!isNaN(val)) totalSalesValue += val;
            }
          }
          // Create a total row with empty cells for all but the last two columns
          let headerCells = rows[0].querySelectorAll("th, td");
          let colCount = headerCells.length;
          let totalRowArr = [];
          for (let j = 0; j < colCount - 2; j++) {
            totalRowArr.push('""');
          }
          totalRowArr.push('"TOTAL SALES"');
          totalRowArr.push(`"${totalSalesValue.toLocaleString()}"`);
          sectionRows.push(totalRowArr.join(","));
        } else if (reportType.toLowerCase() === "weekly") {
          // Rebuild any row that contains "TOTAL WEEKLY SALES"
          sectionRows = sectionRows.map(line => {
            if (line.indexOf("TOTAL WEEKLY SALES") > -1) {
              // Find the original row from the table to extract the total value
              let tr = Array.from(rows).find(r => r.innerText.includes("TOTAL WEEKLY SALES"));
              if (tr) {
                let cells = tr.querySelectorAll("th, td");
                let totalValue = cells[cells.length - 1].innerText.trim();
                return '"" ,"" ,"TOTAL WEEKLY SALES","' + totalValue + '"';
              }
            }
            return line;
          });
        } else if (reportType.toLowerCase() === "monthly") {
          // For monthly, rebuild total row so that:
          // First two cells empty, third cell = "TOTAL MONTHLY SALES",
          // followed by total sales, total expenses, and profit.
          sectionRows = sectionRows.map(line => {
            if (line.indexOf("TOTAL MONTHLY SALES") > -1) {
              let tr = Array.from(rows).find(r => r.innerText.includes("TOTAL MONTHLY SALES"));
              if (tr) {
                let cells = tr.querySelectorAll("th, td");
                // Assuming cells: [0]: label, [1]: total sales, [2]: total expenses, [3]: profit
                let totalSales = cells[1] ? cells[1].innerText.trim() : "";
                let totalExpenses = cells[2] ? cells[2].innerText.trim() : "";
                let profit = cells[3] ? cells[3].innerText.trim() : "";
                return '"" ,"" ,"TOTAL MONTHLY SALES","' + totalSales + '","' + totalExpenses + '","' + profit + '"';
              }
            }
            return line;
          });
        }
  
        // Append the section rows to the CSV lines, then add an empty line
        csvLines.push(...sectionRows);
        csvLines.push("");
      });
    } else {
      // Fallback: single table export if no separate franchise sections exist
      let table = document.getElementById("reportTable");
      if (!table) {
        console.error("❌ Error: Table element not found!");
        alert("Error: Sales report table not found.");
        return;
      }
      let rows = table.querySelectorAll("tr");
      rows.forEach((row, rowIndex) => {
        let cols = row.querySelectorAll("th, td");
        let rowData = [];
        cols.forEach(col => {
          rowData.push(`"${col.innerText.trim()}"`);
        });
        csvLines.push(rowData.join(","));
      });
      // Process totals similarly for daily, weekly, or monthly
      if (reportType.toLowerCase() === "daily") {
        let totalSalesValue = 0;
        for (let i = 1; i < rows.length; i++) {
          let cells = rows[i].querySelectorAll("th, td");
          if (cells.length > 0) {
            let valueText = cells[cells.length - 1].innerText.trim().replace(/,/g, "");
            let val = parseFloat(valueText);
            if (!isNaN(val)) totalSalesValue += val;
          }
        }
        let headerCells = rows[0].querySelectorAll("th, td");
        let colCount = headerCells.length;
        let totalRowArr = [];
        for (let j = 0; j < colCount - 2; j++) {
          totalRowArr.push('""');
        }
        totalRowArr.push('"TOTAL SALES"');
        totalRowArr.push(`"${totalSalesValue.toLocaleString()}"`);
        csvLines.push(totalRowArr.join(","));
      } else if (reportType.toLowerCase() === "weekly") {
        csvLines = csvLines.map(line => {
          if (line.indexOf("TOTAL WEEKLY SALES") > -1) {
            let tr = Array.from(rows).find(r => r.innerText.includes("TOTAL WEEKLY SALES"));
            if (tr) {
              let cells = tr.querySelectorAll("th, td");
              let totalValue = cells[cells.length - 1].innerText.trim();
              return '"" ,"" ,"TOTAL WEEKLY SALES","' + totalValue + '"';
            }
          }
          return line;
        });
      } else if (reportType.toLowerCase() === "monthly") {
        csvLines = csvLines.map(line => {
          if (line.indexOf("TOTAL MONTHLY SALES") > -1) {
            let tr = Array.from(rows).find(r => r.innerText.includes("TOTAL MONTHLY SALES"));
            if (tr) {
              let cells = tr.querySelectorAll("th, td");
              let totalSales = cells[1] ? cells[1].innerText.trim() : "";
              let totalExpenses = cells[2] ? cells[2].innerText.trim() : "";
              let profit = cells[3] ? cells[3].innerText.trim() : "";
              return '"" ,"" ,"TOTAL MONTHLY SALES","' + totalSales + '","' + totalExpenses + '","' + profit + '"';
            }
          }
          return line;
        });
      }
    }
  
    // Build CSV content and trigger download
    let csvContent = "data:text/csv;charset=utf-8," + csvLines.join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `sales_report_${reportType.replace(/\s+/g, "_").toLowerCase()}.csv`);
    document.body.appendChild(link);
    link.click();
  }
  


// EXPORT TO PDF
function exportTableToPDF() {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({
      orientation: "portrait",
      unit: "pt",
      format: "A4"
    });
  
    // Load background images for first page and subsequent pages
    let img1 = new Image();
    let img2 = new Image();
    img1.src = "assets/images/formDesign.png";    // Background for the 1st page
    img2.src = "assets/images/formDesign2.png";     // Background for pages 2+
  
    // Wait for the first image to load
    img1.onload = function () {
      // Add the first page background
      doc.addImage(img1, "PNG", 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
  
      // Override addPage so that every new page gets the alternate background image
      const originalAddPage = doc.addPage.bind(doc);
      doc.addPage = function () {
        originalAddPage();
        doc.addImage(img2, "PNG", 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
      };
  
      let pageWidth = doc.internal.pageSize.getWidth();
      let startY = 140;
  
      // Title
      doc.setFont("helvetica", "bold");
      doc.setFontSize(22);
      doc.text("Sales Report", pageWidth / 2, startY, { align: "center" });
      startY += 40;
  
      // Date Generated & Exported by (printed outside any box)
      let currentDate = new Date().toLocaleDateString();
      doc.setFontSize(10);
      doc.setFont("helvetica", "normal");
      doc.text(`Date Generated: ${currentDate}`, 50, startY);
      startY += 12;
      let exportedBy = typeof loggedInUser !== "undefined" ? loggedInUser : "Unknown User";
      doc.text(`Exported by: ${exportedBy}`, 50, startY);
      startY += 20;
  
      // Get filter info values
      let reportType = document.querySelector(".report-btn.active")?.innerText || "Unknown Report Type";
      let franchiseFilter = document.getElementById("selectedFranchisees")?.innerText || "All";
      let branchFilter = document.getElementById("selectedBranches")?.innerText || "All";
      let dateRange = document.getElementById("selectedDateRange")?.innerText || "Not Set";
  
      // Prepare filter info text lines
      const filterLines = [
        `Report Type: ${reportType}`,
        `Franchisee(s): ${franchiseFilter}`,
        `Branch(es): ${branchFilter}`,
        `Date Range: ${dateRange}`
      ];
  
      // Calculate the height for the filter box.
      // For example: 10pt top padding + 15pt per line + 10pt bottom padding.
      let filterBoxHeight = 10 + filterLines.length * 15 + 5;
  
      // Draw the white rounded rectangle for the filter info
      doc.setDrawColor(0, 0, 0);    // Black outline
      doc.setFillColor(255, 255, 255); // White fill
      doc.roundedRect(40, startY, pageWidth - 80, filterBoxHeight, 5, 5, "FD");
  
      // Write each line inside the box with vertical spacing
      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text(filterLines[0], 50, startY + 15);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(10);
      doc.text(filterLines[1], 50, startY + 30);
      doc.text(filterLines[2], 50, startY + 45);
      doc.text(filterLines[3], 50, startY + 60);
      startY += filterBoxHeight + 35; // update startY after the box
  
      // Now, add the rest of your table export logic here...
      // For example, looping through your franchise sections and generating tables:
      let sections = document.querySelectorAll(".franchise-section");
      if (sections.length > 0) {
        sections.forEach(section => {
          let titleEl = section.querySelector(".franchise-title-report");
          let title = titleEl ? titleEl.innerText.trim() : "Franchise";
  
          if (startY > doc.internal.pageSize.getHeight() - 50) {
            doc.addPage();
            startY = 50;
          }
          doc.setFont("helvetica", "bold");
          doc.setFontSize(16);
          doc.text(title, 40, startY);
          startY += 20;
  
          // (Optional) If your section has a summary, you could add it here.
          let table = section.querySelector("table");
          if (!table) return;
    
          // Extract headers and data from the table
          let headers = [];
          let data = [];
          let rows = table.querySelectorAll("tr");
          rows.forEach((row, rowIndex) => {
            let rowData = [];
            let cols = row.querySelectorAll("th, td");
            cols.forEach(col => {
              rowData.push(col.innerText.trim());
            });
            if (rowIndex === 0) {
              headers = rowData;
            } else {
              data.push(rowData);
            }
          });
    
          // Process total rows based on report type (daily, weekly, monthly)
          if (reportType.toLowerCase() === "daily") {
            let totalSalesValue = 0;
            data.forEach(row => {
              let salesText = row[row.length - 1].replace(/,/g, "");
              let value = parseFloat(salesText);
              if (!isNaN(value)) totalSalesValue += value;
            });
            let totalRow = ["", "", "TOTAL SALES", totalSalesValue.toLocaleString()];
            data.push(totalRow);
          } else if (reportType.toLowerCase() === "weekly") {
            data = data.map(row => {
              if (row.includes("TOTAL WEEKLY SALES")) {
                return ["", "", "TOTAL WEEKLY SALES", row[row.length - 1]];
              }
              return row;
            });
          } else if (reportType.toLowerCase() === "monthly") {
            data = data.map(row => {
              if (row.includes("TOTAL MONTHLY SALES")) {
                return ["", "", "TOTAL MONTHLY SALES", row[1], row[2], row[3]];
              }
              return row;
            });
          }
    
          // Generate the table using autoTable
          doc.autoTable({
            head: [headers],
            body: data,
            startY: startY,
            theme: "grid",
            styles: { fontSize: 10, cellPadding: 3 },
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: "bold" },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            margin: { left: 40, right: 40 },
            tableWidth: "auto",
            columnStyles: { 0: { cellWidth: "auto" } },
            didParseCell: function (dataCell) {
              if (dataCell.row.section === "body") {
                let rowText = dataCell.row.raw.join(" ");
                if (rowText.includes("TOTAL WEEKLY SALES") ||
                    rowText.includes("TOTAL MONTHLY SALES") ||
                    rowText.includes("TOTAL SALES")) {
                  Object.values(dataCell.row.cells).forEach(cell => {
                    cell.styles.fillColor = [255, 240, 178];
                    cell.styles.fontStyle = "bold";
                  });
                }
              }
            }
          });
          startY = doc.lastAutoTable.finalY + 30;
          if (startY > doc.internal.pageSize.getHeight() - 50) {
            doc.addPage();
            startY = 50;
          }
        });
      } else {
        // Fallback if no separate franchise sections exist (single table export)
        let table = document.getElementById("reportTable");
        if (!table) {
          console.error("❌ Error: Table element not found!");
          alert("Error: Sales report table not found.");
          return;
        }
        let headers = [];
        let data = [];
        let rows = table.querySelectorAll("tr");
        rows.forEach((row, rowIndex) => {
          let rowData = [];
          let cols = row.querySelectorAll("th, td");
          cols.forEach(col => rowData.push(col.innerText.trim()));
          if (rowIndex === 0) {
            headers = rowData;
          } else {
            data.push(rowData);
          }
        });
        if (reportType.toLowerCase() === "daily") {
          let totalSalesValue = 0;
          data.forEach(row => {
            let salesText = row[row.length - 1].replace(/,/g, "");
            let value = parseFloat(salesText);
            if (!isNaN(value)) totalSalesValue += value;
          });
          let totalRow = ["", "", "TOTAL SALES", totalSalesValue.toLocaleString()];
          data.push(totalRow);
        } else if (reportType.toLowerCase() === "weekly") {
          data = data.map(row => {
            if (row.includes("TOTAL WEEKLY SALES")) {
              return ["", "", "TOTAL WEEKLY SALES", row[row.length - 1]];
            }
            return row;
          });
        } else if (reportType.toLowerCase() === "monthly") {
          data = data.map(row => {
            if (row.includes("TOTAL MONTHLY SALES")) {
              return ["", "", "TOTAL MONTHLY SALES", row[1], row[2], row[3]];
            }
            return row;
          });
        }
        doc.autoTable({
          head: [headers],
          body: data,
          startY: startY,
          theme: "grid",
          styles: { fontSize: 10, cellPadding: 3 },
          headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: "bold" },
          margin: { left: 40, right: 40 },
          didParseCell: function (dataCell) {
            if (dataCell.row.section === "body" && dataCell.row.raw.join(" ").includes("TOTAL")) {
              dataCell.cell.styles.fillColor = [255, 240, 178];
              dataCell.cell.styles.fontStyle = "bold";
            }
          }
        });
      }
    
      // Save the PDF with a filename based on the report type
      doc.save(`sales_report_${reportType.replace(/\s+/g, "_").toLowerCase()}.pdf`);
    };
  }
  

// SET DEFAULT DATE TO JANUARY 1ST 2025
document.addEventListener("DOMContentLoaded", function () {
    // ✅ Get current date
    let today = new Date();
    
    // ✅ Get the first day of the current year (January 1st)
    let firstDayOfYear = new Date(today.getFullYear(), 0, 2); // adjusted to 2 bc sets as december 31st

    // ✅ Format dates as "YYYY-MM-DD"
    let formattedStartDate = firstDayOfYear.toISOString().split("T")[0];
    let formattedEndDate = today.toISOString().split("T")[0];

    // ✅ Get date input fields
    let startDateInput = document.getElementById("startDate");
    let endDateInput = document.getElementById("endDate");

    // ✅ Remove cached values & override
    startDateInput.removeAttribute("value");
    endDateInput.removeAttribute("value");
    startDateInput.value = formattedStartDate;
    endDateInput.value = formattedEndDate;

    console.log("📆 Setting Default Date Filters:");
    console.log("👉 Start Date:", formattedStartDate);
    console.log("👉 End Date:", formattedEndDate);

    // ✅ Trigger data load with forced reload to ensure filtering applies
    fetchKPIData(true);
});


function updateYearlySalesChart(data) {
    let yearlySalesData = data.yearlySalesTrend;
    if (!yearlySalesData || yearlySalesData.length === 0) {
        console.warn("⚠️ No yearly sales data available.");
        return;
    }

    // Extract labels & values
    let years = yearlySalesData.map(entry => entry.year);
    let sales = yearlySalesData.map(entry => entry.sales);

    // Prepare the chart context
    let canvas = document.getElementById("yearlySalesChart");
    let ctx = canvas.getContext("2d");

    // Destroy old chart instance if it exists
    if (window.yearlySalesChart && typeof window.yearlySalesChart.destroy === "function") {
        window.yearlySalesChart.destroy();
    }

    // Create a nice gradient for the line fill
    let gradientFill = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    gradientFill.addColorStop(0, "rgba(0, 123, 255, 0.4)"); // Start color
    gradientFill.addColorStop(1, "rgba(0, 123, 255, 0)");   // Fade to transparent

    // Create the new chart
    window.yearlySalesChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: years,
            datasets: [{
                label: "Total Sales Per Year",
                data: sales,
                // Use the gradient fill
                backgroundColor: gradientFill,
                fill: true,
                borderColor: "#007BFF",  // Line color
                borderWidth: 3,          // Thicker line
                tension: 0.3,            // Smooth curves
                pointRadius: 5,          // Data point size
                pointBackgroundColor: "#007BFF",
                pointBorderColor: "#fff",
                pointHoverRadius: 7      // Bigger on hover
            }]
        },
        options: {
            responsive: true,
            // Keeps a certain ratio between width & height
            maintainAspectRatio: true,
            aspectRatio: 1,  // Adjust as you like (bigger = wider)
            scales: {
                x: {
                    // Soft grid lines
                    grid: {
                        color: "rgba(0,0,0,0.05)",
                        // If you want no vertical lines:
                        // display: false
                    },
                    // Axis border
                    border: {
                        display: true,
                        color: "rgba(0,0,0,0.2)"
                    },
                    ticks: {
                        color: "#333",
                        font: {
                            size: 14,
                            weight: "bold"
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0,0,0,0.05)"
                    },
                    border: {
                        display: true,
                        color: "rgba(0,0,0,0.2)"
                    },
                    ticks: {
                        color: "#333",
                        font: {
                            size: 12
                        },
                        callback: function(value) {
                            return "₱" + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                        color: "#333",
                        font: {
                            size: 14,
                            weight: "bold"
                        },
                        boxWidth: 15
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: "#000",
                    titleFont: { size: 14, weight: "bold" },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            return "₱" + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}
