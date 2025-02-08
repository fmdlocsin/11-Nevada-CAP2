window.onload = function () {
    console.log("Chart script loaded successfully!");

    let expiringElem = document.getElementById("expiringContracts");
    let renewedElem = document.getElementById("renewedContracts");

    if (!expiringElem || !renewedElem) {
        console.error("Could not find elements for contract values.");
        return;
    }

    let expiringContracts = parseInt(expiringElem.textContent.trim()) || 0;
    let renewedContracts = parseInt(renewedElem.textContent.trim()) || 0;

    console.log("Expiring Contracts:", expiringContracts);
    console.log("Renewed Contracts:", renewedContracts);

    let chartCanvas = document.getElementById("contractRenewalChart");
    if (!chartCanvas) {
        console.error("Chart canvas not found.");
        return;
    }

    let ctx = chartCanvas.getContext("2d");

    // Create gradient effect
    let gradientExpiring = ctx.createLinearGradient(0, 0, 0, 400);
    gradientExpiring.addColorStop(0, "#FF6A88"); // Brighter red
    gradientExpiring.addColorStop(1, "#FF9472"); // Softer orange

    let gradientRenewed = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRenewed.addColorStop(0, "#56CCF2"); // Bright blue
    gradientRenewed.addColorStop(1, "#2F80ED"); // Darker blue

    // Generate Chart
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Expiring Contracts", "Renewed Contracts"],
            datasets: [{
                label: "Contract Status",
                data: [expiringContracts, renewedContracts],
                backgroundColor: [gradientExpiring, gradientRenewed],
                borderColor: ["#FF6A88", "#56CCF2"],
                borderWidth: 1,
                borderRadius: 10, // Rounded edges
                barThickness: 60 // Ensures bars are evenly spaced
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#1F375D", // Matches dashboard theme
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
                        color: "#1F375D" // Matches theme
                    },
                    grid: {
                        color: "rgba(0,0,0,0.1)" // Subtle grid
                    }
                },
                x: {
                    ticks: {
                        font: { size: 14 },
                        color: "#1F375D" // Matches theme
                    },
                    grid: { display: false }
                }
            }
        }
    });
};
