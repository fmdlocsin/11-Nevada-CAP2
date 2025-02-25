$(document).ready(function () {
    displayBranches();
});

function getEatTypeAndBranchFromUrl() {
    var url = window.location.href;
    var parts = url.split("?")[1].split("/");

    var eatType = "";
    var franchisee = "";

    parts.forEach((part) => {
        if (part.startsWith("tp=")) {
            eatType = part.split("=")[1];
        } else if (part.startsWith("franchise=")) {
            franchisee = part.split("=")[1];
        }
    });

    return { eatType: eatType, franchisee: franchisee };
}

function displayBranches() {
    var { eatType, franchisee } = getEatTypeAndBranchFromUrl();
    var formattedFranchisee = franchisee
        .replace(/([a-z])([A-Z])/g, "$1-$2")
        .toLowerCase();
    var form = $("#formGroup1");

    $.ajax({
        method: "POST",
        data: { formattedFranchisee: formattedFranchisee, eatType: eatType },
        url: "../../phpscripts/display-branches.php",
        dataType: "json",    
        success: function (response) {
            if (response.status === "success") {
                form.empty();

                response.details.forEach(function (branchInfo) {
                    // Exclude Kiosk when Dine In is selected
                    if (eatType === "DineIn" && branchInfo.classification === "Kiosk") {
                        return; // Skip kiosks
                    }

                    var formattedName = branchInfo.franchisee
                        .replace(/-/g, " ")
                        .replace(/\b\w/g, function (match) {
                            return match.toUpperCase();
                        });

                    var img = getFranchiseImage(branchInfo.franchisee);

                    var cardHTML = `
                        <a href="salesInformation.php?tp=${eatType}&franchise=${franchisee}&location=${encodeURIComponent(branchInfo.location)}" class="text-decoration-none">
                            <div class="card branch-button">
                                <img src="../../assets/images/${img}" alt="img">
                                <span>${formattedName}</span>
                                <span>${branchInfo.location}</span>
                            </div>
                        </a>
                    `;
                    form.append(cardHTML);
                });
            } else {
                console.log(response.message);
                form.append(`<p class="text-danger">${response.message}.</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        },
    });
}

function getFranchiseImage(franchise) {
    switch (franchise) {
        case "potato-corner":
            return "PotCor.png";
        case "auntie-anne":
            return "AuntieAnn.png";
        case "macao-imperial":
            return "MacaoImp.png";
        default:
            return "default-image.png";
    }
}
