document.addEventListener('DOMContentLoaded', function () {
    // 1. Real-Time Search Functionality
    const searchInput = document.getElementById('search-input');
    const fileCards = document.querySelectorAll('.file-card');

    if (searchInput && fileCards.length > 0) {
        searchInput.addEventListener('input', function () {
            const query = searchInput.value.trim().toLowerCase();

            fileCards.forEach(card => {
                const fileName = card.getAttribute('data-file-name') || '';
                const description = card.getAttribute('data-description') || '';

                if (fileName.includes(query) || description.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    } else {
        console.warn('Search input or file cards not found in the DOM.');
    }

    // 2. File Upload Progress Bar
    const uploadForm = document.querySelector('#upload-form');
    const progressBar = document.querySelector('#progress-bar');
    const progressContainer = document.querySelector('#progress-container');

    if (uploadForm && progressBar) {
        uploadForm.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(uploadForm);
            const xhr = new XMLHttpRequest();

            // Show progress bar
            progressContainer.style.display = 'block';

            xhr.open('POST', uploadForm.action, true);

            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = Math.round(percentComplete) + '%';
                }
            };

            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert('تم رفع الملف بنجاح!');
                    location.reload(); // Refresh the page after successful upload
                } else {
                    alert('حدث خطأ أثناء الرفع.');
                }
            };

            xhr.onerror = function () {
                alert('حدث خطأ أثناء الرفع.');
            };

            xhr.send(formData);
        });
    }

    // 3. Confirm Delete Action
    const deleteButtons = document.querySelectorAll('.delete-file-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            if (!confirm('هل أنت متأكد من حذف هذا الملف؟')) {
                event.preventDefault(); // Prevent form submission
            }
        });
    });

    // 4. Copy Link Functionality
    const copyLinkButtons = document.querySelectorAll('.copy-link-btn');

    if (copyLinkButtons.length > 0) {
        copyLinkButtons.forEach(button => {
            button.addEventListener('click', function () {
                const fileId = this.getAttribute('data-file-id'); // Get the file ID
                const baseUrl = window.location.origin; // Base URL of your site
                const fileUrl = `${baseUrl}/serve_file.php?id=${fileId}`; // Construct the file URL

                console.log('Generated URL:', fileUrl); // Debugging

                // Try using navigator.clipboard first
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(fileUrl).then(() => {
                        alert('تم نسخ الرابط بنجاح!');
                    }).catch(err => {
                        console.error('حدث خطأ أثناء نسخ الرابط:', err);
                        // Fallback for older browsers or insecure environments
                        copyToClipboardFallback(fileUrl);
                    });
                } else {
                    // Fallback for browsers that don't support navigator.clipboard
                    copyToClipboardFallback(fileUrl);
                }
            });
        });
    } else {
        console.warn('No copy link buttons found in the DOM.');
    }

    // Fallback for older browsers or insecure environments
    function copyToClipboardFallback(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy'); // Deprecated but works in older browsers
            alert('تم نسخ الرابط بنجاح!');
        } catch (err) {
            console.error('حدث خطأ أثناء نسخ الرابط:', err);
            alert('لم يتمكن من نسخ الرابط. يرجى المحاولة مرة أخرى.');
        }
        document.body.removeChild(textarea);
    }

    // 5. Toggle Sidebar (Optional for Responsive Design)
    const toggleSidebar = document.querySelector('#toggle-sidebar');
    const sidebar = document.querySelector('#sidebar');

    if (toggleSidebar && sidebar) {
        toggleSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }
});