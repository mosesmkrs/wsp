document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("registrationForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission
        let isValid = true;

        function validateInput(input, errorMessage) {
            let errorSpan = input.nextElementSibling;
            if (input.value.trim() === "") {
                if (!errorSpan || !errorSpan.classList.contains("error-message")) {
                    errorSpan = document.createElement("span");
                    errorSpan.className = "error-message";
                    errorSpan.innerText = errorMessage;
                    errorSpan.style.color = "red";
                    input.parentNode.appendChild(errorSpan);
                    input.classList.add("error-input");
                    return false;
                }
            } else {
                if (errorSpan) {
                    errorSpan.innerText = "";
                }
                return true;
            }
        }

        document.querySelectorAll(".form-group input, .form-group select").forEach(input => {
            input.addEventListener("blur", function () {
                validateInput(input, "This field is required.");
            });
        });

        document.querySelectorAll(".form-group input, .form-group select").forEach(input => {
            if (!validateInput(input, "This field is required")) {
                isValid = false;
            }
        });

        if (isValid) {
            this.submit();
        }
    });

    document.getElementById("nextofkinForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission
        let isValid = true;

        function validateInput(input, errorMessage) {
            let errorSpan = input.nextElementSibling;
            if (input.value.trim() === "") {
                if (!errorSpan || !errorSpan.classList.contains("error-message")) {
                    errorSpan = document.createElement("span");
                    errorSpan.className = "error-message";
                    errorSpan.innerText = errorMessage;
                    errorSpan.style.color = "red";
                    input.parentNode.appendChild(errorSpan);
                    input.classList.add("error-input");
                    return false;
                }
            } else {
                if (errorSpan) {
                    errorSpan.innerText = "";
                }
                return true;
            }
        }

        document.querySelectorAll(".kin-group input, .kin-group select").forEach(input => {
            input.addEventListener("blur", function () {
                validateInput(input, "This field is required.");
            });
        });

        document.querySelectorAll(".kin-group input, .kin-group select").forEach(input => {
            if (!validateInput(input, "This field is required")) {
                isValid = false;
            }
        });

        if (isValid) {
            this.submit();
        }
    });
});