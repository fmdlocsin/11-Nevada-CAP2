// Sales Performance per Franchise
document.addEventListener("DOMContentLoaded", function () {
    var franchiseDataElement = document.querySelector("#franchiseSalesData");

    if (!franchiseDataElement) {
        console.error("Error: #franchiseSalesData element not found.");
        return;
    }

    var franchiseSalesData = JSON.parse(franchiseDataElement.textContent.trim());

    if (!franchiseSalesData || franchiseSalesData.length === 0) {
        console.warn("No data available for franchise sales chart.");
        return;
    }

    // Franchise color mapping
    var franchiseColors = {
        "Potato Corner": "#2E7D32", // Green
        "Auntie Anne's": "#1565C0", // Blue
        "Macao Imperial": "#B71C1C" // Red
    };

    var franchiseNames = franchiseSalesData.map(item => item.franchise);
    var franchiseSales = franchiseSalesData.map(item => item.sales);
    
    // Assign colors based on franchise names
    var colors = franchiseNames.map(name => franchiseColors[name] || '#ffcc00'); // Default to yellow if not listed

    var ctx = document.getElementById('franchiseSalesChart').getContext('2d');

    // 🔴 Fix: Destroy the previous chart if it exists
    if (window.franchiseSalesChart instanceof Chart) {
        window.franchiseSalesChart.destroy();
    }

    window.franchiseSalesChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: franchiseNames,
            datasets: [{
                label: 'Sales per Franchise',
                data: franchiseSales,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            layout: {
                padding: 5
            },
            plugins: {
                legend: {
                    display: false
                },
                datalabels: {
                    color: 'white',
                    font: {
                        size: 14,
                        weight: 'bold'
                    },
                    anchor: 'center',
                    align: 'center',
                    formatter: function(value) {
                        return value.toLocaleString();
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // 🔹 Custom Legend (Manually Styled)
    var legendContainer = document.getElementById("franchiseLegend");
    legendContainer.innerHTML = ""; // Clear previous content

    franchiseNames.forEach((name, index) => {
        var legendItem = document.createElement("div");
        legendItem.innerHTML = `<span style="background-color: ${colors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> ${name}`;
        legendContainer.appendChild(legendItem);
    });
});



// Sales Performance by Franchise & Branch
document.addEventListener("DOMContentLoaded", function () {
    var franchiseDataElement = document.querySelector("#franchiseBranchSalesData");

    if (!franchiseDataElement) {
        console.error("Error: #franchiseBranchSalesData element not found.");
        return;
    }

    var rawData = franchiseDataElement.textContent.trim();
    if (!rawData) {
        console.error("Error: No data found inside #franchiseBranchSalesData.");
        return;
    }

    try {
        var franchiseBranchSalesData = JSON.parse(rawData);
    } catch (error) {
        console.error("Error parsing JSON:", error);
        return;
    }

    if (!franchiseBranchSalesData || Object.keys(franchiseBranchSalesData).length === 0) {
        console.warn("Warning: No data available for franchise branch sales chart.");
        return;
    }

    console.log("Final Parsed Franchise Branch Sales Data:", franchiseBranchSalesData);

    var franchiseCheckboxContainer = document.getElementById('franchiseCheckboxes');
    var ctx = document.getElementById('franchiseBranchChart').getContext('2d');
    var branchLegendContainer = document.getElementById('branchLegend');
    var currentChart;

    // Franchise Color Mapping (Shades)
    const franchiseColors = {
        "Potato Corner": ["#2E7D32", "#388E3C", "#43A047", "#4CAF50", "#66BB6A"], // Dark to Light Green
        "Auntie Anne's": ["#1565C0", "#1976D2", "#1E88E5", "#2196F3", "#42A5F5"], // Dark to Light Blue
        "Macao Imperial": ["#B71C1C", "#C62828", "#D32F2F", "#E53935", "#F44336"] // Dark to Light Red
    };

    // Function to get shades for a franchise
    function getColor(franchise, index) {
        if (franchiseColors[franchise]) {
            return franchiseColors[franchise][index % franchiseColors[franchise].length];
        }
        return "#BDBDBD"; // Default gray for unknown franchises
    }

    // Create checkboxes for each franchise
    Object.keys(franchiseBranchSalesData).forEach(franchise => {
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.value = franchise;
        checkbox.id = "checkbox-" + franchise;
        checkbox.checked = true; // Default all checked

        var label = document.createElement("label");
        label.htmlFor = "checkbox-" + franchise;
        label.textContent = franchise;

        franchiseCheckboxContainer.appendChild(checkbox);
        franchiseCheckboxContainer.appendChild(label);
        franchiseCheckboxContainer.appendChild(document.createElement("br"));
    });

    function updateChart() {
        var selectedFranchises = [];
        var checkboxes = document.querySelectorAll("#franchiseCheckboxes input[type='checkbox']:checked");

        checkboxes.forEach(checkbox => {
            selectedFranchises.push(checkbox.value);
        });

        var labels = [];
        var salesData = [];
        var colors = [];

        selectedFranchises.forEach(franchise => {
            if (franchiseBranchSalesData[franchise]) {
                franchiseBranchSalesData[franchise].forEach((branch, index) => {
                    labels.push(`${franchise} - ${branch.location}`);
                    salesData.push(branch.sales);
                    colors.push(getColor(franchise, index)); // Assign shades based on franchise
                });
            }
        });

        // Destroy previous chart instance
        if (currentChart instanceof Chart) {
            currentChart.destroy();
        }

        currentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales per Branch',
                    data: salesData,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Hide default legend
                    },
                    datalabels: {
                        color: 'white',
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        anchor: 'center',
                        align: 'center',
                        formatter: function(value) {
                            return value.toLocaleString(); // Format numbers
                        }
                    }
                }
            },
            plugins: [ChartDataLabels] // Enables the plugin
        });

        // Custom Legend
        branchLegendContainer.innerHTML = "";
        labels.forEach((label, index) => {
            var legendItem = document.createElement("div");
            legendItem.innerHTML = `<span style="background-color: ${colors[index]}; width: 15px; height: 15px; display: inline-block; margin-right: 8px; border-radius: 3px;"></span> ${label}`;
            branchLegendContainer.appendChild(legendItem);
        });
    }

    // Initial Chart Load
    updateChart();

    // Update Chart when any checkbox is clicked
    document.getElementById('franchiseCheckboxes').addEventListener("change", updateChart);
});



// Top 5 Best/Worst-Selling Products
document.addEventListener("DOMContentLoaded", function () {
    function createBarChart(chartId, data, title) {
        var ctx = document.getElementById(chartId).getContext('2d');

        var productNames = data.map(item => item.product);
        var productSales = data.map(item => item.sales);

        // Muted color palette (non-neon)
        var colors = [
            '#4E79A7', // Muted Blue
            '#F28E2B', // Warm Orange
            '#E15759', // Deep Red
            '#76B7B2', // Muted Teal
            '#59A14F'  // Forest Green
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [{
                    label: title,
                    data: productSales,
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.6', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 5
                },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: '#000',
                        font: { size: 12, weight: 'bold' },
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Load Best-Selling Products Data
    var bestSellingDataElement = document.querySelector("#bestSellingData");
    if (bestSellingDataElement) {
        var bestSellingData = JSON.parse(bestSellingDataElement.textContent.trim());
        createBarChart("bestSellingChart", bestSellingData, "Best-Selling Products");
    }

    // Load Worst-Selling Products Data
    var worstSellingDataElement = document.querySelector("#worstSellingData");
    if (worstSellingDataElement) {
        var worstSellingData = JSON.parse(worstSellingDataElement.textContent.trim());
        createBarChart("worstSellingChart", worstSellingData, "Worst-Selling Products");
    }
});







console.log("Raw Location Sales Data:", document.getElementById("locationSalesData").textContent);
console.log("Parsed Location Sales Data:", locationSalesData);





