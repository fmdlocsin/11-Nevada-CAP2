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

    // Franchise Name Mapping (Same as PHP)
    const franchiseNameMap = {
        "auntie anne": "Auntie Anne's",
        "macao imperial": "Macao Imperial",
        "potato corner": "Potato Corner"
    };

    // Function to apply name formatting
    function formatFranchiseName(name) {
        let formattedName = name.toLowerCase().replace(/-/g, " ").trim();
        return franchiseNameMap[formattedName] || name;
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

         // Apply name formatting
        let formattedDurationLabels = durationPerFranchiseData.map(item => formatFranchiseName(item.franchise));

        new Chart(ctxDurationFranchise, {
            type: "bar",
            data: {
                labels: formattedDurationLabels, // Use formatted labels
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
        let ctxFranchiseContracts = document.getElementById("activeContractsChart").getContext("2d");
    
        // Apply name formatting
        let formattedLabels = franchiseNames.map(formatFranchiseName);
    
        new Chart(ctxFranchiseContracts, {
            type: "pie",
            data: {
                labels: formattedLabels, // Use formatted labels
                datasets: [{
                    data: totalContractsPerFranchise,
                    backgroundColor: ["#36A2EB", "#FF6384", "#FFCE56", "#4BC0C0"],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                layout: {
                    padding: { top: 5, bottom: 5, left: 5, right: 5 }
                },
                plugins: {
                    legend: {
                        position: "right",
                        align: "center",
                        labels: {
                            font: { size: 14 },
                            padding: 15
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

        // Apply name formatting
        let formattedLeasingLabels = leasingFranchiseNames.map(formatFranchiseName);
    
        new Chart(ctxLeasingContracts, {
            type: "pie",
            data: {
                labels: formattedLeasingLabels,
                datasets: [{
                    data: totalLeasesPerFranchise, // Use total leases instead of active only
                    backgroundColor: ["#FF9F40", "#9966FF", "#4BC0C0", "#FF6384"],
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


// -------------------------------- CONTRACT REPORTS -------------------------------- 


// AGREEMENT CONTRACT
$(document).ready(function () {
    // Function to fetch and update the franchise report
    function fetchFranchiseReport() {
        let selectedFranchisee = $("#franchiseeFilter").val(); // Get selected franchisee
        
        $.ajax({
            url: "phpscripts/fetch-franchise-report.php",
            method: "GET",
            data: { franchisee: selectedFranchisee }, // Send filter data
            dataType: "json",
            success: function (data) {
                console.log("Filtered Data received:", data); // Debugging

                let reportContent = $("#franchiseReportContent");
                let summaryContent = $("#franchiseSummary");
                reportContent.html(""); // Clear existing content
                summaryContent.html(""); // Clear existing summary

                if (data.length === 0) {
                    reportContent.html(`<p class="text-center">No data available for the selected franchisee.</p>`);
                    return;
                }

                let franchiseData = {
                    "auntie-anne": { name: "Auntie Anne's", logo: "AuntieAnn.png" },
                    "macao-imperial": { name: "Macao Imperial", logo: "MacaoImp.png" },
                    "potato-corner": { name: "Potato Corner", logo: "PotCor.png" }
                };

                let tables = {};
                let contractSummary = {};

                data.forEach(row => {
                    let franchiseeKey = row.franchisor.toLowerCase().replace(/\s+/g, '-');
                    let franchisee = franchiseData[franchiseeKey] ? franchiseData[franchiseeKey].name : row.franchisor;
                    let logoPath = franchiseData[franchiseeKey] ? `assets/images/${franchiseData[franchiseeKey].logo}` : "assets/images/default.png";

                    if (!contractSummary[franchisee]) {
                        contractSummary[franchisee] = {
                            logo: logoPath,
                            activeContracts: 0,
                            expiringContracts: 0,
                            expiredContracts: 0,
                            totalContracts: 0,
                            renewalRate: 0,
                            branches: []
                        };
                    }

                    let isExpired = new Date(row.expiration_date) < new Date();
                    let isActive = row.remarks.toLowerCase() === "active" && !isExpired;

                    if (isActive) {
                        contractSummary[franchisee].activeContracts += 1;
                    }

                    contractSummary[franchisee].expiringContracts += parseInt(row.expiring_contracts) || 0;
                    contractSummary[franchisee].expiredContracts += isExpired ? 1 : 0;
                    contractSummary[franchisee].totalContracts++;

                    let total = contractSummary[franchisee].activeContracts + contractSummary[franchisee].expiredContracts;
                    contractSummary[franchisee].renewalRate = total > 0 ? ((contractSummary[franchisee].activeContracts / total) * 100).toFixed(2) : "0";

                    contractSummary[franchisee].branches.push({
                        location: row.location,
                        classification: row.classification || "N/A",
                        startDate: formatDate(row.start_date),
                        expirationDate: formatDate(row.expiration_date),
                        remarks: isExpired ? "Expired" : "Active"
                    });
                });

                for (let franchisee in contractSummary) {
                    let summary = contractSummary[franchisee];

                    tables[franchisee] = `
                        <div class="franchise-section">
                            <h3 class="franchise-title">
                                <img src="${summary.logo}" alt="${franchisee}" class="franchise-logo">
                                ${franchisee} (Total Contracts: ${summary.totalContracts})
                            </h3>

                            <div class="contract-summary">
                                <div class="summary-item"><strong>Active Contracts:</strong> <span style="color: green; font-weight: bold;">${summary.activeContracts}</span></div>
                                <div class="summary-item"><strong>Expiring Next Month:</strong> <span style="color: orange; font-weight: bold;">${summary.expiringContracts}</span></div>
                                <div class="summary-item"><strong>Expired Contracts:</strong> <span style="color: red; font-weight: bold;">${summary.expiredContracts}</span></div>
                                <div class="summary-item"><strong>Renewal Rate:</strong> <span style="color: blue; font-weight: bold;">${summary.renewalRate}%</span></div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped report-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Location</th>
                                            <th>Classification</th>
                                            <th>Start Date</th>
                                            <th>Expiration Date</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    summary.branches.forEach(branch => {
                        tables[franchisee] += `
                            <tr>
                                <td>${branch.location}</td>
                                <td>${branch.classification}</td>
                                <td>${branch.startDate}</td>
                                <td>${branch.expirationDate}</td>
                                <td class="text-center">${branch.remarks}</td>
                            </tr>
                        `;
                    });

                    tables[franchisee] += `</tbody></table></div></div><br>`;
                    reportContent.append(tables[franchisee]);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", xhr.responseText);
                $("#franchiseReportContent").html(`<p>Error loading data</p>`);
            }
        });
    }

    // **Trigger fetch when the "Generate Report" button is clicked**
    $("#generateFranchiseReport").click(function () {
        fetchFranchiseReport();
    });

    // **Trigger fetch when the franchisee filter is changed**
    $("#franchiseeFilter").change(function () {
        fetchFranchiseReport();
    });
});


// LEASING REPORT
// LEASING REPORT
$(document).ready(function () {
    function fetchLeasingReport() {
        let selectedFranchisee = $("#leasingFranchiseeFilter").val(); // Get selected franchisee filter

        $.ajax({
            url: "phpscripts/fetch-leasing-report.php",
            method: "GET",
            data: { franchisee: selectedFranchisee }, // Send filter data
            dataType: "json",
            success: function (data) {
                console.log("Filtered Leasing Data received:", data); // Debugging

                let reportContent = $("#leasingReportContent");
                let summaryContent = $("#leasingSummary");
                reportContent.html(""); // Clear existing content
                summaryContent.html(""); // Clear existing summary

                if (data.length === 0) {
                    reportContent.html(`<p class="text-center">No leasing data available for the selected franchisee.</p>`);
                    return;
                }

                // ✅ Franchise Name & Logo Mapping
                const franchiseData = {
                    "potato-corner": { name: "Potato Corner", logo: "PotCor.png" },
                    "auntie-anne": { name: "Auntie Anne's", logo: "AuntieAnn.png" },
                    "macao-imperial": { name: "Macao Imperial", logo: "MacaoImp.png" }
                };

                let tables = {}; // Store separate tables for each franchisee
                let leaseCounts = {}; // Store total leases per franchisee

                let totalActiveLeases = 0;
                let totalOccupancyRate = 0;
                let nextExpiringLease = null;
                let nextExpiringLeaseDetails = {}; // Store franchisee and location for next expiring lease

                // ✅ Calculate summary data & lease counts
                data.forEach(row => {
                    let franchiseKey = row.franchisor.toLowerCase().replace(/\s+/g, '-');
                    let franchisee = franchiseData[franchiseKey] ? franchiseData[franchiseKey].name : row.franchisor;
                    let logoPath = franchiseData[franchiseKey] ? `assets/images/${franchiseData[franchiseKey].logo}` : "assets/images/default.png";

                    if (!leaseCounts[franchisee]) {
                        leaseCounts[franchisee] = {
                            logo: logoPath, 
                            activeLeases: 0,
                            expiringLeases: 0,
                            expiredLeases: 0
                        };
                    }

                    leaseCounts[franchisee].activeLeases += parseInt(row.active_leases) || 0;
                    leaseCounts[franchisee].expiringLeases += parseInt(row.expiring_leases) || 0;
                    leaseCounts[franchisee].expiredLeases += parseInt(row.expired_leases) || 0;

                    totalActiveLeases += parseInt(row.active_leases) || 0;
                    let totalLeases = parseInt(row.active_leases) + parseInt(row.expired_leases);
                    totalOccupancyRate += totalLeases > 0 ? (parseInt(row.active_leases) / totalLeases) * 100 : 0;

                    let leaseExpirationDate = new Date(row.expiration_date);
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

                // ✅ Display Leasing Summary
                let leasingSummaryHTML = `
                    <div class="alert alert-info">
                        <p><strong>Total Active Leases:</strong> <span style="color: green; font-weight: bold;">${totalActiveLeases}</span></p>
                        <p><strong>Overall Occupancy Rate:</strong> <span style="color: blue; font-weight: bold;">${overallOccupancyRate}</span></p>
                        <p><strong>Next Expiring Lease:</strong> <span style="color: orange; font-weight: bold;">${nextExpiringLeaseFormatted}</span></p>
                    </div>`;
                summaryContent.html(leasingSummaryHTML);

                // ✅ Generate tables
                for (let franchisee in leaseCounts) {
                    let summary = leaseCounts[franchisee];

                    let totalLeases = summary.activeLeases + summary.expiredLeases;
                    let occupancyRate = totalLeases > 0 ? ((summary.activeLeases / totalLeases) * 100).toFixed(2) + "%" : "0%";

                    tables[franchisee] = `
                        <div class="franchise-section">
                            <h3 class="franchise-title">
                                <img src="${summary.logo}" alt="${franchisee}" class="franchise-logo">
                                ${franchisee} (Total Leases: ${summary.activeLeases})
                            </h3>

                            <div class="contract-summary">
                                <div class="summary-item"><strong>Active Leases:</strong> <span style="color: green; font-weight: bold;">${summary.activeLeases}</span></div>
                                <div class="summary-item"><strong>Expiring Next Month:</strong> <span style="color: orange; font-weight: bold;">${summary.expiringLeases}</span></div>
                                <div class="summary-item"><strong>Expired Leases:</strong> <span style="color: red; font-weight: bold;">${summary.expiredLeases}</span></div>
                                <div class="summary-item"><strong>Occupancy Rate:</strong> <span style="color: blue; font-weight: bold;">${occupancyRate}</span></div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped report-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Lessor Name</th>
                                            <th>Classification</th>
                                            <th>Area</th> 
                                            <th>Location</th>
                                            <th>Start Date</th>
                                            <th>Expiration Date</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                }

                // ✅ Populate Leasing Table Rows
                data.forEach(row => {
                    let franchiseKey = row.franchisor.toLowerCase().replace(/\s+/g, '-');
                    let franchisee = franchiseData[franchiseKey] ? franchiseData[franchiseKey].name : row.franchisor;

                    let expirationDate = new Date(row.expiration_date);
                    let today = new Date();
                    let leaseRemark = expirationDate < today ? "Expired" : "Active Lease";

                    tables[franchisee] += `
                        <tr>
                            <td>${row.lessor_name || "N/A"}</td>
                            <td>${row.classification || "N/A"}</td>
                            <td>${row.area ? row.area + " sqm" : "N/A"}</td>
                            <td>${row.location}</td>
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
    }

    // ✅ Trigger fetch when the "Generate Report" button is clicked
    $("#generateLeasingReport").click(fetchLeasingReport);

    // ✅ Trigger fetch when the franchisee filter is changed
    $("#leasingFranchiseeFilter").change(fetchLeasingReport);
});




// Export Leasing Contracts to PDF (Fixed Summary Box and Spacing)
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
    doc.text(`Date Generated: ${currentDate}`, pageWidth - 150, 70); // Right-aligned date

    let startY = 100; // Starting Y position for content

    // ✅ Extract Overall Summary
    let overallSummaryDiv = document.querySelector("#leasingSummary");
    if (overallSummaryDiv) {
        let summaryText = overallSummaryDiv.innerText.trim().split("\n");
        let summaryHeight = 20 + summaryText.length * 14; // ✅ Adjust height dynamically

        // ✅ Add Gray Background to Summary with Proper Height
        doc.setFillColor(230, 230, 230); // Light gray background
        doc.roundedRect(40, startY, pageWidth - 80, summaryHeight, 5, 5, "F"); // Rounded background

        doc.setFont("helvetica", "bold");
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.text("Overall Summary:", 50, startY + 20);

        doc.setFont("helvetica", "normal");
        doc.setFontSize(10);
        doc.setTextColor(50, 50, 50); // Dark gray text

        let textY = startY + 40;
        summaryText.forEach(line => {
            doc.text(line, 60, textY);
            textY += 10;
        });

        startY += summaryHeight + 40; // ✅ Properly spaced below the summary
    }

    // ✅ Restrict table selection to the Leasing modal only
    let tables = document.querySelectorAll("#leasingReportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Leasing report table not found.");
        return;
    }

    tables.forEach((table, index) => {
        let franchiseSection = table.closest(".franchise-section");
        let franchiseTitle = franchiseSection.querySelector(".franchise-title")?.innerText || "Unknown Franchise";

        // ✅ Add Franchise Title
        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(0, 0, 0);
        doc.text(franchiseTitle, 50, startY);
        startY += 20;

        // ✅ Extract Franchise Summary
        let summaryDiv = franchiseSection.querySelector(".contract-summary");
        if (summaryDiv) {
            let summaryText = summaryDiv.innerText.trim().split("\n").join("  |  "); // Format summary
            doc.setFont("helvetica", "italic");
            doc.setFontSize(10);
            doc.setTextColor(80, 80, 80);
            doc.text(summaryText, 60, startY);
            startY += 20; // ✅ Space before table
        }

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

        // ✅ Generate Table in PDF with Improved Spacing
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

        startY = doc.lastAutoTable.finalY + 30; // ✅ Extra spacing between tables
    });

    // ✅ Save PDF
    doc.save(`leasing_report_${new Date().toISOString().split("T")[0]}.pdf`);
}

// ✅ Bind to Button
$(document).ready(function () {
    $("#exportLeasingPDF").click(exportLeasingTableToPDF);
});


// Export Leasing Contracts to CSV (Includes Overall Summary)
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

    // ✅ Extract Overall Summary
    let overallSummaryDiv = document.querySelector("#leasingSummary");
    if (overallSummaryDiv) {
        csv.push(`"Overall Summary"`);
        csv.push(""); // Space below title

        let summaryLines = overallSummaryDiv.innerText.trim().split("\n");
        summaryLines.forEach(line => {
            csv.push(`"${line.trim()}"`);
        });

        csv.push(""); // Space after summary
    }

    // ✅ Extract Franchisee Data
    tables.forEach(table => {
        let franchiseTitle = table.closest(".franchise-section").querySelector(".franchise-title")?.innerText || "Unknown Franchise";
        
        csv.push(`"${franchiseTitle}"`);
        
        // ✅ Extract Franchise Summary
        let summaryDiv = table.closest(".franchise-section").querySelector(".contract-summary");
        if (summaryDiv) {
            let summaryText = summaryDiv.innerText.trim().split("\n").join(" | ");
            csv.push(`"${summaryText}"`);
        }

        csv.push(""); // Space before table

        // ✅ Extract Table Data
        let rows = table.querySelectorAll("tr");
        rows.forEach((row, index) => {
            let cols = row.querySelectorAll("th, td");
            let rowData = [];

            cols.forEach(col => {
                rowData.push(`"${col.innerText}"`);
            });

            csv.push(rowData.join(",")); // Add formatted row to CSV
        });

        csv.push(""); // Space between franchise tables
    });

    // ✅ Convert CSV Data to Downloadable File
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



// ✅ Function to Format Dates
function formatDate(dateString) {
    if (!dateString || dateString === "Invalid Date") return "N/A"; // Handle invalid dates

    let date = new Date(dateString);
    if (isNaN(date.getTime())) return "Invalid Date"; // Ensure date is valid

    let options = { year: 'numeric', month: 'long', day: 'numeric' }; // Format: Month Day, Year
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

    let startY = 130;

    // ✅ Restrict table selection to the Franchise Agreement modal only
    let tables = document.querySelectorAll("#franchiseReportModal .franchise-section .report-table");

    if (!tables.length) {
        console.error("❌ Error: No tables found!");
        alert("Error: Franchise report table not found.");
        return;
    }

    tables.forEach((table, index) => {
        let franchiseSection = table.closest(".franchise-section");
        let franchiseTitle = franchiseSection.querySelector(".franchise-title")?.innerText || "Unknown Franchise";

        // ✅ Add Franchise Title
        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.text(franchiseTitle, 50, startY);
        startY += 20;

        // ✅ Extract Franchise Summary
        let summaryDiv = franchiseSection.querySelector(".contract-summary");
        if (summaryDiv) {
            let summaryText = summaryDiv.innerText.trim().split("\n").join("  |  "); // Format summary
            doc.setFont("helvetica", "italic");
            doc.setFontSize(10);
            doc.text(summaryText, 50, startY);
            startY += 20;
        }

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

        startY = doc.lastAutoTable.finalY + 50; // Space between franchise tables
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
