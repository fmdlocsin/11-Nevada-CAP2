window.onload = function () {
    console.log("Chart script loaded successfully!");

    if (!expiredContractsData || expiredContractsData.length === 0) {
        console.error("No expired contracts data available.");
        return;
    }

    let chartCanvas = document.getElementById("contractRenewalChart");
    if (!chartCanvas) {
        console.error("Chart canvas not found.");
        return;
    }

    let ctx = chartCanvas.getContext("2d");

    // Extract months and counts
    let labels = expiredContractsData.map(entry => entry.month);
    let expiredCounts = expiredContractsData.map(entry => entry.count);

    // Create gradient effect
    let gradientExpired = ctx.createLinearGradient(0, 0, 0, 400);
    gradientExpired.addColorStop(0, "#FF6A88");
    gradientExpired.addColorStop(1, "#FF9472");

    // Generate Expired Contracts Trend Chart
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
};
