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


// CONTRACT REPORTS
// leasing report
$(document).ready(function () {
    $("#generateLeasingReport").click(function () {
        $.ajax({
            url: "phpscripts/fetch-leasing-report.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
                console.log("Leasing Data received:", data); // Debugging

                let reportContent = $("#leasingReportContent");
                let summaryContent = $("#leasingSummary");
                reportContent.html(""); // Clear existing content
                summaryContent.html(""); // Clear existing summary

                if (data.length === 0) {
                    reportContent.html(`<p class="text-center">No leasing data available</p>`);
                    return;
                }

                // ✅ Franchise Name Mapping
                const franchiseNameMap = {
                    "potato-corner": "Potato Corner",
                    "auntie-anne": "Auntie Anne's",
                    "macao-imperial": "Macao Imperial",
                };

                let tables = {}; // Store separate tables for each franchisee
                let leaseCounts = {}; // Store total leases per franchisee

                let totalActiveLeases = 0;
                let totalOccupancyRate = 0;
                let nextExpiringLease = null;
                let nextExpiringLeaseDetails = {}; // Store franchisee and location for next expiring lease

                // ✅ First loop: Calculate summary data & lease counts
                data.forEach(row => {
                    let franchisee = franchiseNameMap[row.franchisor] || row.franchisor; // Map the franchise name

                    if (!leaseCounts[franchisee]) {
                        leaseCounts[franchisee] = 0;
                    }
                    leaseCounts[franchisee]++;

                    totalActiveLeases += parseInt(row.active_leases) || 0;
                    let totalLeases = parseInt(row.active_leases) + parseInt(row.expired_leases);
                    totalOccupancyRate += totalLeases > 0 ? (parseInt(row.active_leases) / totalLeases) * 100 : 0;

                    let leaseExpirationDate = new Date(row.expiration_date);
                    
                    // ✅ Track the nearest expiring lease
                    if (!nextExpiringLease || leaseExpirationDate < nextExpiringLease) {
                        nextExpiringLease = leaseExpirationDate;
                        nextExpiringLeaseDetails = {
                            franchisee: franchisee,
                            location: row.location,
                            expiration_date: row.expiration_date
                        };
                    }
                });

                // ✅ Compute overall occupancy rate
                let overallOccupancyRate = totalActiveLeases > 0 ? (totalOccupancyRate / data.length).toFixed(2) + "%" : "0%";
                let nextExpiringLeaseFormatted = nextExpiringLease 
                    ? `${formatDate(nextExpiringLeaseDetails.expiration_date)} (${nextExpiringLeaseDetails.franchisee} - ${nextExpiringLeaseDetails.location})`
                    : "N/A";

                // ✅ Update summary section
                let leasingSummaryHTML = `
                <div class="alert alert-info">
                    <p><strong>Total Active Leases:</strong> ${totalActiveLeases}</p>
                    <p><strong>Overall Occupancy Rate:</strong> ${overallOccupancyRate}</p>
                    <p><strong>Next Expiring Lease:</strong> ${nextExpiringLeaseFormatted}</p>
                </div>`;

                summaryContent.html(leasingSummaryHTML);

                // ✅ Second loop: Generate tables
                data.forEach(row => {
                    let franchisee = franchiseNameMap[row.franchisor] || row.franchisor; // Map the franchise name

                    if (!tables[franchisee]) {
                        tables[franchisee] = `
                            <div class="franchise-section">
                                <h3 class="franchise-title">${franchisee} (Total Active Leases: ${leaseCounts[franchisee]})</h3>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped report-table">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Location</th>
                                                <th class="text-center">Active Leases</th>
                                                <th class="text-center">Expiring Next Month</th>
                                                <th class="text-center">Expired Leases</th>
                                                <th class="text-center">Occupancy Rate (%)</th>
                                                <th>Start Date</th>
                                                <th>Expiration Date</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;
                    }

                    // Check if the lease is expired
                    let expirationDate = new Date(row.expiration_date);
                    let today = new Date();
                    let leaseRemark = expirationDate < today ? "Expired" : "Active Lease";

                    // Calculate Occupancy Rate
                    let totalLeases = parseInt(row.active_leases) + parseInt(row.expired_leases);
                    let occupancyRate = totalLeases > 0 ? ((parseInt(row.active_leases) / totalLeases) * 100).toFixed(2) + "%" : "0%";

                    tables[franchisee] += `
                        <tr>
                            <td>${row.location}</td>
                            <td class="text-center">${row.active_leases}</td>
                            <td class="text-center">${row.expiring_leases}</td>
                            <td class="text-center">${row.expired_leases}</td>
                            <td class="text-center">${occupancyRate}</td>
                            <td>${formatDate(row.start_date)}</td>
                            <td>${formatDate(row.expiration_date)}</td>
                            <td class="text-center">${leaseRemark}</td>
                        </tr>
                    `;
                });

                // ✅ Close tables and append them
                for (let franchisor in tables) {
                    tables[franchisor] += `</tbody></table></div></div><br>`;
                    reportContent.append(tables[franchisor]);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", xhr.responseText);
                $("#leasingReportContent").html(`<p>Error loading data</p>`);
            }
        });
    });
});


// Export Leasing Contracts to PDF
function exportLeasingTableToPDF() {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({
        orientation: "portrait",
        unit: "pt",
        format: "A4"
    });

    // ✅ Add Report Title
    doc.setFont("helvetica", "bold");
    doc.setFontSize(22);
    let pageWidth = doc.internal.pageSize.getWidth();
    doc.text("Leasing Contracts Report", pageWidth / 2, 50, { align: "center" });

    // ✅ Add Date of Report Generation
    let currentDate = new Date().toLocaleDateString();
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text(`Date Generated: ${currentDate}`, 400, 80);

    let startY = 100;

    // ✅ Restrict table selection to the Leasing modal only
    let tables = document.querySelectorAll("#leasingReportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Leasing report table not found.");
        return;
    }

    tables.forEach((table, index) => {
        let franchiseTitle = table.closest(".franchise-section").querySelector(".franchise-title")?.innerText || "Unknown Franchise";

        // ✅ Add Franchise Title
        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.text(franchiseTitle, 50, startY);
        startY += 20;

        // ✅ Extract Table Data
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

        // ✅ Generate Table in PDF
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
            columnStyles: { 0: { cellWidth: "auto" } }
        });

        startY = doc.lastAutoTable.finalY + 30; // Space between franchise tables
    });

    // ✅ Save PDF
    doc.save(`leasing_report_${new Date().toISOString().split("T")[0]}.pdf`);
}

// ✅ Bind to Button
$(document).ready(function () {
    $("#exportLeasingPDF").click(exportLeasingTableToPDF);
});


// Export Leasing Contracts to CSV
function exportLeasingTableToCSV() {
     // ✅ Restrict table selection to the Leasing modal only
     let tables = document.querySelectorAll("#leasingReportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Leasing report table not found.");
        return;
    }

    let csv = [];

    // ✅ Add Report Title
    csv.push(`"Leasing Contracts Report"`);
    csv.push(""); // Empty row for spacing

    tables.forEach(table => {
        let franchiseTitle = table.closest(".franchise-section").querySelector(".franchise-title")?.innerText || "Unknown Franchise";
        csv.push(`"${franchiseTitle}"`);
        csv.push(""); // Space before table

        let rows = table.querySelectorAll("tr");
        rows.forEach(row => {
            let cols = row.querySelectorAll("th, td");
            let rowData = [];

            cols.forEach(col => {
                rowData.push(`"${col.innerText}"`);
            });

            csv.push(rowData.join(",")); // Add formatted row to CSV
        });

        csv.push(""); // Space between franchise tables
    });

    let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `leasing_report_${new Date().toISOString().split("T")[0]}.csv`);
    document.body.appendChild(link);
    link.click();
}

// ✅ Bind to Button
$(document).ready(function () {
    $("#exportLeasingCSV").click(exportLeasingTableToCSV);
});



// AGREEMENT CONTRACT
$(document).ready(function () {
    $("#generateFranchiseReport").click(function () {
        $.ajax({
            url: "phpscripts/fetch-franchise-report.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
                console.log("Data received:", data); // Debugging

                let reportContent = $("#franchiseReportContent");
                reportContent.html(""); // Clear existing content

                if (data.length === 0) {
                    reportContent.html(`<p class="text-center">No data available</p>`);
                    return;
                }

                // ✅ Franchise Name Mapping
                const franchiseNameMap = {
                    "potato-corner": "Potato Corner",
                    "auntie-anne": "Auntie Anne's",
                    "macao-imperial": "Macao Imperial",
                };

                let tables = {}; // Store separate tables for each franchisee
                let contractCounts = {}; // Store total contracts per franchisee

                // ✅ First loop: Count total contracts per franchise
                data.forEach(row => {
                    let franchisee = franchiseNameMap[row.franchisor] || row.franchisor; // Map the franchise name

                    if (!contractCounts[franchisee]) {
                        contractCounts[franchisee] = 0;
                    }
                    contractCounts[franchisee]++;
                });

                // ✅ Second loop: Generate tables
                data.forEach(row => {
                    let franchisee = franchiseNameMap[row.franchisor] || row.franchisor; // Map the franchise name

                    if (!tables[franchisee]) {
                        tables[franchisee] = `
                            <div class="franchise-section">
                                <h3 class="franchise-title">${franchisee} (Total Contracts: ${contractCounts[franchisee]})</h3>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped report-table">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Location</th>
                                                <th class="text-center">Active Contracts</th>
                                                <th class="text-center">Expiring Next Month</th>
                                                <th class="text-center">Expired Contracts</th>
                                                <th class="text-center">Renewal Rate (%)</th>
                                                <th>Start Date</th>
                                                <th>Expiration Date</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;
                    }

                    // Check if the contract is expired
                    let expirationDate = new Date(row.expiration_date);
                    let today = new Date();
                    let contractRemark = expirationDate < today ? "Expired" : "Active";

                    
                    tables[franchisee] += `
                        <tr>
                            <td>${row.location}</td>
                            <td class="text-center">${row.active_contracts}</td>
                            <td class="text-center">${row.expiring_contracts}</td>
                            <td class="text-center">${row.expired_contracts}</td>
                            <td class="text-center">${row.renewal_rate}</td>
                            <td>${formatDate(row.start_date)}</td>
                            <td>${row.expiration_date ? formatDate(row.expiration_date) : "N/A"}</td>
                            <td class="text-center">${contractRemark}</td>
                        </tr>
                    `;
                });

                // ✅ Close tables and append them
                for (let franchisor in tables) {
                    tables[franchisor] += `</tbody></table></div></div><br>`;
                    reportContent.append(tables[franchisor]);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", xhr.responseText);
                $("#franchiseReportContent").html(`<p>Error loading data</p>`);
            }
        });
    });
});

function calculateRenewalRate(renewed, expired) {
    let total = renewed + expired;
    if (total === 0) return "0%"; // Avoid division by zero
    let rate = (renewed / total) * 100;
    return rate.toFixed(2) + "%"; // Return with two decimal places
}

function formatDate(dateString) {
    if (!dateString) return "N/A"; // Handle empty dates
    let date = new Date(dateString);
    let options = { year: 'numeric', month: 'long', day: 'numeric' }; // Format to "Month Day, Year"
    return date.toLocaleDateString('en-US', options);
}


// Export to PDF
function exportFranchiseTableToPDF() {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({
        orientation: "portrait",
        unit: "pt",
        format: "A4"
    });

    // ✅ Add Report Title
    doc.setFont("helvetica", "bold");
    doc.setFontSize(22);
    let pageWidth = doc.internal.pageSize.getWidth();
    doc.text("Franchisee Agreement Contracts Report", pageWidth / 2, 50, { align: "center" });

    // ✅ Add Date of Report Generation
    let currentDate = new Date().toLocaleDateString();
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text(`Date Generated: ${currentDate}`, 400, 80);

    let startY = 100;

    // ✅ Restrict table selection to the Franchise Agreement modal only
    let tables = document.querySelectorAll("#franchiseReportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Franchise report table not found.");
        return;
    }

    tables.forEach((table, index) => {
        let franchiseTitle = table.closest(".franchise-section").querySelector(".franchise-title")?.innerText || "Unknown Franchise";

        // ✅ Add Franchise Title
        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.text(franchiseTitle, 50, startY);
        startY += 20;

        // ✅ Extract Table Data
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

        // ✅ Generate Table in PDF
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
            columnStyles: { 0: { cellWidth: "auto" } }
        });

        startY = doc.lastAutoTable.finalY + 30; // Space between franchise tables
    });

    // ✅ Save PDF
    doc.save(`franchise_report_${new Date().toISOString().split("T")[0]}.pdf`);
}

// ✅ Bind to Button
$(document).ready(function () {
    $("#exportFranchisePDF").click(exportFranchiseTableToPDF);
});


// Export to CSV
function exportFranchiseTableToCSV() {
    let tables = document.querySelectorAll("#franchiseReportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Franchise report table not found.");
        return;
    }

    let csv = [];

    // ✅ Add Report Title
    csv.push(`"Franchisee Agreement Contracts Report"`);
    csv.push(""); // Empty row for spacing

    tables.forEach(table => {
        let franchiseTitle = table.closest(".franchise-section").querySelector(".franchise-title")?.innerText || "Unknown Franchise";
        csv.push(`"${franchiseTitle}"`);
        csv.push(""); // Space before table

        let rows = table.querySelectorAll("tr");
        rows.forEach(row => {
            let cols = row.querySelectorAll("th, td");
            let rowData = [];

            cols.forEach(col => {
                rowData.push(`"${col.innerText}"`);
            });

            csv.push(rowData.join(","));
        });

        csv.push(""); // Space between franchise tables
    });

    let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `franchise_report_${new Date().toISOString().split("T")[0]}.csv`);
    document.body.appendChild(link);
    link.click();
}

// ✅ Bind to Button
$(document).ready(function () {
    $("#exportFranchiseCSV").click(exportFranchiseTableToCSV);
});
