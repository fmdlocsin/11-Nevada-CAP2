document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("generateReportButton").addEventListener("click", generateReport);
});


// 🎯 Fetch and Generate Sales Reports
function generateReport() {
    let reportType = document.getElementById("reportType").value;

    console.log("🟢 Generating Report for:", reportType); // ✅ Debugging

    fetch(`dashboard-sales.php?json=true&reportType=${reportType}`)
        .then(response => response.json())
        .then(data => {
            console.log("📊 Report Data Received:", data);
            displayReport(data.branchSales, data.consolidatedSales);
        })
        .catch(error => console.error("❌ Error fetching report data:", error));
}


// 🎯 Display Report in the UI
function displayReport(branchSales, consolidatedSales) {
    console.log("📝 Displaying Report Data...");

    let reportContainer = document.getElementById("reportContainer");
    reportContainer.innerHTML = ""; // ✅ Clear previous report

    // ✅ Convert `branchSales` from object to array
    let branchSalesArray = [];
    if (branchSales && typeof branchSales === "object") {
        Object.keys(branchSales).forEach(franchise => {
            branchSales[franchise].forEach(sale => {
                branchSalesArray.push({
                    franchisee: franchise,
                    sales_date: sale.sales_date ? new Date(sale.sales_date).toLocaleDateString() : "N/A", // ✅ Fixes Null Dates
                    location: sale.location,
                    total_sales: sale.sales ? parseFloat(sale.sales).toLocaleString() : "N/A"
                });                
            });
        });
    }

    // ✅ Ensure consolidatedSales is an array
    consolidatedSales = Array.isArray(consolidatedSales) ? consolidatedSales : [];

    // 🚨 Check if both reports are empty
    if (branchSalesArray.length === 0 && consolidatedSales.length === 0) {
        reportContainer.innerHTML = "<p class='text-warning'>⚠️ No data found for the selected report type.</p>";
        return;
    }

    let reportHtml = ""; // ✅ Combine both reports into a single update

    // 🔹 Display Branch Sales Report
    if (branchSalesArray.length > 0) {
        reportHtml += "<h3>Sales Per Branch</h3><table class='table table-striped'><thead><tr><th>Date</th><th>Franchise</th><th>Branch</th><th>Sales</th></tr></thead><tbody>";
        branchSalesArray.forEach(item => {
            reportHtml += `<tr>
                                <td>${item.sales_date}</td>
                                <td>${item.franchisee}</td>
                                <td>${item.location}</td>
                                <td>${item.total_sales}</td>
                           </tr>`;
        });
        reportHtml += "</tbody></table>";
    }

    // 🔹 Display Consolidated Report
    if (consolidatedSales.length > 0) {
        reportHtml += "<h3>Consolidated Franchise Sales</h3><table class='table table-striped'><thead><tr><th>Franchise</th><th>Total Sales</th></tr></thead><tbody>";
        consolidatedSales.forEach(item => {
            reportHtml += `<tr>
                              <td>${item.franchisee}</td>
                              <td>${parseFloat(item.total_sales).toLocaleString()}</td>
                           </tr>`;
        });
        reportHtml += "</tbody></table>";
    }

    // ✅ Finally, update the UI
    reportContainer.innerHTML = reportHtml;
}





