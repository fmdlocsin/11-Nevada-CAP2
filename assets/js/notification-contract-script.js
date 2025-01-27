$(document).ready(function () {
    fetchContractNotifications();
});

function fetchContractNotifications() {
    $.ajax({
        url: "../../phpscripts/get-contract-expiry.php",
        method: "GET",
        dataType: "json",
        success: function (response) {
            var notificationList = $("#notification-list");
            notificationList.empty();

            if (response.status === "success") {
                response.notifications.forEach(function (notification) {
                    var imgFile = getFranchiseImage(notification.franchisee);

                    // Add the button for every notification
                    var buttonHtml = `
                        <button class="btn small-action-btn mt-2 action-btn" data-id="${notification.id}">
                            Renew
                        </button>
                    `;

                    var listItem = `
                        <li class="text-center">
                            <h3><img src="../../assets/images/${imgFile}" alt="${notification.franchisee} Logo" class="franchise-logo mb-2"></h3>
                            <h4 class="mb-2">${notification.location}</h4>
                            <span class="notification-details">${notification.status}</span>
                            ${buttonHtml}
                        </li>
                    `;
                    notificationList.append(listItem);
                });

                // Add click event for the Renew buttons
                $(".action-btn").on("click", function () {
                    var contractId = $(this).data("id");
                    handleAction(contractId);
                });
            } else {
                notificationList.append(
                    `<li class="text-danger text-center">${response.message}</li>`
                );
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching notifications:", error);
        },
    });
}

function handleAction(contractId) {
    // Redirect to the editable franchise details page with the contract ID
    window.location.href = `edit-franchisedetails.php?id=${contractId}`;
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
            return "11Nevada_LOGO2.png";
    }
}