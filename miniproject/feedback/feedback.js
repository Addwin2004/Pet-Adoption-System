document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedbackForm');
    const submitBtn = form.querySelector('.submit-btn');
    const feedbackTextarea = document.getElementById('feedback');
    const charCount = document.getElementById('charCount');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable the submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        // Simulate form submission (replace this with actual form submission)
        setTimeout(function() {
            form.submit();
        }, 1500);
    });

    // Add hover effect to rating stars
    const ratingLabels = document.querySelectorAll('.rating label');
    ratingLabels.forEach(label => {
        label.addEventListener('mouseover', function() {
            this.classList.add('hover');
            let prevSibling = this.previousElementSibling;
            while(prevSibling) {
                prevSibling.classList.add('hover');
                prevSibling = prevSibling.previousElementSibling;
            }
        });

        label.addEventListener('mouseout', function() {
            ratingLabels.forEach(l => l.classList.remove('hover'));
        });
    });

    // Character count functionality
    feedbackTextarea.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength} / 500 characters`;
        
        if (currentLength > 500) {
            charCount.classList.add('error');
        } else {
            charCount.classList.remove('error');
        }
    });
});