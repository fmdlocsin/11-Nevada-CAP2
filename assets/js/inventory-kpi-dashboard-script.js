document.addEventListener("DOMContentLoaded", function () {
    loadFranchiseeButtons(); // Load franchisee buttons on page load
    fetchKPIData(); // Load KPI Data including turnover charts
    loadTurnoverCharts();
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
    if (selectedFranchisees.length === 0) {
        document.getElementById("branchButtons").style.display = "none";
        return;
    }

    fetch(`dashboard-inventory.php?json=true&franchisees=${selectedFranchisees.join(",")}`) // ✅ Ensure JSON response
        .then(response => response.json())
        .then(data => {
            let branchButtonsDiv = document.getElementById("branchButtons");
            branchButtonsDiv.innerHTML = ""; // Clear previous buttons
            branchButtonsDiv.style.display = "block";

            data.branches.forEach(branch => {
                let button = document.createElement("button");
                button.classList.add("btn", "btn-outline-secondary", "m-2", "branch-btn"); // ✅ Default styling
                button.innerText = branch.branch;
                button.dataset.value = branch.branch;
                button.addEventListener("click", toggleBranchSelection);
                branchButtonsDiv.appendChild(button);
            });
        })
        .catch(error => console.error("Error loading branches:", error));
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
        .then(response => response.json())
        .then(data => {
            console.log("Fetched Data for Graphs:", data); // ✅ Debugging Log
    
            // Ensure Data Exists Before Rendering Charts
            if (!data.highTurnoverData || data.highTurnoverData.length === 0) {
                console.warn("⚠ No data for High Stock Turnover chart.");
                document.getElementById("highTurnoverChart").style.display = "none";
            } else {
                document.getElementById("highTurnoverChart").style.display = "block";
                renderTurnoverChart("highTurnoverChart", "Top 5 High Stock Turnover", data.highTurnoverData);
            }
    
            if (!data.lowTurnoverData || data.lowTurnoverData.length === 0) {
                console.warn("⚠ No data for Low Stock Turnover chart.");
                document.getElementById("lowTurnoverChart").style.display = "none";
            } else {
                document.getElementById("lowTurnoverChart").style.display = "block";
                renderTurnoverChart("lowTurnoverChart", "Top 5 Low Stock Turnover", data.lowTurnoverData);
            }
        })
        .catch(error => console.error("Error fetching KPI data:", error));
    
}



function loadTurnoverCharts() {
    var highTurnoverElement = document.getElementById("highTurnoverData");
    var lowTurnoverElement = document.getElementById("lowTurnoverData");

    if (!highTurnoverElement || !lowTurnoverElement) {
        console.error("Error: Turnover data elements not found.");
        return;
    }

    var highTurnoverData = JSON.parse(highTurnoverElement.textContent.trim());
    var lowTurnoverData = JSON.parse(lowTurnoverElement.textContent.trim());

    if (!highTurnoverData.length || !lowTurnoverData.length) {
        console.warn("No data available for turnover charts.");
        return;
    }

    createBarChart("highTurnoverChart", highTurnoverData, "Top 5 High Stock Turnover");
    createBarChart("lowTurnoverChart", lowTurnoverData, "Top 5 Low Stock Turnover");
}

function createBarChart(chartId, data, title) {
    var ctx = document.getElementById(chartId).getContext("2d");

    if (window[chartId] instanceof Chart) {
        window[chartId].destroy();
    }

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

