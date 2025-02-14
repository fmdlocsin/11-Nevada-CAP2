window.onload = function () {
    console.log("Chart script loaded successfully!");

    // ==================== Expired Contracts Over Time (Line Chart) ====================
    if (!expiredContractsData || expiredContractsData.length === 0) {
        console.error("No expired contracts data available.");
    } else {
        let chartCanvas = document.getElementById("contractRenewalChart");
        if (chartCanvas) {
            let ctx = chartCanvas.getContext("2d");
            let labels = expiredContractsData.map(entry => entry.month);
            let expiredCounts = expiredContractsData.map(entry => entry.count);

            let gradientExpired = ctx.createLinearGradient(0, 0, 0, 400);
            gradientExpired.addColorStop(0, "#FF6A88");
            gradientExpired.addColorStop(1, "#FF9472");

            new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Expired Contracts Over Time",
                        data: expiredCounts,
                        borderColor: "#FF6A88",
                        backgroundColor: gradientExpired,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            backgroundColor: "#1F375D",
                            titleFont: { weight: "bold" },
                            bodyFont: { size: 14 },
                            bodyColor: "#fff",
                            cornerRadius: 5
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { size: 14 },
                                color: "#1F375D"
                            },
                            grid: {
                                color: "rgba(0,0,0,0.1)"
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 14 },
                                color: "#1F375D"
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        } else {
            console.error("Chart canvas for expired contracts not found.");
        }
    }

    // ==================== Contract Duration Over Time (Line Chart) ====================
    if (typeof contractDurationTrendData !== "undefined" && contractDurationTrendData.length > 0) {
        let ctxDurationTrend = document.getElementById("contractDurationTrendChart").getContext("2d");
        let labelsTrend = contractDurationTrendData.map(item => item.month);
        let dataTrend = contractDurationTrendData.map(item => item.duration);

        new Chart(ctxDurationTrend, {
            type: "line",
            data: {
                labels: labelsTrend,
                datasets: [{
                    label: "Average Contract Duration (Months)",
                    data: dataTrend,
                    borderColor: "#36a2eb",
                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    } else {
        console.error("No contract duration trend data available.");
    }

    // ==================== Contract Duration Per Franchise (Bar Chart) ====================
    if (typeof durationPerFranchiseData !== "undefined" && durationPerFranchiseData.length > 0) {
        let ctxDurationFranchise = document.getElementById("contractDurationPerFranchiseChart").getContext("2d");
        let labelsFranchise = durationPerFranchiseData.map(item => item.franchise);
        let dataFranchise = durationPerFranchiseData.map(item => item.duration);

        new Chart(ctxDurationFranchise, {
            type: "bar",
            data: {
                labels: labelsFranchise,
                datasets: [{
                    label: "Avg. Contract Duration (Months)",
                    data: dataFranchise,
                    backgroundColor: "#ff6384",
                    borderColor: "#ff6384",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    } else {
        console.error("No contract duration per franchise data available.");
    }

    // ==================== Active Contracts Pie Chart ====================
    if (typeof franchiseNames !== "undefined" && franchiseNames.length > 0) {
        let ctxActiveContracts = document.getElementById("activeContractsChart").getContext("2d");

        new Chart(ctxActiveContracts, {
            type: "pie",
            data: {
                labels: franchiseNames,
                datasets: [{
                    data: activeContracts,
                    backgroundColor: ["#36A2EB", "#FF6384", "#FFCE56"],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                layout: {
                    padding: {
                        top: 5, // Reduce padding above chart
                        bottom: 5, // Reduce space below chart
                        left: 5,
                        right: 5
                    }
                },
                plugins: {
                    legend: {
                        position: "right", // Move legend below chart
                        align: "center", // Align legend vertically centered
                        labels: {
                            font: {
                                size: 14 // Reduce font size to prevent overlap
                            },
                            padding: 15 // Adjust padding to avoid overlap
                        }
                    }
                }
            }
        });
    } else {
        console.error("No active contracts data available.");
    }

    // ==================== Leasing Contracts Pie Chart ====================
    if (typeof leasingFranchiseNames !== "undefined" && leasingFranchiseNames.length > 0) {
        let ctxLeasingContracts = document.getElementById("leasingContractsChart").getContext("2d");

        new Chart(ctxLeasingContracts, {
            type: "pie",
            data: {
                labels: leasingFranchiseNames,
                datasets: [{
                    data: activeLeases,
                    backgroundColor: ["#4BC0C0", "#9966FF", "#FF9F40"],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5,
                        left: 5,
                        right: 5
                    }
                },
                plugins: {
                    legend: {
                        position: "right",
                        align: "center",
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 15
                        }
                    }
                }
            }
        });
    } else {
        console.error("No leasing contracts data available.");
    }

};
