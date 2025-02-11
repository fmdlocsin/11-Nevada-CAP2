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
};
