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

    let productNames = bestSellingData.map(item => item.product);
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
    let legendContainer = document.getElementById("bestSellingLegend");
    legendContainer.innerHTML = ""; // Clear previous legend

    bestSellingData.forEach((item, index) => {
        let legendItem = document.createElement("div");
        legendItem.innerHTML = `
            <span style="background-color: ${productColors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> 
            <strong>${item.product}</strong> - ${item.franchise} (${item.location})
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

    let productNames = worstSellingData.map(item => item.product);
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
            <strong>${item.product}</strong> - ${item.franchise} (${item.location})
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
    let table = document.getElementById("reportTable");

    if (!table) {
        console.error("❌ Error: Table element not found!");
        alert("Error: Sales report table not found.");
        return;
    }

    let csv = [];

    // ✅ Get the selected report type and filters
    let reportType = document.querySelector(".report-btn.active")?.innerText || "Unknown Report Type";
    let franchiseFilter = document.getElementById("selectedFranchisees")?.innerText || "All";
    let branchFilter = document.getElementById("selectedBranches")?.innerText || "All";
    let dateRange = document.getElementById("selectedDateRange")?.innerText || "Not Set";

    // ✅ Add Report Type and Filters at the top of the CSV file
    csv.push(`"Sales Report"`);
    csv.push(`"Report Type:","${reportType}"`);
    csv.push(`"Franchise(s):","${franchiseFilter}"`);
    csv.push(`"Branch(es):","${branchFilter}"`);
    csv.push(`"Date Range:","${dateRange}"`);
    csv.push(""); // Empty row for spacing

    let rows = table.querySelectorAll("tr");

    rows.forEach(row => {
        let cols = row.querySelectorAll("th, td");
        let rowData = [];

        cols.forEach(col => {
            rowData.push(`"${col.innerText}"`);
        });

        csv.push(rowData.join(","));
    });

    let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
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

    // ✅ Add Title
    doc.setFont("helvetica", "bold");
    doc.setFontSize(22);
    let pageWidth = doc.internal.pageSize.getWidth();
    doc.text("Sales Report", pageWidth / 2, 50, { align: "center" });

    // ✅ Add "Date" Section
    let currentDate = new Date().toLocaleDateString();
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text(`Date Generated: ${currentDate}`, 400, 80);

    // ✅ Get selected filters
    let reportType = document.querySelector(".report-btn.active")?.innerText || "Unknown Report Type";
    let franchiseFilter = document.getElementById("selectedFranchisees")?.innerText || "All";
    let branchFilter = document.getElementById("selectedBranches")?.innerText || "All";
    let dateRange = document.getElementById("selectedDateRange")?.innerText || "Not Set";

    // ✅ Add Report Type & Filters
    doc.setFontSize(12);
    doc.setFont("helvetica", "bold");
    doc.text(`Report Type: ${reportType}`, 50, 100);
    doc.setFont("helvetica", "normal");
    doc.text(`Franchisee(s): ${franchiseFilter}`, 50, 120);
    doc.text(`Branch(es): ${branchFilter}`, 50, 135);
    doc.text(`Date Range: ${dateRange}`, 50, 150);

    // ✅ Space before table
    let startY = 180;

    // ✅ Extract Table Data
    let table = document.getElementById("reportTable");
    if (!table) {
        console.error("❌ Error: Table element not found!");
        alert("Error: Sales report table not found.");
        return;
    }

    let headers = [];
    let data = [];
    let rows = table.querySelectorAll("tr");

    // ✅ Detect column indices for "Total Expenses" & "Profit"
    let expenseIndex = -1;
    let profitIndex = -1;

    rows.forEach((row, rowIndex) => {
        let rowData = [];
        let cols = row.querySelectorAll("th, td");

        cols.forEach((col, colIndex) => {
            let text = col.innerText.trim();

            // ✅ Detect column positions
            if (rowIndex === 0) {
                if (text === "Total Expenses") expenseIndex = colIndex;
                if (text === "Profit") profitIndex = colIndex;

                // ✅ Remove "Total Expenses" & "Profit" columns for Daily & Weekly
                if ((reportType === "Daily" || reportType === "Weekly") && (colIndex === expenseIndex || colIndex === profitIndex)) {
                    return;
                }
                rowData.push(text);
            } else {
                // ✅ Remove "Total Expenses" & "Profit" data for Daily & Weekly
                if ((reportType === "Daily" || reportType === "Weekly") && (colIndex === expenseIndex || colIndex === profitIndex)) {
                    return;
                }

                rowData.push(text);
            }
        });

        if (rowIndex === 0) {
            headers = rowData; // Store updated headers
        } else {
            data.push(rowData); // Store filtered data rows
        }
    });

    // ✅ Fix: Ensure correct colspan for "TOTAL WEEKLY SALES" & "TOTAL MONTHLY SALES"
    data = data.map(row => {
        if (row.includes("TOTAL WEEKLY SALES")) {
            // ✅ Adjust colspan to 4 and **ensure total sales are included**
            // return ["", "", "",{ content: "TOTAL WEEKLY SALES", styles: { fillColor: [255, 240, 178], fontStyle: "bold" } }, row[row.length - 1]];
            return ["", "", "", "TOTAL WEEKLY SALES" , row[row.length - 1]];
        }
        if (row.includes("TOTAL MONTHLY SALES")) {
            // ✅ Adjust colspan to 4 and **ensure all values are displayed properly**
            // return ["", "", "",{ content: "TOTAL MONTHLY SALES", styles: { fillColor: [255, 240, 178], fontStyle: "bold" } }, row[row.length - 3], row[row.length - 2], row[row.length - 1]];
            return ["", "", "", "TOTAL MONTHLY SALES", row[row.length - 3], row[row.length - 2], row[row.length - 1]];
        }
        return row;
    });

    // ✅ Generate Table with `autoTable`
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

        // ✅ Ensure only the actual total rows are styled and remove unnecessary yellow rows
    didParseCell: function (data) {
        if (data.row.section === 'body') {
            let rowText = data.row.raw.join(" "); // Combine row text to check
            
            // ✅ Only highlight TOTAL SALES rows & make them bold
            if (rowText.includes("TOTAL WEEKLY SALES") || rowText.includes("TOTAL MONTHLY SALES")) {
                Object.values(data.row.cells).forEach(cell => {
                    cell.styles.fillColor = [255, 240, 178]; // Apply soft yellow
                    cell.styles.fontStyle = "bold"; // Bold text
                });
            }

            // ✅ REMOVE yellow rows that have no values (only apply to product rows)
            if (
                !rowText.includes("TOTAL WEEKLY SALES") &&
                !rowText.includes("TOTAL MONTHLY SALES") &&
                rowText.includes("-") // If the row contains only "-" (empty values)
            ) {
                data.row.hidden = true; // Hide empty yellow rows
            }
        }
    }
    });


    // ✅ Save PDF
    doc.save(`sales_report_${reportType.replace(/\s+/g, "_").toLowerCase()}.pdf`);
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

    console.log("📊 Debugging Yearly Sales Data:", yearlySalesData); // ✅ Debugging Output

    if (!yearlySalesData || yearlySalesData.length === 0) {
        console.warn("⚠️ No yearly sales data available.");
        return;
    }

    let years = yearlySalesData.map(entry => entry.year);
    let sales = yearlySalesData.map(entry => entry.sales);

    let ctx = document.getElementById("yearlySalesChart").getContext("2d");

    // ✅ Destroy previous instance if it exists
    if (window.yearlySalesChart && typeof window.yearlySalesChart.destroy === "function") {
        window.yearlySalesChart.destroy();
    }

    // ✅ Initialize the new chart
    window.yearlySalesChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: years,
            datasets: [{
                label: "Total Sales Per Year",
                data: sales,
                backgroundColor: "rgba(54, 162, 235, 0.2)", // Light blue background fill
                borderColor: "#007BFF", // Blue line color
                borderWidth: 2, // Thicker line
                tension: 0.3, // Smooth curves
                pointRadius: 6, // Bigger data points
                pointBackgroundColor: "#007BFF", // Point color
                pointBorderColor: "#fff",
                pointHoverRadius: 8 // Increase on hover
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false // Hide vertical grid lines
                    },
                    ticks: {
                        font: {
                            size: 14,
                            weight: "bold"
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0, 0, 0, 0.1)" // Light grid lines
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        callback: function(value) {
                            return "₱" + value.toLocaleString(); // Format with currency
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
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