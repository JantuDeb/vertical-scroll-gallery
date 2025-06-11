document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.vsg-contact-submission-form');

    forms.forEach(form => {
        let messageDiv = form.querySelector('.vsg-form-message');
        if (!messageDiv) {
            messageDiv = document.createElement('div');
            messageDiv.className = 'vsg-form-message';
            // Insert message div before the first input, or after the button, or append to form
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.parentNode.insertBefore(messageDiv, submitButton.nextSibling);
            } else {
                form.appendChild(messageDiv);
            }
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            messageDiv.innerHTML = ''; // Clear previous messages
            messageDiv.classList.remove('success', 'error');


            const formData = new FormData(form);
            formData.append('action', 'vsg_submit_form');
            formData.append('_ajax_nonce', vsgFormData.nonce); // vsgFormData is localized

            // Disable button during submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
            }

            fetch(vsgFormData.ajaxUrl, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    messageDiv.innerHTML = `<p>${response.data.message}</p>`;
                    messageDiv.classList.add('success');
                    form.reset(); // Clear form fields on success
                } else {
                    let errorHtml = '<p>Please correct the following errors:</p><ul>';
                    if (response.data && response.data.errors) {
                        for (const field in response.data.errors) {
                            errorHtml += `<li>${response.data.errors[field]}</li>`;
                        }
                    } else if (response.data && response.data.message) { // General error message
                        errorHtml += `<li>${response.data.message}</li>`;
                    } else {
                        errorHtml += '<li>An unknown error occurred.</li>';
                    }
                    errorHtml += '</ul>';
                    messageDiv.innerHTML = errorHtml;
                    messageDiv.classList.add('error');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                messageDiv.innerHTML = '<p>An error occurred while submitting the form. Please try again.</p>';
                messageDiv.classList.add('error');
            })
            .finally(() => {
                 // Re-enable button
                if (submitButton) {
                    submitButton.disabled = false;
                    // We need to get the original button text from block attribute,
                    // but this script doesn't have direct access to it.
                    // For now, just reset to a generic "Submit".
                    // A better way would be to store original text in a data attribute on the button.
                    const originalButtonText = form.dataset.submitText || 'Submit';
                    submitButton.textContent = originalButtonText;
                }
            });
        });
    });
});
