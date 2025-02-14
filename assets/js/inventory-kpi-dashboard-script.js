document.addEventListener("DOMContentLoaded", function () {
    loadFranchiseeButtons(); // Load franchisee buttons on page load
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

    fetch(`dashboard-inventory.php?json=true&franchisees=${selectedFranchisees.join(",")}&branches=${selectedBranches.join(",")}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("stockLevel").innerText =
                data.stockData.reduce((acc, item) => acc + (item.stock_available || 0), 0);
        })
        .catch(error => console.error("Error fetching KPI data:", error));
}
