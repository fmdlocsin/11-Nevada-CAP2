document.addEventListener("DOMContentLoaded", function () {
    loadFranchiseeButtons(); // Load franchisee buttons on page load
    fetchKPIData(); // Load KPI Data including turnover charts
    // loadTurnoverCharts();
});

function loadFranchiseeButtons() {
    fetch("dashboard-inventory.php?json=true") // ✅ Ensure JSON response
        .then(response => response.json())
        .then(data => {
            let franchiseeButtonsDiv = document.getElementById("franchiseeButtons");
            franchiseeButtonsDiv.innerHTML = ""; // Clear existing buttons

            data.franchisees.forEach(franchisee => {
                let button = document.createElement("button");
                button.classList.add("btn", "btn-outline-primary", "m-2", "franchisee-btn"); // ✅ Default styling
                button.innerText = franchisee.franchisee;
                button.dataset.value = franchisee.franchisee;
                button.addEventListener("click", toggleFranchiseeSelection);
                franchiseeButtonsDiv.appendChild(button);
            });
        })
        .catch(error => console.error("Error loading franchisees:", error));
}

function toggleFranchiseeSelection(event) {
    let button = event.target;
    button.classList.toggle("btn-primary"); // ✅ Bootstrap class for visual effect
    button.classList.toggle("btn-outline-primary"); // ✅ Toggle between selected & unselected
    button.classList.toggle("btn-selected"); // ✅ Custom class to track selected items

    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);

    loadBranchButtons(selectedFranchisees);
    fetchKPIData();
}

function loadBranchButtons(selectedFranchisees) {
    console.log("🔍 Fetching branches for:", selectedFranchisees); // ✅ Debugging log

    fetch(`dashboard-inventory.php?json=true&franchisees=${selectedFranchisees.join(",")}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text(); // Get raw text before parsing JSON
        })
        .then(text => {
            console.log("🔍 Raw API Response:", text); // ✅ Log raw response

            try {
                let data = JSON.parse(text); // ✅ Attempt to parse JSON
                console.log("✅ Parsed Branch Data:", data); // ✅ Log parsed data

                let branchButtonsDiv = document.getElementById("branchButtons");
                branchButtonsDiv.innerHTML = ""; // Clear previous buttons

                if (data.branches && Array.isArray(data.branches) && data.branches.length > 0) {
                    branchButtonsDiv.style.display = "block";
                    data.branches.forEach(branch => {
                        let button = document.createElement("button");
                        button.classList.add("btn", "btn-outline-secondary", "m-2", "branch-btn");
                        button.innerText = branch.branch;
                        button.dataset.value = branch.branch;
                        button.addEventListener("click", toggleBranchSelection);
                        branchButtonsDiv.appendChild(button);
                    });
                } else {
                    console.warn("⚠ No branches found.");
                    branchButtonsDiv.innerHTML = "<p>No branches available.</p>";
                    branchButtonsDiv.style.display = "none";
                }
            } catch (error) {
                console.error("❌ JSON Parsing Error:", error);
                console.error("🔍 Full Response Before Parsing:", text);
            }
        })
        .catch(error => console.error("❌ Error loading branches:", error));
}



function toggleBranchSelection(event) {
    let button = event.target;
    button.classList.toggle("btn-secondary"); // ✅ Highlight selected
    button.classList.toggle("btn-outline-secondary"); // ✅ Toggle unselected state
    button.classList.toggle("btn-selected"); // ✅ Custom class to track selected items

    fetchKPIData();
}

function fetchKPIData() {
    let selectedFranchisees = Array.from(document.querySelectorAll(".franchisee-btn.btn-selected"))
        .map(btn => btn.dataset.value);
    let selectedBranches = Array.from(document.querySelectorAll(".branch-btn.btn-selected"))
        .map(btn => btn.dataset.value);

        fetch(`dashboard-inventory.php?json=true`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log("✅ Fetched KPI Data:", data); // Debugging Log
    
            if (!data || typeof data !== "object") {
                throw new Error("Invalid JSON structure received.");
            }
    
            if (Array.isArray(data.highTurnoverData) && data.highTurnoverData.length > 0) {
                document.getElementById("highTurnoverChart").style.display = "block";
                createBarChart("highTurnoverChart", data.highTurnoverData, "Top 5 High Stock Turnover");
            } else {
                console.warn("⚠ No data for High Stock Turnover chart.");
                document.getElementById("highTurnoverChart").style.display = "none";
            }
    
            if (Array.isArray(data.lowTurnoverData) && data.lowTurnoverData.length > 0) {
                document.getElementById("lowTurnoverChart").style.display = "block";
                createBarChart("lowTurnoverChart", data.lowTurnoverData, "Top 5 Low Stock Turnover");
            } else {
                console.warn("⚠ No data for Low Stock Turnover chart.");
                document.getElementById("lowTurnoverChart").style.display = "none";
            }
        })
        .catch(error => console.error("❌ Error fetching KPI data:", error));
    
}




// function loadTurnoverCharts() {
//     var highTurnoverElement = document.getElementById("highTurnoverData");
//     var lowTurnoverElement = document.getElementById("lowTurnoverData");

//     if (!highTurnoverElement || !lowTurnoverElement) {
//         console.warn("⚠ No turnover data elements found in the HTML.");
//         return;
//     }

//     try {
//         var highTurnoverData = JSON.parse(highTurnoverElement.textContent.trim());
//         var lowTurnoverData = JSON.parse(lowTurnoverElement.textContent.trim());

//         if (!highTurnoverData.length || !lowTurnoverData.length) {
//             console.warn("⚠ No data available for turnover charts.");
//             return;
//         }

//         createBarChart("highTurnoverChart", highTurnoverData, "Top 5 High Stock Turnover");
//         createBarChart("lowTurnoverChart", lowTurnoverData, "Top 5 Low Stock Turnover");
//     } catch (error) {
//         console.error("Error parsing turnover data:", error);
//     }
// }


function createBarChart(chartId, data, title) {
    var canvas = document.getElementById(chartId);
    if (!canvas) {
        console.error(`Error: Chart canvas '${chartId}' not found.`);
        return;
    }
    var ctx = canvas.getContext("2d");

    // Destroy existing chart instance if it exists
    if (window[chartId] instanceof Chart) {
        window[chartId].destroy();
    }

    // Create new chart
    window[chartId] = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.map(item => item.item_name),
            datasets: [{
                label: "Stock Turnover Rate",
                data: data.map(item => parseFloat(item.turnover_rate).toFixed(2)),
                backgroundColor: "#42A5F5",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function displayInventoryTable(inventoryData) {
    let tableHTML = `
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Days of Inventory</th>
                </tr>
            </thead>
            <tbody>
    `;

    inventoryData.forEach(item => {
        tableHTML += `
            <tr>
                <td>${item.item_name}</td>
                <td>${item.days_of_inventory ? parseFloat(item.days_of_inventory).toFixed(1) : "N/A"}</td>
            </tr>
        `;
    });

    tableHTML += `</tbody></table>`;
    document.getElementById("inventoryTableContainer").innerHTML = tableHTML;
}
