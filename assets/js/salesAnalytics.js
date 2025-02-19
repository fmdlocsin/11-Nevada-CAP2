document.addEventListener("DOMContentLoaded", function () {
    loadFranchiseeButtons(); // Load franchisee buttons on page load
    fetchKPIData(); // Load initial KPI data
});

// 🎯 Load Franchisee Filter Buttons
function loadFranchiseeButtons() {
    fetch("dashboard-sales.php?json=true")
        .then(response => response.json())
        .then(data => {
            let franchiseeButtonsDiv = document.getElementById("franchiseeButtons");
            franchiseeButtonsDiv.innerHTML = ""; // Clear existing buttons

            data.franchisees.forEach(franchisee => {
                let button = document.createElement("button");
                button.classList.add("btn", "btn-outline-primary", "m-2", "franchisee-btn");
                button.innerText = franchisee.franchisee;
                button.dataset.value = franchisee.franchisee;
                button.addEventListener("click", toggleFranchiseeSelection);
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
    if (selectedFranchisees.length === 0) {
        document.getElementById("branchButtons").style.display = "none";
        return;
    }

    fetch(`dashboard-sales.php?json=true&franchisees=${selectedFranchisees.join(",")}`)
        .then(response => response.json())
        .then(data => {
            console.log("🔍 JSON Response for Branches:", data);

            let branchButtonsDiv = document.getElementById("branchButtons");
            branchButtonsDiv.innerHTML = "";
            branchButtonsDiv.style.display = "block";

            // ✅ Check if branches exist and are an array
            if (!data.branches || !Array.isArray(data.branches)) {
                console.warn("⚠️ No valid branches returned!");
                return;
            }

            data.branches.forEach(branch => {
                if (!branch.branch) {
                    console.warn("⚠️ Invalid branch data:", branch);
                    return;
                }

                let button = document.createElement("button");
                button.classList.add("btn", "btn-outline-secondary", "m-2", "branch-btn");
                button.innerText = branch.branch;  // ✅ Ensure correct property
                button.dataset.value = branch.branch;
                button.addEventListener("click", toggleBranchSelection);
                branchButtonsDiv.appendChild(button);
            });
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
function fetchKPIData() {
    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);
    let selectedBranches = Array.from(document.querySelectorAll(".branch-btn.btn-selected"))
        .map(btn => btn.dataset.value);

    let url = "dashboard-sales.php?json=true";

    // ✅ Only add filters if selections exist
    if (selectedFranchisees.length > 0) {
        url += `&franchisees=${selectedFranchisees.join(",")}`;
    }
    if (selectedBranches.length > 0) {
        url += `&branches=${selectedBranches.join(",")}`;
    }

    fetch(url)
    .then(response => response.json())
    .then(data => {
        console.log("JSON Response for KPI Data:", data);
        console.log("Best-Selling Products Data:", data.bestSelling);
        console.log("Worst-Selling Products Data:", data.worstSelling);

        let totalSales = parseFloat(data.totalSales) || 0;
        let totalExpenses = parseFloat(data.totalExpenses) || 0;
        let profit = totalSales - totalExpenses;

        document.getElementById("totalSales").innerText = totalSales.toLocaleString();
        document.getElementById("totalExpenses").innerText = totalExpenses.toLocaleString();
        document.getElementById("profit").innerText = profit.toLocaleString();

        updateSalesCharts(data);
        updateBestSellingChart(data.bestSelling); // ✅ NEW
        updateWorstSellingChart(data.worstSelling); // ✅ NEW
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

// 🎯 Update Best-Selling Products Chart
function updateBestSellingChart(bestSellingData) {
    console.log("🟢 Best-Selling Chart Data Received:", bestSellingData);

    if (!bestSellingData || bestSellingData.length === 0) {
        console.warn("⚠️ No data available for Best-Selling Products chart.");
        return;
    }

    let productNames = bestSellingData.map(item => item.product);
    let productSales = bestSellingData.map(item => item.sales);

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
                backgroundColor: "#4CAF50",
                borderColor: "#388E3C",
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
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => value.toLocaleString() }
                }
            }
        },
        plugins: [ChartDataLabels]
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
                backgroundColor: "#F44336",
                borderColor: "#D32F2F",
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
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => value.toLocaleString() }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

